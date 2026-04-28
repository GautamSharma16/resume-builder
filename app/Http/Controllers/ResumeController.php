<?php

namespace App\Http\Controllers;

use App\Models\ResumeAnalysis;
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
    public function index()
    {
        return view('pages.improve', [
            'razorpayKey' => config('services.razorpay.key'),
            'downloadAmount' => config('services.razorpay.download_amount'),
            'downloadCurrency' => config('services.razorpay.currency'),
        ]);
    }

    public function analyze(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'resume' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'job_role' => ['required', 'string', 'max:160'],
            'job_description' => ['nullable', 'string', 'max:12000'],
        ]);

        $text = $this->cleanText($this->extractText($request->file('resume')));

        if (mb_strlen($text) < 80) {
            throw ValidationException::withMessages([
                'resume' => 'We could not extract enough readable text from this resume. Please upload a text-based PDF or DOCX.',
            ]);
        }

        $resumeJson = $this->structureResume($text);
        $analysis = $this->askGeminiForAnalysis(
            $resumeJson,
            $validated['job_role'],
            $validated['job_description'] ?? null
        );

        $analysisRecord = ResumeAnalysis::create([
            'user_id' => $request->user()?->id,
            'session_id' => $request->session()->getId(),
            'job_role' => $validated['job_role'],
            'job_description' => $validated['job_description'] ?? null,
            'original_filename' => $request->file('resume')->getClientOriginalName(),
            'extracted_text' => $text,
            'resume_json' => $resumeJson,
            'analysis_json' => $analysis,
            'improved_resume_json' => $this->normalizeResume(Arr::get($analysis, 'improved_resume', $resumeJson)),
        ]);

        return response()->json([
            'analysis_id' => $analysisRecord->id,
            'is_paid' => false,
            'score' => (int) Arr::get($analysis, 'score', 0),
            'strengths' => Arr::get($analysis, 'strengths', []),
            'weaknesses' => Arr::get($analysis, 'weaknesses', []),
            'missing_keywords' => Arr::get($analysis, 'missing_keywords', []),
            'suggestions' => Arr::get($analysis, 'suggestions', []),
            'improved_resume' => $analysisRecord->improved_resume_json,
        ]);
    }

    public function improveAgain(Request $request): JsonResponse
    {
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

        $analysisRecord->update([
            'analysis_json' => $analysis,
            'improved_resume_json' => $this->normalizeResume(Arr::get($analysis, 'improved_resume', $resume)),
        ]);

        return response()->json([
            'analysis_id' => $analysisRecord->id,
            'is_paid' => $analysisRecord->is_paid,
            'score' => (int) Arr::get($analysis, 'score', 0),
            'strengths' => Arr::get($analysis, 'strengths', []),
            'weaknesses' => Arr::get($analysis, 'weaknesses', []),
            'missing_keywords' => Arr::get($analysis, 'missing_keywords', []),
            'suggestions' => Arr::get($analysis, 'suggestions', []),
            'improved_resume' => $analysisRecord->improved_resume_json,
        ]);
    }

    public function grammarFix(Request $request): JsonResponse
    {
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

        $analysisRecord->update([
            'analysis_json' => $analysis,
            'improved_resume_json' => $this->normalizeResume(Arr::get($analysis, 'improved_resume', $resume)),
        ]);

        return response()->json([
            'analysis_id' => $analysisRecord->id,
            'is_paid' => $analysisRecord->is_paid,
            'score' => (int) Arr::get($analysis, 'score', 0),
            'strengths' => Arr::get($analysis, 'strengths', []),
            'weaknesses' => Arr::get($analysis, 'weaknesses', []),
            'missing_keywords' => Arr::get($analysis, 'missing_keywords', []),
            'suggestions' => Arr::get($analysis, 'suggestions', []),
            'improved_resume' => $analysisRecord->improved_resume_json,
        ]);
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

        if (!$analysisRecord->is_paid) {
            return response()->json([
                'message' => 'Please unlock download before generating the PDF.',
                'requires_payment' => true,
            ], 402);
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

    private function structureResume(string $text): array
    {
        $lines = collect(preg_split('/\R+/', $text))
            ->map(fn($line) => trim($line))
            ->filter()
            ->values();

        $name = $lines->first() ?: '';
        $skills = $this->extractSectionItems($text, ['skills', 'technical skills', 'core skills']);
        $education = $this->extractSectionItems($text, ['education', 'academic']);
        $experienceLines = $this->extractSectionItems($text, ['experience', 'work experience', 'professional experience']);

        $summaryLines = $lines
            ->reject(fn($line) => str_contains(strtolower($line), '@') || preg_match('/\+?\d[\d\s().-]{7,}/', $line))
            ->slice(1, 4)
            ->values()
            ->all();

        return $this->normalizeResume([
            'name' => $name,
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
        $model = config('services.gemini.model', 'gemini-2.5-flash');

        if (!$key) {
            abort(500, 'Gemini API key is not configured.');
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
    "summary": "",
    "skills": [],
    "experience": [
      { "company": "", "role": "", "points": [] }
    ],
    "education": []
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
                    "https://generativelanguage.googleapis.com/v1/models/{$model}:generateContent?key=" . urlencode($key),
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
                            'maxOutputTokens' => 2000,
                        ],
                    ]
                );

            if (!$response->successful()) {
                return $this->fallbackResponse();
            }

            $text = Arr::get($response->json(), 'candidates.0.content.parts.0.text', '');

            $analysis = $this->decodeGeminiJson($text);

            return [
                'score' => max(0, min(100, (int) ($analysis['score'] ?? 0))),
                'strengths' => array_values($analysis['strengths'] ?? []),
                'weaknesses' => array_values($analysis['weaknesses'] ?? []),
                'missing_keywords' => array_values($analysis['missing_keywords'] ?? []),
                'suggestions' => array_values($analysis['suggestions'] ?? []),
                'improved_resume' => $this->normalizeResume($analysis['improved_resume'] ?? $resume),
            ];

        } catch (\Exception $e) {
            return $this->fallbackResponse();
        }
    }

    private function decodeGeminiJson(string $text): array
    {
        if (!$text) {
            return $this->fallbackResponse();
        }

        $text = trim($text);

        // remove markdown
        $text = preg_replace('/^```json|```$/m', '', $text);

        // try direct decode
        $decoded = json_decode($text, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        // try extracting JSON
        if (preg_match('/\{.*\}/s', $text, $match)) {
            $decoded = json_decode($match[0], true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return $this->fallbackResponse();
    }
    private function fallbackResponse(): array
    {
        return [
            'score' => 50,
            'strengths' => ['Basic resume detected'],
            'weaknesses' => ['AI response parsing failed'],
            'missing_keywords' => [],
            'suggestions' => ['Try again with better job description'],
            'improved_resume' => [
                'name' => '',
                'summary' => '',
                'skills' => [],
                'experience' => [],
                'education' => [],
            ],
        ];
    }

    private function normalizeResume(array $resume): array
    {
        return [
            'name' => (string) ($resume['name'] ?? ''),
            'summary' => (string) ($resume['summary'] ?? ''),
            'skills' => array_values(array_filter(array_map('strval', $resume['skills'] ?? []))),
            'experience' => array_values(array_map(function ($item) {
                return [
                    'company' => (string) ($item['company'] ?? ''),
                    'role' => (string) ($item['role'] ?? ''),
                    'points' => array_values(array_filter(array_map('strval', $item['points'] ?? []))),
                ];
            }, $resume['experience'] ?? [])),
            'education' => array_values(array_filter(array_map('strval', $resume['education'] ?? []))),
        ];
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
