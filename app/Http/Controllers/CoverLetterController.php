<?php

namespace App\Http\Controllers;

use App\Models\CoverLetter;
use App\Models\Resume;
use App\Models\Template;
use App\Services\PlanActivationService;
use App\Services\PdfConversionService;
use App\Services\TemplateRenderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Arr;
use ZipArchive;

class CoverLetterController extends Controller
{
    public function create(Request $request, TemplateRenderService $renderer)
    {
        $templates = Template::where('type', 'cover_letter')->where('is_active', true)->get();
        $sample = $renderer->coverLetterSampleData();
        $selectedTemplateId = $request->query('template_id');
        $selectedTemplateId = $selectedTemplateId && $templates->contains('id', (int) $selectedTemplateId)
            ? (int) $selectedTemplateId
            : null;
        
        $user = auth()->user();
        $prefill = $sample;

        if ($user) {
            $latestResume = \App\Models\Resume::where('user_id', $user->id)->latest()->first();
            if ($latestResume && !empty($latestResume->data)) {
                $rd = $latestResume->data;
                $prefill = [
                    'name'            => $this->firstFilled(Arr::get($rd, 'name'), $sample['name']),
                    'email'           => $this->firstFilled(Arr::get($rd, 'email'), $sample['email']),
                    'mobile'          => $this->firstFilled(Arr::get($rd, 'mobile'), Arr::get($rd, 'contact'), $sample['mobile']),
                    'location'        => $this->firstFilled(Arr::get($rd, 'location'), Arr::get($rd, 'address'), $sample['location']),
                    'company'         => $sample['company'],
                    'company_name'    => $sample['company_name'],
                    'job_role'        => $this->firstFilled(Arr::get($rd, 'job_title'), $sample['job_role']),
                    'skills'          => $this->firstFilled(Arr::get($rd, 'skills'), $sample['skills']),
                    'job_description' => $sample['job_description'],
                    'body'            => $sample['body'],
                ];
            }
        }

        return view('pages.cover-letter', [
            'resumes' => Resume::where('user_id', auth()->id())->latest()->get(),
            'templates' => $templates,
            'renderedTemplates' => $templates->mapWithKeys(fn (Template $template) => [
                $template->id => (string) $renderer->renderCoverLetter($template, $sample),
            ]),
            'prefill' => $prefill,
            'selectedTemplateId' => $selectedTemplateId,
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

            $uploadedResumeText = '';
            $coverLetter = isset($request->cover_letter_id) ? CoverLetter::find($request->cover_letter_id) : null;
            $resume = isset($validated['resume_id']) ? Resume::find($validated['resume_id']) : ($coverLetter ? $coverLetter->resume : null);

            if ($resume && $resume->user_id && $resume->user_id !== $request->user()?->id) {
                abort(403);
            }

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
            } elseif ($coverLetter && Arr::get($coverLetter->data, 'resume_uploaded')) {
                // If regenerating without a new file, but the previous one had a resume, we should ideally have stored the text
                // For now, if we don't have the text, the AI will use the other pre-filled fields.
            }
            
            $result = $this->generateWithGemini($name, $jobRole, $company, $validated['job_description'] ?? '', $resumeContext, $validated['skills'] ?? '');

            $usedFallback = false;
            if (!Arr::get($result, 'success', true)) {
                $body = $this->buildFallbackCoverLetterBody(
                    $name,
                    $jobRole,
                    $company,
                    $validated['skills'] ?? '',
                    $validated['job_description'] ?? '',
                    $resumeContext
                );
                $usedFallback = true;
            } else {
                $body = Arr::get($result, 'body');
            }

            if ($coverLetter) {
                $coverLetter->update([
                    'template_id' => $validated['template_id'] ?? $coverLetter->template_id,
                    'job_role' => $jobRole,
                    'company' => $company ?: null,
                    'data' => array_merge($coverLetter->data ?? [], [
                        'name' => $name,
                        'email' => $email,
                        'mobile' => $mobile,
                        'location' => $location,
                        'company' => $company,
                        'company_name' => $company,
                        'job_role' => $jobRole,
                        'skills' => $validated['skills'] ?? '',
                        'body' => $body,
                        'resume_uploaded' => (bool) ($uploadedResumeText ?: Arr::get($coverLetter->data, 'resume_uploaded')),
                    ]),
                ]);
                $letter = $coverLetter;
            } else {
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
            }

            return response()->json([
                'success' => true,
                'cover_letter_id' => $letter->id,
                'letter' => $letter->data,
                'used_fallback' => $usedFallback,
                'message' => $usedFallback ? 'AI was temporarily unavailable (503), so we generated a professional draft using your details.' : null,
            ]);
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

        $locationKeywords = '\b(?:India|USA|UK|Remote|Bengaluru|Bangalore|Mumbai|Delhi|Pune|Hyderabad|Chennai|Kolkata|Noida|Gurgaon|Ahmedabad|Jaipur|Surat|Lucknow|Kanpur|Nagpur|Indore|Thane|Bhopal|Visakhapatnam|Patna|Vadodara|Ghaziabad|Ludhiana|Agra|Nashik|Faridabad|Meerut|Rajkot|California|Texas|New York|London|Dubai|Singapore|Germany|France|Canada|Australia|Sydney|Melbourne|Toronto|Vancouver|San Francisco|Seattle|Chicago|Austin|Boston|Berlin|Paris|Amsterdam|Tokyo|Seoul)\b';

        $locationLine = $lines->first(function ($line) use ($locationKeywords) {
            $line = trim($line);
            if ($line === '') return false;

            // Skip lines that look like email, mobile, or links
            if (str_contains($line, '@') || preg_match('/\+?\d[\d\s().-]{7,}/', $line) || preg_match('/https?:\/\//i', $line)) {
                return false;
            }

            // Exclude common tech/skill keywords that often appear in comma-separated lists
            $skillKeywords = '\b(?:React|JavaScript|HTML|CSS|PHP|Laravel|Python|Java|SQL|Node|Express|Git|AWS|Cloud|Agile|Scrum|Developer|Engineer|Consultant|Frontend|Backend|Fullstack|UI|UX|Designer|Manager|Lead|Senior|Junior)\b';
            if (preg_match('/'.$skillKeywords.'/i', $line)) {
                return false;
            }

            // High confidence patterns (prefixed with "Location:", "City:", etc.)
            if (preg_match('/^(?:Location|Address|City|Place|Residence|Current Location):\s*(.+)$/i', $line)) {
                return true;
            }

            // Keyword based matches (must contain a known city/country)
            if (preg_match('/'.$locationKeywords.'/i', $line)) {
                return true;
            }

            // Strict city/state/country pattern (e.g. "Mumbai, MH" or "London, UK")
            // Must have a comma, be short, and contain capitalized words
            return str_contains($line, ',') && mb_strlen($line) < 50 && preg_match('/^[A-Z][a-z]+(?:\s+[A-Z][a-z]+)*,\s*[A-Z]/', $line);
        }) ?: '';

        $location = $locationLine;
        if (preg_match('/^(?:Location|Address|City|Place|Residence|Current Location):\s*(.+)$/i', $locationLine, $m)) {
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'letter' => ['required', 'array'],
            'template_id' => ['nullable', 'exists:templates,id'],
        ]);

        $letter = CoverLetter::create([
            'user_id' => auth()->id(),
            'session_id' => $request->session()->getId(),
            'template_id' => $validated['template_id'] ?? null,
            'job_role' => Arr::get($validated['letter'], 'job_role'),
            'company' => Arr::get($validated['letter'], 'company'),
            'data' => $validated['letter'],
        ]);

        return response()->json(['success' => true, 'cover_letter_id' => $letter->id]);
    }

