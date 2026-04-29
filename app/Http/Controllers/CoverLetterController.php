<?php

namespace App\Http\Controllers;

use App\Models\CoverLetter;
use App\Models\Resume;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Arr;

class CoverLetterController extends Controller
{
    public function create()
    {
        return view('pages.cover-letter', [
            'resumes' => Resume::where('user_id', auth()->id())->latest()->get(),
            'templates' => Template::where('type', 'cover_letter')->where('is_active', true)->get(),
        ]);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'resume_id' => ['nullable', 'exists:resumes,id'],
            'name' => ['required_without:resume_id', 'nullable', 'string', 'max:160'],
            'company' => ['nullable', 'string', 'max:160'],
            'job_role' => ['required', 'string', 'max:160'],
            'job_description' => ['nullable', 'string', 'max:8000'],
        ]);

        $resume = isset($validated['resume_id']) ? Resume::find($validated['resume_id']) : null;
        $name = $resume ? Arr::get($resume->data, 'name', '') : ($validated['name'] ?? '');
        $body = $this->generateWithGemini($name, $validated['job_role'], $validated['company'] ?? '', $validated['job_description'] ?? '', $resume?->data ?? []);

        $letter = CoverLetter::create([
            'user_id' => $request->user()?->id,
            'session_id' => $request->session()->getId(),
            'resume_id' => $resume?->id,
            'job_role' => $validated['job_role'],
            'company' => $validated['company'] ?? null,
            'data' => [
                'name' => $name,
                'company' => $validated['company'] ?? '',
                'job_role' => $validated['job_role'],
                'body' => $body,
            ],
        ]);

        return response()->json(['cover_letter_id' => $letter->id, 'letter' => $letter->data]);
    }

    public function save(Request $request, CoverLetter $coverLetter)
    {
        $this->authorizeLetter($coverLetter);
        $validated = $request->validate(['letter' => ['required', 'array']]);
        $coverLetter->update(['data' => $validated['letter']]);

        return response()->json(['ok' => true]);
    }

    public function download(Request $request, CoverLetter $coverLetter, string $format = 'pdf')
    {
        $this->authorizeLetter($coverLetter);

        if (! $request->user()) {
            return redirect()->route('login');
        }

        if (! $coverLetter->is_paid && ! $request->user()->subscription?->plan) {
            return redirect()->route('plans')->with('status', 'Choose a plan to unlock downloads.');
        }

        $html = view('cover-letter.pdf', ['letter' => $coverLetter->data])->render();
        $filename = 'cover-letter-'.$coverLetter->id;

        if ($format === 'doc') {
            return response($html, 200, ['Content-Type' => 'application/msword', 'Content-Disposition' => "attachment; filename={$filename}.doc"]);
        }

        if ($format === 'ppt') {
            return response($html, 200, ['Content-Type' => 'application/vnd.ms-powerpoint', 'Content-Disposition' => "attachment; filename={$filename}.ppt"]);
        }

        return \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4')->download("{$filename}.pdf");
    }

    private function generateWithGemini(string $name, string $role, string $company, string $description, array $resume): string
    {
        $key = config('services.gemini.key');

        if (! $key) {
            return "Dear Hiring Manager,\n\nI am excited to apply for the {$role} role".($company ? " at {$company}" : '').". My background and skills align well with the requirements, and I would welcome the opportunity to contribute measurable value.\n\nSincerely,\n{$name}";
        }
        
        $prompt = "Write a concise professional cover letter. Return only JSON: {\"body\":\"...\"}.\nName: {$name}\nRole: {$role}\nCompany: {$company}\nJob Description: {$description}\nResume JSON: ".json_encode($resume);
        $model = config('services.gemini.model');
        $response = Http::timeout(60)->post("https://generativelanguage.googleapis.com/v1/models/{$model}:generateContent?key=".urlencode($key), [
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => ['temperature' => 0.35, 'maxOutputTokens' => 1500],
        ]);

        $text = Arr::get($response->json(), 'candidates.0.content.parts.0.text', '{}');
        $json = json_decode($text, true);

        return (string) ($json['body'] ?? "Dear Hiring Manager,\n\nI am excited to apply for the {$role} role.\n\nSincerely,\n{$name}");
    }

    private function authorizeLetter(CoverLetter $coverLetter): void
    {
        if ($coverLetter->user_id && $coverLetter->user_id !== auth()->id()) {
            abort(403);
        }
    }
}
