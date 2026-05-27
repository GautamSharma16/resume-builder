<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Models\ResumeAnalysis;
use App\Services\PlanActivationService;
use App\Services\PdfConversionService;
use App\Services\TemplateRenderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Razorpay\Api\Api;
use Throwable;
use ZipArchive;

class ResumeController extends Controller
{
    public function index(TemplateRenderService $renderer)
    {
        $templates = Template::where('type', 'resume')
            ->where('is_active', true)
            ->get();

        return view('pages.improve', [
            'razorpayKey' => config('services.razorpay.key'),
            'downloadAmount' => config('services.razorpay.download_amount'),
            'downloadCurrency' => config('services.razorpay.currency'),
            'templates' => $templates,
            'renderedTemplates' => $templates->mapWithKeys(fn(Template $template) => [
                $template->id => (string) $renderer->renderResume($template, null, false),
            ]),
        ]);
    }

    public function atsChecker()
    {
        return view('pages.ats-checker');
    }

    public function analyze(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'resume' => ['required', 'file', 'mimes:pdf,doc,docx,ppt,pptx', 'max:10240'],
                'mode' => ['nullable', 'in:autofill'],
            ]);

            $text = $this->cleanText($this->extractText($request->file('resume')));

            if (mb_strlen($text) < 80) {
                throw ValidationException::withMessages([
                    'resume' => 'We could not extract enough readable text from this resume. Please upload a text-based PDF or DOCX.',
                ]);
            }

            $resumeJson = $this->structureResume($text);
            $jobRole = $request->input('job_role') ?: 'General';
            $jobDescription = $request->input('job_description');

            $analysis = ($validated['mode'] ?? null) === 'autofill'
                ? [
                    'success' => true,
                    'score' => 0,
                    'strengths' => [],
                    'weaknesses' => [],
                    'missing_keywords' => [],
                    'suggestions' => [],
                    'improved_resume' => $resumeJson,
                ]
                : $this->askGeminiForAnalysis(
                    $resumeJson,
                    $jobRole,
                    $jobDescription
                );

            if (!Arr::get($analysis, 'success', true)) {
                if (($validated['mode'] ?? null) === 'autofill') {
                    return response()->json([
                        'success' => true,
                        'analysis_id' => null,
                        'is_paid' => false,
                        'score' => 0,
                        'strengths' => [],
                        'weaknesses' => [],
                        'missing_keywords' => [],
                        'suggestions' => [Arr::get($analysis, 'message', 'AI analysis was unavailable, so we imported the readable resume text.')],
                        'improved_resume' => $resumeJson,
                    ]);
                }

                $analysis = $this->localAtsAnalysis($resumeJson, $jobRole, $jobDescription);
            }

            $analysisRecord = ResumeAnalysis::create([
                'user_id' => $request->user()?->id,
                'session_id' => $request->session()->getId(),
                'job_role' => $jobRole,
                'job_description' => $jobDescription,
                'original_filename' => $request->file('resume')->getClientOriginalName(),
                'extracted_text' => $text,
                'resume_json' => $resumeJson,
                'analysis_json' => $analysis,
                'improved_resume_json' => $this->normalizeResume(Arr::get($analysis, 'improved_resume', $resumeJson)),
            ]);

            return response()->json([
                'success' => true,
                'analysis_id' => $analysisRecord->id,
                'is_paid' => false,
                'score' => (int) Arr::get($analysis, 'score', 0),
                'strengths' => Arr::get($analysis, 'strengths', []),
                'weaknesses' => Arr::get($analysis, 'weaknesses', []),
                'missing_keywords' => Arr::get($analysis, 'missing_keywords', []),
                'suggestions' => Arr::get($analysis, 'suggestions', []),
                'improved_resume' => $analysisRecord->improved_resume_json,
            ]);

        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Resume Analysis Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'AI analysis failed: ' . $e->getMessage()], 500);
        }
    }

    public function improveAgain(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'analysis_id' => ['required', 'integer', 'exists:resume_analyses,id'],
                'resume' => ['required', 'array'],
            ]);

            $analysisRecord = $this->findAuthorizedAnalysis($request, (int) $validated['analysis_id']);
            $resume = $this->normalizeResume($validated['resume']);

            $analysis = $this->askGeminiForAnalysis(
                $resume,
                $analysisRecord->job_role,
                $analysisRecord->job_description,
                true
            );

            if (!Arr::get($analysis, 'success', true)) {
                return response()->json([
                    'success' => false,
                    'message' => Arr::get($analysis, 'message', 'AI improvement failed.')
                ], 500);
            }

            $analysisRecord->update([
                'analysis_json' => $analysis,
                'improved_resume_json' => $this->normalizeResume(Arr::get($analysis, 'improved_resume', $resume)),
            ]);

            return response()->json([
                'success' => true,
                'analysis_id' => $analysisRecord->id,
                'is_paid' => $analysisRecord->is_paid,
                'score' => (int) Arr::get($analysis, 'score', 0),
                'strengths' => Arr::get($analysis, 'strengths', []),
                'weaknesses' => Arr::get($analysis, 'weaknesses', []),
                'missing_keywords' => Arr::get($analysis, 'missing_keywords', []),
                'suggestions' => Arr::get($analysis, 'suggestions', []),
                'improved_resume' => $analysisRecord->improved_resume_json,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function grammarFix(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'analysis_id' => ['required', 'integer', 'exists:resume_analyses,id'],
                'resume' => ['required', 'array'],
            ]);

            $analysisRecord = $this->findAuthorizedAnalysis($request, (int) $validated['analysis_id']);
            $resume = $this->normalizeResume($validated['resume']);
            $analysis = $this->askGeminiForAnalysis(
                $resume,
                $analysisRecord->job_role,
                $analysisRecord->job_description,
                true,
                'Fix grammar, clarity, tense, and bullet consistency. Do not invent facts.'
            );

            if (!Arr::get($analysis, 'success', true)) {
                return response()->json([
                    'success' => false,
                    'message' => Arr::get($analysis, 'message', 'AI grammar fix failed.')
                ], 500);
            }

            $analysisRecord->update([
                'analysis_json' => $analysis,
                'improved_resume_json' => $this->normalizeResume(Arr::get($analysis, 'improved_resume', $resume)),
            ]);

            return response()->json([
                'success' => true,
                'analysis_id' => $analysisRecord->id,
                'is_paid' => $analysisRecord->is_paid,
                'score' => (int) Arr::get($analysis, 'score', 0),
                'strengths' => Arr::get($analysis, 'strengths', []),
                'weaknesses' => Arr::get($analysis, 'weaknesses', []),
                'missing_keywords' => Arr::get($analysis, 'missing_keywords', []),
                'suggestions' => Arr::get($analysis, 'suggestions', []),
                'improved_resume' => $analysisRecord->improved_resume_json,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function saveResume(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'analysis_id' => ['required', 'integer', 'exists:resume_analyses,id'],
            'resume' => ['required', 'array'],
        ]);

        $analysisRecord = $this->findAuthorizedAnalysis($request, (int) $validated['analysis_id']);
        $analysisRecord->update([
            'improved_resume_json' => $this->normalizeResume($validated['resume']),
        ]);

        return response()->json([
            'ok' => true,
            'analysis_id' => $analysisRecord->id,
            'is_paid' => $analysisRecord->is_paid,
        ]);
    }

    public function createPaymentOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'analysis_id' => ['required', 'integer', 'exists:resume_analyses,id'],
        ]);

        $analysisRecord = $this->findAuthorizedAnalysis($request, (int) $validated['analysis_id']);

        if ($analysisRecord->is_paid) {
            return response()->json(['is_paid' => true]);
        }

        $api = $this->razorpay();
        $amount = config('services.razorpay.download_amount');

        $order = $api->order->create([
            'receipt' => 'resume_' . $analysisRecord->id,
            'amount' => $amount,
            'currency' => config('services.razorpay.currency'),
            'payment_capture' => 1,
            'notes' => [
                'resume_analysis_id' => (string) $analysisRecord->id,
            ],
        ]);

        $analysisRecord->update(['razorpay_order_id' => $order['id']]);

        return response()->json([
            'is_paid' => false,
            'order_id' => $order['id'],
            'key' => config('services.razorpay.key'),
            'amount' => $amount,
            'currency' => config('services.razorpay.currency'),
            'name' => config('app.name', 'Cvbliss'),
            'description' => 'Unlock resume PDF download',
        ]);
    }

    public function verifyPayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'analysis_id' => ['required', 'integer', 'exists:resume_analyses,id'],
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        $analysisRecord = $this->findAuthorizedAnalysis($request, (int) $validated['analysis_id']);

        if ($analysisRecord->razorpay_order_id !== $validated['razorpay_order_id']) {
            abort(422, 'Payment order does not match this resume.');
        }

        try {
            $this->razorpay()->utility->verifyPaymentSignature([
                'razorpay_order_id' => $validated['razorpay_order_id'],
                'razorpay_payment_id' => $validated['razorpay_payment_id'],
                'razorpay_signature' => $validated['razorpay_signature'],
            ]);
        } catch (Throwable) {
            abort(422, 'Payment verification failed.');
        }

        $analysisRecord->update([
            'is_paid' => true,
            'razorpay_payment_id' => $validated['razorpay_payment_id'],
            'razorpay_signature' => $validated['razorpay_signature'],
            'paid_at' => now(),
        ]);

        return response()->json(['ok' => true, 'is_paid' => true]);
    }

    public function download(Request $request, PdfConversionService $pdfConversionService, TemplateRenderService $renderer)
    {
        $validated = $request->validate([
            'analysis_id' => ['required', 'integer', 'exists:resume_analyses,id'],
            'template_id' => ['nullable', 'integer', 'exists:templates,id'],
        ]);

        $analysisRecord = $this->findAuthorizedAnalysis($request, (int) $validated['analysis_id']);

        if (!$request->user()) {
            return redirect()->guest(route('login'));
        }

        // Subscription check (Premium gating)
        if (!$analysisRecord->is_paid && (!$request->user()->activeSubscription?->hasDownloadsRemaining())) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Choose a plan to unlock downloads.',
                    'requires_payment' => true,
                    'pricing_url' => route('plans'),
                ], 402);
            }
            return redirect()->route('plans')->with('status', 'Choose a plan to unlock downloads.');
        }

        if (!$analysisRecord->is_paid) {
            app(PlanActivationService::class)->consumeDownload($request->user());
            $analysisRecord->forceFill([
                'is_paid' => true,
                'paid_at' => now(),
            ])->save();
        }

        $resume = $this->normalizeResume($analysisRecord->improved_resume_json ?? []);
        $filename = $this->pdfFilename($resume['name']);

        $template = null;
        if ($validated['template_id'] ?? null) {
            $template = Template::find($validated['template_id']);
        }

        if ($template) {
            $htmlContent = $renderer->renderResume($template, $resume);
            $html = view('templates.rendered-document', ['html' => $htmlContent])->render();
        } else {
            // Fallback to basic PDF view if no template is specified or found
            $html = view('resume.pdf', ['resume' => $resume])->render();
        }

        $pdf = $pdfConversionService->htmlToPdfWithPuppeteer($html);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function extractText($file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();

        return match ($extension) {
            'pdf' => (new \Smalot\PdfParser\Parser())->parseFile($path)->getText(),
            'docx' => $this->extractTextFromDocx($path),
            'doc' => $this->extractTextFromDoc($path),
            'pptx' => $this->extractTextFromPptx($path),
            'ppt' => $this->extractTextFromPptx($path), // Simple fallback for PPT if it contains readable XML parts or text
            default => '',
        };
    }

    private function extractTextFromPptx(string $path): string
    {
        $zip = new \ZipArchive();

        if ($zip->open($path) !== true) {
            return '';
        }

        $text = "";
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (str_contains($name, 'ppt/slides/slide')) {
                $content = $zip->getFromIndex($i) ?: '';
                // Add space before text tags to prevent merging words
                $content = str_replace('<a:t', ' <a:t', $content);
                $text .= strip_tags($content) . " ";
            }
        }
        $zip->close();

        return html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function extractTextFromDocx(string $path): string
    {
        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            return '';
        }

        $content = $zip->getFromName('word/document.xml') ?: '';
        $zip->close();

        $content = preg_replace('/<\/w:p>/', "\n", $content);
        $content = strip_tags((string) $content);

        return html_entity_decode($content, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function extractTextFromDoc(string $path): string
    {
        $content = file_get_contents($path) ?: '';
        preg_match_all('/[\x20-\x7E]{4,}/', $content, $matches);

        return implode(' ', $matches[0] ?? []);
    }

    private function cleanText(string $text): string
    {
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", (string) $text);

        return trim((string) $text);
    }

    private function structureResume(string $text): array
    {
        $lines = collect(preg_split('/\R+/', $text))
            ->map(fn($line) => trim($line))
            ->filter()
            ->values();

        $name = $lines->first() ?: '';
        $email = '';
        $mobile = '';

        if (preg_match('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', $text, $match)) {
            $email = $match[0];
        }

        if (preg_match('/(?:\+?\d[\d\s().-]{7,}\d)/', $text, $match)) {
            $mobile = trim($match[0]);
        }

        $location = $this->inferLocationFromLines($lines->all());

        $skills = $this->extractSectionItems($text, ['skills', 'technical skills', 'core skills']);
        $educationLines = $this->extractSectionItems($text, ['education', 'academic']);
        $experienceLines = $this->extractSectionItems($text, ['experience', 'work experience', 'professional experience']);
        $projectLines = $this->extractSectionItems($text, ['projects', 'project', 'portfolio', 'projects & accomplishments', 'selected projects']);
        $education = $this->buildEducationFromLines($educationLines);
        $projects = $this->buildProjectsFromLines($projectLines);
        $summarySection = $this->extractSectionBody($text, ['summary', 'professional summary', 'profile summary', 'objective', 'career objective']);

        $summaryLines = $lines
            ->reject(fn($line) => str_contains(strtolower($line), '@') || preg_match('/\+?\d[\d\s().-]{7,}/', $line))
            ->slice(1, 4)
            ->values()
            ->all();

        return $this->normalizeResume([
            'name' => $name,
            'email' => $email,
            'mobile' => $mobile,
            'location' => $location,
            'contact' => trim(implode(' | ', array_filter([$email, $mobile]))),
            'address' => $location,
            'summary' => $summarySection !== '' ? $summarySection : implode(' ', $summaryLines),
            'skills' => $skills,
            'experience' => [
                [
                    'company' => '',
                    'role' => '',
                    'points' => array_slice($experienceLines, 0, 8),
                ]
            ],
            'education' => $education,
            'projects' => $projects,
        ]);
    }

    private function extractSectionBody(string $text, array $headings): string
    {
        $pattern = '/(?:^|\n)(' . implode('|', array_map('preg_quote', $headings)) . ')\s*:?\s*\n(?<body>.*?)(?=\n[A-Z][A-Z &\/-]{2,}:?\s*\n|$)/is';
        if (!preg_match($pattern, $text, $match)) {
            return '';
        }

        $body = trim((string) ($match['body'] ?? ''));
        $body = preg_replace('/[ \t]+/', ' ', $body);
        $body = preg_replace('/\s*\n\s*/', "\n", (string) $body);

        return trim((string) $body);
    }

    private function buildEducationFromLines(array $lines): array
    {
        $rows = [];
        $current = null;

        foreach ($lines as $line) {
            $line = trim((string) $line);
            if ($line === '') {
                continue;
            }

            $isInstitution = preg_match('/\b(university|college|institute|school)\b/i', $line) === 1;
            $isYear = preg_match('/\b(19|20)\d{2}\b|present|^\d{4}\s*(?:-|to)\s*\d{4}$/i', $line) === 1;
            $isDegree = preg_match('/\b(b\.?tech|m\.?tech|bca|mca|b\.?sc|m\.?sc|bachelor|master|ph\.?d|diploma)\b/i', $line) === 1;
            $isCgpa = preg_match('/\b(cgpa|gpa|percentage|percent|marks?)\b/i', $line) === 1;
            $isLocation = preg_match('/\b(india|noida|delhi|mumbai|pune|hyderabad|chennai|kolkata|bengaluru|bangalore)\b/i', $line) === 1;

            if ($isDegree) {
                if ($current && collect($current)->filter()->isNotEmpty()) {
                    $rows[] = $current;
                }
                $current = ['degree' => $line, 'stream' => '', 'institution' => '', 'year' => ''];
                continue;
            }

            if ($isInstitution) {
                if (! $current) {
                    $current = ['degree' => '', 'stream' => '', 'institution' => $line, 'year' => ''];
                } elseif ($current['institution'] === '') {
                    $current['institution'] = $line;
                } else {
                    $rows[] = $current;
                    $current = ['degree' => '', 'stream' => '', 'institution' => $line, 'year' => ''];
                }
                continue;
            }

            if ($isYear) {
                if (! $current) {
                    $current = ['degree' => '', 'stream' => '', 'institution' => '', 'year' => $line];
                } else {
                    $current['year'] = $line;
                }
                continue;
            }

            if ($isCgpa || $isLocation) {
                if ($current && $current['institution'] !== '') {
                    $current['institution'] = trim($current['institution'].', '.$line);
                }
                continue;
            }

            if (! $current) {
                $current = ['degree' => $line, 'stream' => '', 'institution' => '', 'year' => ''];
                continue;
            }

            if ($current['stream'] === '') {
                $current['stream'] = $line;
            } elseif ($current['institution'] === '') {
                $current['institution'] = $line;
            } else {
                $current['degree'] = trim(($current['degree'] ? $current['degree'].' | ' : '').$line);
            }
        }

        if ($current && collect($current)->filter()->isNotEmpty()) {
            $rows[] = $current;
        }

        return array_values(array_filter($rows, fn ($r) => collect($r)->filter()->isNotEmpty()));
    }
    private function buildProjectsFromLines(array $lines): array
    {
        $projects = [];
        $current = null;

        foreach ($lines as $line) {
            $line = trim((string) $line);
            if ($line === '') {
                continue;
            }

            $isUrl = preg_match('/(?:https?:\/\/|www\.|[a-z0-9\-]+\.[a-z]{2,}(?:\/[^\s]*)?)/i', $line) === 1;
            $looksLikeHeader = $this->looksLikeProjectHeader($line) && ! $isUrl;

            if ($current === null || $looksLikeHeader) {
                if ($current !== null) {
                    $projects[] = $current;
                }

                [$title, $tech] = $this->splitProjectHeader($line);
                $current = [
                    'name' => $title,
                    'tech' => $tech,
                    'tech_stack' => $tech,
                    'description' => '',
                    'link' => '',
                ];
                continue;
            }

            if ($isUrl) {
                if ($current['link'] === '') {
                    $current['link'] = $line;
                } else {
                    $current['description'] = trim(($current['description'] ? $current['description'].' ' : '').$line);
                }
                continue;
            }

            $cleanLine = preg_replace('/^\s*(?:[-*]|\x{2022}|\x{25CF}|\x{25AA}|\x{25E6})\s*/u', '', $line);
            $current['description'] = trim(($current['description'] ? $current['description'].' ' : '').$cleanLine);
        }

        if ($current !== null) {
            $projects[] = $current;
        }

        return array_values(array_filter(array_map(function ($project) {
            $name = trim((string) ($project['name'] ?? ''));
            $tech = trim((string) ($project['tech_stack'] ?? $project['tech'] ?? ''));
            $link = trim((string) ($project['link'] ?? ''));
            $description = trim((string) ($project['description'] ?? ''));

            if ($name === '' && $tech === '' && $link === '' && $description === '') {
                return null;
            }

            return [
                'name' => $name,
                'tech' => $tech,
                'tech_stack' => $tech,
                'link' => $link,
                'description' => $description,
            ];
        }, $projects)));
    }

    private function looksLikeProjectHeader(string $line): bool
    {
        if (mb_strlen($line) < 3 || mb_strlen($line) > 170) {
            return false;
        }

        if (preg_match('/\b(project|app|system|platform|extension|shortener|dashboard|portal|website)\b/i', $line) === 1) {
            return true;
        }

        if (preg_match('/\s(?:-|\||\x{2013}|\x{2014}|\x{00B7})\s/u', $line) === 1) {
            return true;
        }

        return preg_match('/\b(react|node|spring|django|laravel|postgres|mysql|mongodb|next\.?js|express|api)\b/i', $line) === 1;
    }

    private function splitProjectHeader(string $line): array
    {
        $line = trim($line);
        $normalized = str_replace(["\u{2013}", "\u{2014}", "\u{00B7}"], ' - ', $line);
        $parts = preg_split('/\s+(?:-|\|)\s+/', $normalized);
        $parts = array_values(array_filter(array_map('trim', $parts), fn ($part) => $part !== ''));

        if (count($parts) < 2) {
            return [$line, ''];
        }

        $title = array_shift($parts) ?: '';
        $tech = implode(' · ', $parts);

        return [$title, $tech];
    }
    private function inferLocationFromLines(array $lines): string
    {
        $candidates = collect($lines)->take(10)->filter(function ($line) {
            $line = trim((string) $line);
            if ($line === '') {
                return false;
            }
            if (str_contains($line, '@') || preg_match('/\+?\d[\d\s().-]{7,}/', $line)) {
                return false;
            }
            if (preg_match('/\b(university|college|institute|school|cgpa|gpa|percentage|project|experience|education)\b/i', $line)) {
                return false;
            }

            return preg_match('/\b(?:india|usa|uk|remote|bengaluru|bangalore|mumbai|delhi|pune|hyderabad|chennai|kolkata|noida|gurgaon)\b/i', $line);
        });

        return trim((string) ($candidates->first() ?? ''));
    }

    private function extractSectionItems(string $text, array $headings): array
    {
        $pattern = '/(?:^|\n)(' . implode('|', array_map('preg_quote', $headings)) . ')\s*:?\s*\n(?<body>.*?)(?=\n[A-Z][A-Z &\/-]{2,}:?\s*\n|$)/is';

        if (!preg_match($pattern, $text, $match)) {
            return [];
        }

        $body = trim($match['body']);
        $body = preg_replace('/[\x{2022}\x{25CF}\x{25AA}\x{25E6}]/u', "\n", (string) $body);
        $parts = preg_split('/(?:\R|;)+/', (string) $body);

        return collect($parts)
            ->map(fn($item) => trim((string) $item))
            ->map(fn($item) => trim(preg_replace('/^\s*[-*]\s+/', '', $item)))
            ->filter(fn($item) => mb_strlen($item) > 1 && mb_strlen($item) < 220)
            ->take(24)
            ->values()
            ->all();
    }

    private function askGeminiForAnalysis(array $resume, string $jobRole, ?string $jobDescription, bool $refine = false, ?string $customInstruction = null): array
    {
        $key = config('services.gemini.key');
        $model = config('services.gemini.model', 'gemini-flash-latest');

        if (!$key) {
            return ['success' => false, 'message' => 'Gemini API key is not configured.'];
        }

        $instruction = $customInstruction ?: ($refine
            ? 'Refine the resume with better ATS formatting and clarity.'
            : 'Analyze and improve this resume for ATS optimization.');

        $prompt = <<<PROMPT
You are an ATS resume analyzer.

STRICT RULES:
- Return ONLY valid JSON
- No markdown
- No explanation text
- Only JSON response
- Do not invent LinkedIn, GitHub, portfolio links, employers, dates, schools, or metrics. Leave unknown fields empty.

FORMAT:
{
  "score": 0-100,
  "strengths": ["list of 3-5 specific strengths"],
  "weaknesses": ["list of 3-5 specific weaknesses"],
  "missing_keywords": ["list of 5-10 specific missing industry keywords"],
  "suggestions": ["specific actionable advice"],
  "improved_resume": {
    "name": "Full Name",
    "email": "Email",
    "mobile": "Phone",
    "location": "City, State",
    "social_links": ["LinkedIn URL", "GitHub URL"],
    "summary": "Professional summary...",
    "skills": ["Skill 1", "Skill 2", ...],
    "experience": [
      { 
        "company": "Company", 
        "role": "Title", 
        "period": "Jan 2020 - Present",
        "points": ["Achievement 1", "Achievement 2"] 
      }
    ],
    "education": [
      { "degree": "Degree", "stream": "Major", "institution": "University", "year": "2020" }
    ],
    "projects": [
      { "name": "Name", "tech": "Stack", "description": "Description" }
    ],
    "certifications": [
      { "name": "Certificate Name", "description": "Issuer or year" }
    ],
    "languages": [
      { "name": "Language", "level": "Proficiency" }
    ]
  }
}

CRITICAL:
1. "improved_resume" MUST contain all keys: name, email, mobile, location, social_links, summary, skills, experience, education, projects, certifications, languages.
2. "experience" items MUST have: company, role, period, points (array of bullets).
3. "education" items MUST have: degree, stream, institution, year.
4. "projects" items MUST have: name, tech, description.
5. If a section is missing in input, try to infer it from context or leave as empty array/string. DO NOT OMIT THE KEYS.

{$instruction}

Job Role: {$jobRole}

Job Description:
{$jobDescription}

Resume:
PROMPT;

        $prompt .= "\n" . json_encode($resume, JSON_UNESCAPED_SLASHES);

        try {
            $response = Http::request(90)

                ->retry(2, 500)
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
                            'temperature' => 0.3,
                            'maxOutputTokens' => 2500,
                        ],
                    ]
                );

            if (!$response->successful()) {
                $err = $response->json();
                $msg = Arr::get($err, 'error.message', 'API Error ' . $response->status());

                if ($response->status() === 429) {
                    return ['success' => false, 'message' => 'AI Rate limit exceeded. Please try again in a minute.'];
                }

                return ['success' => false, 'message' => $msg];
            }

            $text = Arr::get($response->json(), 'candidates.0.content.parts.0.text', '');

            if (!$text) {
                return ['success' => false, 'message' => 'Empty response from AI.'];
            }

            $analysis = $this->decodeGeminiJson($text);

            if (empty($analysis) || !isset($analysis['score'])) {
                return ['success' => false, 'message' => 'Could not parse AI response.'];
            }

            $improvedResume = Arr::get($analysis, 'improved_resume');

            if (!is_array($improvedResume) || !array_filter($improvedResume)) {
                $improvedResume = $resume;
            }

            return [
                'success' => true,
                'score' => max(0, min(100, (int) ($analysis['score'] ?? 50))),
                'strengths' => array_values($analysis['strengths'] ?? []),
                'weaknesses' => array_values($analysis['weaknesses'] ?? []),
                'missing_keywords' => array_values($analysis['missing_keywords'] ?? []),
                'suggestions' => array_values($analysis['suggestions'] ?? []),
                'improved_resume' => $improvedResume,
            ];

        } catch (\Exception $e) {
            \Log::error('Gemini API Exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'AI Connection Error: ' . $e->getMessage()];
        }
    }

    private function localAtsAnalysis(array $resume, string $jobRole = 'General', ?string $jobDescription = null): array
    {
        $resume = $this->normalizeResume($resume);
        $textParts = [
            $resume['summary'] ?? '',
            implode(' ', $resume['skills'] ?? []),
            collect($resume['experience'] ?? [])->pluck('points')->flatten()->join(' '),
            collect($resume['projects'] ?? [])->map(fn($p) => trim(($p['name'] ?? '') . ' ' . ($p['tech'] ?? '') . ' ' . ($p['description'] ?? '')))->join(' '),
            $jobDescription ?? '',
        ];
        $text = strtolower(implode(' ', $textParts));

        $roleKeywordMap = [
            'developer' => ['HTML', 'CSS', 'JavaScript', 'React', 'Node.js', 'API', 'Git', 'SQL', 'Testing', 'Responsive Design'],
            'designer' => ['Figma', 'User Research', 'Wireframes', 'Prototyping', 'Design Systems', 'Accessibility'],
            'marketing' => ['SEO', 'Analytics', 'Campaigns', 'Content Strategy', 'Conversion', 'CRM'],
            'sales' => ['CRM', 'Pipeline', 'Prospecting', 'Negotiation', 'Revenue', 'Client Relationships'],
            'data' => ['SQL', 'Python', 'Excel', 'Dashboard', 'Analytics', 'ETL', 'Visualization'],
            'general' => ['Communication', 'Leadership', 'Problem Solving', 'Collaboration', 'Ownership'],
        ];

        $role = strtolower($jobRole ?: 'general');
        $keywords = $roleKeywordMap['general'];
        foreach ($roleKeywordMap as $needle => $items) {
            if ($needle !== 'general' && str_contains($role, $needle)) {
                $keywords = $items;
                break;
            }
        }

        if ($jobDescription) {
            preg_match_all('/\b[A-Za-z][A-Za-z.+#-]{2,}\b/', $jobDescription, $matches);
            $jdTerms = collect($matches[0] ?? [])
                ->map(fn($term) => trim($term))
                ->reject(fn($term) => in_array(strtolower($term), ['and', 'the', 'for', 'with', 'you', 'our', 'are', 'will', 'this', 'that', 'from', 'your', 'have', 'has'], true))
                ->unique(fn($term) => strtolower($term))
                ->take(12)
                ->values()
                ->all();
            $keywords = array_values(array_unique(array_merge($jdTerms, $keywords)));
        }

        $missing = array_values(array_filter($keywords, fn($kw) => !str_contains($text, strtolower($kw))));
        $strengths = [];
        $weaknesses = [];
        $suggestions = [];
        $score = 20;

        if (!empty($resume['email']) && !empty($resume['mobile'])) {
            $score += 12;
            $strengths[] = 'Contact information includes email and phone.';
        } else {
            $weaknesses[] = 'Add complete contact information with email and phone.';
            $suggestions[] = 'Place email, phone, location, and relevant profile links in the header.';
        }

        $summaryWords = str_word_count(strip_tags((string) ($resume['summary'] ?? '')));
        if ($summaryWords >= 35) {
            $score += 14;
            $strengths[] = 'Professional summary has enough detail for recruiter scanning.';
        } else {
            $weaknesses[] = 'Professional summary is short or missing.';
            $suggestions[] = 'Write a 3-5 sentence summary with target role, core skills, domain, and business value.';
        }

        $skillsCount = count($resume['skills'] ?? []);
        if ($skillsCount >= 6) {
            $score += 14;
            $strengths[] = 'Skills section contains multiple ATS-readable keywords.';
        } else {
            $weaknesses[] = 'Skills section needs more role-specific keywords.';
            $suggestions[] = 'Add a dedicated skills section with tools, languages, frameworks, and methods from the job description.';
        }

        $experiencePoints = collect($resume['experience'] ?? [])->pluck('points')->flatten()->filter()->count();
        if ($experiencePoints >= 4) {
            $score += 16;
            $strengths[] = 'Experience section includes bullet-style detail.';
        } else {
            $weaknesses[] = 'Experience section lacks enough responsibility or impact bullets.';
            $suggestions[] = 'Add 3-5 bullets per role using action verbs, scope, tools, and measurable outcomes where true.';
        }

        if (count($resume['education'] ?? []) > 0) {
            $score += 8;
        } else {
            $weaknesses[] = 'Education section is missing.';
            $suggestions[] = 'Add degree, institution, field of study, and graduation year if available.';
        }

        if (count($resume['projects'] ?? []) > 0) {
            $score += 8;
            $strengths[] = 'Projects are present, which helps demonstrate practical work.';
        } else {
            $weaknesses[] = 'Projects section is missing.';
            $suggestions[] = 'Add 1-3 relevant projects with tech stack, your role, and outcome.';
        }

        $matchedKeywords = count($keywords) - count($missing);
        $score += min(18, $matchedKeywords * 3);

        foreach (array_slice($missing, 0, 8) as $kw) {
            $suggestions[] = "Consider adding '{$kw}' where it accurately reflects your experience.";
        }

        return [
            'success' => true,
            'score' => max(0, min(100, $score)),
            'strengths' => array_values(array_unique($strengths ?: ['Resume content is readable and can be parsed for ATS analysis.'])),
            'weaknesses' => array_values(array_unique($weaknesses)),
            'missing_keywords' => array_slice($missing, 0, 10),
            'suggestions' => array_values(array_unique($suggestions)),
            'improved_resume' => $resume,
        ];
    }

    private function decodeGeminiJson(string $text): array
    {
        if (!$text) {
            return $this->fallbackResponse();
        }

        $candidate = trim($text);

        // Remove common markdown fences
        $candidate = preg_replace('/```(?:json)?/i', '', $candidate);
        $candidate = str_replace('```', '', $candidate);

        // Extract first {...} block to avoid extra commentary
        $start = strpos($candidate, '{');
        $end = strrpos($candidate, '}');
        if ($start !== false && $end !== false && $end > $start) {
            $candidate = substr($candidate, $start, $end - $start + 1);
        }

        $decoded = json_decode($candidate, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        // Fallback: non-greedy search for a JSON object
        if (preg_match('/\{.*?\}/s', $candidate, $m)) {
            $decoded = json_decode($m[0], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return $this->fallbackResponse();
    }
    private function fallbackResponse(array $resume = []): array
    {
        return [
            'score' => 50,
            'strengths' => [],
            'weaknesses' => [],
            'missing_keywords' => [],
            'suggestions' => [],
            'improved_resume' => $this->normalizeResume($resume),
        ];
    }

    private function normalizeResume(array $resume): array
    {
        $safeStr = function ($val) {
            if (is_array($val))
                return json_encode($val);
            return (string) $val;
        };
        $safeSocial = fn($val) => preg_match('/(linkedin\.com\/in\/(?:alex|you)|github\.com\/(?:alex|you))/i', (string) $val)
            ? ''
            : $safeStr($val);

        $normalized = [
            'name' => (string) ($resume['name'] ?? ''),
            'email' => (string) ($resume['email'] ?? ''),
            'mobile' => (string) ($resume['mobile'] ?? $resume['contact'] ?? ''),
            'location' => (string) ($resume['location'] ?? $resume['address'] ?? ''),
            'contact' => (string) ($resume['contact'] ?? $resume['mobile'] ?? ''),
            'address' => (string) ($resume['address'] ?? $resume['location'] ?? ''),
            'social_links' => array_values(array_filter(array_map($safeSocial, $resume['social_links'] ?? []))),
            'summary' => (string) ($resume['summary'] ?? ''),
            'profile_image' => (string) ($resume['profile_image'] ?? ''),
            'skills' => array_values(array_filter(array_map($safeStr, $resume['skills'] ?? []))),
            'experience' => array_values(array_map(function ($item) use ($safeStr) {
                return [
                    'company' => (string) ($item['company'] ?? ''),
                    'role' => (string) ($item['role'] ?? ''),
                    'period' => (string) ($item['period'] ?? $item['duration'] ?? ''),
                    'points' => array_values(array_filter(array_map($safeStr, $item['points'] ?? []))),
                ];
            }, $resume['experience'] ?? [])),
            'education' => array_values(array_map(function ($item) {
                if (!is_array($item)) {
                    return [
                        'degree' => (string) $item,
                        'stream' => '',
                        'institution' => '',
                        'year' => '',
                    ];
                }
                return [
                    'degree' => (string) ($item['degree'] ?? ''),
                    'stream' => (string) ($item['stream'] ?? $item['field'] ?? $item['specialization'] ?? ''),
                    'institution' => (string) ($item['institution'] ?? ''),
                    'year' => (string) ($item['year'] ?? ''),
                ];
            }, $resume['education'] ?? [])),
            'projects' => array_values(array_map(function ($item) {
                if (!is_array($item)) {
                    return [
                        'name' => (string) $item,
                        'tech' => '',
                        'tech_stack' => '',
                        'link' => '',
                        'description' => '',
                    ];
                }
                return [
                    'name' => (string) ($item['name'] ?? ''),
                    'tech' => (string) ($item['tech'] ?? $item['tech_stack'] ?? ''),
                    'tech_stack' => (string) ($item['tech_stack'] ?? $item['tech'] ?? ''),
                    'link' => (string) ($item['link'] ?? $item['url'] ?? ''),
                    'description' => (string) ($item['description'] ?? ''),
                ];
            }, $resume['projects'] ?? [])),
            'certifications' => $this->normalizeNamedItems($resume['certifications'] ?? $resume['certificates'] ?? []),
            'certificates' => $this->normalizeNamedItems($resume['certifications'] ?? $resume['certificates'] ?? []),
            'languages' => $this->normalizeLanguages($resume['languages'] ?? []),
            'achievements' => $this->normalizeNamedItems($resume['achievements'] ?? []),
        ];

        $primaryColor = trim((string) ($resume['primary_color'] ?? ''));
        if ($primaryColor !== '') {
            $normalized['primary_color'] = $primaryColor;
            $normalized['primary_color_customized'] = filter_var($resume['primary_color_customized'] ?? true, FILTER_VALIDATE_BOOLEAN);
        }

        return $normalized;
    }

    private function normalizeNamedItems(array|string|null $items): array
    {
        if ($items === null) {
            return [];
        }

        $items = is_array($items) ? $items : explode(',', $items);

        return array_values(array_filter(array_map(function ($item) {
            if (is_array($item)) {
                $name = trim((string) ($item['name'] ?? ''));
                $description = trim((string) ($item['description'] ?? $item['details'] ?? ''));

                if ($name === '' && $description === '') {
                    return null;
                }

                return compact('name', 'description');
            }

            $name = trim((string) $item);

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
                $name = trim((string) ($item['name'] ?? $item['language'] ?? ''));
                $level = trim((string) ($item['level'] ?? $item['proficiency'] ?? ''));

                if ($name === '' && $level === '') {
                    return null;
                }

                return compact('name', 'level');
            }

            $name = trim((string) $item);

            return $name === '' ? null : ['name' => $name, 'level' => ''];
        }, $items)));
    }

    private function findAuthorizedAnalysis(Request $request, int $id): ResumeAnalysis
    {
        $analysis = ResumeAnalysis::findOrFail($id);

        if ($request->user() && $analysis->user_id === $request->user()->id) {
            return $analysis;
        }

        if (!$analysis->user_id && $analysis->session_id === $request->session()->getId()) {
            return $analysis;
        }

        abort(403, 'You do not have access to this resume analysis.');
    }

    private function razorpay(): Api
    {
        $key = config('services.razorpay.key');
        $secret = config('services.razorpay.secret');

        if (!$key || !$secret) {
            abort(500, 'Razorpay credentials are not configured.');
        }

        return new Api($key, $secret);
    }

    private function pdfFilename(string $name): string
    {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($name)) ?: 'resume');

        return trim($slug, '-') . '-improved-resume.pdf';
    }
}

