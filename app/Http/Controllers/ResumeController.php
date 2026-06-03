<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Models\ResumeAnalysis;
use App\Support\Utf8Sanitizer;
use App\Services\GeminiService;
use App\Services\PlanActivationService;
use App\Services\PdfConversionService;
use App\Services\ResumeParseOrchestrator;
use App\Services\ResumeNormalizerService;
use App\Services\ResumeSchema;
use App\Services\ResumeSectionValidatorService;
use App\Services\StructuredResumeExtractionService;
use App\Services\TemplateRenderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Razorpay\Api\Api;
use Symfony\Component\Process\Process;
use Throwable;
use ZipArchive;

class ResumeController extends Controller
{
    private const MAX_PARSE_PAGES = 4;

    public function __construct(
        private readonly GeminiService $gemini,
        private readonly StructuredResumeExtractionService $structuredExtractor,
    ) {}

    // ─────────────────────────────────────────────
    // VIEWS
    // ─────────────────────────────────────────────

    public function index(Request $request, TemplateRenderService $renderer)
    {
        $templates = Template::where('type', 'resume')
            ->where('is_active', true)
            ->get();

        return view('pages.improve', [
            'razorpayKey'      => config('services.razorpay.key'),
            'downloadAmount'   => config('services.razorpay.download_amount'),
            'downloadCurrency' => config('services.razorpay.currency'),
            'templates'        => $templates,
            'renderedTemplates' => $templates->mapWithKeys(fn(Template $t) => [
                $t->id => (string) $renderer->renderResume($t, null, false),
            ]),
            'selectedTemplateId' => $request->query('template_id') ? (int) $request->query('template_id') : null,
        ]);
    }

    public function atsChecker()
    {
        return view('pages.ats-checker');
    }

    // ─────────────────────────────────────────────
    // ANALYZE  (primary endpoint)
    // ─────────────────────────────────────────────

    public function analyze(Request $request, ResumeParseOrchestrator $parseOrchestrator): JsonResponse
    {
        $prevLimit = (int) ini_get('max_execution_time');
        // Upload parse can include document extraction, section detection, and Gemini.
        if ($prevLimit > 0 && $prevLimit < 240) {
            @set_time_limit(240);
        }

        try {
            $validated = $request->validate([
                'resume'      => ['required_unless:mode,enhance', 'nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx', 'max:10240'],
                'mode'        => ['nullable', 'in:autofill,parse,enhance'],
                'analysis_id' => ['required_if:mode,enhance', 'nullable', 'integer', 'exists:resume_analyses,id'],
            ]);

            $mode           = $validated['mode'] ?? null;
            $isAutofill     = $mode === 'autofill';
            $isParseOnly    = $mode === 'parse';
            $isEnhanceOnly  = $mode === 'enhance';
            $jobRole        = $this->scalarString($request->input('job_role') ?: 'General') ?: 'General';
            $jobDescription = $this->scalarString($request->input('job_description'));

            if ($isEnhanceOnly) {
                $record = $this->findAuthorizedAnalysis($request, (int) $request->input('analysis_id'));
                $builderBase = $this->normalizeResume($record->improved_resume_json ?? []);
                $text = $record->extracted_text ?: $this->resumeToText($builderBase);

                $geminiReview = $this->geminiReviewAffindaAndFill( // AFFINDA DISABLED: legacy method name, Gemini-only review.
                    $builderBase,
                    $text,
                    $jobRole ?: $record->job_role,
                    $jobDescription ?: $record->job_description
                );

                $improvedResume = Arr::get($geminiReview, 'success', true) && $this->resumeHasContent(Arr::get($geminiReview, 'improved_resume', []))
                    ? $this->finalizeParsedResume(
                        $this->mergeAffindaPrimary($builderBase, Arr::get($geminiReview, 'improved_resume', [])), // AFFINDA DISABLED: legacy merge name.
                        false,
                        $text
                    )
                    : $this->finalizeParsedResume($builderBase, false, $text);

                $analysis = $this->localAtsAnalysis($improvedResume, $jobRole ?: $record->job_role, $jobDescription ?: $record->job_description);
                $analysis['improved_resume'] = $improvedResume;

                $record->update([
                    'job_role'             => $jobRole ?: $record->job_role,
                    'job_description'      => $jobDescription ?: $record->job_description,
                    'resume_json'          => $improvedResume,
                    'analysis_json'        => array_merge($analysis, [
                        'parser_source' => Arr::get($record->analysis_json, 'parser_source', 'gemini'),
                    ]),
                    'improved_resume_json' => $improvedResume,
                ]);

                return response()->json([
                    'success'          => true,
                    'analysis_id'      => $record->id,
                    'is_paid'          => false,
                    'parser_source'    => Arr::get($record->analysis_json, 'parser_source', 'gemini'),
                    'score'            => (int) Arr::get($analysis, 'score', 0),
                    'strengths'        => Arr::get($analysis, 'strengths', []),
                    'weaknesses'       => Arr::get($analysis, 'weaknesses', []),
                    'missing_keywords' => Arr::get($analysis, 'missing_keywords', []),
                    'suggestions'      => Arr::get($analysis, 'suggestions', []),
                    'improved_resume'  => $improvedResume,
                    'standard_resume'  => Arr::get($record->analysis_json, 'standard_resume', []),
                ]);
            }

            $file = $request->file('resume');

            // 1. Extract raw text (fallback + ATS context)
            $raw  = $this->extractText($file);
            $text = $this->prepareTextForParsing($this->cleanText($raw));

            if (mb_strlen($text) < 80) {
                throw ValidationException::withMessages([
                    'resume' => 'We could not extract enough readable text. Please upload a text-based PDF or DOCX.',
                ]);
            }

            // 2. Document extraction -> section detection -> Gemini extraction.
            $parseResult  = $parseOrchestrator->extractFromUpload($file, $text);
            $parseSource  = $parseResult['source'];
            $standardJson = $parseResult['standard'] ?? ResumeSchema::empty();
            $parserMessage = $parseResult['message'] ?? null;

            // 3. Gemini result -> normalize -> autofill payload (all upload modes).
            $hybrid         = $this->buildHybridAutofillResume($parseResult, $text, $jobRole, $jobDescription);
            $improvedResume = $hybrid['builder'];
            $parseSource    = $hybrid['source'];
            $standardJson   = $hybrid['standard'] ?? $standardJson;

            if ($isAutofill) {
                return response()->json(Utf8Sanitizer::jsonSafe([
                    'success'          => true,
                    'analysis_id'      => null,
                    'parser_source'    => $parseSource,
                    'parser_message'   => $parserMessage,
                    'score'            => 0,
                    'strengths'        => [],
                    'weaknesses'       => [],
                    'missing_keywords' => [],
                    'suggestions'      => $this->hybridAutofillSuggestions($parseSource),
                    'improved_resume'  => $improvedResume,
                    'standard_resume'  => $standardJson,
                ]));
            }

            if ($isParseOnly) {
                $analysis = [
                    'success'          => true,
                    'score'            => 0,
                    'strengths'        => [],
                    'weaknesses'       => [],
                    'missing_keywords' => [],
                    'suggestions'      => $this->hybridAutofillSuggestions($parseSource),
                    'improved_resume'  => $improvedResume,
                ];
            } else {
                $analysis = $this->localAtsAnalysis($improvedResume, $jobRole, $jobDescription);
                $analysis['improved_resume'] = $improvedResume;
            }

            $improvedResume = Utf8Sanitizer::jsonSafe($this->normalizeResume($improvedResume ?? []));
            $standardJson   = Utf8Sanitizer::jsonSafe($this->normalizeResume($standardJson ?? ResumeSchema::empty()));
            $analysis       = Utf8Sanitizer::jsonSafe(is_array($analysis) ? $analysis : []);

            $record = ResumeAnalysis::create([
                'user_id'              => $request->user()?->id,
                'session_id'           => $request->session()->getId(),
                'job_role'             => $jobRole,
                'job_description'      => $jobDescription,
                'original_filename'    => $file->getClientOriginalName(),
                'extracted_text'       => $text,
                'resume_json'          => $improvedResume,
                'analysis_json'        => array_merge($analysis ?? [], [
                    'parser_source'   => $parseSource,
                    'standard_resume' => $standardJson,
                ]),
                'improved_resume_json' => $improvedResume,
            ]);

            return response()->json(Utf8Sanitizer::jsonSafe([
                'success'          => true,
                'analysis_id'      => $record->id,
                'is_paid'          => false,
                'parser_source'    => $parseSource,
                'parser_message'   => $parserMessage,
                'score'            => (int) Arr::get($analysis, 'score', 0),
                'strengths'        => Arr::get($analysis, 'strengths', []),
                'weaknesses'       => Arr::get($analysis, 'weaknesses', []),
                'missing_keywords' => Arr::get($analysis, 'missing_keywords', []),
                'suggestions'      => Arr::get($analysis, 'suggestions', []),
                'improved_resume'  => $record->improved_resume_json,
                'standard_resume'  => $standardJson,
            ]));

        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Resume Analysis Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            // Provide helpful error messages based on error type
            $message = $this->getHelpfulErrorMessage($e);

            return response()->json(['success' => false, 'message' => $message], 500);
        } finally {
            if ($prevLimit > 0) {
                @set_time_limit($prevLimit);
            }
        }
    }

    // ─────────────────────────────────────────────
    // IMPROVE AGAIN
    // ─────────────────────────────────────────────

