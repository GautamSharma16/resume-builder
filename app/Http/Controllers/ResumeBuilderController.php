<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use App\Models\ResumeAnalysis;
use App\Models\Template;
use App\Services\GeminiService;
use App\Services\PlanActivationService;
use App\Services\PendingDownloadService;
use App\Services\PdfConversionService;
use App\Services\TemplateRenderService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ResumeBuilderController extends Controller
{
    public function __construct(
        private readonly GeminiService $gemini,
    ) {}

    public function index(TemplateRenderService $renderer)
    {
        $resumes = Resume::where('user_id', auth()->id())
            ->with('template')
            ->latest()
            ->get();

        $previews = $resumes->mapWithKeys(function (Resume $resume) use ($renderer) {
            if (! $resume->template) {
                return [$resume->id => null];
            }

            $html = (string) $renderer->renderResume($resume->template, $resume->data ?? []);

            return [
                $resume->id => view('templates.rendered-document', ['html' => $html])->render(),
            ];
        });

        return view('resume.index', [
            'resumes' => $resumes,
            'previews' => $previews,
        ]);
    }

    public function create(Request $request)
    {
        $templates = Template::where('type', 'resume')->where('is_active', true)->get();
        $selectedTemplateId = $request->query('template_id');
        $selectedTemplate = null;
        $initialResume = null;

        if ($selectedTemplateId) {
            $selectedTemplate = Template::where('type', 'resume')->where('is_active', true)->findOrFail($selectedTemplateId);
            $selectedTemplateId = $selectedTemplate->id;
        } else {
            $savedTemplateId = $request->user()
                ? Resume::where('user_id', $request->user()->id)
                    ->whereNotNull('template_id')
                    ->latest()
                    ->value('template_id')
                : null;

            $selectedTemplate = $savedTemplateId
                ? $templates->firstWhere('id', $savedTemplateId)
                : $templates->first();
            $selectedTemplateId = $selectedTemplate?->id;
        }

        $analysisId = $request->query('analysis_id');
        if ($analysisId) {
            $analysis = ResumeAnalysis::findOrFail($analysisId);
            $authorized = ($request->user() && $analysis->user_id === $request->user()->id) ||
                (!$request->user() && $analysis->session_id === $request->session()->getId());

            if (! $authorized) {
                abort(403, 'You do not have access to this resume analysis.');
            }

            $initialResume = $analysis->improved_resume_json ?? [];
        }

        return view('resume.create', [
            'templates' => $templates,
            'selectedTemplate' => $selectedTemplate,
            'selectedTemplateId' => $selectedTemplateId,
            'initialResume' => $initialResume,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'template_id' => ['nullable', 'exists:templates,id'],
            'source' => ['required', 'in:manual,upload'],
            'resume' => ['required', 'array'],
            'download_format' => ['nullable', 'in:pdf,doc,docx,ppt'],
        ]);

        $subscription = $request->user()?->activeSubscription;
        if ($subscription && ! $subscription->hasResumeSlotsRemaining()) {
            return response()->json([
                'success' => false,
                'message' => 'Your plan resume limit has been reached. Please renew or upgrade your plan.',
                'pricing_url' => route('plans'),
            ], 402);
        }

        $normalizedResume = $this->normalizeResume($validated['resume']);
        $resume = Resume::create([
            'user_id' => $request->user()?->id,
            'session_id' => $request->session()->getId(),
            'template_id' => $validated['template_id'] ?? null,
            'title' => trim(($normalizedResume['name'] ?? '').' '.($normalizedResume['last_name'] ?? '')) ?: 'Untitled Resume',
            'source' => $validated['source'],
            'data' => $normalizedResume,
        ]);

        if ($subscription) {
            $subscription->incrementResumeCount();
        }

        $redirect = null;
        if (! $request->user()) {
            $downloadUrl = app(PendingDownloadService::class)->rememberResume(
                $request,
                $resume,
                $validated['download_format'] ?? 'pdf'
            );
            $redirect = route('login', ['redirect' => $downloadUrl]);
        }

        return response()->json([
            'resume' => ['id' => $resume->id],
            'redirect' => $redirect,
        ]);
    }

    public function generateAiText(Request $request)
    {
        $validated = $request->validate([
            'context' => ['required', 'in:summary,experience'],
            'resume' => ['nullable', 'array'],
            'text' => ['nullable', 'string', 'max:5000'],
            'source' => ['nullable', 'in:manual,upload'],
            'job_role' => ['nullable', 'string', 'max:180'],
            'variation_seed' => ['nullable', 'string', 'max:120'],
            'previous_outputs' => ['nullable', 'array', 'max:3'],
            'previous_outputs.*' => ['nullable', 'string', 'max:5000'],
        ]);

        $resume = $this->normalizeResume($validated['resume'] ?? []);
        $context = $validated['context'];
        $source = $this->toText($validated['source'] ?? 'manual');
        $existingText = $this->toText($validated['text'] ?? '');
        $existingText = $context === 'experience'
            ? $this->cleanAiSeedText($existingText, 700)
            : $this->cleanAiSeedText($existingText, 900);
        $clickedJobRole = $this->toText($validated['job_role'] ?? '');
        $jobTitle = $clickedJobRole
            ?: $this->toText($resume['job_title'] ?? $resume['designation'] ?? '')
            ?: $this->toText(Arr::get($resume, 'experience.0.role', ''));
        $skills = implode(', ', $resume['skills'] ?? []);
        $profileSummary = $this->toText($resume['summary'] ?? '');
        $experience = collect($resume['experience'] ?? [])
            ->map(fn ($item) => trim(collect([
                $item['role'] ?? '',
                $item['company'] ? 'at '.$item['company'] : '',
                $item['period'] ?? '',
                $this->toText($item['points'] ?? ''),
            ])->filter()->join(' ')))
            ->filter()
            ->join('; ');

        $education = collect($resume['education'] ?? [])
            ->map(fn ($item) => is_array($item)
                ? trim(collect([$item['degree'] ?? '', $item['stream'] ?? '', $item['institution'] ?? '', $item['year'] ?? ''])->filter()->join(', '))
                : $this->toText($item))
            ->filter()
            ->join('; ');

        if ($context === 'summary' && $source !== 'upload' && $existingText === '') {
            return response()->json([
                'message' => 'Please write 2-3 lines about yourself first.',
            ], 422);
        }

        if ($context === 'experience' && $jobTitle === '') {
            return response()->json([
                'message' => 'Please enter Job Role first.',
            ], 422);
        }
        if ($jobTitle !== '') {
            $resume['job_title'] = $jobTitle;
            $resume['designation'] = $jobTitle;
        }

        $previousOutputs = collect($validated['previous_outputs'] ?? [])
            ->map(fn ($item) => $this->cleanAiSeedText($item, 500))
            ->filter()
            ->take(1)
            ->values()
            ->all();

        $variationSeed = $this->toText($validated['variation_seed'] ?? '');
        $variationSeed = $variationSeed !== '' ? $variationSeed : now()->format('Uu').'|'.random_int(1000, 9999);
        $styleDirections = [
            'Use concise, confident wording with strong recruiter-friendly verbs.',
            'Use a polished corporate tone with varied sentence openings.',
            'Use energetic, human wording while staying factual and ATS-friendly.',
            'Use crisp, impact-oriented phrasing with natural professional language.',
            'Use mature, credible language and avoid copying prior sentence patterns.',
        ];
        $styleDirection = $styleDirections[abs(crc32($variationSeed)) % count($styleDirections)];

        $profileLevel = $this->candidateLooksFresher($resume) ? 'fresher/intern' : 'experienced';
        $summaryLimit = $profileLevel === 'fresher/intern' ? '35-60 words maximum' : '50-80 words maximum';
        $prompt = $context === 'summary'
            ? "Create ONE clean professional resume summary paragraph for a {$profileLevel} candidate. Rewrite from scratch each time and replace any old summary completely. If Existing text is present, improve and rewrite it instead of appending. If Existing text is empty, use parsed resume/profile data. Include role, skills, strengths, impact/value, and career goal. Keep {$summaryLimit}, 3-4 lines maximum, concise, ATS-friendly, readable, human-like, and non-repetitive. Use fresh wording and sentence structure every request. Do not reuse phrases from Previous outputs. Do not invent employers, years, degrees, certifications, links, metrics, or tools that are not present. No heading, no markdown, no placeholders, no bullet points, no explanation."
            : "Generate ONLY 4 or 5 professional resume responsibility bullet lines for the CURRENT clicked job role. Total response must be 50-100 words and never more than 100 words. Rewrite from scratch each time and replace any old responsibilities completely. Keep every line concise, ATS-friendly, readable, role-specific, professional, meaningful, and non-repetitive. Use different wording, sentence structure, and action verbs from Previous outputs. Tailor the content to the actual role domain: for example, drivers should mention routes, safety, delivery, vehicle checks; developers should mention code, APIs, debugging, performance, deployment; project managers should mention planning, deadlines, stakeholders, coordination. Use uploaded/profile context when available, but focus only on the current clicked role. Do not invent employers, dates, tools, certifications, or metrics. No heading, no introduction, no explanation, no paragraph; return one responsibility per newline.";

        $prompt .= "\n\nCandidate name: ".trim(($resume['name'] ?? '').' '.($resume['last_name'] ?? ''));
        $prompt .= "\nCreation source: ".$source;
        $prompt .= "\nTarget/clicked job role: ".$jobTitle;
        $prompt .= "\nSkills: ".$skills;
        $prompt .= "\nExtracted or current profile summary: ".$profileSummary;
        $prompt .= "\nExperience context: ".$experience;
        $prompt .= "\nEducation context: ".$education;
        $prompt .= "\nExisting text: ".$existingText;
        $prompt .= "\nPrevious outputs to avoid close repetition: ".($previousOutputs ? implode("\n---\n", $previousOutputs) : 'None');
        $prompt .= "\nVariation token: ".$variationSeed;
        $prompt .= "\nVariation direction: ".$styleDirection;
        $prompt .= "\nReturn a fresh phrasing variant every request. Do not reuse the same opening phrase, sentence order, or responsibility wording from Previous outputs. Preserve factual meaning.";

        $generated = $this->callGeminiForText($prompt);

        if ($generated === '' || ($context === 'summary' && str_word_count(strip_tags($generated)) < 25)) {
            $generated = $this->buildLocalAiText($context, $resume, $existingText);
            if ($context === 'experience') {
                $generated = $this->normalizeExperienceAiOutputStrict($generated, $resume, $existingText);
            } else {
                $generated = $this->normalizeSummaryAiOutput($generated, $resume);
            }
            return response()->json(['text' => $generated, 'source' => 'local_fallback']);
        }

        if ($context === 'experience') {
            $generated = $this->normalizeExperienceAiOutputStrict($generated, $resume, $existingText);
        } else {
            $generated = $this->normalizeSummaryAiOutput($generated, $resume);
        }

        return response()->json(['text' => $generated, 'source' => 'ai']);
    }

    private function normalizeExperienceAiOutput(string $generated, array $resume, string $existingText): string
    {
        $lines = collect(preg_split('/\R+/', strip_tags($generated)))
            ->map(fn ($line) => trim(preg_replace('/^\s*[-*•]\s*/u', '', (string) $line)))
            ->filter()
            ->values();

        if ($lines->isEmpty()) {
            $lines = collect(preg_split('/\R+/', strip_tags($existingText)))
                ->map(fn ($line) => trim(preg_replace('/^\s*[-*•]\s*/u', '', (string) $line)))
                ->filter()
                ->values();
        }

        if ($lines->isEmpty()) {
            $lines = collect(preg_split('/\.\s+/', strip_tags($this->buildLocalAiText('experience', $resume, $existingText))))
                ->map(fn ($line) => trim((string) $line))
                ->filter()
                ->values();
        }

        $seed = $lines->all();
        while (count($seed) < 4 && count($seed) > 0) {
            $seed[] = $seed[count($seed) % count($lines)];
        }

        return collect($seed)
            ->take(4)
            ->map(function ($line) {
                $line = trim((string) $line);
                $line = preg_replace('/\s+/', ' ', $line);
                $line = rtrim($line, '.');

                return $line === '' ? '' : $line.'.';
            })
            ->filter()
            ->join("\n");
    }

    private function normalizeExperienceAiOutputStrict(string $generated, array $resume, string $existingText): string
    {
        $cleanLine = fn ($line) => trim(preg_replace('/^\s*[-*•]\s*/u', '', (string) $line));
        $lines = collect(preg_split('/\R+/', strip_tags($generated)))
            ->map($cleanLine)
            ->map(fn ($line) => trim(preg_replace('/\s+/', ' ', (string) $line)))
            ->filter()
            ->unique(fn ($line) => strtolower(rtrim((string) $line, '.')))
            ->values();

        if ($lines->isEmpty()) {
            $lines = collect(preg_split('/\R+/', strip_tags($existingText)))
                ->map($cleanLine)
                ->filter()
                ->unique(fn ($line) => strtolower(rtrim((string) $line, '.')))
                ->values();
        }

        if ($lines->count() < 4) {
            $fallbackLines = preg_split('/\R+/', $this->buildLocalAiText('experience', $resume, $existingText)) ?: [];
            foreach ($fallbackLines as $line) {
                $line = $cleanLine($line);
                if ($line !== '' && ! $lines->contains($line)) {
                    $lines->push($line);
                }
                if ($lines->count() >= 4) {
                    break;
                }
            }
        }

        $outputLines = $lines
            ->take(5)
            ->map(function ($line) {
                $line = trim(preg_replace('/\s+/', ' ', (string) $line));
                $line = rtrim($line, '.');

                return $line === '' ? '' : $line.'.';
            })
            ->filter()
            ->values()
            ->all();

        $role = $this->toText($resume['job_title'] ?? $resume['designation'] ?? '') ?: 'role';
        if ($this->countWordsInLines($outputLines) < 50 && count($outputLines) < 5) {
            $outputLines[] = "Supported {$role} goals through reliable coordination, clear communication, and consistent follow-through.";
        }

        return implode("\n", $this->limitLinesToWords($outputLines, 100));
    }

    private function countWordsInLines(array $lines): int
    {
        return str_word_count(implode(' ', $lines));
    }

    private function limitLinesToWords(array $lines, int $maxWords): array
    {
        $remaining = $maxWords;
        $limited = [];

        foreach ($lines as $line) {
            $words = preg_split('/\s+/', trim((string) $line)) ?: [];
            $words = array_values(array_filter($words, fn ($word) => $word !== ''));
            if (! $words || $remaining <= 0) {
                continue;
            }

            if (count($words) > $remaining) {
                $words = array_slice($words, 0, $remaining);
                $line = rtrim(implode(' ', $words), " \t\n\r\0\x0B,;:.-").'.';
            }

            $limited[] = $line;
            $remaining -= count($words);

            if ($remaining <= 0) {
                break;
            }
        }

        return array_slice($limited, 0, 5);
    }

    private function normalizeSummaryAiOutput(string $generated, array $resume): string
    {
        $text = trim(strip_tags($generated));
        $text = preg_replace('/\s+/', ' ', (string) $text);
        $text = preg_replace('/^(professional summary|summary|profile)\s*:?\s*/i', '', (string) $text);

        $isFresher = $this->candidateLooksFresher($resume);
        $maxWords = $isFresher ? 60 : 80;
        $minWords = $isFresher ? 35 : 50;

        $sentences = preg_split('/(?<=[.!?])\s+/', $text) ?: [];
        $seen = [];
        $unique = [];
        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            if ($sentence === '') {
                continue;
            }

            $key = strtolower(trim(preg_replace('/[^a-z0-9]+/i', ' ', $sentence)));
            if ($key !== '' && ! isset($seen[$key])) {
                $seen[$key] = true;
                $unique[] = $sentence;
            }
        }

        if ($unique) {
            $text = implode(' ', array_slice($unique, 0, 4));
        }

        $words = preg_split('/\s+/', $text) ?: [];
        if (count($words) > $maxWords) {
            $text = implode(' ', array_slice($words, 0, $maxWords));
            $text = rtrim($text, " \t\n\r\0\x0B,;:").'.';
        }

        if (str_word_count($text) < $minWords) {
            $fallback = $this->buildLocalAiText('summary', $resume, $text);
            if ($fallback !== $generated) {
                return $this->normalizeSummaryAiOutput($fallback, $resume);
            }
        }

        return trim($text);
    }

    private function cleanAiSeedText(string $value, int $maxLength = 900): string
    {
        $value = trim(strip_tags($value));
        $value = preg_replace('/^\s*[-*•]\s*/um', '', (string) $value);
        $value = preg_replace('/[ \t]+/', ' ', (string) $value);
        $value = preg_replace('/\R{3,}/', "\n\n", (string) $value);

        return mb_substr(trim((string) $value), 0, $maxLength);
    }

    private function candidateLooksFresher(array $resume): bool
    {
        $experience = collect($resume['experience'] ?? [])->filter(function ($item) {
            if (! is_array($item)) {
                return false;
            }

            return collect($item)->flatten()->map(fn ($value) => trim((string) $value))->filter()->isNotEmpty();
        });

        $roleText = strtolower($this->toText($resume['job_title'] ?? $resume['designation'] ?? ''));

        return $experience->isEmpty()
            || preg_match('/\b(fresher|fresh graduate|intern|trainee|student|entry[- ]?level)\b/i', $roleText) === 1;
    }

    public function edit(Resume $resume)
    {
        $this->authorizeResume($resume);

        if (! $resume->user_id && auth()->check()) {
            $resume->forceFill(['user_id' => auth()->id(), 'session_id' => null])->save();
        }

        $templates = Template::where('type', 'resume')->where('is_active', true)->get();

        return view('resume.create', [
            'templates' => $templates,
            'selectedTemplate' => $resume->template,
            'selectedTemplateId' => $resume->template_id,
            'initialResume' => $resume->data,
            'editingResume' => $resume,
        ]);
    }

    public function update(Request $request, Resume $resume)
    {
        $this->authorizeResume($resume);

        $validated = $request->validate(['resume' => ['required', 'array'], 'template_id' => ['nullable', 'exists:templates,id']]);
        $resume->update([
            'data' => $this->normalizeResume($validated['resume']),
            'template_id' => $validated['template_id'] ?? $resume->template_id,
        ]);

        return response()->json(['ok' => true]);
    }

    public function rename(Request $request, Resume $resume)
    {
        $this->authorizeResume($resume);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
        ]);

        $resume->update([
            'title' => trim($validated['title']) ?: 'Untitled Resume',
        ]);

        return back()->with('status', 'Resume title updated.');
    }

    public function preview(Resume $resume)
    {
        $this->authorizeResume($resume);
        return view('resume.preview', ['resume' => $resume]);
    }

    public function previewDocument(Resume $resume)
    {
        $this->authorizeResume($resume);

        $html = $resume->template
            ? view('templates.rendered-document', ['html' => app(TemplateRenderService::class)->renderResume($resume->template, $resume->data)])->render()
            : view('templates.rendered-document', ['html' => app(TemplateRenderService::class)->renderResume(new \App\Models\Template(), $resume->data)])->render();

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    public function download(Request $request, Resume $resume, PdfConversionService $pdfConversionService, string $format = 'pdf')
    {
        $this->authorizeResume($resume);

        if (! $resume->is_paid) {
            app(PlanActivationService::class)->consumeDownload($request->user());
            $resume->forceFill(['is_paid' => true])->save();
        }

        app(PendingDownloadService::class)->clear($request);

        $html = $resume->template
            ? view('templates.rendered-document', ['html' => app(TemplateRenderService::class)->renderResume($resume->template, $resume->data)])->render()
            : view('templates.rendered-document', ['html' => app(TemplateRenderService::class)->renderResume(new \App\Models\Template(), $resume->data)])->render();
        $filename = str($resume->title ?: 'resume')->slug()->toString();

        if ($format === 'docx') {
            return $this->downloadDocx($html, $filename);
        }

        if ($format === 'doc') {
            return response($html, 200, [
                'Content-Type' => 'application/msword',
                'Content-Disposition' => "attachment; filename={$filename}.doc",
            ]);
        }

        if ($format === 'ppt') {
            return response($html, 200, [
                'Content-Type' => 'application/vnd.ms-powerpoint',
                'Content-Disposition' => "attachment; filename={$filename}.ppt",
            ]);
        }

        $pdf = $pdfConversionService->htmlToPdfWithPuppeteer($html);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename={$filename}.pdf",
        ]);
    }

    public function destroy(Resume $resume)
    {
        $this->authorizeResume($resume);
        $resume->delete();

        return back()->with('status', 'Resume deleted successfully.');
    }

    private function downloadDocx(string $html, string $filename)
    {
        $path = tempnam(sys_get_temp_dir(), 'resume_docx_').'.docx';
        $this->writeDocx($path, $this->bodyHtmlForDocx($html));

        return response()->download(
            $path,
            "{$filename}.docx",
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
        )->deleteFileAfterSend(true);
    }

    private function bodyHtmlForDocx(string $html): string
    {
        if (preg_match('/<body[^>]*>([\s\S]*?)<\/body>/i', $html, $matches)) {
            return $matches[1];
        }

        return $html;
    }

    private function writeDocx(string $path, string $html): void
    {
        if (class_exists(\PhpOffice\PhpWord\PhpWord::class) && class_exists(\PhpOffice\PhpWord\Shared\Html::class)) {
            try {
                $phpWord = new \PhpOffice\PhpWord\PhpWord();
                $phpWord->setDefaultFontName('Arial');
                $phpWord->setDefaultFontSize(10);

                $section = $phpWord->addSection([
                    'marginTop' => 720,
                    'marginRight' => 720,
                    'marginBottom' => 720,
                    'marginLeft' => 720,
                ]);

                \PhpOffice\PhpWord\Shared\Html::addHtml($section, $this->htmlForPhpWord($html), false, false);
                \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007')->save($path);

                if ($this->isValidDocx($path)) {
                    return;
                }
            } catch (\Throwable) {
                @unlink($path);
            }
        }

        $zip = new \ZipArchive();
        if ($zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Unable to create Word document.');
        }

        $zip->addFromString('[Content_Types].xml', $this->docxContentTypesXml());
        $zip->addFromString('_rels/.rels', $this->docxRootRelsXml());
        $zip->addFromString('word/_rels/document.xml.rels', $this->docxDocumentRelsXml());
        $zip->addFromString('word/styles.xml', $this->docxStylesXml());
        $zip->addFromString('word/numbering.xml', $this->docxNumberingXml());
        $zip->addFromString('word/document.xml', $this->docxDocumentXml($html));
        $zip->close();

        if (! $this->isValidDocx($path)) {
            @unlink($path);
            abort(500, 'Unable to create a valid Word document.');
        }
    }

    private function htmlForPhpWord(string $html): string
    {
        $html = preg_replace('/<script\b[^>]*>[\s\S]*?<\/script>/i', '', $html) ?? '';
        $html = preg_replace('/<style\b[^>]*>[\s\S]*?<\/style>/i', '', $html) ?? $html;
        $html = preg_replace('/<svg\b[^>]*>[\s\S]*?<\/svg>/i', '', $html) ?? $html;
        $html = preg_replace('/\s(class|id|data-[\w-]+)="[^"]*"/i', '', $html) ?? $html;

        return '<div>'.$html.'</div>';
    }

    private function isValidDocx(string $path): bool
    {
        if (! is_file($path) || filesize($path) <= 0) {
            return false;
        }

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            return false;
        }

        $required = ['[Content_Types].xml', '_rels/.rels', 'word/document.xml'];
        foreach ($required as $entry) {
            if ($zip->locateName($entry) === false) {
                $zip->close();
                return false;
            }
        }

        $documentXml = $zip->getFromName('word/document.xml') ?: '';
        $zip->close();

        if ($documentXml === '') {
            return false;
        }

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $valid = $dom->loadXML($documentXml);
        libxml_clear_errors();

        return $valid;
    }

    private function docxDocumentXml(string $html): string
    {
        $paragraphs = $this->htmlParagraphsForDocx($html);
        if ($paragraphs === []) {
            $paragraphs[] = ['style' => null, 'list' => false, 'runs' => [['text' => 'Resume', 'bold' => false, 'italic' => false, 'underline' => false]]];
        }

        $body = collect($paragraphs)->map(fn ($paragraph) => $this->docxParagraphXml($paragraph))->join('');

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:document xmlns:wpc="http://schemas.microsoft.com/office/word/2010/wordprocessingCanvas" xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:m="http://schemas.openxmlformats.org/officeDocument/2006/math" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:wp14="http://schemas.microsoft.com/office/word/2010/wordprocessingDrawing" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" xmlns:w10="urn:schemas-microsoft-com:office:word" xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:w14="http://schemas.microsoft.com/office/word/2010/wordml" xmlns:wpg="http://schemas.microsoft.com/office/word/2010/wordprocessingGroup" xmlns:wpi="http://schemas.microsoft.com/office/word/2010/wordprocessingInk" xmlns:wne="http://schemas.microsoft.com/office/word/2006/wordml" xmlns:wps="http://schemas.microsoft.com/office/word/2010/wordprocessingShape" mc:Ignorable="w14 wp14">'
            .'<w:body>'.$body.'<w:sectPr><w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="720" w:right="720" w:bottom="720" w:left="720" w:header="360" w:footer="360" w:gutter="0"/></w:sectPr></w:body></w:document>';
    }

    private function htmlParagraphsForDocx(string $html): array
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8"><body>'.$html.'</body>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $paragraphs = [];
        foreach ($dom->childNodes as $node) {
            $this->collectDocxParagraphs($node, $paragraphs);
        }

        return $paragraphs;
    }

    private function collectDocxParagraphs(\DOMNode $node, array &$paragraphs, bool $inList = false): void
    {
        if ($node instanceof \DOMText) {
            $text = trim(preg_replace('/\s+/', ' ', $node->nodeValue));
            if ($text !== '') {
                $paragraphs[] = ['style' => null, 'list' => $inList, 'runs' => [['text' => $text, 'bold' => false, 'italic' => false, 'underline' => false]]];
            }
            return;
        }

        if (! $node instanceof \DOMElement) {
            foreach ($node->childNodes as $child) {
                $this->collectDocxParagraphs($child, $paragraphs, $inList);
            }
            return;
        }

        $tag = strtolower($node->tagName);
        if (in_array($tag, ['style', 'script', 'svg', 'img'], true)) {
            return;
        }

        if ($tag === 'ul' || $tag === 'ol') {
            foreach ($node->childNodes as $child) {
                $this->collectDocxParagraphs($child, $paragraphs, true);
            }
            return;
        }

        $style = match ($tag) {
            'h1' => 'Heading1',
            'h2' => 'Heading2',
            'h3', 'h4' => 'Heading3',
            default => null,
        };

        if (in_array($tag, ['h1', 'h2', 'h3', 'h4', 'p', 'li'], true) || ! $this->hasDocxBlockChild($node)) {
            $runs = $this->docxInlineRuns($node);
            if ($runs !== []) {
                $paragraphs[] = ['style' => $style, 'list' => $tag === 'li' || $inList, 'runs' => $runs];
            }
            return;
        }

        foreach ($node->childNodes as $child) {
            $this->collectDocxParagraphs($child, $paragraphs, $inList);
        }
    }

    private function hasDocxBlockChild(\DOMElement $node): bool
    {
        foreach ($node->childNodes as $child) {
            if ($child instanceof \DOMElement && in_array(strtolower($child->tagName), ['div', 'section', 'article', 'header', 'footer', 'table', 'tr', 'td', 'ul', 'ol', 'li', 'p', 'h1', 'h2', 'h3', 'h4'], true)) {
                return true;
            }
        }

        return false;
    }

    private function docxInlineRuns(\DOMNode $node, array $format = []): array
    {
        $runs = [];
        foreach ($node->childNodes as $child) {
            if ($child instanceof \DOMText) {
                $text = preg_replace('/\s+/', ' ', $child->nodeValue);
                if (trim($text) !== '') {
                    $runs[] = [
                        'text' => $this->cleanDocxText($text),
                        'bold' => $format['bold'] ?? false,
                        'italic' => $format['italic'] ?? false,
                        'underline' => $format['underline'] ?? false,
                    ];
                }
                continue;
            }

            if (! $child instanceof \DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);
            if ($tag === 'br') {
                $runs[] = ['text' => "\n", 'bold' => false, 'italic' => false, 'underline' => false];
                continue;
            }

            $childFormat = $format;
            $childFormat['bold'] = ($childFormat['bold'] ?? false) || in_array($tag, ['strong', 'b'], true);
            $childFormat['italic'] = ($childFormat['italic'] ?? false) || in_array($tag, ['em', 'i'], true);
            $childFormat['underline'] = ($childFormat['underline'] ?? false) || $tag === 'u';
            array_push($runs, ...$this->docxInlineRuns($child, $childFormat));
        }

        return $runs;
    }

    private function docxParagraphXml(array $paragraph): string
    {
        $properties = '';
        if (! empty($paragraph['style'])) {
            $properties .= '<w:pStyle w:val="'.$paragraph['style'].'"/>';
        }
        if (! empty($paragraph['list'])) {
            $properties .= '<w:numPr><w:ilvl w:val="0"/><w:numId w:val="1"/></w:numPr>';
        }

        $runs = collect($paragraph['runs'] ?? [])->map(fn ($run) => $this->docxRunXml($run))->join('');

        return '<w:p>'.($properties ? '<w:pPr>'.$properties.'</w:pPr>' : '').$runs.'</w:p>';
    }

    private function docxRunXml(array $run): string
    {
        $props = '';
        if (! empty($run['bold'])) {
            $props .= '<w:b/>';
        }
        if (! empty($run['italic'])) {
            $props .= '<w:i/>';
        }
        if (! empty($run['underline'])) {
            $props .= '<w:u w:val="single"/>';
        }

        $text = (string) ($run['text'] ?? '');
        if ($text === "\n") {
            return '<w:r><w:br/></w:r>';
        }

        return '<w:r>'.($props ? '<w:rPr>'.$props.'</w:rPr>' : '').'<w:t xml:space="preserve">'.e($text).'</w:t></w:r>';
    }

    private function cleanDocxText(string $text): string
    {
        return preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}]/u', '', $text) ?? '';
    }

    private function docxContentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/><Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/><Override PartName="/word/numbering.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.numbering+xml"/></Types>';
    }

    private function docxRootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>';
    }

    private function docxDocumentRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/numbering" Target="numbering.xml"/></Relationships>';
    }

    private function docxStylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:style w:type="paragraph" w:default="1" w:styleId="Normal"><w:name w:val="Normal"/><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial"/><w:sz w:val="20"/></w:rPr><w:pPr><w:spacing w:after="120"/></w:pPr></w:style><w:style w:type="paragraph" w:styleId="Heading1"><w:name w:val="heading 1"/><w:basedOn w:val="Normal"/><w:next w:val="Normal"/><w:rPr><w:b/><w:sz w:val="32"/></w:rPr><w:pPr><w:spacing w:before="120" w:after="120"/></w:pPr></w:style><w:style w:type="paragraph" w:styleId="Heading2"><w:name w:val="heading 2"/><w:basedOn w:val="Normal"/><w:next w:val="Normal"/><w:rPr><w:b/><w:sz w:val="24"/></w:rPr><w:pPr><w:spacing w:before="180" w:after="80"/></w:pPr></w:style><w:style w:type="paragraph" w:styleId="Heading3"><w:name w:val="heading 3"/><w:basedOn w:val="Normal"/><w:next w:val="Normal"/><w:rPr><w:b/><w:sz w:val="22"/></w:rPr></w:style></w:styles>';
    }

    private function docxNumberingXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:numbering xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:abstractNum w:abstractNumId="0"><w:lvl w:ilvl="0"><w:start w:val="1"/><w:numFmt w:val="bullet"/><w:lvlText w:val="•"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="720" w:hanging="360"/></w:pPr></w:lvl></w:abstractNum><w:num w:numId="1"><w:abstractNumId w:val="0"/></w:num></w:numbering>';
    }

    private function authorizeResume(Resume $resume): void
    {
        if ($resume->user_id && $resume->user_id !== auth()->id()) {
            abort(403);
        }

        if (! $resume->user_id && $resume->session_id !== request()->session()->getId()) {
            abort(403);
        }
    }

    private function callGeminiForText(string $prompt): string
    {
        $result = $this->gemini->generateContent($prompt, [
            'temperature'     => 0.94,
            'topP'            => 0.92,
            'topK'            => 40,
            'maxOutputTokens' => 260,
            'timeout'         => 45,
        ]);

        if (! ($result['success'] ?? false)) {
            return '';
        }

        $text = (string) ($result['text'] ?? '');
        $text = preg_replace('/```(?:text|markdown)?/i', '', $text);
        $text = str_replace('```', '', $text);
        $text = preg_replace('/^\s*[-*]\s*/m', '', $text);

        return trim($text);
    }

    private function buildLocalAiText(string $context, array $resume, string $existingText): string
    {
        $role = $this->toText($resume['job_title'] ?? '') ?: 'professional';
        $roleLower = strtolower($role);
        $skills = array_slice($resume['skills'] ?? [], 0, 6);
        $skillsText = $skills ? implode(', ', $skills) : 'cross-functional collaboration, problem solving, and delivery-focused execution';
        $experience = collect($resume['experience'] ?? [])
            ->filter(fn ($item) => is_array($item))
            ->first();

        if ($context === 'experience') {
            $base = collect(preg_split('/\R+/', $existingText))
                ->map(fn ($line) => trim(preg_replace('/^[\-•»]\s*/', '', $line)))
                ->filter()
                ->take(5)
                ->values();

            if ($base->isNotEmpty()) {
                $verbs = ['Led', 'Delivered', 'Executed', 'Improved', 'Drove', 'Built'];
                $seed = $base->values()->all();
                while (count($seed) < 4) {
                    $seed[] = $base[count($seed) % $base->count()];
                }

                return collect($seed)->take(4)->values()->map(function ($line, $index) use ($verbs) {
                    $verb = $verbs[$index % count($verbs)];

                    return $verb.' '.$line;
                })->join("\n");
            }

            $roleFamilies = [
                'driver' => [
                    'Planned efficient routes and completed transport assignments on schedule while following safety rules.',
                    'Inspected vehicle condition, reported issues early, and maintained cleanliness before every trip.',
                    'Coordinated with dispatch or customers to confirm pickup, delivery, and timing updates.',
                    'Maintained logs, delivery records, and trip documentation with accuracy and accountability.',
                    'Handled goods carefully to prevent damage and support smooth daily operations.',
                ],
                'project manager' => [
                    'Defined project plans, milestones, and dependencies to keep delivery aligned with business goals.',
                    'Coordinated with stakeholders, design, and development teams to remove blockers quickly.',
                    'Tracked progress against deadlines, communicated status updates, and managed changing priorities.',
                    'Reviewed risks early and guided execution to keep work organized and on schedule.',
                    'Supported team alignment through clear expectations, follow-ups, and delivery ownership.',
                ],
                'full stack developer' => [
                    'Developed responsive user interfaces and connected them with backend services and APIs.',
                    'Worked across database, server-side, and frontend layers to build stable application features.',
                    'Debugged issues, improved code quality, and optimized performance for better user experience.',
                    'Collaborated with product and design teams to translate requirements into reliable releases.',
                    'Maintained reusable code structure and supported deployment-ready application updates.',
                ],
                'web developer' => [
                    'Built responsive web pages and interactive features using modern frontend technologies.',
                    'Integrated REST APIs and supported backend workflows for smooth data exchange.',
                    'Debugged browser issues, improved loading speed, and refined user-facing functionality.',
                    'Worked with designers and developers to deliver clean, accessible, and consistent interfaces.',
                    'Wrote reusable code and helped keep applications maintainable across updates.',
                ],
            ];

            $matchedFamily = null;
            foreach ($roleFamilies as $family => $bullets) {
                if (str_contains($roleLower, $family) || ($family === 'web developer' && (str_contains($roleLower, 'developer') || str_contains($roleLower, 'web')))) {
                    $matchedFamily = $bullets;
                    break;
                }
            }

            if (! $matchedFamily) {
                $matchedFamily = [
                    "Performed {$role} responsibilities with a focus on quality, coordination, and timely delivery.",
                    "Worked with team members and stakeholders to turn requirements into practical day-to-day results.",
                    "Applied {$skillsText} where relevant to improve output quality and support business objectives.",
                    "Maintained clear communication, organized execution, and consistent follow-through on assigned work.",
                    "Adapted to changing priorities while keeping tasks accurate, reliable, and professional.",
                ];
            }

            shuffle($matchedFamily);

            return implode("\n", array_slice($matchedFamily, 0, 5));
        }

        if ($existingText !== '') {
            $base = trim(preg_replace('/\s+/', ' ', strip_tags($existingText)));
            $base = implode(' ', array_slice(preg_split('/\s+/', $base) ?: [], 0, 26));
            $templates = [
                "{$role} with a foundation in {$base}, supported by skills in {$skillsText}. Brings clear communication, disciplined execution, and a practical approach to learning quickly, solving problems, and contributing dependable value to professional teams.",
                "Results-focused {$role} with strengths shaped by {$base} and practical exposure to {$skillsText}. Known for ownership, adaptability, and structured problem solving, with a career goal of building reliable work that supports team and business outcomes.",
                "Motivated {$role} with hands-on interest in {$skillsText} and a background in {$base}. Offers attention to detail, collaborative energy, and a growth mindset focused on turning requirements into clear, useful results.",
            ];

            return $templates[random_int(0, count($templates) - 1)];
        }

        $company = is_array($experience) ? $this->toText($experience['company'] ?? '') : '';
        $contextLine = $company ? "with experience at {$company}" : 'with practical experience across professional projects';
        $openers = [
            "{$role} {$contextLine}, skilled in {$skillsText}.",
            "Results-focused {$role} {$contextLine}, with strengths in {$skillsText}.",
            "{$role} {$contextLine}, bringing hands-on capability in {$skillsText}.",
        ];
        $opener = $openers[random_int(0, count($openers) - 1)];

        return "{$opener} Brings a disciplined, detail-oriented approach to understanding requirements, building dependable solutions, and improving user-facing outcomes. Experienced in collaborating with teams, organizing work clearly, and turning business needs into polished deliverables. Focused on continuous learning, clean execution, and contributing meaningful value to teams that need reliable ownership, strong communication, and consistent delivery.";
    }

    private function normalizeResume(array $resume): array
    {
        $additionalInformation = $resume['additional_information'] ?? $resume['additionalInformation'] ?? [];

        $normalized = array_merge($resume, [
            'name' => $this->toText($resume['name'] ?? ''),
            'mobile' => $this->toText($resume['mobile'] ?? $resume['contact'] ?? ''),
            'email' => $this->toText($resume['email'] ?? ''),
            'location' => $this->toText($resume['location'] ?? $resume['address'] ?? ''),
            'contact' => $this->toText($resume['contact'] ?? $resume['mobile'] ?? ''),
            'address' => $this->toText($resume['address'] ?? $resume['location'] ?? ''),
            'summary' => $this->toText($resume['summary'] ?? ''),
            'last_name' => $this->toText($resume['last_name'] ?? ''),
            'job_title' => $this->toText($resume['job_title'] ?? ''),
            'designation' => $this->toText($resume['designation'] ?? ''),
            'desired_job_role' => '',
            'linkedin' => $this->toText($resume['linkedin'] ?? ''),
            'portfolio' => $this->toText($resume['portfolio'] ?? $resume['link'] ?? ''),
            'link' => $this->toText($resume['link'] ?? $resume['portfolio'] ?? ''),
            'github' => $this->toText($resume['github'] ?? ''),
            'tech_stack' => $this->toText($resume['tech_stack'] ?? ''),
            'skills' => $this->normalizeArray($resume['skills'] ?? []),
            'experience' => $this->normalizeNestedItems($resume['experience'] ?? []),
            'education' => $this->normalizeEducation($resume['education'] ?? []),
            'projects' => $this->normalizeNestedItems($resume['projects'] ?? []),
            'social_links' => [],
            'certifications' => $this->normalizeNamedItems($resume['certifications'] ?? $resume['certificates'] ?? []),
            'certificates' => $this->normalizeNamedItems($resume['certifications'] ?? $resume['certificates'] ?? []),
            'languages' => $this->normalizeLanguages($resume['languages'] ?? $resume['language'] ?? $resume['language_skills'] ?? $resume['language_proficiency'] ?? []),
            'additional_information' => $this->normalizeNamedItems($additionalInformation),
            'achievements' => $this->normalizeNamedItems($resume['achievements'] ?? []),
            'profile_image' => $this->toText($resume['profile_image'] ?? ''),
        ]);

        $primaryColor = $this->toText($resume['primary_color'] ?? '');
        if ($primaryColor !== '') {
            $normalized['primary_color'] = $primaryColor;
            $normalized['primary_color_customized'] = filter_var($resume['primary_color_customized'] ?? true, FILTER_VALIDATE_BOOLEAN);
        } else {
            unset($normalized['primary_color']);
            unset($normalized['primary_color_customized']);
        }

        return $normalized;
    }

    private function normalizeArray(array|string|null $items): array
    {
        if ($items === null) {
            return [];
        }

        if (! is_array($items)) {
            return $this->stringList($items);
        }

        return array_values(array_filter(array_map(function ($item) {
            if (is_array($item)) {
                return $this->toText($item);
            }

            return $this->toText($item);
        }, $items), fn ($item) => $item !== null && $item !== ''));
    }

    private function normalizeNamedItems(array|string|null $items): array
    {
        if ($items === null) {
            return [];
        }

        $items = is_array($items) ? $items : explode(',', $items);

        return array_values(array_filter(array_map(function ($item) {
            if (is_array($item)) {
                $name = $this->toText($item['name'] ?? $item['title'] ?? $item['label'] ?? '');
                $description = $this->toText($item['description'] ?? $item['details'] ?? '');

                if ($name === '' && $description === '') {
                    return null;
                }

                return compact('name', 'description');
            }

            $name = $this->toText($item);

            return $name === '' ? null : ['name' => $name, 'description' => ''];
        }, $items)));
    }

    private function normalizeLanguages(array|string|null $items): array
    {
        if ($items === null) {
            return [];
        }

        $items = is_array($items) ? $items : explode(',', $items);

        return array_values(array_filter(array_map(function ($item) {
            if (is_array($item)) {
                $name = $this->toText($item['name'] ?? $item['language'] ?? '');
                $level = $this->toText($item['level'] ?? $item['proficiency'] ?? '');

                if ($name === '' && $level === '') {
                    return null;
                }

                return compact('name', 'level');
            }

            $name = $this->toText($item);

            return $name === '' ? null : ['name' => $name, 'level' => ''];
        }, $items)));
    }

    private function normalizeNestedItems(array|string|null $items): array
    {
        if ($items === null) {
            return [];
        }

        if (! is_array($items)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($item) {
            if (! is_array($item)) {
                return null;
            }

            return collect($item)->map(function ($value) {
                if (is_array($value)) {
                    return $this->normalizeArray($value);
                }

                return $this->toText($value);
            })->all();
        }, $items), fn ($item) => ! empty($item)));
    }

    private function normalizeEducation(array|string|null $items): array
    {
        if ($items === null) {
            return [];
        }

        $items = is_array($items) ? $items : [$items];

        return array_values(array_filter(array_map(function ($item) {
            if (is_array($item)) {
                return [
                    'degree' => $this->toText($item['degree'] ?? $item['course'] ?? ''),
                    'stream' => $this->toText($item['stream'] ?? $item['field'] ?? $item['specialization'] ?? ''),
                    'institution' => $this->toText($item['institution'] ?? $item['school'] ?? $item['university'] ?? $item['college'] ?? ''),
                    'year' => $this->toText($item['year'] ?? $item['duration'] ?? $item['period'] ?? ''),
                ];
            }

            $parts = array_values(array_filter(array_map(fn ($part) => trim($part), explode(',', $this->toText($item)))));

            return [
                'degree' => $parts[0] ?? '',
                'stream' => count($parts) > 3 ? ($parts[1] ?? '') : '',
                'institution' => count($parts) > 2 ? implode(', ', array_slice($parts, 1, -1)) : ($parts[1] ?? ''),
                'year' => count($parts) > 1 ? end($parts) : '',
            ];
        }, $items), fn ($item) => collect($item)->filter()->isNotEmpty()));
    }

    private function stringList(array|string|null $items): array
    {
        if ($items === null) {
            return [];
        }

        $items = is_array($items) ? $items : explode(',', $items);

        return array_values(array_filter(array_map(fn ($item) => $this->toText($item), $items)));
    }

    private function projectList(array|string|null $items): array
    {
        if ($items === null) {
            return [];
        }

        $items = is_array($items) ? $items : explode(',', $items);

        return array_values(array_filter(array_map(function ($item) {
            if (is_array($item)) {
                $name = $this->toText($item['name'] ?? '');
                $description = $this->toText($item['description'] ?? '');

                if ($name === '' && $description === '') {
                    return null;
                }

                return compact('name', 'description');
            }

            $name = $this->toText($item);

            return $name === '' ? null : ['name' => $name, 'description' => ''];
        }, $items)));
    }

    private function toText(mixed $value): string
    {
        if (is_array($value)) {
            return collect($value)
                ->flatten()
                ->map(fn ($part) => is_scalar($part) ? trim((string) $part) : '')
                ->filter()
                ->join(' - ');
        }

        return trim((string) ($value ?? ''));
    }
}
