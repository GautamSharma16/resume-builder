<?php

namespace App\Http\Controllers;

use App\Models\CoverLetter;
use App\Models\Resume;
use App\Models\Template;
use App\Services\PlanActivationService;
use App\Services\TemplateRenderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Arr;

class CoverLetterController extends Controller
{
    public function create(TemplateRenderService $renderer)
    {
        $templates = Template::where('type', 'cover_letter')->where('is_active', true)->get();
        $user = auth()->user();
        $sample = $renderer->coverLetterSampleData([
            'name' => $user?->name ?: 'John Doe',
            'email' => $user?->email ?: 'john.doe@example.com',
            'mobile' => $user?->mobile ?: '+91 98765 43210',
        ]);

        return view('pages.cover-letter', [
            'resumes' => Resume::where('user_id', auth()->id())->latest()->get(),
            'templates' => $templates,
            'renderedTemplates' => $templates->mapWithKeys(fn (Template $template) => [
                $template->id => (string) $renderer->renderCoverLetter($template, $sample),
            ]),
            'prefill' => $sample,
        ]);
    }

    public function generate(Request $request)
    {
        try {
            $validated = $request->validate([
                'resume_id' => ['nullable', 'exists:resumes,id'],
                'template_id' => ['nullable', 'exists:templates,id'],
                'name' => ['required_without:resume_id', 'nullable', 'string', 'max:160'],
                'email' => ['nullable', 'email', 'max:190'],
                'mobile' => ['nullable', 'string', 'max:30'],
                'location' => ['nullable', 'string', 'max:160'],
                'company' => ['nullable', 'string', 'max:160'],
                'company_name' => ['nullable', 'string', 'max:160'],
                'job_role' => ['nullable', 'string', 'max:160'],
                'skills' => ['nullable', 'string', 'max:500'],
                'job_description' => ['nullable', 'string', 'max:8000'],
            ]);

            $resume = isset($validated['resume_id']) ? Resume::find($validated['resume_id']) : null;
            $name = $resume ? Arr::get($resume->data, 'name', '') : ($validated['name'] ?? '');
            $company = $validated['company_name'] ?? $validated['company'] ?? '';
            
            $result = $this->generateWithGemini($name, $validated['job_role'], $company, $validated['job_description'] ?? '', $resume?->data ?? [], $validated['skills'] ?? '');

            if (!Arr::get($result, 'success', true)) {
                return response()->json(['success' => false, 'message' => Arr::get($result, 'message', 'AI Generation failed.')], 500);
            }

            $body = Arr::get($result, 'body');

            $letter = CoverLetter::create([
                'user_id' => $request->user()?->id,
                'session_id' => $request->session()->getId(),
                'template_id' => $validated['template_id'] ?? null,
                'resume_id' => $resume?->id,
                'job_role' => $validated['job_role'],
                'company' => $company ?: null,
                'data' => [
                    'name' => $name,
                    'email' => $validated['email'] ?? $request->user()?->email ?? '',
                    'mobile' => $validated['mobile'] ?? $request->user()?->mobile ?? '',
                    'location' => $validated['location'] ?? '',
                    'company' => $company,
                    'company_name' => $company,
                    'job_role' => $validated['job_role'],
                    'skills' => $validated['skills'] ?? '',
                    'body' => $body,
                ],
            ]);

            return response()->json(['success' => true, 'cover_letter_id' => $letter->id, 'letter' => $letter->data]);
        } catch (\Exception $e) {
            \Log::error('Cover Letter Generation Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to generate cover letter: ' . $e->getMessage()], 500);
        }
    }

    public function save(Request $request, CoverLetter $coverLetter)
    {
        $this->authorizeLetter($coverLetter);
        $validated = $request->validate(['letter' => ['required', 'array']]);
        $coverLetter->update([
            'template_id' => $validated['letter']['template_id'] ?? $coverLetter->template_id,
            'data' => $validated['letter'],
        ]);

        return response()->json(['ok' => true]);
    }

    public function download(Request $request, CoverLetter $coverLetter, string $format = 'pdf')
    {
        $this->authorizeLetter($coverLetter);

        if (! $request->user()) {
            return redirect()->route('login');
        }

        if (! $coverLetter->is_paid && ! $request->user()->activeSubscription?->hasDownloadsRemaining()) {
            return redirect()->route('plans')->with('status', 'Choose a plan to unlock downloads.');
        }

        if (! $coverLetter->is_paid) {
            app(PlanActivationService::class)->consumeDownload($request->user());
            $coverLetter->forceFill(['is_paid' => true])->save();
        }

        $html = $coverLetter->template
            ? view('templates.rendered-document', ['html' => app(TemplateRenderService::class)->renderCoverLetter($coverLetter->template, $coverLetter->data)])->render()
            : view('cover-letter.pdf', ['letter' => $coverLetter->data])->render();
        $filename = 'cover-letter-'.$coverLetter->id;

        if ($format === 'doc') {
            return response($html, 200, ['Content-Type' => 'application/msword', 'Content-Disposition' => "attachment; filename={$filename}.doc"]);
        }

        if ($format === 'ppt') {
            return response($html, 200, ['Content-Type' => 'application/vnd.ms-powerpoint', 'Content-Disposition' => "attachment; filename={$filename}.ppt"]);
        }

        return \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4')->download("{$filename}.pdf");
    }

    private function generateWithGemini(string $name, string $role, string $company, string $description, array $resume, string $skills = ''): array
    {
        $key = config('services.gemini.key');
        $model = config('services.gemini.model', 'gemini-flash-latest');

        if (! $key) {
            return [
                'success' => false,
                'message' => 'Gemini API key not configured.',
                'body' => "Dear Hiring Manager,\n\nI am excited to apply for the {$role} role".($company ? " at {$company}" : '').". My background in {$skills} aligns well with the requirements, and I would welcome the opportunity to contribute measurable value.\n\nSincerely,\n{$name}"
            ];
        }
        
        $prompt = "Write a concise professional cover letter. Return only JSON: {\"body\":\"...\"}.\nName: {$name}\nRole: {$role}\nCompany: {$company}\nSkills: {$skills}\nJob Description: {$description}\nResume JSON: ".json_encode($resume);
        
        try {
            $response = Http::timeout(60)->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=".urlencode($key), [
                'contents' => [['parts' => [['text' => $prompt]]]],
                'generationConfig' => ['temperature' => 0.35, 'maxOutputTokens' => 1500],
            ]);

            if (!$response->successful()) {
                if ($response->status() === 429) {
                    return ['success' => false, 'message' => 'AI Rate limit exceeded.'];
                }
                return ['success' => false, 'message' => 'AI generation failed with status ' . $response->status()];
            }

            $text = Arr::get($response->json(), 'candidates.0.content.parts.0.text', '');
            
            if (!$text) {
                return ['success' => false, 'message' => 'Empty response from AI.'];
            }

            $json = $this->decodeGeminiJson($text);

            if (empty($json) || !isset($json['body'])) {
                return ['success' => false, 'message' => 'Could not parse AI response.'];
            }

            $body = $json['body'];
            if (is_array($body)) {
                $body = implode("\n\n", array_map(function($val) {
                    return is_array($val) ? json_encode($val) : (string) $val;
                }, $body));
            }

            return [
                'success' => true,
                'body' => (string) $body
            ];
        } catch (\Exception $e) {
            \Log::error('Gemini Cover Letter Exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'AI Connection Error: ' . $e->getMessage()];
        }
    }

    private function decodeGeminiJson(string $text): array
    {
        if (! $text) {
            return [];
        }

        $candidate = trim($text);
        $candidate = preg_replace('/```(?:json)?/i', '', $candidate);
        $candidate = str_replace('```', '', $candidate);

        $start = strpos($candidate, '{');
        $end = strrpos($candidate, '}');
        if ($start !== false && $end !== false && $end > $start) {
            $candidate = substr($candidate, $start, $end - $start + 1);
        }

        $decoded = json_decode($candidate, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\{.*?\}/s', $candidate, $m)) {
            $decoded = json_decode($m[0], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    private function authorizeLetter(CoverLetter $coverLetter): void
    {
        if ($coverLetter->user_id && $coverLetter->user_id !== auth()->id()) {
            abort(403);
        }
    }
}