    public function improveAgain(Request $request): JsonResponse
    {
        $prevLimit = (int) ini_get('max_execution_time');
        if ($prevLimit > 0 && $prevLimit < 180) {
            @set_time_limit(180);
        }

        try {
            $validated = $request->validate([
                'analysis_id' => ['required', 'integer', 'exists:resume_analyses,id'],
                'resume'      => ['required', 'array'],
            ]);

            $record = $this->findAuthorizedAnalysis($request, (int) $validated['analysis_id']);
            $resume = $this->normalizeResume($validated['resume']);

            $pseudoText = $this->resumeToText($resume);

            $analysis = $this->geminiParseAndAnalyze(
                $pseudoText,
                $record->job_role,
                $record->job_description,
                'Refine the resume with better ATS formatting, stronger action verbs, and clarity. Do not drop employers, education, or projects.',
                $resume
            );

            if (!Arr::get($analysis, 'success', true)) {
                return response()->json([
                    'success' => false,
                    'message' => Arr::get($analysis, 'message', 'AI improvement failed.'),
                ], 500);
            }

            $improved = $this->postProcessGeminiResume(
                $this->mergeAffindaPrimary($resume, Arr::get($analysis, 'improved_resume', []))
            );
            $record->update(['analysis_json' => $analysis, 'improved_resume_json' => $improved]);

            return response()->json([
                'success'          => true,
                'analysis_id'      => $record->id,
                'is_paid'          => $record->is_paid,
                'score'            => (int) Arr::get($analysis, 'score', 0),
                'strengths'        => Arr::get($analysis, 'strengths', []),
                'weaknesses'       => Arr::get($analysis, 'weaknesses', []),
                'missing_keywords' => Arr::get($analysis, 'missing_keywords', []),
                'suggestions'      => Arr::get($analysis, 'suggestions', []),
                'improved_resume'  => $record->improved_resume_json,
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        } finally {
            if ($prevLimit > 0) {
                @set_time_limit($prevLimit);
            }
        }
    }

    // ─────────────────────────────────────────────
    // GRAMMAR FIX
    // ─────────────────────────────────────────────

    public function grammarFix(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'analysis_id' => ['required', 'integer', 'exists:resume_analyses,id'],
                'resume'      => ['required', 'array'],
            ]);

            $record = $this->findAuthorizedAnalysis($request, (int) $validated['analysis_id']);
            $resume = $this->normalizeResume($validated['resume']);

            $pseudoText = $this->resumeToText($resume);

            $analysis = $this->geminiParseAndAnalyze(
                $pseudoText,
                $record->job_role,
                $record->job_description,
                'Fix grammar, clarity, tense consistency, and bullet-point style. Do NOT invent any facts or metrics.'
            );

            if (!Arr::get($analysis, 'success', true)) {
                return response()->json([
                    'success' => false,
                    'message' => Arr::get($analysis, 'message', 'AI grammar fix failed.'),
                ], 500);
            }

            $improved = $this->normalizeResume(Arr::get($analysis, 'improved_resume', $resume));
            $record->update(['analysis_json' => $analysis, 'improved_resume_json' => $improved]);

            return response()->json([
                'success'          => true,
                'analysis_id'      => $record->id,
                'is_paid'          => $record->is_paid,
                'score'            => (int) Arr::get($analysis, 'score', 0),
                'strengths'        => Arr::get($analysis, 'strengths', []),
                'weaknesses'       => Arr::get($analysis, 'weaknesses', []),
                'missing_keywords' => Arr::get($analysis, 'missing_keywords', []),
                'suggestions'      => Arr::get($analysis, 'suggestions', []),
                'improved_resume'  => $record->improved_resume_json,
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────
    // SAVE RESUME
    // ─────────────────────────────────────────────

    public function saveResume(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'analysis_id' => ['required', 'integer', 'exists:resume_analyses,id'],
            'resume'      => ['required', 'array'],
        ]);

        $record = $this->findAuthorizedAnalysis($request, (int) $validated['analysis_id']);
        $record->update(['improved_resume_json' => $this->normalizeResume($validated['resume'])]);

        return response()->json(['ok' => true, 'analysis_id' => $record->id, 'is_paid' => $record->is_paid]);
    }

    // ─────────────────────────────────────────────
    // PAYMENT – CREATE ORDER
    // ─────────────────────────────────────────────

    public function createPaymentOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'analysis_id' => ['required', 'integer', 'exists:resume_analyses,id'],
        ]);

        $record = $this->findAuthorizedAnalysis($request, (int) $validated['analysis_id']);

        if ($record->is_paid) {
            return response()->json(['is_paid' => true]);
        }

        $api    = $this->razorpay();
        $amount = config('services.razorpay.download_amount');

        $order = $api->order->create([
            'receipt'         => 'resume_' . $record->id,
            'amount'          => $amount,
            'currency'        => config('services.razorpay.currency'),
            'payment_capture' => 1,
            'notes'           => ['resume_analysis_id' => (string) $record->id],
        ]);

        $record->update(['razorpay_order_id' => $order['id']]);

        return response()->json([
            'is_paid'     => false,
            'order_id'    => $order['id'],
            'key'         => config('services.razorpay.key'),
            'amount'      => $amount,
            'currency'    => config('services.razorpay.currency'),
            'name'        => config('app.name', 'Cvbliss'),
            'description' => 'Unlock resume PDF download',
        ]);
    }

    // ─────────────────────────────────────────────
    // PAYMENT – VERIFY
    // ─────────────────────────────────────────────

    public function verifyPayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'analysis_id'        => ['required', 'integer', 'exists:resume_analyses,id'],
            'razorpay_order_id'  => ['required', 'string'],
            'razorpay_payment_id'=> ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        $record = $this->findAuthorizedAnalysis($request, (int) $validated['analysis_id']);

        if ($record->razorpay_order_id !== $validated['razorpay_order_id']) {
            abort(422, 'Payment order does not match this resume.');
        }

        try {
            $this->razorpay()->utility->verifyPaymentSignature([
                'razorpay_order_id'   => $validated['razorpay_order_id'],
                'razorpay_payment_id' => $validated['razorpay_payment_id'],
                'razorpay_signature'  => $validated['razorpay_signature'],
            ]);
        } catch (Throwable) {
            abort(422, 'Payment verification failed.');
        }

        $record->update([
            'is_paid'             => true,
            'razorpay_payment_id' => $validated['razorpay_payment_id'],
            'razorpay_signature'  => $validated['razorpay_signature'],
            'paid_at'             => now(),
        ]);

        return response()->json(['ok' => true, 'is_paid' => true]);
    }

    // ─────────────────────────────────────────────
    // DOWNLOAD
    // ─────────────────────────────────────────────

    public function download(Request $request, PdfConversionService $pdf, TemplateRenderService $renderer)
    {
        $validated = $request->validate([
            'analysis_id' => ['required', 'integer', 'exists:resume_analyses,id'],
            'template_id' => ['nullable', 'integer', 'exists:templates,id'],
        ]);

        $record = $this->findAuthorizedAnalysis($request, (int) $validated['analysis_id']);

        if (!$request->user()) {
            return redirect()->guest(route('login'));
        }

        if (!$record->is_paid && !$request->user()->activeSubscription?->hasDownloadsRemaining()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message'          => 'Choose a plan to unlock downloads.',
                    'requires_payment' => true,
                    'pricing_url'      => route('plans'),
                ], 402);
            }
            return redirect()->route('plans')->with('status', 'Choose a plan to unlock downloads.');
        }

        if (!$record->is_paid) {
            app(PlanActivationService::class)->consumeDownload($request->user());
            $record->forceFill(['is_paid' => true, 'paid_at' => now()])->save();
        }

        $resume   = $this->normalizeResume($record->improved_resume_json ?? []);
        $filename = $this->pdfFilename($resume['name']);
        $template = $validated['template_id'] ? Template::find($validated['template_id']) : new Template();

        $html = view('templates.rendered-document', [
            'html' => $renderer->renderResume($template, $resume),
        ])->render();

        $pdfContent = $pdf->htmlToPdfWithPuppeteer($html);

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // ═════════════════════════════════════════════
    // GEMINI – PRIMARY PARSER & ANALYZER
    // ═════════════════════════════════════════════

    /**
     * FIX: Added designation, desired_job_role, last_name to the Gemini prompt schema
     * so these fields are always returned and populate the form correctly.
     */
    private function geminiParseAndAnalyze(
        string  $rawText,
        string  $jobRole = 'General',
        ?string $jobDescription = null,
        ?string $extraInstruction = null,
        array   $parsedHints = []
    ): array {
        $extra = $extraInstruction ? "\n\nSPECIAL INSTRUCTION: {$extraInstruction}" : '';

        $hintsJson = !empty($parsedHints)
            ? json_encode($this->normalizeResume($parsedHints), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : '{}';

        $prompt = <<<PROMPT
You are an expert resume parser and ATS analyst. Your job has TWO parts:
PART 1 – Parse the resume text with 100% accuracy into structured JSON.
PART 2 – Analyse the structured resume against the job role and provide ATS scoring.

══════════════════════════════════════
PARSING RULES (PART 1)
══════════════════════════════════════
- Extract ONLY what exists in the text. NEVER invent names, dates, companies, URLs, or metrics.
- NEVER mix sections: education must stay in education, certifications in certifications, projects in projects.
- Use the PARSE_HINTS_JSON only as a helper when raw text is noisy; do not copy wrong values from hints.
- name: First name only (e.g. "Rahul" not "Rahul Sharma").
- last_name: Last name / family name only (e.g. "Sharma"). If only one name found, put it in name and leave last_name empty.
- designation: The person's current or most recent job title (e.g. "Software Engineer", "Product Manager"). Infer from the most recent experience entry if not stated explicitly.
- desired_job_role: Target role the person is seeking — take from the resume objective/summary if mentioned, otherwise leave empty.
- email: Any email address found.
- mobile: Any phone/mobile number found (include country code if present).
- location: City, State or Country – only if explicitly mentioned.
- social_links: Only real URLs (LinkedIn, GitHub, portfolio, etc.) found in the text. Exclude placeholder examples.
- summary: The professional summary / objective paragraph as-is only. If none exists in the text, leave summary empty.
- skills: Every individual skill, technology, tool, or framework mentioned. Split comma/slash/pipe-separated lists into separate items.
- experience: For each role –
    company: employer name only.
    role: job title only.
    period: date range exactly as written (e.g. "Jan 2022 – Mar 2024", "2021 – Present").
    points: ALL bullet points / responsibilities / achievements exactly as written. Keep every bullet; do not truncate.
- education: For each qualification –
    degree: e.g. B.Tech, M.Sc, MBA, 10th, 12th, Diploma – exactly as written.
    stream: specialisation / subject / field / percentage / CGPA – only what is explicitly stated.
    institution: full institution name.
    year: graduation year or date range.
- projects: For each project –
    name: project title.
    tech / tech_stack: technologies used (comma-separated).
    link: only a real URL starting with http/https/www/github.com/gitlab.com. Empty string if none.
    description: project summary, features, and outcomes.
- certifications: array of {name, description} – issuer and year if mentioned.
- languages: array of {name, level} – proficiency level if mentioned.
- achievements: array of {name, description} for awards, honours, publications, etc.

══════════════════════════════════════
ANALYSIS RULES (PART 2)
══════════════════════════════════════
- score (0-100): ATS compatibility score based on keyword match, completeness, formatting.
- strengths: 3-5 specific strengths observed in this exact resume.
- weaknesses: 2-4 specific gaps or weaknesses.
- missing_keywords: keywords from the job role/description NOT found in the resume (max 10).
- suggestions: 3-6 actionable, specific suggestions for this exact resume.{$extra}

══════════════════════════════════════
JOB CONTEXT
══════════════════════════════════════
Job Role: {$jobRole}
Job Description: {$jobDescription}

══════════════════════════════════════
OUTPUT FORMAT – STRICT JSON ONLY
══════════════════════════════════════
Do NOT wrap in markdown. Return only the raw JSON object below.

{
  "score": 0,
  "strengths": [],
  "weaknesses": [],
  "missing_keywords": [],
  "suggestions": [],
  "improved_resume": {
    "name": "",
    "last_name": "",
    "designation": "",
    "desired_job_role": "",
    "email": "",
    "mobile": "",
    "location": "",
    "social_links": [],
    "summary": "",
    "skills": [],
    "experience": [
      { "company": "", "role": "", "period": "", "points": [] }
    ],
    "education": [
      { "degree": "", "stream": "", "institution": "", "year": "" }
    ],
    "projects": [
      { "name": "", "tech": "", "tech_stack": "", "link": "", "description": "" }
    ],
    "certifications": [
      { "name": "", "description": "" }
    ],
    "languages": [
      { "name": "", "level": "" }
    ],
    "achievements": [
      { "name": "", "description": "" }
    ]
  }
}

══════════════════════════════════════
RESUME TEXT TO PARSE
══════════════════════════════════════
PARSE_HINTS_JSON:
{$hintsJson}

PROMPT;

        $prompt .= "\n" . mb_substr($rawText, 0, 12000);

        return $this->callGemini($prompt, [], $this->geminiMaxTokens());
    }

    /**
     * Autofill-only extraction: structured JSON, no ATS scoring, never invent missing facts.
     */
    private function geminiExtractAndAutofill(
        string  $rawText,
        string  $jobRole,
        ?string $jobDescription,
        array   $parsedHints = []
    ): array {
        $hintsJson = ! empty($parsedHints)
            ? json_encode($this->normalizeResume($parsedHints), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : '{}';

        $jobContext = trim($jobRole) !== '' && strtolower($jobRole) !== 'general'
            ? "Optional target role hint (use only if clearly supported by resume text): {$jobRole}"
            : 'No target role hint.';

        $prompt = <<<PROMPT
You are an expert resume parser for a resume builder autofill feature.
Extract ONLY information that explicitly appears in RESUME_TEXT. NEVER invent, guess, or embellish.

{$jobContext}

SECTION HEADING ALIASES (map flexibly, case-insensitive):
- summary: Summary, Professional Summary, Profile, About, About Me, Objective, Career Objective, Professional Profile
- experience: Experience, Work Experience, Professional Experience, Employment, Work History, Career History, Internships, Positions, Roles
- education: Education, Academics, Academic Background, Qualifications, Educational Background
- projects: Projects, Project, Portfolio, Academic Projects, Personal Projects, Key Projects
- skills: Skills, Technical Skills, Core Skills, Key Skills, Competencies, Expertise, Technologies, Tools, Tech Stack
- certifications: Certifications, Certificates, Licenses, Credentials, Professional Certifications
- languages: Languages, Language Proficiency
- achievements: Achievements, Awards, Honors, Honours, Accomplishments, Publications

LAYOUT NOTES:
- Resumes may be single-column, two-column, table-based, or multi-page. Read all text; do not skip sidebars or tables.
- Preserve bullet lists as experience/project points, not as summary prose.

STRICT RULES:
1. Put each fact in exactly ONE section. Education degrees/universities NEVER go in certifications. Skill lists NEVER go in summary.
2. Extract summary/objective/profile/career objective verbatim when present in RESUME_TEXT. Leave "" only if no such paragraph exists. Do NOT invent or rewrite a new summary.
3. name = first/given name only. last_name = family name only. If only one name token, put it in name.
4. designation = current or most recent job title from experience (or headline if stated). desired_job_role only if explicitly stated as target role.
5. skills: individual items (split comma/pipe/slash lists). Short phrases only (max 80 chars each). No full sentences.
6. experience entries: company, role, period (dates as written), points = bullet lines only (array of strings).
7. education: degree, stream/field, institution, year (or date range).
8. projects: name, tech_stack, link (http/https only), description.
9. certifications / achievements / languages: use {name, description} or {name, level} shapes as specified.
10. linkedin, github, portfolio: extract dedicated URLs when present; also include them in social_links.
11. Use PARSE_HINTS_JSON only to disambiguate noisy text — prefer RESUME_TEXT when they conflict.

Return STRICT JSON only (no markdown), with this exact top-level shape:
{
  "improved_resume": {
    "name": "",
    "last_name": "",
    "designation": "",
    "desired_job_role": "",
    "email": "",
    "mobile": "",
    "location": "",
    "linkedin": "",
    "github": "",
    "portfolio": "",
    "social_links": [],
    "summary": "",
    "skills": [],
    "experience": [{"company":"","role":"","period":"","points":[]}],
    "education": [{"degree":"","stream":"","institution":"","year":""}],
    "projects": [{"name":"","tech_stack":"","link":"","description":""}],
    "certifications": [{"name":"","description":""}],
    "languages": [{"name":"","level":""}],
    "achievements": [{"name":"","description":""}]
  }
}

PARSE_HINTS_JSON:
{$hintsJson}

RESUME_TEXT:
PROMPT;

        $prompt .= "\n".mb_substr($rawText, 0, 10000);

        return $this->callGemini($prompt, $parsedHints, $this->geminiMaxTokens());
    }

    private function geminiMaxTokens(): int
    {
        return max(8192, (int) config('services.gemini.max_output_tokens', 8192));
    }

    /**
     * Central Gemini HTTP call with model fallback.
     */
    private function callGemini(string $prompt, array $fallbackResume = [], ?int $maxTokens = null): array
    {
        $maxTokens ??= $this->geminiMaxTokens();

        try {
            $result = $this->gemini->generateContent($prompt, [
                'maxOutputTokens' => $maxTokens,
                'temperature'     => (float) config('services.gemini.temperature', 0.2),
                'timeout'         => 90,
                'responseMimeType'=> 'application/json',
            ]);

            if (! ($result['success'] ?? false)) {
                return [
                    'success' => false,
                    'message' => $result['message'] ?? GeminiService::BUSY_MESSAGE,
                ];
            }

            $text = (string) ($result['text'] ?? '');
            $data = $this->decodeGeminiJson($text);

            if (empty($data)) {
                $salvaged = $this->salvageGeminiResumeFromText($text);
                if ($salvaged !== []) {
                    \Log::info('Gemini parse: recovered partial JSON via salvage', [
                        'fields' => array_keys($salvaged),
                    ]);
                    $data = ['improved_resume' => $salvaged];
                } else {
                    \Log::warning('Gemini parse: unable to decode JSON payload', [
                        'text_preview' => mb_substr(Utf8Sanitizer::cleanString($text), 0, 1200),
                    ]);

                    return ['success' => false, 'message' => 'Could not parse AI response.'];
                }
            }

            if (! isset($data['improved_resume']) && $this->looksLikeResumeObject($data)) {
                $data = ['improved_resume' => $data];
            }

            if (! isset($data['improved_resume'])) {
                \Log::warning('Gemini parse: JSON decoded but improved_resume missing', [
                    'keys' => array_slice(array_keys($data), 0, 20),
                ]);

                return ['success' => false, 'message' => 'Could not parse AI response.'];
            }

            $improved = Arr::get($data, 'improved_resume');
            if (! is_array($improved)) {
                $improved = [];
            }

            if ($this->resumeHasContent($fallbackResume)) {
                $improved = $this->mergeAffindaPrimary($fallbackResume, $improved);
            } elseif (empty(array_filter($improved))) {
                $improved = $fallbackResume;
            }

            $improved = $this->postProcessGeminiResume($improved);

            return [
                'success'          => true,
                'score'            => max(0, min(100, (int) ($data['score'] ?? 50))),
                'strengths'        => array_values($data['strengths'] ?? []),
                'weaknesses'       => array_values($data['weaknesses'] ?? []),
                'missing_keywords' => array_values($data['missing_keywords'] ?? []),
                'suggestions'      => array_values($data['suggestions'] ?? []),
                'improved_resume'  => $this->normalizeResume($improved),
            ];
        } catch (\Throwable $e) {
            \Log::error('Gemini Exception: '.$e->getMessage());

            return ['success' => false, 'message' => GeminiService::BUSY_MESSAGE];
        }
    }

    // ═════════════════════════════════════════════
    // FILE TEXT EXTRACTION
    // ═════════════════════════════════════════════

    private function extractText($file): string
    {
        $ext  = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();

        if ($ext === 'doc') {
            $magic = file_get_contents($path, false, null, 0, 4) ?: '';
            if ($magic === "PK\x03\x04") {
                $ext = 'docx';
            }
        }

        return match ($ext) {
            'pdf'         => $this->extractTextFromPdf($path),
            'docx'        => $this->extractTextFromDocx($path),
            'doc'         => $this->extractTextFromDoc($path),
            'pptx', 'ppt' => $this->extractTextFromPptx($path),
            default       => '',
        };
    }

    private function extractTextFromPdf(string $path): string
    {
        $prevLimit = (int) ini_get('max_execution_time');
        if ($prevLimit > 0 && $prevLimit < 120) {
            set_time_limit(120);
        }

        try {
            $pythonText = $this->extractTextViaPythonStack($path, 'pdf');
            if ($this->scoreExtractedText($pythonText) >= 20) {
                return $pythonText;
            }

            $config = new \Smalot\PdfParser\Config();
            $config->setRetainImageContent(false);
            $config->setIgnoreEncryption(true);

            $parser    = new \Smalot\PdfParser\Parser([], $config);
            $pdf       = $parser->parseFile($path);
            $plainText = trim((string) $pdf->getText(self::MAX_PARSE_PAGES));

            if ($this->scoreExtractedText($plainText) >= 20) {
                return $plainText;
            }

            $layoutText = trim($this->extractPdfTextByPosition($pdf));

            if ($layoutText === '') return $plainText;
            if ($plainText === '')  return $layoutText;

            return $this->scoreExtractedText($layoutText) >= $this->scoreExtractedText($plainText)
                ? $layoutText
                : $plainText;

        } catch (\Throwable $e) {
            \Log::warning('PDF parse error: ' . $e->getMessage());
            $lo = $this->extractViaLibreOffice($path);
            return $lo !== '' ? $lo : '';
        } finally {
            if ($prevLimit > 0) set_time_limit($prevLimit);
        }
    }

    private function extractPdfTextByPosition(\Smalot\PdfParser\Document $pdf): string
    {
        $pages = [];

        foreach (array_slice($pdf->getPages(), 0, self::MAX_PARSE_PAGES) as $page) {
            $items = [];

            foreach ($page->getDataTm() as $item) {
                $matrix = $item[0] ?? [];
                $text   = trim(preg_replace('/\s+/', ' ', (string) ($item[1] ?? '')));
                if ($text === '' || !isset($matrix[4], $matrix[5])) continue;

                $items[] = ['x' => (float) $matrix[4], 'y' => (float) $matrix[5], 'text' => $text];
            }

            if (!$items) {
                $pages[] = trim((string) $page->getText());
                continue;
            }

            usort($items, fn($a, $b) => $b['y'] <=> $a['y'] ?: $a['x'] <=> $b['x']);

            $rows = [];
            foreach ($items as $item) {
                $rowIndex = null;
                foreach ($rows as $i => $row) {
                    if (abs($row['y'] - $item['y']) <= 2.5) { $rowIndex = $i; break; }
                }
                if ($rowIndex === null) {
                    $rows[] = ['y' => $item['y'], 'items' => [$item]];
                } else {
                    $rows[$rowIndex]['items'][] = $item;
                    $rows[$rowIndex]['y']        = ($rows[$rowIndex]['y'] + $item['y']) / 2;
                }
            }

            usort($rows, fn($a, $b) => $b['y'] <=> $a['y']);

            $pageLines = [];
            foreach ($rows as $row) {
                usort($row['items'], fn($a, $b) => $a['x'] <=> $b['x']);
                $line  = '';
                $lastX = null;
                foreach ($row['items'] as $part) {
                    $gap       = $lastX === null ? 0 : $part['x'] - $lastX;
                    $separator = $line === '' ? '' : ($gap > 90 ? "\t" : ' ');
                    $line     .= $separator . $part['text'];
                    $lastX     = $part['x'] + max(18, mb_strlen($part['text']) * 4.2);
                }
                $line = trim(preg_replace('/[ \t]+/', ' ', $line));
                if ($line !== '') $pageLines[] = $line;
            }

            $pages[] = implode("\n", $pageLines);
        }

        return trim(implode("\n\n", array_filter($pages)));
    }

    private function scoreExtractedText(string $text): int
    {
        $lines    = collect(preg_split('/\R+/', $text))->map(fn($l) => trim($l))->filter();
        $headings = preg_match_all('/^\s*(summary|objective|skills?|education|experience|projects?|certifications?|languages?)\s*:?$/im', $text) ?: 0;
        $contacts = preg_match('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', $text) ? 4 : 0;
        $contacts += preg_match('/\+?\d[\d\s().-]{7,}\d/', $text) ? 3 : 0;
        $penalty  = $lines->filter(fn($l) => mb_strlen($l) > 180)->count() * 2;

        return ($headings * 8) + min(35, $lines->count()) + $contacts - $penalty;
    }

    private function extractTextFromPptx(string $path): string
    {
        if (!class_exists('ZipArchive')) {
            return '';
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) return '';

        $text = '';
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (str_contains($name, 'ppt/slides/slide')) {
                $content = $zip->getFromIndex($i) ?: '';
                $content = str_replace('<a:t', ' <a:t', $content);
                $text   .= strip_tags($content) . ' ';
            }
        }
        $zip->close();

        return html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function extractTextFromDocx(string $path): string
    {
        $pythonText = $this->extractTextViaPythonStack($path, 'docx');
        if ($this->scoreExtractedText($pythonText) >= 20) {
            return $pythonText;
        }

        if (!class_exists('ZipArchive')) {
            $lo = $this->extractViaLibreOffice($path);
            if ($lo !== '') return $this->cleanDocText($lo);
            return '';
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) return '';

        $content = $zip->getFromName('word/document.xml') ?: '';
        $zip->close();

        if (!$content) return '';

        $content = str_replace(['&amp;', '&lt;', '&gt;', '&quot;', '&apos;'], ['&', '<', '>', '"', "'"], $content);
        $content = preg_replace('/<\/w:p>/i', "\n", $content);
        $content = preg_replace('/<\/w:tc>/i', "\t", $content);
        $content = preg_replace('/<\/w:tr>/i', "\n", $content);
        $content = preg_replace('/<w:br[^>]*>/i', "\n", $content);
        $content = preg_replace('/<\/w:r>/i', ' ', $content);
        $content = preg_replace('/<\/w:t>/i', ' ', $content);
        $content = preg_replace('/<w:tab[^>]*\/>/i', "\t", $content);
        $content = str_replace(['<w:softHyphen/>', '<w:noBreakHyphen/>'], ['-', '-'], $content);
        $content = strip_tags($content);
        $content = html_entity_decode($content, ENT_QUOTES | ENT_XML1, 'UTF-8');

        $lines = explode("\n", $content);
        $lines = array_map(function (string $line): string {
            $line = preg_replace('/[ \t]+/', ' ', $line);
            return trim($line);
        }, $lines);

        $content = implode("\n", $lines);
        $content = preg_replace('/\n{3,}/', "\n\n", $content);

        return trim($content);
    }

    private function extractTextViaPythonStack(string $path, string $kind): string
    {
        $script = base_path('scripts/extract_resume_text.py');
        if (! is_file($script) || ! class_exists(Process::class)) {
            return '';
        }

        foreach (['python', 'python3', 'py'] as $binary) {
            try {
                $process = new Process([$binary, $script, $kind, $path]);
                $process->setTimeout(90);
                $process->run();

                if (! $process->isSuccessful()) {
                    continue;
                }

                $payload = json_decode(trim($process->getOutput()), true);
                $text = is_array($payload) ? trim((string) ($payload['text'] ?? '')) : '';
                if ($text !== '') {
                    \Log::info('Resume document extraction used Python stack', [
                        'kind' => $kind,
                        'engine' => $payload['engine'] ?? 'python',
                    ]);
                    return $text;
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        return '';
    }

    private function extractTextFromDoc(string $path): string
    {
        $rawBytes = file_get_contents($path) ?: '';

        if ($rawBytes === '') {
            return '';
        }

        // Detect if it's actually a DOCX (ZIP) masquerading as .doc
        if (substr($rawBytes, 0, 4) === "PK\x03\x04") {
            $tmpDocx = tempnam(sys_get_temp_dir(), 'doc_') . '.docx';
            file_put_contents($tmpDocx, $rawBytes);
            $result = $this->extractTextFromDocx($tmpDocx);
            @unlink($tmpDocx);
            if (trim($result) !== '') return $result;
        }

        $isWindows = PHP_OS_FAMILY === 'Windows';
        $tmpPath   = tempnam(sys_get_temp_dir(), 'doc_') . '.doc';
        file_put_contents($tmpPath, $rawBytes);

        // Strategy 1: LibreOffice (most reliable, cleanest output)
        $libreOut = $this->extractViaLibreOffice($tmpPath);
        if (trim($libreOut) !== '') {
            @unlink($tmpPath);
            $cleaned = $this->cleanDocText($libreOut);
            // Only return if it passes garbage check (no bjbj etc)
            if ($this->isCleanExtractedText($cleaned)) {
                return $cleaned;
            }
        }

        // Strategy 2: antiword (Linux/Mac only)
        if (!$isWindows && function_exists('shell_exec')) {
            $antiwordBin = trim(shell_exec('which antiword 2>/dev/null') ?: '');
            if ($antiwordBin !== '') {
                $escaped     = escapeshellarg($tmpPath);
                $antiwordOut = shell_exec("antiword -w 0 {$escaped} 2>/dev/null") ?: '';
                if (trim($antiwordOut) !== '') {
                    @unlink($tmpPath);
                    return $this->cleanDocText($antiwordOut);
                }
            }
        }

        // Strategy 3: catdoc (Linux/Mac only)
        if (!$isWindows && function_exists('shell_exec')) {
            $catdocBin = trim(shell_exec('which catdoc 2>/dev/null') ?: '');
            if ($catdocBin !== '') {
                $escaped   = escapeshellarg($tmpPath);
                $catdocOut = shell_exec("catdoc -w {$escaped} 2>/dev/null") ?: '';
                if (trim($catdocOut) !== '') {
                    @unlink($tmpPath);
                    return $this->cleanDocText($catdocOut);
                }
            }
        }

        @unlink($tmpPath);

        // Strategy 4: Pure PHP binary extraction — apply extra aggressive cleaning
        $binaryResult = $this->extractDocBinary($rawBytes);
        return $this->aggressiveCleanDocText($binaryResult);
    }

    /**
     * Check if extracted text is actually clean (no binary garbage).
     */
    private function isCleanExtractedText(string $text): bool
    {
        if (trim($text) === '') return false;

        // If it contains these artifacts, it's garbage
        $garbageIndicators = ['bjbj', 'HYPERLINK "mailto:', 'gdaY', 'ugYL', 'hIUL', 'MSWordDoc'];
        foreach ($garbageIndicators as $indicator) {
            if (str_contains($text, $indicator)) return false;
        }

        // Must have at least some readable content (letters > 60% of chars)
        $letterCount = preg_match_all('/[A-Za-z]/', $text);
        $totalChars  = max(1, mb_strlen($text));
        return ($letterCount / $totalChars) > 0.40;
    }

    /**
     * Extra aggressive cleaning for binary-extracted .doc text.
     * Removes all the garbage patterns that Word's binary format leaves behind.
     */
    private function aggressiveCleanDocText(string $text): string
    {
        if (!$text) return '';

        // Remove null bytes and all control characters
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F\x80-\x9F]/', '', $text);

        // Remove known Word binary artifact words/patterns
        $stripPatterns = [
            '/\bbjbj\w*\b/i',
            '/\bHYPERLINK\s+"[^"]*"\s*/i',
            '/\bHYPERLINK\b[^\n]*/i',
            '/mailto:[^\s\]>")]+/i',
            '/\b(?:gdaY|gda\[|gdiY|gZOA6?|ugYL|hIUL|h2\.U|bjbj|LNLN)\b/i',
            '/\bMSWordDoc\b/i',
            '/\bWord\.Document\.\d+\b/i',
            '/\b_PID_HLINKS\b/i',
            '/\[Content_Types\]\.xml/i',
            '/theme\/theme[^\/]*/i',
            '/\b_rels\b/i',
            '/<\?xml[^>]*>/i',
            '/\bNormal\.dotm\b/i',
            '/\bDocument\.xml\b/i',
            // Remove lines that are purely hex/binary-looking
            '/^[0-9a-f]{8,}\s*$/im',
            // Remove Windows path artifacts
            '/[A-Z]:\\\\[^\s]+/i',
            // Remove XML-like artifacts
            '/<[a-z][a-z0-9:_-]*(?:\s[^>]*)?\/?>/i',
            '/<\/[a-z][a-z0-9:_-]*>/i',
        ];

        foreach ($stripPatterns as $pattern) {
            $text = preg_replace($pattern, ' ', $text);
        }

        // Split into lines and filter aggressively
        $lines = explode("\n", $text);
        $cleanLines = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || mb_strlen($line) < 2) continue;

            // Skip lines that are mostly non-alphanumeric
            $alphaCount = preg_match_all('/[A-Za-z0-9@.\-+() :,\'"\/@]/', $line);
            $ratio      = $alphaCount / max(1, mb_strlen($line));
            if ($ratio < 0.55) continue;

            // Skip lines with too many consecutive non-printable-looking chars
            if (preg_match('/[^\x20-\x7E\xA0-\xFF]{3,}/', $line)) continue;

            // Skip lines containing binary artifact keywords
            if (preg_match('/\b(bjbj|HYPERLINK|gdaY|ugYL|hIUL|MSWordDoc)\b/i', $line)) continue;

            $cleanLines[] = $line;
        }

        $text = implode("\n", $cleanLines);
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
    }

    private function extractViaLibreOffice(string $docPath): string
    {
        if (!function_exists('shell_exec')) {
            return '';
        }

        $binaries = PHP_OS_FAMILY === 'Windows'
            ? [
                'C:\\Program Files\\LibreOffice\\program\\soffice.exe',
                'C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe',
            ]
            : [
                '/usr/bin/libreoffice',
                '/usr/bin/soffice',
                '/usr/local/bin/libreoffice',
                '/opt/libreoffice/program/soffice',
                '/Applications/LibreOffice.app/Contents/MacOS/soffice',
            ];

        $soffice = '';
        foreach ($binaries as $bin) {
            if (file_exists($bin)) {
                $soffice = $bin;
                break;
            }
        }

        if ($soffice === '' && PHP_OS_FAMILY !== 'Windows') {
            $found = trim(shell_exec('which libreoffice 2>/dev/null || which soffice 2>/dev/null') ?: '');
            if ($found !== '') $soffice = $found;
        }

        if ($soffice === '') {
            return '';
        }

        $outDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'lo_' . uniqid();
        @mkdir($outDir, 0755, true);

        $escapedDoc = escapeshellarg($docPath);
        $escapedDir = escapeshellarg($outDir);
        $escapedBin = escapeshellarg($soffice);

        if (PHP_OS_FAMILY === 'Windows') {
            $cmd = "{$escapedBin} --headless --convert-to txt:Text --outdir {$escapedDir} {$escapedDoc} 2>NUL";
        } else {
            $cmd = "{$escapedBin} --headless --convert-to txt:Text --outdir {$escapedDir} {$escapedDoc} 2>/dev/null";
        }

        shell_exec($cmd);

        $txtFile = $outDir . DIRECTORY_SEPARATOR . pathinfo($docPath, PATHINFO_FILENAME) . '.txt';
        $result  = '';

        if (file_exists($txtFile)) {
            $result = file_get_contents($txtFile) ?: '';
            @unlink($txtFile);
        }

        @rmdir($outDir);

        return trim($result);
    }

    private function extractDocBinary(string $raw): string
    {
        $utf16 = $this->extractUtf16Stream($raw);

        preg_match_all('/[\x20-\x7E]{5,}/', $raw, $cp1252Matches);
        $cp1252Chunks = array_filter($cp1252Matches[0] ?? [], function (string $chunk): bool {
            $good  = preg_match_all('/[A-Za-z0-9 @.,:\-+()\/% ]/', $chunk);
            $ratio = $good / max(1, strlen($chunk));
            // FIX: Extended garbage pattern list
            if (preg_match('/^(?:bjbj|Normal|Microsoft|HYPERLINK|PID_|gdaY|gda\[|hIUL|ugYL|LNLN|MSWord|_rels|Content_Types|theme\/)/', $chunk)) {
                return false;
            }
            // Skip chunks containing HYPERLINK anywhere
            if (stripos($chunk, 'HYPERLINK') !== false) return false;
            if (stripos($chunk, 'bjbj') !== false) return false;
            return $ratio > 0.62;
        });

        $cp1252Chunks = array_unique($cp1252Chunks);
        $cp1252       = implode("\n", $cp1252Chunks);

        $scoreUtf16  = $this->scoreExtractedText($utf16);
        $scoreCp1252 = $this->scoreExtractedText($cp1252);

        $best = $scoreUtf16 >= $scoreCp1252 ? $utf16 : $cp1252;

        // Use aggressive clean for binary-extracted text
        return $this->aggressiveCleanDocText($best);
    }

    private function extractUtf16Stream(string $raw): string
    {
        $len    = strlen($raw);
        $result = '';
        $block     = '';
        $blockLen  = 0;

        for ($i = 0; $i < $len - 1; $i += 2) {
            $lo = ord($raw[$i]);
            $hi = ord($raw[$i + 1]);
            $cp = ($hi << 8) | $lo;

            if ($cp === 0x000D || $cp === 0x0007 || $cp === 0x000B) {
                if ($blockLen >= 3) {
                    $result .= $block . "\n";
                }
                $block    = '';
                $blockLen = 0;
                continue;
            }

            if (($cp >= 0x0020 && $cp <= 0x007E) || ($cp >= 0x00A0 && $cp <= 0x024F)) {
                $char = mb_convert_encoding(pack('v', $cp), 'UTF-8', 'UTF-16LE');
                if ($char !== false) {
                    $block    .= $char;
                    $blockLen++;
                }
            } else {
                if ($blockLen >= 5) {
                    $result .= $block . "\n";
                }
                $block    = '';
                $blockLen = 0;
            }
        }

        if ($blockLen >= 5) {
            $result .= $block;
        }

        return $result;
    }

    private function cleanDocText(string $text): string
    {
        // Remove null bytes and non-printable control chars (keep \n \t)
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);

        // Strip inline HYPERLINK artifacts before line splitting
        $text = preg_replace('/\bHYPERLINK\s+"[^"]*"\s*/i', '', $text);
        $text = preg_replace('/\bHYPERLINK\b[^\n]*/i', '', $text);
        $text = preg_replace('/mailto:[^\s\]>")]+/i', '', $text);

        $garbageLinePatterns = [
            '/\bbjbj\w*/i',
            '/\b(?:gdaY|gda\[|gdiY|gZOA6?|ugYL|hIUL|h2\.U|LNLN)\b/i',
            '/\[Content_Types\]\.xml/i',
            '/theme\/theme/i',
            '/\b_rels\//i',
            '/\bMSWordDoc\b/i',
            '/\bWord\.Document\.\d\b/i',
            '/\b_PID_HLINKS\b/i',
            '/<\?xml/i',
            '/\bNormal\.dotm\b/i',
        ];

        $lines = explode("\n", $text);
        $lines = array_filter($lines, function (string $line) use ($garbageLinePatterns): bool {
            $line = trim($line);
            if ($line === '') return false;
            if (strlen($line) < 2) return false;

            foreach ($garbageLinePatterns as $pat) {
                if (preg_match($pat, $line)) return false;
            }

            $alphaCount = (int) preg_match_all('/[A-Za-z0-9@.+() :,\'"\/@ -]/', $line);
            $ratio      = $alphaCount / max(1, strlen($line));

            return $ratio >= 0.50;
        });

        $text = implode("\n", array_values($lines));
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
    }

    private function cleanText(string $text): string
    {
        $text = Utf8Sanitizer::cleanString($text);

        // Normalize line endings
        $text = preg_replace('/\r\n|\r/', "\n", $text);
        $text = str_replace("\t", ' ', (string) $text);

        // FIX: Strip any binary garbage that leaked through doc extraction
        // This is the last defense before sending text to Gemini
        $text = preg_replace('/\bbjbj\w*/i', '', $text);
        $text = preg_replace('/\bHYPERLINK\s+"[^"]*"\s*/i', '', $text);
        $text = preg_replace('/\bHYPERLINK\b[^\n]*/i', '', $text);
        $text = preg_replace('/mailto:[^\s\]>")]+/i', '', $text);
        $text = preg_replace('/\b(?:gdaY|ugYL|hIUL|LNLN|gda\[)\b/i', '', $text);
        // Collapse multiple spaces per line, preserve newlines
        $lines = array_map(fn($l) => trim(preg_replace('/ {2,}/', ' ', $l)), explode("\n", (string) $text));

        // Filter out lines that are still garbage after all cleaning
        $lines = array_filter($lines, function(string $line): bool {
            if (trim($line) === '') return true; // keep blank lines for spacing
            $alphaCount = preg_match_all('/[A-Za-z0-9]/', $line);
            return ($alphaCount / max(1, mb_strlen($line))) > 0.30;
        });

        $text  = implode("\n", $lines);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return Utf8Sanitizer::cleanString(trim((string) $text));
    }

    /**
     * Prepare extracted text for parsing by normalizing headings, merging broken lines,
     * converting bullets, and removing excessive whitespace while preserving meaningful
     * line breaks so the AI can detect sections reliably across layouts.
     */
    private function prepareTextForParsing(string $text): string
    {
        if (trim($text) === '') return '';

        // Normalize newlines and collapse mixed whitespace
        $text = preg_replace('/\r\n|\r/', "\n", $text);
        $text = preg_replace('/[ \t]+/', ' ', $text);

        // Normalize common bullet characters to a simple hyphen marker
        // Use \x{...} for Unicode codepoints for PCRE compatibility
        $text = preg_replace('/[•●▪◦→➤·\*\x{2022}\x{00B7}]/u', '-', $text);

        // Ensure common headings appear on their own line (uppercased)
        $headings = [
            'summary', 'professional summary', 'profile', 'objective', 'career objective', 'about', 'about me',
            'experience', 'work experience', 'work history', 'employment', 'professional experience', 'internships',
            'education', 'academics', 'academic background', 'qualifications',
            'projects', 'project', 'portfolio', 'personal projects', 'academic projects',
            'skills', 'technical skills', 'core skills', 'competencies', 'expertise', 'technologies',
            'certifications', 'certificates', 'licenses', 'credentials',
            'languages', 'language',
            'achievements', 'awards', 'honors', 'honours', 'accomplishments',
            'contact', 'publications', 'positions', 'roles',
        ];
        foreach ($headings as $h) {
            $text = preg_replace('/\b' . preg_quote($h, '/') . '\b[\s:\-]*/i', strtoupper($h) . "\n", $text);
        }

        // Split lines and merge obvious broken lines that are mid-sentence
        $lines = preg_split('/\n+/', $text);
        $out = [];
        $count = count($lines);
        for ($i = 0; $i < $count; $i++) {
            $line = trim($lines[$i]);
            if ($line === '') { $out[] = ''; continue; }

            $next = $lines[$i + 1] ?? '';
            $nextTrim = trim($next);
            // Merge if current line does not end with sentence punctuation and next line looks like continuation
            $noEndPunct = !preg_match('/[\.\?\!\:\;\)\-]$/u', $line);
            $nextIsCont = $nextTrim !== '' && preg_match('/^[a-z0-9\(\-\[\"]/iu', $nextTrim);
            $shortLine = mb_strlen($line) < 45;

            if ($noEndPunct && ($nextIsCont || $shortLine)) {
                // merge into next line to fix broken wraps
                $lines[$i + 1] = $line . ' ' . $nextTrim;
                continue;
            }

            $out[] = $line;
        }

        $text = implode("\n", $out);

        // Collapse excessive blank lines but keep paragraph breaks
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
    }

    // ═════════════════════════════════════════════
    // LOCAL FALLBACK PARSER  (Gemini unavailable)
    // ═════════════════════════════════════════════

    private function localParseResume(string $text): array
    {
        $lines = collect(preg_split('/\R+/', $text))
            ->map(fn($l) => trim($l))
            ->filter()
            ->values();

        $header    = $this->extractHeaderIdentity($text);
        $rawName   = $header['name'] !== '' ? trim($header['name'].' '.$header['last_name']) : $this->detectCandidateName($lines->all(), $text);

        $email  = '';
        $mobile = '';

        if (preg_match('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $text, $m)) {
            $email = $m[0];
        }
        $rawName = $this->validateDetectedName($rawName, $email);
        if ($this->isLikelyCityName($rawName) && $header['name'] !== '') {
            $rawName = trim($header['name'].' '.$header['last_name']);
        }
        $nameParts = explode(' ', $rawName, 2);
        $firstName = $nameParts[0] ?? $rawName;
        $lastName  = $nameParts[1] ?? '';
        if ($lastName === '' && $header['last_name'] !== '') {
            $lastName = $header['last_name'];
        }

        $mobile = $this->extractBestPhone($text);

        preg_match_all('/https?:\/\/[^\s,<>"\']+/i', $text, $urlMatches);
        $socialLinks = array_values(array_unique($urlMatches[0] ?? []));
        $linkedin = '';
        $github = '';
        $portfolio = '';
        foreach ($socialLinks as $url) {
            if ($linkedin === '' && preg_match('/linkedin\.com/i', $url)) {
                $linkedin = $url;
            }
            if ($github === '' && preg_match('/github\.com/i', $url)) {
                $github = $url;
            }
        }

        $sectionMap = $this->detectSections($text);

        $summary      = $sectionMap['summary']        ?? '';
        $skillsText   = $sectionMap['skills']         ?? '';
        $expText      = $sectionMap['experience']     ?? '';
        $eduText      = $sectionMap['education']      ?? '';
        $projText     = $sectionMap['projects']       ?? '';
        $certText     = $sectionMap['certifications'] ?? '';
        $langText     = $sectionMap['languages']      ?? '';
        $achieveText  = $sectionMap['achievements']   ?? '';

        if ($summary === '') {
            $summary = $this->inferSummaryFromText($text);
        }

        $skills = [];
        if ($skillsText) {
            $skills = $this->normalizeStringList($skillsText);
        }
        if (empty($skills)) {
            $skills = $this->inferSkillsFromText($text);
        }

        $experience = $expText ? $this->parseExperienceText($expText) : [];
        $education  = $eduText ? $this->parseEducationText($eduText) : [];
        $projects   = $projText ? $this->parseProjectsText($projText) : [];
        if (empty($experience)) {
            $experience = $this->parseExperienceText($text);
        }
        if (empty($education) && $this->textHasEducationSignals($text)) {
            $education = $this->parseEducationText($eduText ?: $text);
        }

        // FIX: Infer designation from first experience role
        $designation = '';
        if (!empty($experience[0]['role'])) {
            $designation = $experience[0]['role'];
        }

        $certifications = $this->parseNamedList($certText);
        $languages      = $this->parseLanguageList($langText);
        $achievements   = $this->parseNamedList($achieveText);

        return $this->normalizeResume([
            'name'           => $firstName,
            'last_name'      => $lastName,
            'designation'    => $designation,
            'desired_job_role' => '',
            'email'          => $email,
            'mobile'         => $mobile,
            'location'       => $header['location'] !== '' ? $header['location'] : $this->extractLocationFromText($text),
            'linkedin'       => $linkedin,
            'github'         => $github,
            'portfolio'      => $portfolio,
            'social_links'   => $socialLinks,
            'summary'        => $summary,
            'skills'         => $skills,
            'experience'     => $experience,
            'education'      => $education,
            'projects'       => $projects,
            'certifications' => $certifications,
            'languages'      => $languages,
            'achievements'   => $achievements,
        ]);
    }

    /**
     * Parse "SANDEEP MISHRA Bangalore, Karnataka" style headers common on Indian resumes.
     *
     * @return array{name:string,last_name:string,location:string}
     */
    private function extractHeaderIdentity(string $text): array
    {
        $empty = ['name' => '', 'last_name' => '', 'location' => ''];
        $header = mb_substr($text, 0, 800);

        $cities = 'Bangalore|Bengaluru|Mumbai|Delhi|New Delhi|Chennai|Hyderabad|Pune|Kolkata|Gurgaon|Gurugram|Noida|Jaipur|Ahmedabad|Kochi|Coimbatore|Indore|Bhopal|Lucknow|Chandigarh|Nagpur|Surat|Vadodara|Thiruvananthapuram|Mysore|Mysuru';

        if (preg_match('/\b([A-Z][A-Z][A-Za-z.\'-]*(?:\s+[A-Z][A-Z][A-Za-z.\'-]*){1,4})\s+('.$cities.')\s*(?:,\s*([A-Za-z][A-Za-z\s]{2,30}))?/u', $header, $m)) {
            $parts = preg_split('/\s+/', trim($m[1]), -1, PREG_SPLIT_NO_EMPTY) ?: [];

            return [
                'name'      => $parts[0] ?? '',
                'last_name' => implode(' ', array_slice($parts, 1)),
                'location'  => $this->sanitizeLocation(trim($m[2].(isset($m[3]) ? ', '.$m[3] : ''))),
            ];
        }

        if (preg_match('/\b([A-Z][a-z]+(?:\s+[A-Z][a-z]+){1,3})\s+('.$cities.')\s*(?:,\s*([A-Za-z][a-z]+))?/u', $header, $m)) {
            $parts = preg_split('/\s+/', trim($m[1]), -1, PREG_SPLIT_NO_EMPTY) ?: [];

            return [
                'name'      => $parts[0] ?? '',
                'last_name' => implode(' ', array_slice($parts, 1)),
                'location'  => $this->sanitizeLocation(trim($m[2].(isset($m[3]) ? ', '.$m[3] : ''))),
            ];
        }

        foreach (preg_split('/\R+/', $header) as $line) {
            $line = trim((string) $line);
            if ($line === '' || str_contains($line, '@')) {
                continue;
            }
            $line = trim((string) preg_replace('/\b(?:email|e-mail|phone|mobile|mob)\b.*$/i', '', $line));
            if (preg_match('/^([A-Za-z][A-Za-z.\'-]+(?:\s+[A-Za-z][A-Za-z.\'-]+){1,4})\s+([A-Za-z][A-Za-z]+(?:\s*,\s*[A-Za-z][A-Za-z]+)+)$/u', $line, $m)) {
                $parts = preg_split('/\s+/', trim($m[1]), -1, PREG_SPLIT_NO_EMPTY) ?: [];
                if ($this->isLikelyCityName($parts[0] ?? '')) {
                    continue;
                }

                return [
                    'name'      => $parts[0] ?? '',
                    'last_name' => implode(' ', array_slice($parts, 1)),
                    'location'  => $this->sanitizeLocation(trim($m[2])),
                ];
            }
        }

        return $empty;
    }

    private function detectCandidateName(array $lines, string $text): string
    {
        $header = $this->extractHeaderIdentity($text);
        if ($header['name'] !== '') {
            return trim($header['name'].' '.$header['last_name']);
        }

        $rejectPattern = '/(@|https?:\/\/|www\.|linkedin|github|phone|mobile|email|contact|\+?\d[\d\s().-]{6,}|address)\b/i';

        foreach (array_slice($lines, 0, 12) as $line) {
            $line = trim((string) $line);
            if ($this->isBoilerplateLine($line)) {
                continue;
            }
            if ($line === '' || preg_match($rejectPattern, $line)) {
                continue;
            }
            if (preg_match('/\b(?:email|phone|mob)\s*:/i', $line)) {
                continue;
            }
            if (mb_strlen($line) > 70) {
                continue;
            }

            if (preg_match('/^([A-Z][A-Z][A-Za-z.\'-]*(?:\s+[A-Z][A-Z][A-Za-z.\'-]*){1,4})\s+/u', $line, $m)) {
                return trim($m[1]);
            }

            if (preg_match('/^[A-Za-z][A-Za-z .\'-]{1,44}$/', $line) && ! $this->isLikelyCityName($line)) {
                $wordCount = count(array_filter(explode(' ', preg_replace('/\s+/', ' ', $line))));
                if ($wordCount >= 1 && $wordCount <= 5) {
                    return $line;
                }
            }
        }

        if (preg_match('/\b([A-Z][A-Z][A-Z]+(?:\s+[A-Z][A-Z][A-Z]+){1,3})\b/u', $text, $m)) {
            return trim((string) $m[1]);
        }

        return '';
    }

    private function isLikelyCityName(string $value): bool
    {
        static $cities = [
            'bangalore', 'bengaluru', 'mumbai', 'delhi', 'new delhi', 'chennai', 'hyderabad', 'pune',
            'kolkata', 'gurgaon', 'gurugram', 'noida', 'jaipur', 'ahmedabad', 'kochi', 'coimbatore',
            'indore', 'bhopal', 'lucknow', 'chandigarh', 'nagpur', 'surat', 'vadodara', 'mysore', 'mysuru',
        ];

        return in_array(strtolower(trim($value)), $cities, true);
    }

    private function textHasEducationSignals(string $text): bool
    {
        return (bool) preg_match(
            '/\b(education|academic|qualification|university|college|school|b\.?\s*tech|m\.?\s*tech|b\.?\s*sc|m\.?\s*sc|mba|bca|mca|diploma|cgpa|gpa|12th|10th|intermediate|matriculation)\b/i',
            $text
        );
    }

    private function isWorkExperienceEducationRow(array $item): bool
    {
        $hay = strtolower(implode(' ', array_filter([
            (string) ($item['degree'] ?? ''),
            (string) ($item['stream'] ?? ''),
            (string) ($item['institution'] ?? ''),
            (string) ($item['year'] ?? ''),
        ])));

        if ($hay === '') {
            return false;
        }

        if (preg_match('/[@+]?\d[\d\s().-]{7,}|email\s*:|phone\s*:|mob\s*:/i', $hay)) {
            return true;
        }

        $workSignals = preg_match(
            '/\b(ltd|limited|pvt|inc|llc|corp|cellular|teleservices|manager|sales|territory|deliverables|relationship|responsible for|key deliverables|business)\b/i',
            $hay
        );
        $eduSignals = preg_match(
            '/\b(b\.?\s*tech|m\.?\s*tech|bsc|msc|mba|bca|mca|diploma|university|college|school|cgpa|gpa|graduat|12th|10th|intermediate)\b/i',
            $hay
        );

        return $workSignals && ! $eduSignals;
    }

    private function educationSectionLooksCorrupted(array $items): bool
    {
        if (count($items) < 2) {
            return false;
        }
        $workLike = 0;
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }
            if ($this->isWorkExperienceEducationRow($item)) {
                $workLike++;
            }
        }

        return $workLike >= 2 || ($workLike / max(1, count($items))) >= 0.35;
    }

    private function extractLocationFromText(string $text): string
    {
        $header = $this->extractHeaderIdentity($text);
        if ($header['location'] !== '') {
            return $header['location'];
        }

        $patterns = [
            '/\b(?:location|address)\s*:\s*([^\n]{2,80})/i',
            '/\b([A-Za-z][A-Za-z]+(?:\s*,\s*[A-Za-z][A-Za-z]+){1,2})\b/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $m)) {
                $value = trim((string) ($m[1] ?? ''));
                $value = preg_replace('/\b(?:email|e-mail|phone|mobile|mob)\b.*$/i', '', $value);
                $value = preg_replace('/^[A-Z][A-Za-z]+(?:\s+[A-Z][A-Za-z]+){0,4}\s+/u', '', $value);
                $value = trim($value);
                if ($value !== '' && ! str_contains($value, '@')) {
                    $value = $this->sanitizeLocation($value);
                    if ($value !== '' && ! $this->isLikelyCityName($value)) {
                        return $value;
                    }
                    if ($value !== '' && $this->isLikelyCityName(explode(',', $value)[0] ?? '')) {
                        return $value;
                    }
                }
            }
        }

        return '';
    }

    private function extractBestPhone(string $text): string
    {
        preg_match_all('/(?:\+?\d[\d\s().-]{7,}\d)/', $text, $matches);
        $candidates = $matches[0] ?? [];
        if (empty($candidates)) return '';

        $best = '';
        foreach ($candidates as $candidate) {
            $clean = trim((string) $candidate);
            $digits = preg_replace('/\D+/', '', $clean);
            $len = strlen((string) $digits);
            if ($len < 10 || $len > 13) continue;
            if ($best === '' || $len < strlen((string) preg_replace('/\D+/', '', $best))) {
                $best = $clean;
            }
        }

        return $best;
    }

    private function sanitizeLocation(string $value): string
    {
        $value = trim(preg_replace('/\s+/', ' ', $value));
        if ($value === '') {
            return '';
        }
        $value = preg_replace('/\b(?:email|e-mail|phone|mobile|mob)\s*$/i', '', $value);
        $value = preg_replace('/\b(?:email|e-mail|phone|mobile|mob)\s*:.*$/i', '', $value);
        $value = preg_replace('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', '', $value);
        $value = preg_replace('/\+?\d[\d\s().-]{8,}\d/', '', $value);
        $value = preg_replace('/^[A-Z][A-Za-z]+(?:\s+[A-Z][A-Za-z]+){0,4}\s+(?=[A-Za-z])/u', '', $value);
        $value = trim(preg_replace('/\s+/', ' ', $value), " \t,");
        if ($value === '') {
            return '';
        }
        if (preg_match('/^(view|click|open|email|phone|mobile|contact)$/i', $value)) {
            return '';
        }
        if ($this->isBoilerplateLine($value)) {
            return '';
        }
        if (preg_match('/[@]/', $value)) {
            return '';
        }
        $value = preg_replace('/^\s*(view|flat|apartment|apartments|plot|tower|building)\b[\s,:-]*/i', '', $value);
        $value = trim((string) $value);
        if ($value === '' || $this->isBoilerplateLine($value)) {
            return '';
        }

        return $value;
    }

    private function isBoilerplateLine(string $value): bool
    {
        $line = strtolower(trim($value));
        if ($line === '') return false;
        return (bool) preg_match('/\b(view|apartments?|apartment|flat|tower|building|project|brochure|catalog|menu|invoice|receipt|property)\b/i', $line);
    }

    private function detectSections(string $text): array
    {
        // Heading lines can appear as: "EXPERIENCE", "Work Experience", "1. Experience", "• Experience", "PROJECTS:"
        $headingRegex = '/^\s*(?:[-•*]\s*)?(?:\d+[\.\)]\s*)?(?:(?:([A-Z][A-Z\s&]{2,58}))|(?:([A-Z][a-z]+(?:\s+[A-Z][a-z]+){0,4}):?))\s*$/m';

        preg_match_all($headingRegex, $text, $matches, PREG_OFFSET_CAPTURE);

        $headings = [];
        foreach ($matches[0] as $match) {
            $headings[] = ['label' => trim($match[0]), 'offset' => $match[1]];
        }

        $canonicalMap = [
            'summary'        => ['summary', 'professional summary', 'profile', 'objective', 'career objective', 'about me', 'about'],
            'skills'         => ['skills', 'technical skills', 'core skills', 'key skills', 'technologies', 'competencies', 'expertise', 'tech stack', 'tools'],
            'experience'     => ['experience', 'work experience', 'professional experience', 'employment', 'career history', 'internship', 'internships', 'work history'],
            'education'      => ['education', 'academics', 'qualification', 'academic background', 'educational background'],
            'projects'       => ['projects', 'project', 'portfolio', 'academic projects', 'personal projects', 'selected projects', 'key projects'],
            'certifications' => ['certifications', 'certification', 'certificates', 'licenses', 'credentials'],
            'languages'      => ['languages', 'language'],
            'achievements'   => ['achievements', 'awards', 'honours', 'honors', 'publications', 'accomplishments', 'highlights'],
        ];

        $sections = [];
        $textLen  = mb_strlen($text);

        foreach ($headings as $idx => $heading) {
            $labelLower = strtolower($heading['label']);
            $canonical  = null;

            foreach ($canonicalMap as $key => $variants) {
                foreach ($variants as $v) {
                    if (str_contains($labelLower, $v)) {
                        $canonical = $key;
                        break 2;
                    }
                }
            }

            if (!$canonical) continue;

            $start = $heading['offset'] + mb_strlen($heading['label']);
            $end   = $headings[$idx + 1]['offset'] ?? $textLen;
            $body  = mb_substr($text, $start, $end - $start);
            $body  = trim(preg_replace('/\s*\n\s*/', "\n", $body));

            if (!isset($sections[$canonical])) {
                $sections[$canonical] = $body;
            }
        }

        return $sections;
    }

    private function isExperienceCompanyLine(string $line): bool
    {
        $line = trim($line);
        if ($line === '') {
            return false;
        }

        if ((bool) preg_match(
            '/\b(LTD|LIMITED|PVT\.?|INC|LLC|CORP|CORPORATION|TELESERVICES|CELLULAR|SERVICES|SOLUTIONS|SYSTEMS)\b/i',
            $line
        )) {
            return true;
        }

        if (
            (bool) preg_match('/^[A-Z0-9][A-Z0-9\s&.\'-]{4,}$/u', $line)
            && ! preg_match('/\b(manager|deliverables|responsible|summary|skills?|education)\b/i', $line)
        ) {
            return true;
        }

        // Title-case company names like "Google", "Infosys", "Tata Consultancy Services".
        if (
            (bool) preg_match('/^[A-Z][A-Za-z0-9&.\'-]+(?:\s+[A-Z][A-Za-z0-9&.\'-]+){0,5}$/u', $line)
            && ! preg_match('/\b(summary|skills?|experience|education|projects?|languages?)\b/i', $line)
            && ! $this->isExperienceRoleLine($line)
            && mb_strlen($line) <= 70
        ) {
            return true;
        }

        return false;
    }

    private function isExperienceRoleLine(string $line): bool
    {
        return (bool) preg_match(
            '/\b(manager|engineer|developer|analyst|designer|lead|consultant|associate|specialist|director|officer|executive|intern|relationship|territory|sales|representative|head)\b/i',
            $line
        ) && ! $this->isExperienceCompanyLine($line);
    }

    private function parseExperienceText(string $text): array
    {
        $lines   = array_filter(array_map('trim', preg_split('/\R/', $text)));
        $entries = [];
        $current = null;

        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            if (preg_match('/^\s*key\s+deliverables\s*:?\s*/iu', $line)) {
                $line = trim((string) preg_replace('/^\s*key\s+deliverables\s*:?\s*/iu', '', $line));
                if ($line === '') {
                    continue;
                }
            }
            if (preg_match('/^\s*(?:key\s+responsibilit(?:y|ies)|responsibilit(?:y|ies)|duties)\s*:?\s*$/iu', $line)) {
                continue;
            }

            if (preg_match('/^(.+?)\s+\bat\b\s+(.+)$/iu', $line, $m)
                && $this->isExperienceRoleLine(trim($m[1]))
                && $this->isExperienceCompanyLine(trim($m[2]))) {
                if ($current) {
                    $entries[] = $current;
                }
                $current = [
                    'company' => trim($m[2]),
                    'role'    => trim($m[1]),
                    'period'  => '',
                    'points'  => [],
                ];
                continue;
            }

            if ($this->isExperienceCompanyLine($line)) {
                if ($current) {
                    $entries[] = $current;
                }
                $current = ['company' => $line, 'role' => '', 'period' => '', 'points' => []];
                continue;
            }

            if ($current && $current['role'] === '' && $this->isExperienceRoleLine($line)) {
                $current['role'] = trim((string) preg_replace('/\s*key\s+deliverables\s*:?\s*$/iu', '', $line));
                continue;
            }
            if ($current && $current['company'] === '' && $this->isExperienceCompanyLine($line)) {
                $current['company'] = $line;
                continue;
            }

            $hasPeriod  = (bool) preg_match('/\b(?:jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec|\d{4}|present)\b/i', $line);
            $isBullet   = (bool) preg_match('/^\s*(?:[-*•◦▪▸]|\d+\.)\s+/u', $line);
            $isShortish = mb_strlen($line) <= 120;
            if ($current && trim((string) ($current['period'] ?? '')) === '' && $hasPeriod
                && preg_match('/((?:jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)?\s*\d{4}\s*(?:-|to|–|—)\s*(?:present|(?:jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)?\s*\d{4}))/iu', $line, $pmOnly)) {
                $current['period'] = trim($pmOnly[1]);
                continue;
            }

            $looksLikeTitle = $isShortish && ! $isBullet && (
                $hasPeriod ||
                preg_match('/\||-|–|—|\bat\s/i', $line) ||
                $this->isExperienceRoleLine($line)
            );

            if ($looksLikeTitle && ! $this->isExperienceCompanyLine($line)) {
                if ($current) {
                    $entries[] = $current;
                }

                $period = '';
                if ($hasPeriod && preg_match('/((?:jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)?\s*\d{4}\s*(?:-|to|–|—)\s*(?:present|(?:jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)?\s*\d{4}))/iu', $line, $pm)) {
                    $period = trim($pm[1]);
                    $line   = trim(str_replace($pm[1], '', $line));
                }

                $parts = array_values(array_filter(array_map('trim', preg_split('/\s*(?:\||–|—|,\s*(?=[A-Z]))\s*/', $line))));

                $current = [
                    'role'    => trim((string) preg_replace('/\s*key\s+deliverables\s*:?\s*$/iu', '', $parts[0] ?? $line)),
                    'company' => $parts[1] ?? '',
                    'period'  => $period ?: ($parts[2] ?? ''),
                    'points'  => [],
                ];
                continue;
            }

            if (! $current) {
                $current = ['role' => '', 'company' => '', 'period' => '', 'points' => []];
            }

            $point = trim(preg_replace('/^\s*[-*•◦▪▸\d.]+\s*/u', '', $line));
            if ($point !== '' && ! preg_match('/^(key deliverables|responsible for)\s*:?\s*$/i', $point)) {
                $current['points'][] = $point;
            }
        }

        if ($current) {
            $entries[] = $current;
        }

        return array_values(array_filter($entries, function ($e) {
            $role    = trim((string) ($e['role'] ?? ''));
            $company = trim((string) ($e['company'] ?? ''));
            $points  = array_filter($e['points'] ?? [], fn ($p) => trim((string) $p) !== '');

            return $company !== '' || $role !== '' || ! empty($points);
        }));
    }

    private function parseEducationText(string $text): array
    {
        $lines  = array_filter(array_map('trim', preg_split('/\R/', $text)));
        $rows   = [];
        $current = null;

        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            if ($this->isWorkExperienceEducationRow(['degree' => $line, 'stream' => '', 'institution' => '', 'year' => ''])) {
                continue;
            }

            $hasYear = (bool) preg_match('/\b(?:19|20)\d{2}\b/', $line);

            if ($current === null) {
                $current = ['degree' => $line, 'stream' => '', 'institution' => '', 'year' => ''];
                if ($hasYear && preg_match('/\b(?:19|20)\d{2}(?:\s*[-–]\s*(?:\d{4}|present))?\b/i', $line, $ym)) {
                    $current['year'] = $ym[0];
                }
                continue;
            }

            if ($hasYear && $current['year'] === '') {
                preg_match('/\b(?:19|20)\d{2}(?:\s*[-–]\s*(?:\d{4}|present))?\b/i', $line, $ym);
                $current['year'] = $ym[0] ?? '';
                $rest = trim(str_replace($current['year'], '', $line), " ,|-\t");
                if ($rest && $current['institution'] === '') $current['institution'] = $rest;
                continue;
            }

            if ($current['institution'] === '') {
                $rows[]  = $current;
                $current = ['degree' => $line, 'stream' => '', 'institution' => '', 'year' => ''];
                continue;
            }

            if ($current['stream'] === '') {
                $current['stream'] = $line;
            } else {
                $current['institution'] .= ', ' . $line;
            }
        }

        if ($current && array_filter($current)) $rows[] = $current;

        return array_values(array_filter($rows, fn($r) => array_filter($r)));
    }

    private function parseProjectsText(string $text): array
    {
        $lines    = array_filter(array_map('trim', preg_split('/\R/', $text)));
        $projects = [];
        $current  = null;

        foreach ($lines as $line) {
            $isBullet = (bool) preg_match('/^\s*[-*•◦▪▸]\s+/u', $line);
            // Match:
            //  - http(s)://...
            //  - www....
            //  - bare domains like "example.com/" (common in portfolios)
            $isUrl = (bool) preg_match('/(?:https?:\/\/|www\.)\S+/i', $line)
                || (bool) preg_match('/\b(?:[a-z0-9-]+\.)+[a-z]{2,}(?:\/[^\s,;]*)?\b/i', $line);

            if (!$isBullet && !$isUrl && mb_strlen($line) < 130) {
                if ($current !== null) $projects[] = $current;

                [$title, $tech] = $this->splitOnTechDelimiter($line);
                $current = ['name' => $title, 'tech' => $tech, 'tech_stack' => $tech, 'link' => '', 'description' => ''];
                continue;
            }

            if ($current === null) {
                $current = ['name' => $line, 'tech' => '', 'tech_stack' => '', 'link' => '', 'description' => ''];
                continue;
            }

            if ($isUrl) {
                $current['link'] = $line;
                continue;
            }

            $clean = trim(preg_replace('/^\s*[-*•◦▪▸]\s+/u', '', $line));
            $current['description'] = trim($current['description'] . ' ' . $clean);
        }

        if ($current !== null) $projects[] = $current;

        return array_values(array_filter($projects, fn($p) => !empty(array_filter($p))));
    }

    private function splitOnTechDelimiter(string $line): array
    {
        if (preg_match('/^(.+?)\s*(?:\||–|—|:)\s*(.+)$/', $line, $m)) {
            return [trim($m[1]), trim($m[2])];
        }
        return [$line, ''];
    }

    private function parseNamedList(string $text): array
    {
        if (!$text) return [];

        $lines = array_filter(array_map('trim', preg_split('/\R|[•;]/', $text)));

        return array_values(array_filter(array_map(function ($line) {
            $line = trim(preg_replace('/^[-*•◦▪▸\d.]+\s*/u', '', $line));
            return $line ? ['name' => $line, 'description' => ''] : null;
        }, $lines)));
    }

    private function parseLanguageList(string $text): array
    {
        if (!$text) return [];

        $known = [
            'english','hindi','german','spanish','french','marathi','bengali','tamil','telugu',
            'kannada','malayalam','punjabi','urdu','arabic','chinese','korean','japanese'
        ];
        $lines = array_filter(array_map('trim', preg_split('/\R|[,;•\/]/', $text)));

        $parsed = array_values(array_filter(array_map(function ($line) use ($known) {
            $line = trim(preg_replace('/^[-*•◦▪▸\d.]+\s*/u', '', $line));
            $line = trim((string) preg_replace('/^\s*languages?\s*:?\s*/iu', '', $line));
            if (!$line) return null;

            if (preg_match('/^(.+?)\s*(?:–|-|:|\()\s*(.+?)\)?$/', $line, $m)) {
                $name = trim($m[1]);
                $level = trim($m[2]);
                foreach ($known as $lang) {
                    if (preg_match('/\b'.preg_quote($lang, '/').'\b/i', $name)) {
                        return ['name' => ucfirst($lang), 'level' => $level];
                    }
                }
                return ['name' => $name, 'level' => $level];
            }

            foreach ($known as $lang) {
                if (preg_match('/\b'.preg_quote($lang, '/').'\b/i', $line)) {
                    return ['name' => ucfirst($lang), 'level' => ''];
                }
            }

            return ['name' => $line, 'level' => ''];
        }, $lines)));

        return array_values(array_unique($parsed, SORT_REGULAR));
    }

    // ═════════════════════════════════════════════
    // LOCAL ATS ANALYSIS  (Gemini unavailable)
    // ═════════════════════════════════════════════

    private function localAtsAnalysis(array $resume, string $jobRole = 'General', ?string $jobDescription = null): array
    {
        $resume      = $this->normalizeResume($resume);
        $textCorpus  = strtolower(implode(' ', [
            $resume['summary'] ?? '',
            implode(' ', $resume['skills'] ?? []),
            collect($resume['experience'] ?? [])->pluck('points')->flatten()->join(' '),
            collect($resume['projects'] ?? [])->map(fn($p) => ($p['name'] ?? '') . ' ' . ($p['tech'] ?? '') . ' ' . ($p['description'] ?? ''))->join(' '),
            $jobDescription ?? '',
        ]));

        $keywords = [];
        if ($jobDescription) {
            preg_match_all('/\b[A-Za-z][A-Za-z.+#\-]{2,}\b/', $jobDescription, $jdMatches);
            $stopWords = ['and', 'the', 'for', 'with', 'you', 'our', 'are', 'will', 'this', 'that', 'from', 'your', 'have', 'has', 'been', 'not'];
            $keywords  = collect($jdMatches[0] ?? [])
                ->map('trim')
                ->reject(fn($t) => in_array(strtolower($t), $stopWords, true))
                ->unique(fn($t) => strtolower($t))
                ->take(15)
                ->values()
                ->all();
        }

        $missing    = array_values(array_filter($keywords, fn($kw) => !str_contains($textCorpus, strtolower($kw))));
        $strengths  = [];
        $weaknesses = [];
        $suggestions = [];
        $score       = 20;

        if (!empty($resume['email']) && !empty($resume['mobile'])) {
            $score += 12; $strengths[] = 'Contact information is complete with email and phone.';
        } else {
            $weaknesses[] = 'Add complete contact info (email + phone).';
        }

        $summaryWords = str_word_count(strip_tags((string) ($resume['summary'] ?? '')));
        if ($summaryWords >= 30) {
            $score += 14; $strengths[] = 'Professional summary is present and sufficiently detailed.';
        } else {
            $weaknesses[]  = 'Professional summary is missing or too short.';
            $suggestions[] = 'Write a 3-5 sentence summary highlighting your target role, core skills, and value.';
        }

        if (count($resume['skills'] ?? []) >= 5) {
            $score += 14; $strengths[] = 'Skills section has ATS-readable keywords.';
        } else {
            $weaknesses[]  = 'Skills section needs more keywords.';
            $suggestions[] = 'Add tools, languages, frameworks, and methods from the job description.';
        }

        $bulletCount = collect($resume['experience'] ?? [])->pluck('points')->flatten()->filter()->count();
        if ($bulletCount >= 3) {
            $score += 16; $strengths[] = 'Experience section has bullet-point detail.';
        } else {
            $weaknesses[]  = 'Experience bullets are missing or sparse.';
            $suggestions[] = 'Add 3-5 bullets per role with action verbs, tools used, and measurable outcomes.';
        }

        if (count($resume['education'] ?? []) > 0) {
            $score += 8;
        } else {
            $weaknesses[]  = 'Education section is missing.';
            $suggestions[] = 'Add your degree, institution, and graduation year.';
        }

        if (count($resume['projects'] ?? []) > 0) {
            $score += 8; $strengths[] = 'Projects section demonstrates practical experience.';
        } else {
            $suggestions[] = 'Add 1-3 relevant projects with tech stack and outcomes.';
        }

        $matchedCount = count($keywords) - count($missing);
        $score       += min(18, $matchedCount * 3);

        foreach (array_slice($missing, 0, 8) as $kw) {
            $suggestions[] = "Consider adding '{$kw}' if it reflects your actual experience.";
        }

        return [
            'success'          => true,
            'score'            => max(0, min(100, $score)),
            'strengths'        => array_values(array_unique($strengths ?: ['Resume is readable and parseable.'])),
            'weaknesses'       => array_values(array_unique($weaknesses)),
            'missing_keywords' => array_slice($missing, 0, 10),
            'suggestions'      => array_values(array_unique($suggestions)),
            'improved_resume'  => $resume,
        ];
    }

    // ═════════════════════════════════════════════
    // HELPERS
    // ═════════════════════════════════════════════

    private function resumeToText(array $resume): string
    {
        $lines = [];

        $fullName = trim(($resume['name'] ?? '') . ' ' . ($resume['last_name'] ?? ''));
        if ($fullName) $lines[] = $fullName;
        if ($resume['designation'] ?? '') $lines[] = $resume['designation'];
        if ($resume['email'] ?? '')    $lines[] = $resume['email'];
        if ($resume['mobile'] ?? '')   $lines[] = $resume['mobile'];
        if ($resume['location'] ?? '') $lines[] = $resume['location'];

        foreach ($resume['social_links'] ?? [] as $link) {
            $lines[] = $link;
        }

        if ($resume['summary'] ?? '') {
            $lines[] = '';
            $lines[] = 'SUMMARY';
            $lines[] = $resume['summary'];
        }

        if (!empty($resume['skills'])) {
            $lines[] = '';
            $lines[] = 'SKILLS';
            $lines[] = implode(', ', $resume['skills']);
        }

        if (!empty($resume['experience'])) {
            $lines[] = '';
            $lines[] = 'EXPERIENCE';
            foreach ($resume['experience'] as $exp) {
                $lines[] = implode(' | ', array_filter([$exp['role'] ?? '', $exp['company'] ?? '', $exp['period'] ?? '']));
                foreach ($exp['points'] ?? [] as $pt) {
                    $lines[] = '• ' . $pt;
                }
            }
        }

        if (!empty($resume['education'])) {
            $lines[] = '';
            $lines[] = 'EDUCATION';
            foreach ($resume['education'] as $edu) {
                $lines[] = implode(', ', array_filter([$edu['degree'] ?? '', $edu['stream'] ?? '', $edu['institution'] ?? '', $edu['year'] ?? '']));
            }
        }

        if (!empty($resume['projects'])) {
            $lines[] = '';
            $lines[] = 'PROJECTS';
            foreach ($resume['projects'] as $proj) {
                $lines[] = ($proj['name'] ?? '') . (($proj['tech'] ?? '') ? ' | ' . $proj['tech'] : '');
                if ($proj['link'] ?? '') $lines[] = $proj['link'];
                if ($proj['description'] ?? '') $lines[] = $proj['description'];
            }
        }

        if (!empty($resume['certifications'])) {
            $lines[] = '';
            $lines[] = 'CERTIFICATIONS';
            foreach ($resume['certifications'] as $cert) {
                $lines[] = implode(' – ', array_filter([$cert['name'] ?? '', $cert['description'] ?? '']));
            }
        }

        if (!empty($resume['languages'])) {
            $lines[] = '';
            $lines[] = 'LANGUAGES';
            foreach ($resume['languages'] as $lang) {
                $lines[] = implode(': ', array_filter([$lang['name'] ?? '', $lang['level'] ?? '']));
            }
        }

        if (!empty($resume['achievements'])) {
            $lines[] = '';
            $lines[] = 'ACHIEVEMENTS';
            foreach ($resume['achievements'] as $ach) {
                $lines[] = implode(' – ', array_filter([$ach['name'] ?? '', $ach['description'] ?? '']));
            }
        }

        return implode("\n", $lines);
    }

    private function decodeGeminiJson(string $text): array
    {
        if (! $text) return [];

        $candidate = trim($text);
        $candidate = preg_replace('/^```(?:json)?\s*/i', '', $candidate) ?? $candidate;
        $candidate = preg_replace('/\s*```$/', '', $candidate) ?? $candidate;
        $candidate = str_replace('```', '', $candidate);
        $candidate = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $candidate) ?? $candidate;
        $candidate = trim($candidate);

        $decoded = json_decode($candidate, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) return $decoded;

        $object = $this->extractBalancedJsonObject($candidate);
        if ($object !== '') {
            $decoded = json_decode($object, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) return $decoded;
        }

        $repaired = $this->repairTruncatedJsonObject($candidate);
        if ($repaired !== '') {
            $decoded = json_decode($repaired, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) return $decoded;
        }

        return [];
    }

    private function repairTruncatedJsonObject(string $text): string
    {
        $start = strpos($text, '{');
        if ($start === false) {
            return '';
        }

        $slice = substr($text, $start);
        $slice = preg_replace('/```/u', '', $slice) ?? $slice;
        $slice = rtrim($slice);

        if (preg_match('/,\s*"([a-zA-Z_][a-zA-Z0-9_]*)"\s*$/', $slice)) {
            $slice = preg_replace('/,\s*"([a-zA-Z_][a-zA-Z0-9_]*)"\s*$/', ',"$1": ""', $slice) ?? $slice;
        }

        if (preg_match('/:\s*"[^"]*$/', $slice)) {
            $slice .= '"';
        }

        $slice = preg_replace('/,\s*$/', '', $slice) ?? $slice;

        $openBraces = substr_count($slice, '{');
        $closeBraces = substr_count($slice, '}');
        if ($openBraces > $closeBraces) {
            $slice .= str_repeat('}', $openBraces - $closeBraces);
        }

        $openBrackets = substr_count($slice, '[');
        $closeBrackets = substr_count($slice, ']');
        if ($openBrackets > $closeBrackets) {
            $slice .= str_repeat(']', $openBrackets - $closeBrackets);
        }

        return $slice;
    }

    /**
     * Recover a partial improved_resume when Gemini JSON was truncated mid-stream.
     *
     * @return array<string, mixed>
     */
    private function salvageGeminiResumeFromText(string $text): array
    {
        if (preg_match('/"improved_resume"\s*:\s*(\{.*)/s', $text, $match)) {
            $inner = $this->repairTruncatedJsonObject($match[1]);
            if ($inner !== '') {
                $decoded = json_decode('{"improved_resume":'.$inner.'}', true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded['improved_resume'] ?? null)) {
                    return $decoded['improved_resume'];
                }
            }
        }

        $repaired = $this->repairTruncatedJsonObject($text);
        if ($repaired !== '') {
            $decoded = json_decode($repaired, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                if (is_array($decoded['improved_resume'] ?? null)) {
                    return $decoded['improved_resume'];
                }
                if ($this->looksLikeResumeObject($decoded)) {
                    return $decoded;
                }
            }
        }

        $resume = [];
        $scalarFields = [
            'name', 'last_name', 'designation', 'desired_job_role', 'email', 'mobile',
            'location', 'linkedin', 'github', 'portfolio', 'link', 'summary',
        ];

        foreach ($scalarFields as $field) {
            if (preg_match('/"'.preg_quote($field, '/').'"\s*:\s*"((?:[^"\\\\]|\\\\.)*)"/s', $text, $m)) {
                $resume[$field] = stripcslashes($m[1]);
            }
        }

        if (preg_match('/"skills"\s*:\s*\[(.*?)\]/s', $text, $m)) {
            if (preg_match_all('/"((?:[^"\\\\]|\\\\.)*)"/', $m[1], $skills)) {
                $resume['skills'] = array_values(array_filter(array_map(
                    fn ($s) => stripcslashes($s),
                    $skills[1] ?? []
                )));
            }
        }

        return array_filter($resume, fn ($v) => $v !== '' && $v !== []);
    }

    private function looksLikeResumeObject(array $data): bool
    {
        $keys = ['name', 'last_name', 'designation', 'email', 'skills', 'experience', 'education', 'projects'];
        $hits = 0;
        foreach ($keys as $k) {
            if (array_key_exists($k, $data)) $hits++;
        }
        return $hits >= 4;
    }

    private function extractBalancedJsonObject(string $text): string
    {
        $start = strpos($text, '{');
        if ($start === false) return '';

        $depth = 0;
        $inString = false;
        $escape = false;
        $len = strlen($text);

        for ($i = $start; $i < $len; $i++) {
            $ch = $text[$i];

            if ($escape) {
                $escape = false;
                continue;
            }

            if ($ch === '\\') {
                $escape = true;
                continue;
            }

            if ($ch === '"') {
                $inString = ! $inString;
                continue;
            }

            if ($inString) {
                continue;
            }

            if ($ch === '{') $depth++;
            if ($ch === '}') {
                $depth--;
                if ($depth === 0) {
                    return substr($text, $start, $i - $start + 1);
                }
            }
        }

        return '';
    }

    /**
     * Gemini-first autofill (Resume Maker upload & Enhance CV parse step).
     *
     * @return array{builder:array,source:string,standard:array}
     */
    private function buildHybridAutofillResume(
        array $parseResult,
        string $extractedText,
        string $jobRole,
        ?string $jobDescription
    ): array {
        $standardJson   = $parseResult['standard'] ?? ResumeSchema::empty();
        $parseSource    = $parseResult['source'] ?? 'gemini';
        $parsedBuilder  = is_array($parseResult['builder'] ?? null) ? $this->normalizeResume($parseResult['builder']) : [];

        $localHints = $this->postProcessGeminiResume($this->localParseResume($extractedText));

        if ($this->resumeHasContent($parsedBuilder)) {
            $geminiAnalysis = ['success' => true, 'improved_resume' => $parsedBuilder];
        } else {
            $geminiAnalysis = $this->geminiExtractAndAutofill($extractedText, $jobRole, $jobDescription, $localHints);
        }

        $geminiBuilder = Arr::get($geminiAnalysis, 'success', true)
            ? $this->normalizeResume(Arr::get($geminiAnalysis, 'improved_resume', []))
            : [];

        if (! Arr::get($geminiAnalysis, 'success', true)) {
            \Log::warning('Hybrid autofill: Gemini unavailable', [
                'message'             => Arr::get($geminiAnalysis, 'message'),
                'parser_source'       => $parseSource,
            ]);
        }

        $builder = [];
        $source  = $parseSource;

        if ($this->resumeHasContent($geminiBuilder)) {
            $builder = $geminiBuilder;
            $source  = 'gemini';
        }

        if (! $this->resumeHasContent($builder)) {
            $builder = $this->resumeHasContent($geminiBuilder)
                ? $this->mergeAffindaPrimary($geminiBuilder, $localHints) // AFFINDA DISABLED: legacy merge helper name.
                : ($this->resumeHasContent($localHints) ? $localHints : $geminiBuilder);
            $source = match (true) {
                $this->resumeHasContent($geminiBuilder) && $this->resumeHasContent($localHints) => 'gemini+local',
                $this->resumeHasContent($geminiBuilder) => 'gemini',
                default => 'local',
            };
        }

        $builder     = $this->finalizeParsedResume($builder, false, $extractedText);
        $standardJson = app(ResumeNormalizerService::class)->fromBuilderFormat($builder);

        return [
            'builder'  => $builder,
            'source'   => $source,
            'standard' => $standardJson,
        ];
    }

    /**
     * @return list<string>
     */
    private function hybridAutofillSuggestions(string $parseSource): array
    {
        return match ($parseSource) {
            'gemini', 'gemini+local' => ['Resume imported with AI extraction — fields filled automatically.'],
            default          => ['Resume imported with local parsing — review all fields before saving.'],
        };
    }

    /**
     * Convert raw exceptions into helpful user-facing error messages
     */
    private function getHelpfulErrorMessage(\Exception $e): string
    {
        $message = $e->getMessage();

        // Gemini API rate limit
        if (str_contains($message, '429') || str_contains($message, 'quota') || str_contains($message, 'rate')) {
            return 'AI service is temporarily rate-limited. Please try again in a few moments.';
        }

        if (str_contains($message, '404') || str_contains($message, 'not found')) {
            return 'Resume parsing service is misconfigured. Please contact support. (Code: 404)';
        }

        // Timeout
        if (str_contains($message, 'timeout') || str_contains($message, 'Maximum execution')) {
            return 'Resume processing took too long. Try uploading a smaller or simpler resume.';
        }

        // File not readable
        if (str_contains($message, 'not readable') || str_contains($message, 'permission denied')) {
            return 'Could not read the uploaded file. Ensure it is a valid PDF, DOC, or DOCX document.';
        }

        // File extraction failed
        if (str_contains($message, 'extract') || str_contains($message, 'parse')) {
            return 'The resume could not be read. Ensure it is a text-based PDF (not an image/scan) or valid DOCX.';
        }

        // Generic API errors
        if (str_contains($message, 'connection') || str_contains($message, 'network')) {
            return 'Network error occurred. Please check your connection and try again.';
        }

        // Invalid JSON responses
        if (str_contains($message, 'JSON') || str_contains($message, 'decode')) {
            return 'Service returned invalid data. This is usually temporary. Please try again.';
        }

        // Default: return generic message but log the actual one
        \Log::warning('Unhelpful error passed through: ' . $message);
        return 'Resume upload failed. Please try a different file or contact support.';
    }

    /**
     * AFFINDA DISABLED
     * Legacy method name retained; this is now a Gemini second-pass review for existing builder data.
     */
    private function geminiReviewAffindaAndFill(
        array $affindaBuilder,
        string $rawText,
        string $jobRole,
        ?string $jobDescription
    ): array {
        $normalized = $this->normalizeResume($affindaBuilder);
        $resumeJson = json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $roleHint = trim($jobRole) !== '' ? $jobRole : 'General';
        $jdHint = $this->scalarString($jobDescription);
        $jdText = $jdHint !== '' ? $jdHint : 'N/A';

        $prompt = <<<PROMPT
Review this parsed resume.

Rules:
- Preserve all existing fields.
- Do not remove entries.
- Do not merge entries.
- Fill missing fields from RAW_TEXT only.
- Fix wrong section placement when clear from RAW_TEXT.
- Improve grammar and summary quality.
- Return JSON only.

MUST NOT:
- Remove experience/company entries.
- Reduce company count.
- Remove education rows.
- Delete skills.
- Overwrite correct existing values.

MERGE POLICY:
- Existing non-empty values are source of truth.
- Fill blanks with stronger values from RAW_TEXT.

TARGET_ROLE:
{$roleHint}

JOB_DESCRIPTION:
{$jdText}

PARSED_RESUME_JSON:
{$resumeJson}

Return STRICT JSON only:
{
  "improved_resume": { }
}

IMPORTANT OUTPUT SIZE RULE:
- Include ONLY fields you add, fix, or improve.
- OMIT keys that are already correct in PARSED_RESUME_JSON.
- For sections (experience, education, projects, skills, summary, linkedin, github, portfolio, social_links), include a section ONLY if you are adding rows/items or filling blanks.
- Do NOT repeat unchanged existing values.

RAW_TEXT:
PROMPT;

        $prompt .= "\n" . mb_substr($rawText, 0, 10000);

        return $this->callGemini($prompt, $normalized, $this->geminiMaxTokens());
    }

    private function ensureSummaryFilled(array $resume, string $text): array
    {
        $summary = $this->scalarString($resume['summary'] ?? '');
        if ($summary !== '' && mb_strlen($summary) > 15) {
            return $resume;
        }

        $sections = $this->detectSections($text);
        if (! empty($sections['summary'])) {
            $candidate = trim(preg_replace('/\s+/', ' ', $sections['summary']));
            if (mb_strlen($candidate) > 15 && ! $this->looksLikeSkillListText($candidate)) {
                $resume['summary'] = $candidate;

                return $resume;
            }
        }

        $inferred = $this->inferSummaryFromText($text);
        if ($inferred !== '' && ! $this->looksLikeSkillListText($inferred)) {
            $resume['summary'] = $inferred;

            return $resume;
        }

        $fromRegex = $this->extractSummaryFromText($text);
        if ($fromRegex !== '') {
            $resume['summary'] = $fromRegex;

            return $resume;
        }

        $designation = $this->scalarString($resume['designation'] ?? $resume['job_title'] ?? '');
        $skills      = is_array($resume['skills'] ?? null) ? $resume['skills'] : [];
        if ($designation !== '' && count($skills) >= 4) {
            $resume['summary'] = sprintf(
                '%s with strengths in %s.',
                $designation,
                implode(', ', array_slice(array_map(fn ($s) => $this->scalarString($s), $skills), 0, 8))
            );
        }

        return $resume;
    }

    /**
     * Fill empty experience periods and bullet lists from the raw experience section.
     */
    private function enrichExperienceFromRawText(array $resume, string $text): array
    {
        $experience = is_array($resume['experience'] ?? null) ? $resume['experience'] : [];
        if ($experience === []) {
            return $resume;
        }

        $needsEnrich = false;
        foreach ($experience as $exp) {
            if (! is_array($exp)) {
                continue;
            }
            $points = array_filter($exp['points'] ?? [], fn ($p) => trim((string) $p) !== '');
            if ($points === [] || trim((string) ($exp['period'] ?? '')) === '') {
                $needsEnrich = true;
                break;
            }
        }

        if (! $needsEnrich) {
            return $resume;
        }

        $sections = $this->detectSections($text);
        $expText  = trim((string) ($sections['experience'] ?? ''));
        // Fallback: if section detection fails (common in DOC/DOCX layouts), parse from whole text.
        if ($expText === '') {
            $expText = $text;
        }

        $parsed = $this->parseExperienceText($expText);
        if ($parsed === []) {
            return $resume;
        }

        foreach ($experience as $i => $exp) {
            if (! is_array($exp)) {
                continue;
            }

            $company = strtolower(trim((string) ($exp['company'] ?? '')));
            $role    = strtolower(trim((string) ($exp['role'] ?? '')));

            foreach ($parsed as $pj) {
                if (! is_array($pj)) {
                    continue;
                }

                $pCompany = strtolower(trim((string) ($pj['company'] ?? '')));
                $pRole    = strtolower(trim((string) ($pj['role'] ?? '')));

                $companyMatch = $company !== '' && $pCompany !== ''
                    && (str_contains($pCompany, $company) || str_contains($company, $pCompany));
                $roleMatch = $role !== '' && $pRole !== ''
                    && (str_contains($pRole, $role) || str_contains($role, $pRole));

                if (! $companyMatch && ! $roleMatch) {
                    continue;
                }

                if (trim((string) ($exp['period'] ?? '')) === '' && trim((string) ($pj['period'] ?? '')) !== '') {
                    $experience[$i]['period'] = trim((string) $pj['period']);
                }

                $points = array_filter($exp['points'] ?? [], fn ($p) => trim((string) $p) !== '');
                $pjPts  = array_filter($pj['points'] ?? [], fn ($p) => trim((string) $p) !== '');
                if ($points === [] && $pjPts !== []) {
                    $experience[$i]['points'] = array_values($pjPts);
                }

                break;
            }
        }

        $resume['experience'] = $experience;

        return $resume;
    }

    private function pickNonBlank(mixed $primary, mixed $fallback): string
    {
        $p = $this->scalarString($primary);
        if ($p !== '') {
            return $p;
        }

        return $this->scalarString($fallback);
    }

    /**
     * Recover missing identity fields from raw text (name/designation/contact/links).
     * Never overwrites non-empty values.
     */
    private function enrichIdentityFromRawText(array $resume, string $text): array
    {
        // localParseResume does lightweight extraction without Inventing; use as a fallback source.
        $local = $this->localParseResume($text);

        $map = [
            'name'           => $local['name'] ?? '',
            'last_name'      => $local['last_name'] ?? '',
            'designation'    => $local['designation'] ?? $local['job_title'] ?? '',
            'email'          => $local['email'] ?? '',
            'mobile'         => $local['mobile'] ?? '',
            'location'       => $local['location'] ?? '',
            'linkedin'       => $local['linkedin'] ?? '',
            'github'         => $local['github'] ?? '',
            'portfolio'      => $local['portfolio'] ?? $local['website'] ?? $local['link'] ?? '',
            'link'           => $local['link'] ?? $local['portfolio'] ?? $local['website'] ?? '',
            'summary'        => $local['summary'] ?? '',
        ];

        foreach ($map as $key => $fallbackVal) {
            if ($this->scalarString($resume[$key] ?? '') === '' && $this->scalarString($fallbackVal) !== '') {
                $resume[$key] = $this->scalarString($fallbackVal);
            }
        }

        // Social links: if current is empty, adopt inferred; otherwise keep existing.
        if (empty($resume['social_links'] ?? []) && ! empty($local['social_links'] ?? [])) {
            $resume['social_links'] = array_values(array_unique(array_filter(array_merge(
                is_array($local['social_links'] ?? null) ? $local['social_links'] : [],
                []
            ))));
        }

        return $resume;
    }

    private function enrichProjectsFromRawText(array $resume, string $text): array
    {
        $projects = is_array($resume['projects'] ?? null) ? $resume['projects'] : [];
        $needs = $projects === [] || collect($projects)->every(fn ($p) => is_array($p) && ($this->scalarString($p['link'] ?? '') === ''));

        if (! $needs) {
            // If some projects have links missing, fill only those.
            $anyMissingLink = collect($projects)->contains(fn ($p) => is_array($p) && ($this->scalarString($p['link'] ?? '') === ''));
            if (! $anyMissingLink) {
                return $resume;
            }
        }

        $sections = $this->detectSections($text);
        $projText = trim((string) ($sections['projects'] ?? ''));
        $parsed = $this->parseProjectsText($projText !== '' ? $projText : $text);
        if ($parsed === [] && $projText !== '') {
            // Fallback when heading detection exists but parsing still failed.
            $parsed = $this->parseProjectsText($text);
        }
        if ($parsed === []) {
            return $resume;
        }

        // If we have no projects, just adopt parsed ones.
        if ($projects === []) {
            $resume['projects'] = $parsed;
            return $resume;
        }

        // Otherwise, fill missing `link` and `tech_stack` by matching project name.
        foreach ($projects as $i => $p) {
            if (! is_array($p)) continue;
            $needLink = $this->scalarString($p['link'] ?? '') === '';
            $needTech = $this->scalarString($p['tech_stack'] ?? $p['tech'] ?? '') === '';
            if (! ($needLink || $needTech)) continue;

            $pName = strtolower(trim((string) ($p['name'] ?? '')));
            foreach ($parsed as $candidate) {
                if (! is_array($candidate)) continue;
                $cName = strtolower(trim((string) ($candidate['name'] ?? '')));
                if ($pName !== '' && $cName !== '' && ($cName === $pName || str_contains($cName, $pName) || str_contains($pName, $cName))) {
                    if ($needLink) {
                        $projects[$i]['link'] = $candidate['link'] ?? $projects[$i]['link'] ?? '';
                    }
                    if ($needTech) {
                        $projects[$i]['tech_stack'] = $candidate['tech_stack'] ?? $candidate['tech'] ?? $projects[$i]['tech_stack'] ?? '';
                    }
                    break;
                }
            }
        }

        $resume['projects'] = $projects;
        return $resume;
    }

    private function enrichLanguagesFromRawText(array $resume, string $text): array
    {
        $langs = is_array($resume['languages'] ?? null) ? $resume['languages'] : [];
        if (! empty($langs)) {
            return $resume;
        }

        // Prefer structured section if present, fallback to whitelist-based extraction.
        $sections = $this->detectSections($text);
        $langText = trim((string) ($sections['languages'] ?? ''));
        $parsed = $this->parseLanguageList($langText !== '' ? $langText : $text);

        $whitelist = [
            'english','hindi','german','spanish','french','marathi','bengali','tamil','telugu','kannada','malayalam','punjabi','urdu','arabic','chinese','korean','japanese'
        ];

        $filtered = array_values(array_filter($parsed, function ($l) use ($whitelist) {
            if (! is_array($l)) return false;
            $name = strtolower(trim((string) ($l['name'] ?? '')));
            if ($name === '') return false;
            foreach ($whitelist as $w) {
                if ($name === $w || str_starts_with($name, $w.' ')) return true;
            }
            return false;
        }));

        if ($filtered !== []) {
            $resume['languages'] = $filtered;
        }

        return $resume;
    }

    private function isExperienceRowEducationLike(array $exp): bool
    {
        $company = trim((string) ($exp['company'] ?? ''));
        $role    = trim((string) ($exp['role'] ?? ''));
        $period  = trim((string) ($exp['period'] ?? ''));
        $blob    = strtolower(implode(' ', array_filter([$company, $role, $period])));

        if ($blob === '') {
            return false;
        }

        if (preg_match('/\b(intern|developer|engineer|manager|analyst|consultant|designer|executive)\b/i', $role)
            && ! preg_match('/\b(b\.?\s*sc|b\.?\s*tech|m\.?\s*sc|12th|10th)\b/i', $role)) {
            return false;
        }

        if (preg_match('/\b(b\.?\s*sc\.?|b\.?\s*tech\.?|m\.?\s*tech\.?|m\.?\s*sc\.?|bca|mca|mba|diploma|intermediate|hsc|ssc)\b/i', $blob)) {
            return true;
        }

        if (preg_match('/\b(12th|10th|high\s+school|secondary|senior\s+secondary)\b/i', $blob)) {
            return true;
        }

        return $company === '' && preg_match('/\b(b\.?\s*sc|b\.?\s*tech|m\.?\s*sc|12th|10th)\b/i', $role);
    }

    private function extractSummaryFromText(string $text): string
    {
        return app(ResumeNormalizerService::class)->extractSummaryFromRawText($text);
    }

    private function resumeHasContent(array $resume): bool
    {
        foreach (['name', 'last_name', 'email', 'mobile', 'summary', 'designation'] as $field) {
            if ($this->scalarString($resume[$field] ?? '') !== '') {
                return true;
            }
        }

        foreach (['skills', 'experience', 'education', 'projects'] as $section) {
            $items = $resume[$section] ?? [];
            if (! is_array($items) || $items === []) {
                continue;
            }
            foreach ($items as $item) {
                if (is_string($item) && trim($item) !== '') {
                    return true;
                }
                if (is_array($item) && collect($item)->contains(fn ($v) => $this->scalarString($v) !== '')) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Final autofill payload — structure repair, validator, then raw-text gap fill.
     */
    private function finalizeParsedResume(array $resume, bool $fromAffinda, string $rawText = ''): array
    {
        $resume = $this->postProcessGeminiResume($this->normalizeResume($resume));

        $sanitized = app(ResumeSectionValidatorService::class)->sanitizeBuilder($resume);

        if (! $this->resumeHasContent($sanitized) && $this->resumeHasContent($resume)) {
            $out = $resume;
        } else {
            $out = $sanitized;
        }

        if ($rawText !== '') {
            // Identity first so summary heuristics (designation -> summary) work.
            $out = $this->enrichIdentityFromRawText($out, $rawText);
            $out = $this->ensureSummaryFilled($out, $rawText);
            $out = $this->ensureSkillsFilled($out, $rawText);
            $out = $this->enrichExperienceFromRawText($out, $rawText);
            $out = $this->enrichProjectsFromRawText($out, $rawText);
            $out = $this->enrichLanguagesFromRawText($out, $rawText);
        }

        return $this->dedupeFinalResumePayload($out);
    }

    private function dedupeFinalResumePayload(array $resume): array
    {
        $resume = $this->normalizeResume($resume);
        $resume['desired_job_role'] = '';

        foreach (['skills', 'social_links'] as $field) {
            $resume[$field] = $this->uniqueNormalizedStrings($resume[$field] ?? []);
        }

        $resume['experience'] = $this->uniqueNormalizedRows(
            $resume['experience'] ?? [],
            fn (array $row): string => implode(' ', [
                $row['company'] ?? '',
                $row['role'] ?? '',
                $row['period'] ?? '',
                implode(' ', is_array($row['points'] ?? null) ? $row['points'] : []),
            ])
        );
        $resume['education'] = $this->uniqueNormalizedRows(
            $resume['education'] ?? [],
            fn (array $row): string => implode(' ', [$row['degree'] ?? '', $row['stream'] ?? '', $row['institution'] ?? '', $row['year'] ?? ''])
        );
        $resume['projects'] = $this->uniqueNormalizedRows(
            $resume['projects'] ?? [],
            fn (array $row): string => implode(' ', [$row['name'] ?? '', $row['tech_stack'] ?? $row['tech'] ?? '', $row['link'] ?? '', $row['description'] ?? ''])
        );
        foreach (['certifications', 'certificates', 'languages', 'achievements'] as $field) {
            $resume[$field] = $this->uniqueNormalizedRows(
                $resume[$field] ?? [],
                fn (array $row): string => implode(' ', array_map(fn ($v) => is_scalar($v) ? (string) $v : $this->scalarString($v), $row))
            );
        }

        $portfolio = $this->normalizeProfileUrl($resume['portfolio'] ?? $resume['link'] ?? '');
        $linkedin = $this->normalizeProfileUrl($resume['linkedin'] ?? '');
        $github = $this->normalizeProfileUrl($resume['github'] ?? '');
        foreach ($resume['social_links'] as $url) {
            $url = $this->normalizeProfileUrl($url);
            if ($linkedin === '' && preg_match('/linkedin\.com/i', $url)) {
                $linkedin = $url;
            } elseif ($github === '' && preg_match('/github\.com/i', $url)) {
                $github = $url;
            } elseif ($portfolio === '' && ! preg_match('/linkedin\.com/i', $url)) {
                $portfolio = $url;
            }
        }

        $resume['linkedin'] = $linkedin;
        $resume['github'] = $github;
        $resume['portfolio'] = $portfolio;
        $resume['link'] = $portfolio;
        $resume['social_links'] = [];

        return $resume;
    }

    private function uniqueNormalizedStrings(mixed $items): array
    {
        $out = [];
        $seen = [];
        foreach (is_array($items) ? $items : [$items] as $item) {
            $value = $this->scalarString($item);
            $key = $this->normalizedDuplicateKey($value);
            if ($key === '' || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = $value;
        }

        return $out;
    }

    private function uniqueNormalizedRows(mixed $rows, callable $keyFn): array
    {
        $out = [];
        $seen = [];
        foreach (is_array($rows) ? $rows : [] as $row) {
            $row = is_array($row) ? $row : ['name' => $this->scalarString($row)];
            $key = $this->normalizedDuplicateKey($keyFn($row));
            if ($key === '' || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = $row;
        }

        return array_values($out);
    }

    private function normalizedDuplicateKey(string $value): string
    {
        $value = strip_tags($value);
        $value = preg_replace('/https?:\/\/(?:www\.)?/i', '', $value) ?? $value;
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return mb_strtolower(trim($value));
    }

    private function normalizeProfileUrl(mixed $value): string
    {
        $url = $this->scalarString($value);
        if ($url === '') {
            return '';
        }
        if (preg_match('/^(?:https?:\/\/|mailto:|tel:)/i', $url)) {
            return $url;
        }
        if (preg_match('/^(?:www\.|[a-z0-9-]+\.)+[a-z]{2,}(?:\/[^\s,;]*)?$/i', $url)) {
            return 'https://'.$url;
        }

        return $url;
    }

    private function ensureSkillsFilled(array $resume, string $text): array
    {
        $skills = is_array($resume['skills'] ?? null) ? $resume['skills'] : [];
        if (count($skills) >= 3) {
            return $resume;
        }

        $sections = $this->detectSections($text);
        if (! empty($sections['skills'])) {
            $fromSection = $this->normalizeStringList($sections['skills'], 80);
            if (count($fromSection) >= 3) {
                $resume['skills'] = array_values(array_unique(array_merge($skills, $fromSection)));

                return $resume;
            }
        }

        $inferred = $this->inferSkillsFromText($text);
        if ($inferred !== []) {
            $resume['skills'] = array_values(array_unique(array_merge($skills, $inferred)));
        }

        return $resume;
    }

    /**
     * Move job-responsibility lines out of education without reshuffling Affinda experience/projects.
     */
    private function relocateWorkLikeEducationRows(array $resume): array
    {
        $resume = $this->normalizeResume($resume);
        $cleanEducation = [];
        $movedToExperience = [];

        foreach ($resume['education'] ?? [] as $edu) {
            if (! is_array($edu)) {
                continue;
            }
            if (! $this->isWorkExperienceEducationRow($edu)) {
                $cleanEducation[] = $edu;
                continue;
            }

            $degree      = trim((string) ($edu['degree'] ?? ''));
            $institution = trim((string) ($edu['institution'] ?? ''));
            $company     = '';
            $role        = $degree;
            $points      = [];

            if (preg_match('/\b(LTD|LIMITED|PVT|INC|LLC|CORP|CELLULAR|TELESERVICES)\b/i', $degree)) {
                $company = $degree;
                $role    = $institution !== '' ? $institution : '';
            } elseif ($this->isExperienceCompanyLine($degree)) {
                $company = $degree;
                $role    = $institution;
            } elseif ($institution !== '' && $this->isExperienceCompanyLine($institution)) {
                $company = $institution;
            }

            $role = trim((string) preg_replace('/\s*key\s+deliverables\s*:?\s*$/iu', '', $role));
            if ($role !== '' && preg_match('/\b(responsible|accountable|managed|led|developed)\b/i', $role)) {
                $points[] = $role;
                $role     = '';
            }
            if ($points === [] && $institution !== '' && ! $this->isExperienceCompanyLine($institution)
                && preg_match('/\b(responsible|accountable|sales|market|development|services|clients|deliverables)\b/i', $institution)) {
                $points[] = $institution;
            }

            if ($company !== '' || $role !== '' || $points !== []) {
                $movedToExperience[] = [
                    'company' => $company,
                    'role'    => $role,
                    'period'  => trim((string) ($edu['year'] ?? '')),
                    'points'  => $points,
                ];
            }
        }

        $resume['education'] = $cleanEducation;
        if ($movedToExperience !== []) {
            $resume['experience'] = array_values(array_merge($resume['experience'] ?? [], $movedToExperience));
        }

        return $resume;
    }

    private function postProcessGeminiResume(array $resume): array
    {
        $resume = $this->normalizeResume($resume);

        // Skills wrongly placed in summary (comma-separated tech list)
        $summary = $this->scalarString($resume['summary'] ?? '');
        if ($summary !== '' && $this->looksLikeSkillListText($summary)) {
            $extraSkills = $this->splitSkillTokens($summary);
            $resume['skills'] = array_values(array_unique(array_merge($resume['skills'] ?? [], $extraSkills)));
            $resume['summary'] = '';
        }

        // Summary that is actually a section dump — trim if mostly bullets from other sections
        if ($summary !== '' && preg_match('/^(?:skills?|experience|education|projects?)\s*:/im', $summary)) {
            $resume['summary'] = '';
        }

        // Dedupe and clean skills
        $resume['skills'] = array_values(array_unique(array_filter(array_map(
            fn ($s) => trim((string) $s),
            $resume['skills'] ?? []
        ), fn ($s) => $s !== '' && mb_strlen($s) <= 80 && ! preg_match('/^(and|or|the)$/i', $s))));

        $isEducationLike = static function (array $item): bool {
            $hay = strtolower(implode(' ', array_filter([
                (string) ($item['degree'] ?? ''),
                (string) ($item['stream'] ?? ''),
                (string) ($item['institution'] ?? ''),
                (string) ($item['year'] ?? ''),
                (string) ($item['name'] ?? ''),
                (string) ($item['description'] ?? ''),
            ])));
            return (bool) preg_match('/\b(b\.?tech|m\.?tech|bsc|msc|mba|bca|mca|diploma|college|university|school|cgpa|gpa|graduat|12th|10th)\b/i', $hay);
        };

        $isCertificationLike = static function (array $item): bool {
            $hay = strtolower(implode(' ', array_filter([
                (string) ($item['name'] ?? ''),
                (string) ($item['description'] ?? ''),
            ])));
            return (bool) preg_match('/\b(certif|certificate|aws|azure|gcp|coursera|udemy|nptel|oracle|microsoft)\b/i', $hay);
        };

        $movedToEducation = [];
        $cleanCertifications = [];
        foreach ($resume['certifications'] ?? [] as $cert) {
            $cert = is_array($cert) ? $cert : ['name' => (string) $cert, 'description' => ''];
            if ($isEducationLike($cert) && !$isCertificationLike($cert)) {
                $movedToEducation[] = [
                    'degree' => (string) ($cert['name'] ?? ''),
                    'stream' => '',
                    'institution' => '',
                    'year' => '',
                ];
                continue;
            }
            $cleanCertifications[] = $cert;
        }
        $resume['certifications'] = $cleanCertifications;
        if (! empty($movedToEducation)) {
            $resume['education'] = array_merge($resume['education'] ?? [], $movedToEducation);
        }

        $resume = $this->relocateWorkLikeEducationRows($resume);

        // Experience entries that are really education lines
        $cleanExperience = [];
        foreach ($resume['experience'] ?? [] as $exp) {
            if (! is_array($exp)) {
                continue;
            }
            if ($this->isExperienceRowEducationLike($exp)) {
                $resume['education'] = is_array($resume['education'] ?? null) ? $resume['education'] : [];
                $resume['education'][] = [
                    'degree'      => trim((string) ($exp['role'] ?? $exp['company'] ?? '')),
                    'stream'      => '',
                    'institution' => trim((string) ($exp['company'] ?? '')),
                    'year'        => trim((string) ($exp['period'] ?? '')),
                ];
                continue;
            }
            $cleanExperience[] = $exp;
        }
        $resume['experience'] = $cleanExperience;

        // Orphan bullet lists misclassified as experience (common in two-column resumes)
        $finalExperience = [];
        foreach ($resume['experience'] ?? [] as $exp) {
            if (! is_array($exp)) {
                continue;
            }
            $company = trim((string) ($exp['company'] ?? ''));
            $role    = trim((string) ($exp['role'] ?? ''));
            $points  = is_array($exp['points'] ?? null) ? $exp['points'] : [];
            $pointText = trim(implode(' ', array_map('strval', $points)));
            if ($company === '' && $role === '' && $pointText !== '' && $this->looksLikeSkillListText($pointText)) {
                $resume['skills'] = array_values(array_unique(array_merge(
                    $resume['skills'] ?? [],
                    $this->splitSkillTokens($pointText)
                )));
                continue;
            }
            $blob = strtolower(implode(' ', array_filter([
                $company,
                $role,
                $pointText,
            ])));
            if ($this->isContactHeaderBlob($blob)) {
                continue;
            }

            if ($company !== '' || $role !== '' || ! empty(array_filter($points, fn ($p) => trim((string) $p) !== ''))) {
                $finalExperience[] = $exp;
            }
        }
        $resume['experience'] = $finalExperience;

        $cleanProjects = [];
        foreach ($resume['projects'] ?? [] as $project) {
            if (!is_array($project)) continue;
            $link = (string) ($project['link'] ?? '');
            $tech = (string) ($project['tech'] ?? $project['tech_stack'] ?? '');
            $description = (string) ($project['description'] ?? '');

            if ($link !== '' && !preg_match('/^(?:https?:\/\/|www\.)/i', $link)) {
                // Preserve bare domains like "globalgauri.com" by converting to https.
                if (preg_match('/^(?:[a-z0-9-]+\.)+[a-z]{2,}(?:\/[^\s,;]*)?$/i', $link)) {
                    $link = 'https://' . $link;
                } else {
                    $description = trim($description . ' ' . $link);
                    $link = '';
                }
            }

            $normalizedProject = [
                'name' => (string) ($project['name'] ?? ''),
                'tech' => $tech,
                'tech_stack' => $tech,
                'link' => $link,
                'description' => $description,
            ];

            if (collect($normalizedProject)->filter()->isNotEmpty()) {
                $cleanProjects[] = $normalizedProject;
            }
        }
        $resume['projects'] = $cleanProjects;

        return $this->normalizeResume($resume);
    }

    /**
     * Coerce AI/local values that may arrive as nested arrays into a plain string.
     */
    private function scalarString(mixed $value, string $separator = ' '): string
    {
        if ($value === null) {
            return '';
        }
        if (is_bool($value)) {
            return $value ? 'true' : '';
        }
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }
        if (is_string($value)) {
            return Utf8Sanitizer::cleanString(trim($value));
        }
        if (is_array($value)) {
            $parts = [];
            foreach ($value as $item) {
                $part = $this->scalarString($item, $separator);
                if ($part !== '') {
                    $parts[] = $part;
                }
            }

            return trim(implode($separator, $parts));
        }

        return Utf8Sanitizer::cleanString(trim((string) $value));
    }

    /**
     * @return list<string>
     */
    private function normalizeStringList(mixed $items, int $maxItemLength = 80): array
    {
        if ($items === null || $items === '') {
            return [];
        }
        if (! is_array($items)) {
            $items = preg_split('/[,|•\n;\/]+/', $this->scalarString($items)) ?: [];
        }

        $out = [];
        foreach ($items as $item) {
            $s = $this->scalarString($item);
            if ($s !== '' && mb_strlen($s) <= $maxItemLength) {
                $out[] = $s;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * FIX: normalizeResume now properly handles name/last_name splitting
     * and designation/desired_job_role fallbacks.
     */
    private function normalizeResume(array $resume): array
    {
        $safeStr    = fn ($v) => $this->scalarString($v);
        $safeSocial = fn ($v) => preg_match('/(linkedin\.com\/in\/(?:alex|you)|github\.com\/(?:alex|you))/i', $this->scalarString($v)) ? '' : $this->scalarString($v);

        // FIX: Smart name splitting — if last_name is empty but name has spaces, split it
        $rawName     = $this->scalarString($resume['name'] ?? '');
        $rawLastName = $this->scalarString($resume['last_name'] ?? '');

        if ($this->looksLikeJunkName($rawName)) {
            $rawName = '';
        }
        if ($this->looksLikeJunkName($rawLastName)) {
            $rawLastName = '';
        }

        if ($rawLastName === '' && str_contains($rawName, ' ')) {
            $parts       = explode(' ', $rawName, 2);
            $rawName     = $parts[0];
            $rawLastName = $parts[1];
        }

        if ($this->isLikelyCityName($rawName)) {
            $locationBlob = $this->scalarString($resume['location'] ?? $resume['address'] ?? '');
            if (preg_match('/^([A-Z][A-Za-z]+(?:\s+[A-Z][A-Za-z]+){1,3})\s+/u', $locationBlob, $nm)) {
                $parts = preg_split('/\s+/', trim($nm[1]), -1, PREG_SPLIT_NO_EMPTY) ?: [];
                $rawName     = $parts[0] ?? '';
                $rawLastName = $rawLastName !== '' ? $rawLastName : implode(' ', array_slice($parts, 1));
            } else {
                $rawName = '';
            }
        }

        $designation = $this->scalarString($resume['designation'] ?? $resume['job_title'] ?? '');
        $identity  = $this->splitIdentityFields($rawName, $rawLastName, $designation);
        $rawName     = $identity['name'];
        $rawLastName = $identity['last_name'];
        $designation = $identity['designation'];

        // Designation fallback from first real experience role (skip contact/header dumps)
        if ($designation === '' && ! empty($resume['experience'])) {
            foreach ($resume['experience'] as $exp) {
                if (! is_array($exp)) {
                    continue;
                }
                $blob = strtolower(implode(' ', array_filter([
                    $this->scalarString($exp['company'] ?? ''),
                    $this->scalarString($exp['role'] ?? ''),
                ])));
                if ($this->isContactHeaderBlob($blob)) {
                    continue;
                }
                $role = $this->scalarString($exp['role'] ?? $exp['title'] ?? $exp['position'] ?? '');
                if ($role !== '' && $this->looksLikeJobTitle($role)) {
                    $designation = $role;
                    break;
                }
            }
        }

        $desiredJobRole = '';

        $linkedin  = $this->scalarString($resume['linkedin'] ?? '');
        $github    = $this->scalarString($resume['github'] ?? '');
        $portfolio = $this->scalarString($resume['portfolio'] ?? $resume['link'] ?? '');
        $socialLinks = array_values(array_filter(array_map($safeSocial, is_array($resume['social_links'] ?? null) ? $resume['social_links'] : [])));
        foreach ($socialLinks as $url) {
            $url = $this->scalarString($url);
            if ($url === '') {
                continue;
            }
            if ($linkedin === '' && preg_match('/linkedin\.com/i', $url)) {
                $linkedin = $url;
            }
            if ($github === '' && preg_match('/github\.com/i', $url)) {
                $github = $url;
            }
            if ($portfolio === '' && preg_match('/(?:behance|dribbble|portfolio|github\.io|\.me\/|about\.me|website)/i', $url)
                && ! preg_match('/linkedin\.com|github\.com/i', $url)) {
                $portfolio = $url;
            }
        }

        foreach ([$linkedin, $github, $portfolio] as $url) {
            if ($url !== '' && preg_match('/(?:https?:\/\/|www\.|linkedin\.com|github\.com)/i', $url)) {
                $socialLinks[] = $url;
            }
        }
        $socialLinks = array_values(array_unique(array_filter(array_map(
            fn ($url) => $this->scalarString($url),
            $socialLinks
        ), static function (string $url) use ($linkedin, $github): bool {
            if ($url === '') {
                return false;
            }
            if ($linkedin !== '' && stripos($url, 'linkedin.com') !== false) {
                return false;
            }
            if ($github !== '' && stripos($url, 'github.com') !== false) {
                return false;
            }

            return true;
        })));

        $additionalInformation = $resume['additional_information'] ?? $resume['additionalInformation'] ?? [];
        if (empty($additionalInformation)) {
            $additionalInformation = $resume['achievements'] ?? [];
        }

        $normalized = [
            'name'         => $rawName,
            'last_name'    => $rawLastName,
            'designation'  => $designation,
            'job_title'    => $designation,
            'desired_job_role' => $desiredJobRole,
            'email'        => $this->scalarString($resume['email'] ?? ''),
            'mobile'       => $this->extractBestPhone($this->scalarString($resume['mobile'] ?? $resume['contact'] ?? '')),
            'location'     => $this->sanitizeLocation($this->scalarString($resume['location'] ?? $resume['address'] ?? '')),
            'contact'      => $this->scalarString($resume['contact'] ?? $resume['mobile'] ?? ''),
            'address'      => $this->scalarString($resume['address'] ?? $resume['location'] ?? ''),
            'linkedin'     => $linkedin,
            'github'     => $github,
            'portfolio'    => $portfolio,
            'link'         => $portfolio,
            'social_links' => [],
            'summary'      => $this->scalarString($resume['summary'] ?? ''),
            'profile_image'=> $this->scalarString($resume['profile_image'] ?? ''),
            'skills'       => $this->normalizeStringList($resume['skills'] ?? []),

            'experience' => array_values(array_map(function ($item) use ($safeStr) {
                if (! is_array($item)) {
                    return [
                        'company' => '',
                        'role'    => $this->scalarString($item),
                        'period'  => '',
                        'points'  => [],
                    ];
                }
                $points = $item['points'] ?? $item['highlights'] ?? $item['responsibilities'] ?? [];
                if (! is_array($points)) {
                    $points = preg_split('/(?:\R|[•]+)+/u', $this->scalarString($points)) ?: [];
                }
                return [
                    'company' => $this->scalarString($item['company'] ?? $item['organization'] ?? $item['employer'] ?? ''),
                    'role'    => $this->scalarString($item['role'] ?? $item['title'] ?? $item['position'] ?? ''),
                    'period'  => $this->scalarString($item['period'] ?? $item['duration'] ?? ''),
                    'points'  => array_values(array_filter(array_map($safeStr, $points))),
                ];
            }, is_array($resume['experience'] ?? null) ? $resume['experience'] : [])),

            'education' => array_values(array_filter(array_map(function ($item) {
                if (! is_array($item)) {
                    return ['degree' => $this->scalarString($item), 'stream' => '', 'institution' => '', 'year' => ''];
                }
                return [
                    'degree'      => $this->scalarString($item['degree'] ?? ''),
                    'stream'      => $this->scalarString($item['stream'] ?? $item['field'] ?? $item['specialization'] ?? ''),
                    'institution' => $this->scalarString($item['institution'] ?? ''),
                    'year'        => $this->scalarString($item['year'] ?? ''),
                ];
            }, is_array($resume['education'] ?? null) ? $resume['education'] : []), fn ($i) => collect($i)->filter()->isNotEmpty())),

            'projects' => array_values(array_filter(array_map(function ($item) {
                if (! is_array($item)) {
                    return ['name' => $this->scalarString($item), 'tech' => '', 'tech_stack' => '', 'link' => '', 'description' => ''];
                }
                $link = $this->scalarString($item['link'] ?? $item['url'] ?? '');
                if ($link && ! preg_match('/^(?:https?:\/\/|www\.)/i', $link)) {
                    $link = '';
                }
                $tech = $this->scalarString($item['tech'] ?? $item['tech_stack'] ?? '');
                return [
                    'name'        => $this->scalarString($item['name'] ?? ''),
                    'tech'        => $tech,
                    'tech_stack'  => $tech,
                    'link'        => $link,
                    'description' => $this->scalarString($item['description'] ?? ''),
                ];
            }, is_array($resume['projects'] ?? null) ? $resume['projects'] : []), fn ($i) => collect($i)->filter()->isNotEmpty())),

            'certifications' => $this->normalizeNamedItems($resume['certifications'] ?? $resume['certificates'] ?? []),
            'certificates'   => $this->normalizeNamedItems($resume['certifications'] ?? $resume['certificates'] ?? []),
            'languages'      => $this->normalizeLanguages($resume['languages'] ?? $resume['language'] ?? $resume['language_skills'] ?? $resume['language_proficiency'] ?? []),
            'additional_information' => $this->normalizeNamedItems($additionalInformation),
            'achievements'   => [],
        ];

        $primaryColor = trim((string) ($resume['primary_color'] ?? ''));
        if ($primaryColor !== '') {
            $normalized['primary_color']            = $primaryColor;
            $normalized['primary_color_customized'] = filter_var($resume['primary_color_customized'] ?? true, FILTER_VALIDATE_BOOLEAN);
        }

        return Utf8Sanitizer::jsonSafe($normalized);
    }

    private function looksLikeJobTitle(string $value): bool
    {
        $value = trim($this->scalarString($value));
        if ($value === '' || mb_strlen($value) > 72) {
            return false;
        }

        return (bool) preg_match(
            '/\b(software|developer|engineer|manager|designer|analyst|consultant|lead|architect|intern|specialist|director|officer|executive|programmer|administrator|coordinator|associate|senior|junior|trainee|devops|fullstack|full\s*stack|front\s*end|back\s*end|data\s*scientist|product\s*manager|sales|marketing|recruiter|relationship|territory|admin|qa|tester|sde)\b/i',
            $value
        );
    }

    /**
     * @return array{name:string,last_name:string,designation:string}
     */
    private function splitIdentityFields(string $name, string $lastName, string $designation): array
    {
        $name        = trim($name);
        $lastName    = trim($lastName);
        $designation = trim($designation);

        if ($designation === '' && $this->looksLikeJobTitle($lastName) && ! str_contains($lastName, ' ')) {
            $designation = $lastName;
            $lastName    = '';
        }

        if ($lastName !== '' && str_contains($lastName, ' ')) {
            $words = preg_split('/\s+/', $lastName) ?: [];
            for ($i = 1; $i < count($words); $i++) {
                $tail = implode(' ', array_slice($words, $i));
                if ($this->looksLikeJobTitle($tail)) {
                    if ($designation === '') {
                        $designation = $tail;
                    }
                    $lastName = implode(' ', array_slice($words, 0, $i));
                    break;
                }
            }
        }

        if ($lastName === '' && str_contains($name, ' ')) {
            $words = preg_split('/\s+/', $name) ?: [];
            for ($i = 1; $i < count($words); $i++) {
                $tail = implode(' ', array_slice($words, $i));
                if ($this->looksLikeJobTitle($tail)) {
                    if ($designation === '') {
                        $designation = $tail;
                    }
                    $name     = implode(' ', array_slice($words, 0, $i));
                    $lastName = '';
                    break;
                }
            }
            if ($lastName === '' && str_contains($name, ' ')) {
                $parts    = explode(' ', $name, 2);
                $name     = $parts[0];
                $lastName = $parts[1] ?? '';
            }
        }

        return [
            'name'        => $name,
            'last_name'   => $lastName,
            'designation' => $designation,
        ];
    }

    private function isContactHeaderBlob(string $blob): bool
    {
        $blob = strtolower(trim($blob));
        if ($blob === '') {
            return false;
        }

        return (bool) preg_match('/\b(contact|apartment|apartments|flat|plot|tower|golden view|email|phone|mob)\b/i', $blob)
            && ! preg_match('/\b(ltd|limited|pvt|inc|llc|corp|present|internship|developer|engineer|manager|\d{4})\b/i', $blob);
    }

    private function looksLikeJunkName(string $value): bool
    {
        $value = trim($value);
        if ($value === '') return false;
        if (preg_match('/[@\d]/', $value)) return true;
        if (mb_strlen($value) > 40) return true;
        if ($this->isBoilerplateLine($value)) return true;
        if (preg_match('/\b(view|apartments?|email|phone|mobile|contact|address|objective|summary)\b/i', $value)) return true;
        return false;
    }

    private function validateDetectedName(string $name, string $email): string
    {
        $name = trim($name);
        if ($name === '' || $this->looksLikeJunkName($name)) return '';

        $parts = preg_split('/\s+/', $name) ?: [];
        if (count($parts) === 1) {
            $emailLocal = strtolower(trim((string) strstr($email, '@', true)));
            $token = strtolower($parts[0]);
            $isReasonableSingleName = (bool) preg_match('/^[A-Z][a-z]{2,15}$/', $name);
            if (($emailLocal === '' || !str_contains($emailLocal, $token)) && !$isReasonableSingleName) {
                return '';
            }
        }

        return $name;
    }

    private function inferSummaryFromText(string $text): string
    {
        $lines = array_values(array_filter(array_map('trim', preg_split('/\R+/', $text))));
        $picked = [];
        foreach (array_slice($lines, 0, 40) as $line) {
            if ($line === '' || mb_strlen($line) < 40) continue;
            if (preg_match('/@|https?:\/\/|^\+?\d[\d\s().-]{7,}$/', $line)) continue;
            if (preg_match('/\b(summary|objective|skills?|education|experience|projects?|certifications?|languages?)\b/i', $line)) continue;
            $picked[] = $line;
            if (count($picked) >= 2) break;
        }
        return trim(implode(' ', $picked));
    }

    private function inferSkillsFromText(string $text): array
    {
        $catalog = [
            'php','laravel','javascript','typescript','react','vue','angular','node','express',
            'python','java','spring','mysql','postgresql','mongodb','html','css','tailwind',
            'bootstrap','aws','azure','docker','kubernetes','git','github','rest','api'
        ];
        $hay = strtolower($text);
        $skills = [];
        foreach ($catalog as $skill) {
            if (preg_match('/\b' . preg_quote($skill, '/') . '\b/i', $hay)) {
                $skills[] = strtoupper($skill) === $skill ? $skill : ucfirst($skill);
            }
        }
        return array_values(array_unique($skills));
    }

    private function normalizeNamedItems(array|string|null $items): array
    {
        if ($items === null) {
            return [];
        }
        if (! is_array($items)) {
            $items = explode(',', $this->scalarString($items));
        }

        return array_values(array_filter(array_map(function ($item) {
            if (is_array($item)) {
                $name = $this->scalarString($item['name'] ?? $item['title'] ?? $item['label'] ?? '');
                $desc = $this->scalarString($item['description'] ?? $item['details'] ?? '');
                return ($name === '' && $desc === '') ? null : ['name' => $name, 'description' => $desc];
            }
            $name = $this->scalarString($item);
            return $name === '' ? null : ['name' => $name, 'description' => ''];
        }, $items)));
    }

    private function normalizeLanguages(array|string|null $items): array
    {
        if ($items === null) {
            return [];
        }
        if (! is_array($items)) {
            $items = explode(',', $this->scalarString($items));
        }

        return array_values(array_filter(array_map(function ($item) {
            if (is_array($item)) {
                $name  = $this->scalarString($item['name'] ?? $item['language'] ?? '');
                $level = $this->scalarString($item['level'] ?? $item['proficiency'] ?? '');
                return ($name === '' && $level === '') ? null : compact('name', 'level');
            }
            $name = $this->scalarString($item);
            return $name === '' ? null : ['name' => $name, 'level' => ''];
        }, $items)));
    }

    private function mergeResumeData(array $primary, array $fallback): array
    {
        $primary  = $this->normalizeResume($primary);
        $fallback = $this->normalizeResume($fallback);

        $pick = fn ($a, $b) => $this->scalarString($a) !== '' ? $this->scalarString($a) : $this->scalarString($b);
        $merged = $primary;

        foreach (['name', 'last_name', 'designation', 'job_title', 'desired_job_role', 'email', 'mobile', 'location', 'summary', 'linkedin', 'github', 'portfolio', 'link'] as $field) {
            $primaryVal  = $primary[$field] ?? '';
            $fallbackVal = $fallback[$field] ?? '';
            $primaryStr  = $this->scalarString($primaryVal);
            $fallbackStr = $this->scalarString($fallbackVal);

            if ($field === 'name' && $this->isLikelyCityName($primaryStr) && $fallbackStr !== '') {
                $merged[$field] = $fallbackStr;
                continue;
            }

            if (in_array($field, ['location', 'address'], true) && preg_match('/[@+]?\d|email\s*:/i', $primaryStr)) {
                $merged[$field] = $fallbackStr !== '' ? $fallbackStr : $this->sanitizeLocation($primaryStr);
                continue;
            }

            $merged[$field] = $pick($primaryVal, $fallbackVal);
        }

        foreach (['social_links', 'skills'] as $field) {
            $merged[$field] = array_values(array_unique(array_filter(array_merge(
                is_array($primary[$field] ?? null) ? $primary[$field] : [],
                is_array($fallback[$field] ?? null) ? $fallback[$field] : []
            ))));
        }

        foreach (['experience', 'education', 'projects', 'certifications', 'languages', 'achievements'] as $section) {
            $primaryItems  = is_array($primary[$section] ?? null) ? $primary[$section] : [];
            $fallbackItems = is_array($fallback[$section] ?? null) ? $fallback[$section] : [];
            $primaryScore  = $this->sectionRichnessScore($primaryItems);
            $fallbackScore = $this->sectionRichnessScore($fallbackItems);

            if ($section === 'education' && $this->educationSectionLooksCorrupted($fallbackItems)) {
                $merged[$section] = $primaryScore > 0 ? $primaryItems : [];
                continue;
            }

            if ($primaryScore === 0) {
                $merged[$section] = $fallbackItems;
            } elseif ($fallbackScore > $primaryScore * 1.35 && ! ($section === 'education' && $this->educationSectionLooksCorrupted($fallbackItems))) {
                $merged[$section] = $fallbackItems;
            } else {
                $merged[$section] = $primaryItems;
            }
        }

        return $this->normalizeResume($merged);
    }

    /**
     * AFFINDA DISABLED
     * Legacy helper name retained; merge primary structured sections with Gemini fill data.
     */
    private function mergeAffindaPrimary(array $affindaBuilder, array $geminiResume): array
    {
        $primary  = $this->normalizeResume($affindaBuilder);
        $gemini   = $this->normalizeResume($geminiResume);
        $merged   = $primary;
        $pick     = fn ($a, $b) => $this->scalarString($a) !== '' ? $this->scalarString($a) : $this->scalarString($b);

        foreach (['name', 'last_name', 'designation', 'job_title', 'desired_job_role', 'email', 'mobile', 'location', 'linkedin', 'github', 'portfolio', 'link'] as $field) {
            $merged[$field] = $pick($primary[$field] ?? '', $gemini[$field] ?? '');
        }

        $primarySummary = $this->scalarString($primary['summary'] ?? '');
        $geminiSummary  = $this->scalarString($gemini['summary'] ?? '');
        if ($primarySummary === '' && $geminiSummary !== '') {
            $merged['summary'] = $geminiSummary;
        } elseif ($geminiSummary !== '' && mb_strlen($geminiSummary) > mb_strlen($primarySummary) + 20) {
            $merged['summary'] = $geminiSummary;
        } else {
            $merged['summary'] = $pick($primarySummary, $geminiSummary);
        }

        foreach (['social_links', 'skills'] as $field) {
            $merged[$field] = array_values(array_unique(array_filter(array_merge(
                is_array($primary[$field] ?? null) ? $primary[$field] : [],
                is_array($gemini[$field] ?? null) ? $gemini[$field] : []
            ))));
        }

        foreach (['education', 'projects', 'certifications', 'languages', 'achievements'] as $section) {
            $merged[$section] = $this->mergeSectionFillOnly(
                is_array($primary[$section] ?? null) ? $primary[$section] : [],
                is_array($gemini[$section] ?? null) ? $gemini[$section] : []
            );
        }

        $merged['experience'] = $this->mergeExperienceEnriched(
            is_array($primary['experience'] ?? null) ? $primary['experience'] : [],
            is_array($gemini['experience'] ?? null) ? $gemini['experience'] : []
        );

        return $this->normalizeResume($merged);
    }

    /**
     * AFFINDA DISABLED
     * Row-wise primary experience + Gemini bullets/periods matched by company or role.
     */
    private function mergeExperienceEnriched(array $primaryItems, array $geminiItems): array
    {
        if ($primaryItems === []) {
            return $geminiItems;
        }

        $merged = [];

        foreach ($primaryItems as $p) {
            if (! is_array($p)) {
                $merged[] = $p;
                continue;
            }

            $bestGemini = null;
            $pCompany   = strtolower(trim((string) ($p['company'] ?? '')));
            $pRole      = strtolower(trim((string) ($p['role'] ?? '')));

            foreach ($geminiItems as $g) {
                if (! is_array($g)) {
                    continue;
                }
                $gCompany = strtolower(trim((string) ($g['company'] ?? '')));
                $gRole    = strtolower(trim((string) ($g['role'] ?? '')));

                $companyMatch = $pCompany !== '' && $gCompany !== ''
                    && (str_contains($gCompany, $pCompany) || str_contains($pCompany, $gCompany));
                $roleMatch = $pRole !== '' && $gRole !== ''
                    && (str_contains($gRole, $pRole) || str_contains($pRole, $gRole));

                if ($companyMatch || $roleMatch) {
                    $bestGemini = $g;
                    break;
                }
            }

            if ($bestGemini === null) {
                $idx = count($merged);
                $bestGemini = is_array($geminiItems[$idx] ?? null) ? $geminiItems[$idx] : null;
            }

            $row = $p;
            if (is_array($bestGemini)) {
                foreach (['company', 'role', 'period'] as $key) {
                    if (trim((string) ($row[$key] ?? '')) === '' && trim((string) ($bestGemini[$key] ?? '')) !== '') {
                        $row[$key] = $bestGemini[$key];
                    }
                }
                $primaryPoints = array_filter($row['points'] ?? [], fn ($pt) => trim((string) $pt) !== '');
                $geminiPoints  = array_filter($bestGemini['points'] ?? [], fn ($pt) => trim((string) $pt) !== '');
                if ($primaryPoints === [] && $geminiPoints !== []) {
                    $row['points'] = array_values($geminiPoints);
                }
            }

            $merged[] = $row;
        }

        return $merged;
    }

    /**
     * AFFINDA DISABLED
     * Keep all primary rows and enrich row-by-row using Gemini only for blanks.
     * Never reduces row count or drops existing values.
     */
    private function mergeSectionFillOnly(array $primaryItems, array $geminiItems): array
    {
        if ($primaryItems === []) {
            return $geminiItems;
        }

        $merged = [];
        $max = max(count($primaryItems), count($geminiItems));

        for ($i = 0; $i < $max; $i++) {
            $p = $primaryItems[$i] ?? null;
            $g = $geminiItems[$i] ?? null;

            if (is_array($p)) {
                if (is_array($g)) {
                    $row = $p;
                    foreach ($row as $k => $v) {
                        if (is_array($v)) {
                            $pv = is_array($v) ? $v : [];
                            $gv = is_array($g[$k] ?? null) ? $g[$k] : [];
                            $row[$k] = array_values(array_unique(array_filter(array_merge($pv, $gv))));
                        } else {
                            $pv = $this->scalarString($v);
                            $gv = $this->scalarString($g[$k] ?? '');
                            $row[$k] = $pv !== '' ? $pv : $gv;
                        }
                    }
                    $merged[] = $row;
                } else {
                    $merged[] = $p;
                }
                continue;
            }

            if ($this->scalarString($p) !== '') {
                $merged[] = $p;
                continue;
            }

            if ($g !== null) {
                $merged[] = $g;
            }
        }

        for ($i = count($primaryItems); $i < count($geminiItems); $i++) {
            $extra = $geminiItems[$i] ?? null;
            if (is_array($extra) && collect($extra)->contains(fn ($v) => $this->scalarString($v) !== '')) {
                $merged[] = $extra;
            } elseif ($this->scalarString($extra) !== '') {
                $merged[] = $extra;
            }
        }

        return $merged;
    }

    private function experienceSectionLooksCorrupted(array $items): bool
    {
        if ($items === []) {
            return false;
        }

        foreach ($items as $exp) {
            if (! is_array($exp)) {
                continue;
            }
            $blob = strtolower(implode(' ', array_filter([
                (string) ($exp['company'] ?? ''),
                (string) ($exp['role'] ?? ''),
                implode(' ', $exp['points'] ?? []),
            ])));
            if (preg_match('/\bprojects?\b/i', $blob) && preg_match('/\b(interests?|skills?)\b/i', $blob)) {
                return true;
            }
        }

        if (count($items) === 1) {
            $exp = $items[0];
            if (! is_array($exp)) {
                return false;
            }
            $company = trim((string) ($exp['company'] ?? ''));
            $role    = trim((string) ($exp['role'] ?? ''));
            $points  = is_array($exp['points'] ?? null) ? $exp['points'] : [];
            $pointLen = mb_strlen(implode(' ', array_map('strval', $points)));

            if ($company === '' && $role === '' && $pointLen > 200) {
                return true;
            }
            if ($company === '' && preg_match('/\b(developed|implemented|built|projects?)\b/i', $role.' '.implode(' ', $points))) {
                return true;
            }
        }

        return false;
    }

    private function sectionRichnessScore(array $items): int
    {
        $score = 0;
        foreach ($items as $item) {
            if (is_array($item)) {
                $score += collect($item)->filter(fn ($v) => $this->scalarString($v) !== '')->count();
                if (! empty($item['points']) && is_array($item['points'])) {
                    $score += count(array_filter($item['points'], fn ($p) => $this->scalarString($p) !== ''));
                }
            } elseif ($this->scalarString($item) !== '') {
                $score += 1;
            }
        }

        return $score;
    }

    private function looksLikeSkillListText(string $text): bool
    {
        if (mb_strlen($text) > 320) {
            return false;
        }
        if (preg_match('/[.!?]\s+[A-Z]/', $text) && str_word_count($text) > 25) {
            return false;
        }
        $tokens = $this->splitSkillTokens($text);

        return count($tokens) >= 4;
    }

    private function splitSkillTokens(string $text): array
    {
        $parts = preg_split('/[,|•\n;\/]+/', $text) ?: [];

        return array_values(array_filter(array_map(
            fn ($s) => trim((string) $s),
            $parts
        ), fn ($s) => $s !== '' && mb_strlen($s) <= 80));
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
        $key    = config('services.razorpay.key');
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
