<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use App\Models\ResumeAnalysis;
use App\Models\Template;
use App\Services\PlanActivationService;
use App\Services\PendingDownloadService;
use App\Services\PdfConversionService;
use App\Services\TemplateRenderService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class ResumeBuilderController extends Controller
{
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
            'download_format' => ['nullable', 'in:pdf,doc,ppt'],
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
        $key = config('services.gemini.key');
        $model = config('services.gemini.model', 'gemini-flash-latest');

        if (! $key) {
            return '';
        }

        try {
             $response = Http::timeout(45)
                ->retry(1, 400)
                ->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=".urlencode($key),
                    [
                        'contents' => [
                            ['parts' => [['text' => $prompt]]],
                        ],
                        'generationConfig' => [
                            'temperature' => 0.94,
                            'topP' => 0.92,
                            'topK' => 40,
                            'maxOutputTokens' => 260,
                        ],
                    ]
                );

            if (! $response->successful()) {
                \Log::warning('Resume maker AI text failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return '';
            }

            $text = (string) Arr::get($response->json(), 'candidates.0.content.parts.0.text', '');
            $text = preg_replace('/```(?:text|markdown)?/i', '', $text);
            $text = str_replace('```', '', $text);
            $text = preg_replace('/^\s*[-*]\s*/m', '', $text);

            return trim($text);
        } catch (\Throwable $e) {
            \Log::warning('Resume maker AI text exception: '.$e->getMessage());

            return '';
        }
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
            'desired_job_role' => $this->toText($resume['desired_job_role'] ?? ''),
            'linkedin' => $this->toText($resume['linkedin'] ?? ''),
            'portfolio' => $this->toText($resume['portfolio'] ?? $resume['link'] ?? ''),
            'link' => $this->toText($resume['link'] ?? $resume['portfolio'] ?? ''),
            'github' => $this->toText($resume['github'] ?? ''),
            'tech_stack' => $this->toText($resume['tech_stack'] ?? ''),
            'skills' => $this->normalizeArray($resume['skills'] ?? []),
            'experience' => $this->normalizeNestedItems($resume['experience'] ?? []),
            'education' => $this->normalizeEducation($resume['education'] ?? []),
            'projects' => $this->normalizeNestedItems($resume['projects'] ?? []),
            'social_links' => array_values(array_filter(
                $this->normalizeArray($resume['social_links'] ?? []),
                fn ($link) => ! preg_match('/(linkedin\.com\/in\/(?:alex|you)|github\.com\/(?:alex|you))/i', $link)
            )),
            'certifications' => $this->normalizeNamedItems($resume['certifications'] ?? $resume['certificates'] ?? []),
            'certificates' => $this->normalizeNamedItems($resume['certifications'] ?? $resume['certificates'] ?? []),
            'languages' => $this->normalizeLanguages($resume['languages'] ?? []),
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
                $name = $this->toText($item['name'] ?? '');
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
