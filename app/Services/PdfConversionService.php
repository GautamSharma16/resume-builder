<?php

namespace App\Services;

use Symfony\Component\Process\Process;
use Smalot\PdfParser\Parser as PdfParser;

/**
 * Universal PDF ↔ HTML conversion.
 * Works on: Windows (dev), Linux VPS, shared hosting — any platform.
 *
 * PDF → HTML strategy (best → fallback):
 *   1. pdftohtml binary    — best layout (VPS with poppler-utils)
 *   2. smalot/pdfparser    — pure PHP, works everywhere
 *   3. Raw text extraction — absolute last resort
 *
 * HTML → PDF strategy (best → fallback):
 *   1. wkhtmltopdf binary  — best quality (VPS)
 *   2. Puppeteer/Node.js   — great quality (VPS with Node)
 *   3. DomPDF              — pure PHP, works everywhere
 *
 * ── One-time setup (works on ALL hosting) ────────────────────────────────
 *   composer require smalot/pdfparser dompdf/dompdf
 *
 * ── Optional extras for VPS (better quality) ─────────────────────────────
 *   Linux : apt install poppler-utils wkhtmltopdf
 *   Windows: see https://github.com/oschwartz10612/poppler-windows/releases
 *            and https://wkhtmltopdf.org/downloads.html
 * ─────────────────────────────────────────────────────────────────────────
 */
class PdfConversionService
{
    // Override with absolute paths if binaries exist but aren't in PATH
    private string $pdftohtmlBin   = 'pdftohtml';
    private string $wkhtmltopdfBin = 'wkhtmltopdf';

    // ──────────────────────────────────────────────────────────────────────
    //  PDF → HTML
    // ──────────────────────────────────────────────────────────────────────

    public function pdfToHtml(string $absolutePdfPath, bool $enhanceWithAI = true): string
    {
        // 1. Binary — best layout fidelity (requires poppler)
        if ($this->commandWorks($this->pdftohtmlBin)) {
            try {
                $html = $this->viaBinaryPdfToHtml($absolutePdfPath);
                if ($enhanceWithAI) {
                    return $this->improveHtmlWithAI($html);
                }
                return $html;
            } catch (\Throwable) { /* fall through */ }
        }

        // 2. Pure PHP — works on all hosting
        if (class_exists(\Smalot\PdfParser\Parser::class)) {
            try {
                $html = $this->viaPdfParser($absolutePdfPath);
                if ($enhanceWithAI) {
                    return $this->improveHtmlWithAI($html);
                }
                return $html;
            } catch (\Throwable) { /* fall through */ }
        }

        // 3. Raw byte extraction — last resort
        $html = $this->viaRawExtraction($absolutePdfPath);
        if ($enhanceWithAI) {
            return $this->improveHtmlWithAI($html);
        }
        return $html;
    }

