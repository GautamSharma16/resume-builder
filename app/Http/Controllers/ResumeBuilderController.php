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

        return response()->json(['resume_id' => $resume->id, 'redirect' => route('resume.edit', $resume)]);
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
            ? app(TemplateRenderService::class)->renderResume($resume->template, $this->normalizeResume($resume->data))
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
        return [
            'name' => (string) ($resume['name'] ?? ''),
            'mobile' => (string) ($resume['mobile'] ?? $resume['contact'] ?? ''),
            'email' => (string) ($resume['email'] ?? ''),
            'location' => (string) ($resume['location'] ?? $resume['address'] ?? ''),
            'contact' => (string) ($resume['contact'] ?? $resume['mobile'] ?? ''),
            'address' => (string) ($resume['address'] ?? $resume['location'] ?? ''),
            'summary' => (string) ($resume['summary'] ?? ''),
            'skills' => array_values(array_filter(array_map('strval', $resume['skills'] ?? []))),
            'experience' => array_values(array_map(function ($item) {
                return [
                    'company' => (string) ($item['company'] ?? ''),
                    'role' => (string) ($item['role'] ?? ''),
                    'period' => (string) ($item['period'] ?? ''),
                    'points' => array_values(array_filter(array_map('strval', $item['points'] ?? []))),
                ];
            }, $resume['experience'] ?? [])),
            'education' => array_values(array_filter(array_map('strval', $resume['education'] ?? []))),
            'projects' => array_values(array_filter(array_map('strval', $resume['projects'] ?? []))),
            'social_links' => array_values($resume['social_links'] ?? []),
        ];
    }
}