    public function save(Request $request, CoverLetter $coverLetter)
    {
        $this->authorizeLetter($coverLetter);
        $validated = $request->validate([
            'letter' => ['required', 'array'],
            'template_id' => ['nullable', 'exists:templates,id'],
        ]);

        $letterData = $validated['letter'];
        $templateId = $validated['template_id']
            ?? Arr::get($letterData, 'template_id')
            ?? Arr::get($letterData, 'templateId')
            ?? $coverLetter->template_id;

        if ($templateId) {
            $letterData['template_id'] = (int) $templateId;
            $letterData['templateId'] = (int) $templateId;
        }

        $coverLetter->update([
            'template_id' => $templateId,
            'data' => $letterData,
        ]);

        return response()->json(['ok' => true]);
    }

    public function download(Request $request, CoverLetter $coverLetter, PdfConversionService $pdfConversionService, string $format = 'pdf')
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

        $pdf = $pdfConversionService->htmlToPdfWithPuppeteer($html);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename={$filename}.pdf",
        ]);
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
            $response = Http::timeout(60)
                ->retry(2, 500)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=".urlencode($key), [
                'contents' => [['parts' => [['text' => $prompt]]]],
                'generationConfig' => ['temperature' => 0.35, 'maxOutputTokens' => 1500],
            ]);

            if (!$response->successful()) {
                if ($response->status() === 429) {
                    return ['success' => false, 'message' => 'AI Rate limit exceeded.'];
                }
                if ($response->status() === 503) {
                    return ['success' => false, 'message' => 'AI service temporarily unavailable (503).'];
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

    private function buildFallbackCoverLetterBody(
        string $name,
        string $jobRole,
        string $company,
        string $skills,
        string $jobDescription,
        array $resumeContext
    ): string {
        $role = trim($jobRole) !== '' ? trim($jobRole) : 'the role';
        $companyName = trim($company);
        $skillsText = trim($skills);

        if ($skillsText === '' && !empty($resumeContext['skills']) && is_array($resumeContext['skills'])) {
            $skillsText = implode(', ', array_slice(array_map('strval', $resumeContext['skills']), 0, 6));
        }

        $jobLine = trim(strip_tags($jobDescription));
        $jobLine = mb_substr($jobLine, 0, 220);
        $salutation = $companyName !== '' ? "Dear Hiring Team at {$companyName}," : 'Dear Hiring Manager,';
        $closingName = trim($name) !== '' ? trim($name) : 'Candidate';

        $paragraph1 = "I am excited to apply for {$role}" . ($companyName !== '' ? " at {$companyName}" : '') . ". I bring hands-on experience delivering reliable, user-focused work and collaborating across teams to ship high-quality outcomes.";
        $paragraph2 = $skillsText !== ''
            ? "My background includes {$skillsText}, and I am confident these strengths align well with your requirements."
            : "My background aligns well with the role requirements, and I am confident I can contribute quickly.";
        $paragraph3 = $jobLine !== ''
            ? "I am especially interested in your focus on {$jobLine}, and I would value the opportunity to contribute with strong ownership, communication, and execution."
            : "I would value the opportunity to contribute with strong ownership, communication, and execution.";

        return "{$salutation}\n\n{$paragraph1}\n\n{$paragraph2}\n\n{$paragraph3}\n\nSincerely,\n{$closingName}";
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