    private function improveHtmlWithAI(string $html): string
    {
        $key = config('services.gemini.key');
        $model = config('services.gemini.model', 'gemini-1.5-flash');

        if (!$key) return $html;

        // Strip tags but keep some structure for AI to understand
        $textContent = strip_tags($html, '<h1><h2><h3><p><li>');
        
        $prompt = <<<PROMPT
You are a master UI/UX designer. 
Task: Convert the following resume text into a PIXEL-PERFECT, PREMIUM HTML/CSS template.

VISUAL STYLE (MATCH THIS EXACTLY):
- Theme: Professional Dark Theme (Background: #1a1a1a or #0f172a).
- Header: Large, centered Name in a clean sans-serif (Inter/Roboto), colored light blue or white.
- Accent Color: Blue (#3b82f6) for headings and links.
- Layout: Single column with clean section dividers (lines).
- Typography: Use 'Inter', sans-serif. White/Light gray text on dark background.
- Spacing: Ample whitespace, professional padding.

STRICT TECHNICAL RULES:
1. Use ONLY Vanilla CSS inside a <style> tag.
2. The HTML MUST use these Blade-like placeholders for data mapping:
   - {{ \$resume['name'] }}, {{ \$resume['email'] }}, {{ \$resume['mobile'] }}, {{ \$resume['location'] }}, {{ \$resume['summary'] }}, {{ \$resume['linkedin'] }}, {{ \$resume['github'] }}.
   - For Experience/Education: @foreach(\$resume['experience'] as \$item) ... @endforeach.
   - For Skills: @foreach(\$resume['skills'] as \$skill) ... @endforeach.
3. Ensure all links are clickable.
4. Return ONLY the complete HTML/CSS code. No markdown fences. No explanation.

Text to convert:
{$textContent}
PROMPT;

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(120)
                ->withoutVerifying()
                ->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . urlencode($key),
                    [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt]
                                ]
                            ]
                        ],
                        'generationConfig' => [
                            'temperature' => 0.1, // Lower temperature for more consistent layout
                            'maxOutputTokens' => 8000,
                        ],
                    ]
                );

            if ($response->successful()) {
                $aiHtml = \Illuminate\Support\Arr::get($response->json(), 'candidates.0.content.parts.0.text', '');
                $aiHtml = preg_replace('/```(?:html)?/i', '', $aiHtml);
                $aiHtml = str_replace('```', '', $aiHtml);
                
                // If it looks like a valid HTML doc, return it
                if (str_contains($aiHtml, '<style>') && str_contains($aiHtml, '{{')) {
                    return trim($aiHtml);
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("PDF AI Conversion failed: " . $e->getMessage());
        }

        return $html;
    }



    // ──────────────────────────────────────────────────────────────────────
    //  HTML → PDF
    // ──────────────────────────────────────────────────────────────────────

    public function htmlToPdf(string $html): string
    {
        // 1. wkhtmltopdf binary — best output
        if ($this->commandWorks($this->wkhtmltopdfBin)) {
            try {
                return $this->viaWkhtmltopdf($html);
            } catch (\Throwable) { /* fall through */ }
        }

        // 2. Puppeteer / Node.js
        if ($this->commandWorks('node') && $this->nodeModuleAvailable('puppeteer')) {
            try {
                return $this->viaPuppeteer($html);
            } catch (\Throwable) { /* fall through */ }
        }

        // 3. DomPDF — pure PHP, works everywhere
        if (class_exists(\Dompdf\Dompdf::class)) {
            return $this->viaDompdf($html);
        }

        throw new \RuntimeException(
            "No PDF generator available.\n" .
            "Run: composer require dompdf/dompdf\n" .
            "This works on shared hosting and all other environments."
        );
    }

    // ──────────────────────────────────────────────────────────────────────
    //  PDF → HTML Implementations
    // ──────────────────────────────────────────────────────────────────────

    private function viaBinaryPdfToHtml(string $pdfPath): string
    {
        $sep     = DIRECTORY_SEPARATOR;
        $outDir  = sys_get_temp_dir() . $sep . uniqid('pdftohtml_', true);
        $outBase = $outDir . $sep . 'out';
        mkdir($outDir, 0755, true);

        $process = new Process([
            $this->pdftohtmlBin, '-s', '-noframes', '-enc', 'UTF-8',
            $pdfPath, $outBase,
        ]);
        $process->setTimeout(60);
        $process->run();

        $htmlFile = $outBase . 's.html'; // pdftohtml appends 's' for single-file mode

        if (! file_exists($htmlFile)) {
            $this->cleanup($outDir);
            throw new \RuntimeException('pdftohtml produced no output.');
        }

        $html = file_get_contents($htmlFile);
        $html = $this->inlineLocalImages($html, $outDir);
        $this->cleanup($outDir);

        return $this->stripEventHandlers($html);
    }

    private function viaPdfParser(string $pdfPath): string
    {
        $parser = new PdfParser();
        $pdf    = $parser->parseFile($pdfPath);
        $pages  = $pdf->getPages();

        $details = $pdf->getDetails();
        $title   = $details['Title'] ?? basename($pdfPath, '.pdf');
        $escaped = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

        $bodyHtml = '';

        foreach ($pages as $i => $page) {
            $text       = $page->getText();
            $paragraphs = preg_split('/\n{2,}/', $text);
            $pageHtml   = '';

            foreach ($paragraphs as $para) {
                $para = trim($para);
                if ($para === '') continue;

                $lines     = explode("\n", $para);
                $firstLine = trim($lines[0]);

                if (count($lines) === 1 && strlen($firstLine) < 70 && strlen($firstLine) > 2 && strtoupper($firstLine) === $firstLine) {
                    $pageHtml .= '<h2 style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin-top: 1.5rem; margin-bottom: 0.5rem; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.25rem;">' . htmlspecialchars($firstLine, ENT_QUOTES, 'UTF-8') . '</h2>' . "\n";
                } else {
                    $content   = htmlspecialchars($para, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $pageHtml .= '<p style="margin-bottom: 0.75rem; color: #334155; line-height: 1.6;">' . nl2br($content) . '</p>' . "\n";
                }
            }

            $num       = $i + 1;
            $bodyHtml .= "<div class=\"pdf-page\" id=\"page-{$num}\" style=\"background: #ffffff; padding: 2.5rem; margin-bottom: 2rem; border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);\">{$pageHtml}</div>\n";
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$escaped}</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    font-size: 14px;
    line-height: 1.6;
    color: #1e293b;
    background: #f8fafc;
    padding: 2rem 1rem;
  }
  .pdf-container {
    max-width: 800px;
    margin: 0 auto;
  }
  h1 { font-size: 1.5rem; font-weight: 800; color: #0f172a; margin-bottom: 1.5rem; text-align: center; }
</style>
</head>
<body>
<div class="pdf-container">
  <h1>{$escaped}</h1>
  {$bodyHtml}
</div>
</body>
</html>
HTML;
    }

    private function viaRawExtraction(string $pdfPath): string
    {
        $raw     = file_get_contents($pdfPath);
        $cleaned = preg_replace('/[^\x20-\x7E\n\r\t]/', ' ', $raw);
        $cleaned = preg_replace('/\s{4,}/', "\n\n", $cleaned);
        $escaped = htmlspecialchars(trim($cleaned), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: monospace; font-size: 12px; line-height: 1.6;
         padding: 40px; max-width: 860px; margin: auto; }
  pre  { white-space: pre-wrap; word-wrap: break-word; }
</style>
</head>
<body><pre>{$escaped}</pre></body>
</html>
HTML;
    }

    // ──────────────────────────────────────────────────────────────────────
    //  HTML → PDF Implementations
    // ──────────────────────────────────────────────────────────────────────

    private function viaWkhtmltopdf(string $html): string
    {
        $inFile  = tempnam(sys_get_temp_dir(), 'wk_in_')  . '.html';
        $outFile = tempnam(sys_get_temp_dir(), 'wk_out_') . '.pdf';
        file_put_contents($inFile, $html);

        $process = new Process([
            $this->wkhtmltopdfBin,
            '--enable-local-file-access',
            '--encoding', 'UTF-8',
            '--margin-top', '10mm', '--margin-bottom', '10mm',
            '--margin-left', '10mm', '--margin-right', '10mm',
            '--page-size', 'A4', '--quiet',
            $inFile, $outFile,
        ]);
        $process->setTimeout(60);
        $process->run();

        @unlink($inFile);

        if (! file_exists($outFile)) {
            throw new \RuntimeException('wkhtmltopdf: ' . $process->getErrorOutput());
        }

        $pdf = file_get_contents($outFile);
        @unlink($outFile);
        return $pdf;
    }

    private function viaPuppeteer(string $html): string
    {
        $inFile  = tempnam(sys_get_temp_dir(), 'pup_in_')  . '.html';
        $outFile = tempnam(sys_get_temp_dir(), 'pup_out_') . '.pdf';
        $jsFile  = tempnam(sys_get_temp_dir(), 'pup_js_')  . '.js';
        file_put_contents($inFile, $html);

        $script = <<<'JS'
const puppeteer = require('puppeteer');
(async () => {
    const b = await puppeteer.launch({ args:['--no-sandbox','--disable-setuid-sandbox'] });
    const p = await b.newPage();
    await p.goto('file:///'+process.argv[2].replace(/\\/g,'/'), { waitUntil:'networkidle0' });
    await p.pdf({ path:process.argv[3], format:'A4', printBackground:true,
        margin:{ top:'10mm',bottom:'10mm',left:'10mm',right:'10mm' } });
    await b.close();
})();
JS;
        file_put_contents($jsFile, $script);

        $process = new Process(['node', $jsFile, $inFile, $outFile]);
        $process->setTimeout(60);
        $process->run();

        @unlink($jsFile);
        @unlink($inFile);

        if (! file_exists($outFile)) {
            throw new \RuntimeException('Puppeteer: ' . $process->getErrorOutput());
        }

        $pdf = file_get_contents($outFile);
        @unlink($outFile);
        return $pdf;
    }

    private function viaDompdf(string $html): string
    {
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    // ──────────────────────────────────────────────────────────────────────
    //  Helpers
    // ──────────────────────────────────────────────────────────────────────

    private function inlineLocalImages(string $html, string $basePath): string
    {
        return preg_replace_callback(
            '/<img([^>]*)src=["\']([^"\']+)["\']([^>]*)>/i',
            function (array $m) use ($basePath): string {
                $src = $m[2];
                if (str_starts_with($src, 'data:') || str_starts_with($src, 'http')) {
                    return $m[0];
                }
                $file = $basePath . DIRECTORY_SEPARATOR . basename($src);
                if (! file_exists($file)) {
                    return $m[0];
                }
                $mime = mime_content_type($file) ?: 'image/png';
                $b64  = base64_encode(file_get_contents($file));
                return "<img{$m[1]}src=\"data:{$mime};base64,{$b64}\"{$m[3]}>";
            },
            $html
        );
    }

    private function stripEventHandlers(string $html): string
    {
        return preg_replace('/\s+on\w+=["\'][^"\']*["\']/i', '', $html);
    }

    /** Works on Windows (where) and Linux/Mac (which) */
    private function commandWorks(string $bin): bool
    {
        if (str_contains($bin, '/') || str_contains($bin, '\\')) {
            return file_exists($bin);
        }
        $finder  = PHP_OS_FAMILY === 'Windows' ? 'where' : 'which';
        $process = new Process([$finder, $bin]);
        $process->run();
        return $process->isSuccessful();
    }

    private function nodeModuleAvailable(string $module): bool
    {
        $p = new Process(['node', '-e', "require('{$module}')"]);
        $p->run();
        return $p->isSuccessful();
    }

    private function cleanup(string $dir): void
    {
        if (is_dir($dir)) {
            foreach (glob($dir . DIRECTORY_SEPARATOR . '*') ?: [] as $f) {
                is_file($f) && @unlink($f);
            }
            @rmdir($dir);
        }
    }
}