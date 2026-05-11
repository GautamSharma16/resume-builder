<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use App\Models\ResumeAnalysis;
use App\Models\Template;
use App\Services\PlanActivationService;
use App\Services\PdfConversionService;
use App\Services\TemplateRenderService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class ResumeBuilderController extends Controller
{
    public function index()
    {
        return view('resume.index', [
            'resumes' => Resume::where('user_id', auth()->id())->latest()->get(),
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
        ]);

        $normalizedResume = $this->normalizeResume($validated['resume']);
        $resume = Resume::create([
            'user_id' => $request->user()?->id,
            'session_id' => $request->session()->getId(),
            'template_id' => $validated['template_id'] ?? null,
            'title' => trim(($normalizedResume['name'] ?? '').' '.($normalizedResume['last_name'] ?? '')) ?: 'Untitled Resume',
            'source' => $validated['source'],
            'data' => $normalizedResume,
        ]);

        if (! $request->user()) {
            $request->session()->put('pending_resume_id', $resume->id);
        }

        return response()->json([
            'resume' => ['id' => $resume->id],
            'redirect' => $request->user() ? null : route('login').'?redirect='.urlencode(route('plans'))
        ]);
    }

    public function generateAiText(Request $request)
    {
        $validated = $request->validate([
            'context' => ['required', 'in:summary,experience'],
            'resume' => ['nullable', 'array'],
            'text' => ['nullable', 'string', 'max:5000'],
        ]);

        $resume = $this->normalizeResume($validated['resume'] ?? []);
        $context = $validated['context'];
        $existingText = $this->toText($validated['text'] ?? '');
        $jobTitle = $this->toText($resume['job_title'] ?? '');
        $skills = implode(', ', $resume['skills'] ?? []);
        $experience = collect($resume['experience'] ?? [])
            ->map(fn ($item) => trim(($item['role'] ?? '').' at '.($item['company'] ?? '')))
            ->filter()
            ->join('; ');

        $education = collect($resume['education'] ?? [])
            ->map(fn ($item) => is_array($item)
                ? trim(collect([$item['degree'] ?? '', $item['stream'] ?? '', $item['institution'] ?? '', $item['year'] ?? ''])->filter()->join(', '))
                : $this->toText($item))
            ->filter()
            ->join('; ');

        $prompt = $context === 'summary'
            ? "Write one polished resume professional summary using the candidate data below. Make it 75-110 words in one strong paragraph, with specific role, skills, work context, and value delivered. Do not invent employers, years, degrees, certifications, links, metrics, or tools that are not present. No heading, no markdown, no placeholders."
            : "Rewrite this resume experience entry into 3-5 strong responsibility or achievement lines. Use action verbs and measurable impact only when supported by the candidate data. Do not invent employers, dates, tools, or metrics. No heading, no markdown bullets; separate each line with a newline.";

        $prompt .= "\n\nCandidate name: ".trim(($resume['name'] ?? '').' '.($resume['last_name'] ?? ''));
        $prompt .= "\nTarget role: ".$jobTitle;
        $prompt .= "\nSkills: ".$skills;
        $prompt .= "\nExperience context: ".$experience;
        $prompt .= "\nEducation context: ".$education;
        $prompt .= "\nExisting text: ".$existingText;

        $generated = $this->callGeminiForText($prompt);

        if ($generated === '' || ($context === 'summary' && str_word_count(strip_tags($generated)) < 55)) {
            $generated = $this->buildLocalAiText($context, $resume, $existingText);
        }

        return response()->json(['text' => $generated]);
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

    public function preview(Resume $resume)
    {
        $this->authorizeResume($resume);

        $renderedTemplate = null;
        if ($resume->template) {
            $rendered = app(TemplateRenderService::class)->renderResume($resume->template, $resume->data);
            $renderedTemplate = view('templates.rendered-document', ['html' => $rendered])->render();
        }

        return view('resume.preview', ['resume' => $resume, 'renderedTemplate' => $renderedTemplate]);
    }

    public function download(Request $request, Resume $resume, PdfConversionService $pdfConversionService, string $format = 'pdf')
    {
        $this->authorizeResume($resume);

        if (! $request->user()) {
            return redirect()->route('login');
        }

        if (! $resume->is_paid && ! $request->user()->activeSubscription?->hasDownloadsRemaining()) {
            return redirect()->route('plans')->with('status', 'Choose a plan to unlock downloads.');
        }

        if (! $resume->is_paid) {
            app(PlanActivationService::class)->consumeDownload($request->user());
            $resume->forceFill(['is_paid' => true])->save();
        }

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
                            'temperature' => 0.35,
                            'maxOutputTokens' => 520,
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
                return $base->map(fn ($line) => 'Improved and delivered '.$line)->join("\n");
            }

            return implode("\n", [
                "Built and maintained {$role} workflows with attention to quality, timelines, and user needs.",
                "Collaborated with stakeholders to translate requirements into reliable, production-ready outcomes.",
                "Used {$skillsText} to improve delivery quality and support measurable business goals.",
            ]);
        }

        $company = is_array($experience) ? $this->toText($experience['company'] ?? '') : '';
        $contextLine = $company ? "with experience at {$company}" : 'with practical experience across professional projects';

        return "{$role} {$contextLine}, skilled in {$skillsText}. Brings a disciplined, detail-oriented approach to understanding requirements, building dependable solutions, and improving user-facing outcomes. Experienced in collaborating with teams, organizing work clearly, and turning business needs into polished deliverables. Focused on continuous learning, clean execution, and contributing meaningful value to teams that need reliable ownership, strong communication, and consistent delivery.";
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
            'linkedin' => $this->toText($resume['linkedin'] ?? ''),
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
            'certifications' => $this->normalizeArray($resume['certifications'] ?? []),
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
