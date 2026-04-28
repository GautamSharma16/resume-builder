<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use App\Models\Template;
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
        return view('resume.create', [
            'templates' => Template::where('type', 'resume')->where('is_active', true)->get(),
            'selectedTemplate' => $request->query('template'),
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

        return view('resume.edit', ['resume' => $resume]);
    }

    public function update(Request $request, Resume $resume)
    {
        $this->authorizeResume($resume);

        $validated = $request->validate(['resume' => ['required', 'array']]);
        $resume->update(['data' => $this->normalizeResume($validated['resume'])]);

        return response()->json(['ok' => true]);
    }

    public function preview(Resume $resume)
    {
        $this->authorizeResume($resume);

        return view('resume.preview', ['resume' => $resume]);
    }

    public function download(Request $request, Resume $resume, string $format = 'pdf')
    {
        $this->authorizeResume($resume);

        if (! $request->user()) {
            return redirect()->route('login');
        }

        if (! $resume->is_paid && ! $request->user()->subscription?->plan) {
            return redirect()->route('plans')->with('status', 'Choose a plan to unlock downloads.');
        }

        $html = view('resume.pdf', ['resume' => $this->normalizeResume($resume->data)])->render();
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
            'contact' => (string) ($resume['contact'] ?? ''),
            'address' => (string) ($resume['address'] ?? ''),
            'summary' => (string) ($resume['summary'] ?? ''),
            'skills' => array_values(array_filter(array_map('strval', $resume['skills'] ?? []))),
            'experience' => array_values($resume['experience'] ?? []),
            'education' => array_values(array_filter(array_map('strval', $resume['education'] ?? []))),
        ];
    }
}
