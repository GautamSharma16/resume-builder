<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Models\ResumeAnalysis;
use App\Services\PlanActivationService;
use App\Services\TemplateRenderService;
use Barryvdh\DomPDF\Facade\Pdf;
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
            'renderedTemplates' => $templates->mapWithKeys(fn (Template $template) => [
                $template->id => (string) $renderer->renderResume($template),
            ]),
        ]);
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
            $jobRole = 'General';
            $jobDescription = null;

            $analysis = $this->askGeminiForAnalysis(
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

                 return response()->json([
                    'success' => false,
                    'message' => Arr::get($analysis, 'message', 'AI analysis failed.')
                ], 500);
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

    public function download(Request $request)
    {
        $validated = $request->validate([
            'analysis_id' => ['required', 'integer', 'exists:resume_analyses,id'],
        ]);

        $analysisRecord = $this->findAuthorizedAnalysis($request, (int) $validated['analysis_id']);

        if (! $request->user()) {
            return redirect()->route('login');
        }

        if (! $analysisRecord->is_paid && ! $request->user()->activeSubscription?->hasDownloadsRemaining()) {
            return response()->json([
                'message' => 'Choose a plan to unlock downloads.',
                'requires_payment' => true,
                'pricing_url' => route('plans'),
            ], 402);
        }

        if (! $analysisRecord->is_paid) {
            app(PlanActivationService::class)->consumeDownload($request->user());
            $analysisRecord->forceFill([
                'is_paid' => true,
                'paid_at' => now(),
            ])->save();
        }

        $resume = $this->normalizeResume($analysisRecord->improved_resume_json ?? []);
        $filename = $this->pdfFilename($resume['name']);

        return Pdf::loadView('resume.pdf', ['resume' => $resume])
            ->setPaper('a4')
            ->download($filename);
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

        $location = $lines
            ->first(fn($line) => !str_contains($line, '@') && !preg_match('/\+?\d[\d\s().-]{7,}/', $line) && preg_match('/\b(?:India|USA|UK|Remote|Bengaluru|Bangalore|Mumbai|Delhi|Pune|Hyderabad|Chennai|Kolkata|Noida|Gurgaon)\b/i', $line)) ?: '';

        $skills = $this->extractSectionItems($text, ['skills', 'technical skills', 'core skills']);
        $education = $this->extractSectionItems($text, ['education', 'academic']);
        $experienceLines = $this->extractSectionItems($text, ['experience', 'work experience', 'professional experience']);
        $projects = $this->extractSectionItems($text, ['projects', 'project', 'portfolio', 'projects & accomplishments', 'selected projects']);

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
            'summary' => implode(' ', $summaryLines),
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

    private function extractSectionItems(string $text, array $headings): array
    {
        $pattern = '/(?:^|\n)(' . implode('|', array_map('preg_quote', $headings)) . ')\s*:?\s*\n(?<body>.*?)(?=\n[A-Z][A-Z &\/-]{2,}:?\s*\n|$)/is';

        if (!preg_match($pattern, $text, $match)) {
            return [];
        }

        $body = trim($match['body']);
        $parts = preg_split('/(?:\R|,|;|•|- )+/', $body);

        return collect($parts)
            ->map(fn($item) => trim($item, " \t\n\r\0\x0B-•"))
            ->filter(fn($item) => mb_strlen($item) > 1 && mb_strlen($item) < 180)
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

FORMAT:
{
  "score": number,
  "strengths": [],
  "weaknesses": [],
  "missing_keywords": [],
  "suggestions": [],
  "improved_resume": {
    "name": "",
    "email": "",
    "mobile": "",
    "location": "",
    "social_links": [],
    "summary": "",
    "skills": [],
    "experience": [
      { "company": "", "role": "", "points": [] }
    ],
    "education": [
      { "degree": "", "institution": "", "year": "" }
    ],
    "projects": [
      { "name": "", "tech": "", "description": "" }
    ]
  }
}

{$instruction}

Job Role: {$jobRole}

Job Description:
{$jobDescription}

Resume:
PROMPT;

        $prompt .= "\n" . json_encode($resume, JSON_UNESCAPED_SLASHES);

        try {
            $response = Http::timeout(90)
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

    private function decodeGeminiJson(string $text): array
    {
        if (! $text) {
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
        $safeStr = function($val) {
            if (is_array($val)) return json_encode($val);
            return (string) $val;
        };

        return [
            'name' => (string) ($resume['name'] ?? ''),
            'email' => (string) ($resume['email'] ?? ''),
            'mobile' => (string) ($resume['mobile'] ?? $resume['contact'] ?? ''),
            'location' => (string) ($resume['location'] ?? $resume['address'] ?? ''),
            'contact' => (string) ($resume['contact'] ?? $resume['mobile'] ?? ''),
            'address' => (string) ($resume['address'] ?? $resume['location'] ?? ''),
            'social_links' => array_values(array_filter(array_map($safeStr, $resume['social_links'] ?? []))),
            'summary' => (string) ($resume['summary'] ?? ''),
            'skills' => array_values(array_filter(array_map($safeStr, $resume['skills'] ?? []))),
            'experience' => array_values(array_map(function ($item) use ($safeStr) {
                return [
                    'company' => (string) ($item['company'] ?? ''),
                    'role' => (string) ($item['role'] ?? ''),
                    'points' => array_values(array_filter(array_map($safeStr, $item['points'] ?? []))),
                ];
            }, $resume['experience'] ?? [])),
            'education' => array_values(array_map(function ($item) {
                if (!is_array($item)) {
                    return [
                        'degree' => (string) $item,
                        'institution' => '',
                        'year' => '',
                    ];
                }
                return [
                    'degree' => (string) ($item['degree'] ?? ''),
                    'institution' => (string) ($item['institution'] ?? ''),
                    'year' => (string) ($item['year'] ?? ''),
                ];
            }, $resume['education'] ?? [])),
            'projects' => array_values(array_map(function ($item) {
                if (!is_array($item)) {
                    return [
                        'name' => (string) $item,
                        'tech' => '',
                        'description' => '',
                    ];
                }
                return [
                    'name' => (string) ($item['name'] ?? ''),
                    'tech' => (string) ($item['tech'] ?? ''),
                    'description' => (string) ($item['description'] ?? ''),
                ];
            }, $resume['projects'] ?? [])),
        ];

        $primaryColor = trim((string) ($resume['primary_color'] ?? ''));
        if ($primaryColor !== '') {
            $normalized['primary_color'] = $primaryColor;
            $normalized['primary_color_customized'] = filter_var($resume['primary_color_customized'] ?? true, FILTER_VALIDATE_BOOLEAN);
        }

        return $normalized;
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
