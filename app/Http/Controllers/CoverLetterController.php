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
use ZipArchive;

class CoverLetterController extends Controller
{
    public function create(TemplateRenderService $renderer)
    {
        $templates = Template::where('type', 'cover_letter')->where('is_active', true)->get();
        $sample = $renderer->coverLetterSampleData();
        
        $user = auth()->user();
        $prefill = $sample;

        if ($user) {
            $latestResume = \App\Models\Resume::where('user_id', $user->id)->latest()->first();
            if ($latestResume && !empty($latestResume->data)) {
                $prefill = $renderer->coverLetterSampleData($latestResume->data);
            }
        }

        return view('pages.cover-letter', [
            'resumes' => Resume::where('user_id', auth()->id())->latest()->get(),
            'templates' => $templates,
            'renderedTemplates' => $templates->mapWithKeys(fn (Template $template) => [
                $template->id => (string) $renderer->renderCoverLetter($template, $sample),
            ]),
            'prefill' => $prefill,
        ]);
    }

    public function generate(Request $request)
    {
        try {
            $validated = $request->validate([
                'resume_id' => ['nullable', 'exists:resumes,id'],
                'resume_file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
                'template_id' => ['nullable', 'exists:templates,id'],
                'name' => ['nullable', 'string', 'max:160'],
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
            if ($resume && $resume->user_id && $resume->user_id !== $request->user()?->id) {
                abort(403);
            }

            $uploadedResumeText = '';

            if ($request->hasFile('resume_file')) {
                $uploadedResumeText = $this->cleanText($this->extractResumeText($request->file('resume_file')));

                if (mb_strlen($uploadedResumeText) < 80) {
                    return response()->json([
                        'success' => false,
                        'message' => 'We could not read enough text from this resume. Please upload a text-based PDF, DOC, or DOCX.',
                    ], 422);
                }
            }

            $resumeContext = $resume?->data ?? [];
            $uploadedResumeContact = $uploadedResumeText ? $this->extractResumeContact($uploadedResumeText) : [];

            $name = $this->firstFilled(Arr::get($uploadedResumeContact, 'name'), $validated['name'] ?? null, Arr::get($resumeContext, 'name'));
            $email = $this->firstFilled(Arr::get($uploadedResumeContact, 'email'), $validated['email'] ?? null, Arr::get($resumeContext, 'email'));
            $mobile = $this->firstFilled(Arr::get($uploadedResumeContact, 'mobile'), $validated['mobile'] ?? null, Arr::get($resumeContext, 'mobile', Arr::get($resumeContext, 'contact')));
            $location = $this->firstFilled(Arr::get($uploadedResumeContact, 'location'), $validated['location'] ?? null, Arr::get($resumeContext, 'location', Arr::get($resumeContext, 'address')));
            $company = $validated['company_name'] ?? $validated['company'] ?? '';
            $jobRole = $validated['job_role'] ?? '';

            if ($uploadedResumeText) {
                $resumeContext['uploaded_resume_text'] = mb_substr($uploadedResumeText, 0, 12000);
                $resumeContext['uploaded_resume_contact'] = $uploadedResumeContact;
            }
            
            $result = $this->generateWithGemini($name, $jobRole, $company, $validated['job_description'] ?? '', $resumeContext, $validated['skills'] ?? '');

            if (!Arr::get($result, 'success', true)) {
                return response()->json(['success' => false, 'message' => Arr::get($result, 'message', 'AI Generation failed.')], 500);
            }

            $body = Arr::get($result, 'body');

            $letter = CoverLetter::create([
                'user_id' => $request->user()?->id,
                'session_id' => $request->session()->getId(),
                'template_id' => $validated['template_id'] ?? null,
                'resume_id' => $resume?->id,
                'job_role' => $jobRole,
                'company' => $company ?: null,
                'data' => [
                    'name' => $name,
                    'email' => $email,
                    'mobile' => $mobile,
                    'location' => $location,
                    'company' => $company,
                    'company_name' => $company,
                    'job_role' => $jobRole,
                    'skills' => $validated['skills'] ?? '',
                    'body' => $body,
                    'resume_uploaded' => (bool) $uploadedResumeText,
                ],
            ]);

            return response()->json(['success' => true, 'cover_letter_id' => $letter->id, 'letter' => $letter->data]);
        } catch (\Exception $e) {
            \Log::error('Cover Letter Generation Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to generate cover letter: ' . $e->getMessage()], 500);
        }
    }

    private function extractResumeContact(string $text): array
    {
        $lines = collect(preg_split('/\R+/', $text))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values();

        $name = $lines
            ->first(fn ($line) => ! str_contains($line, '@') && ! preg_match('/\+?\d[\d\s().-]{7,}/', $line) && ! preg_match('/\b(resume|curriculum vitae|cv)\b/i', $line)) ?: '';
        $email = '';
        $mobile = '';
        $location = '';

        if (preg_match('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', $text, $match)) {
            $email = $match[0];
        }

        if (preg_match('/(?:\+?\d[\d\s().-]{7,}\d)/', $text, $match)) {
            $mobile = trim($match[0]);
        }

        $locationKeywords = '\b(?:India|USA|UK|Remote|Bengaluru|Bangalore|Mumbai|Delhi|Pune|Hyderabad|Chennai|Kolkata|Noida|Gurgaon|Ahmedabad|Jaipur|Surat|Lucknow|Kanpur|Nagpur|Indore|Thane|Bhopal|Visakhapatnam|Patna|Vadodara|Ghaziabad|Ludhiana|Agra|Nashik|Faridabad|Meerut|Rajkot|California|Texas|New York|London|Dubai|Singapore|Germany|France|Canada|Australia|Dubai|UAE|Singapore|Sydney|Melbourne|Toronto|Vancouver)\b';

        $locationLine = $lines->first(function ($line) use ($locationKeywords) {
            if (str_contains($line, '@') || preg_match('/\+?\d[\d\s().-]{7,}/', $line)) {
                return false;
            }

            if (preg_match('/^(?:Location|Address|City|Place|Residence):\s*(.+)$/i', $line)) {
                return true;
            }

            if (preg_match('/'.$locationKeywords.'/i', $line)) {
                return true;
            }

            return str_contains($line, ',') && mb_strlen($line) < 60;
        }) ?: '';

        $location = $locationLine;
        if (preg_match('/^(?:Location|Address|City|Place|Residence):\s*(.+)$/i', $locationLine, $m)) {
            $location = $m[1];
        }

        return [
            'name' => $name,
            'email' => $email,
            'mobile' => $mobile,
            'location' => $location,
        ];
    }

    private function firstFilled(mixed ...$values): string
    {
        foreach ($values as $value) {
            if (is_array($value)) {
                $value = collect($value)->flatten()->filter()->join(', ');
            }

            $value = trim((string) ($value ?? ''));

            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function extractResumeText($file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();

        return match ($extension) {
            'pdf' => class_exists(\Smalot\PdfParser\Parser::class)
                ? (new \Smalot\PdfParser\Parser())->parseFile($path)->getText()
                : '',
            'docx' => $this->extractTextFromDocx($path),
            'doc' => $this->extractTextFromDoc($path),
            default => '',
        };
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
