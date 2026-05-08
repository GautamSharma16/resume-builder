<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use App\Models\ResumeAnalysis;
use App\Models\Template;
use App\Services\PlanActivationService;
use App\Services\TemplateRenderService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

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

        $resume = Resume::create([
            'user_id' => $request->user()?->id,
            'session_id' => $request->session()->getId(),
            'template_id' => $validated['template_id'] ?? null,
            'title' => Arr::get($validated, 'resume.name', 'Untitled Resume'),
            'source' => $validated['source'],
            'data' => $this->normalizeResume($validated['resume']),
        ]);

        return response()->json([
            'resume' => ['id' => $resume->id],
            'redirect' => $request->user() ? null : route('login').'?redirect='.urlencode(route('resume.edit', $resume))
        ]);
    }

    public function edit(Resume $resume)
    {
        $this->authorizeResume($resume);

        $templates = Template::where('type', 'resume')->where('is_active', true)->get();

        return view('resume.edit', [
            'resume' => $resume,
            'templates' => $templates,
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

        $renderedTemplate = $resume->template
            ? app(TemplateRenderService::class)->renderResume($resume->template, $resume->data)
            : null;

        return view('resume.preview', ['resume' => $resume, 'renderedTemplate' => $renderedTemplate]);
    }

    public function download(Request $request, Resume $resume, string $format = 'pdf')
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

        $normalized = $this->normalizeResume($resume->data);
        $html = $resume->template
            ? view('templates.rendered-document', ['html' => app(TemplateRenderService::class)->renderResume($resume->template, $normalized)])->render()
            : view('resume.pdf', ['resume' => $normalized])->render();
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

        return \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4')->download("{$filename}.pdf");
    }

    private function authorizeResume(Resume $resume): void
    {
        if ($resume->user_id && $resume->user_id !== auth()->id()) {
            abort(403);
        }
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
            'linkedin' => $this->toText($resume['linkedin'] ?? ''),
            'github' => $this->toText($resume['github'] ?? ''),
            'tech_stack' => $this->toText($resume['tech_stack'] ?? ''),
            'skills' => $this->normalizeArray($resume['skills'] ?? []),
            'experience' => $this->normalizeNestedItems($resume['experience'] ?? []),
            'education' => $this->normalizeArray($resume['education'] ?? []),
            'projects' => $this->normalizeNestedItems($resume['projects'] ?? []),
            'social_links' => $this->normalizeArray($resume['social_links'] ?? []),
            'certifications' => $this->normalizeArray($resume['certifications'] ?? []),
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
                return collect($item)->map(function ($value) {
                    return is_array($value) ? $this->normalizeArray($value) : $this->toText($value);
                })->all();
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
