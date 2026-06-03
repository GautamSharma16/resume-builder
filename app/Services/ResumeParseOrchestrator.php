<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;

class ResumeParseOrchestrator
{
    public function __construct(
        private readonly GeminiService $gemini,
        private readonly StructuredResumeExtractionService $structuredExtractor,
        private readonly ResumeNormalizerService $normalizer,
        private readonly ResumeSectionValidatorService $validator,
    ) {}

    /**
     * @return array{
     *   source:string,
     *   standard:array,
     *   builder:array,
     *   message?:string,
     *   meta?:array
     * }
     */
    public function extractFromUpload(UploadedFile $file, ?string $preparedText = null): array
    {
        // AFFINDA DISABLED
        $rawText = trim((string) $preparedText);
        if ($rawText === '') {
            return [
                'source'   => 'local',
                'standard' => ResumeSchema::empty(),
                'builder'  => [],
                'message'  => 'No extracted resume text was available for parsing.',
            ];
        }

        $extraction = $this->structuredExtractor->extractStructuredResume(
            $rawText,
            strtolower($file->getClientOriginalExtension())
        );

        $sections = is_array($extraction['sections'] ?? null) ? $extraction['sections'] : [];
        $geminiInput = $this->geminiSectionInput($sections);
        $this->structuredExtractor->logParserStep((string) ($extraction['extraction_id'] ?? uniqid('extract_', true)), 'step_3_gemini_input', $geminiInput);

        $gemini = $this->extractWithGemini($geminiInput, $rawText);
        $builder = Arr::get($gemini, 'success') ? (array) Arr::get($gemini, 'builder', []) : [];
        $geminiHasContent = $this->hasResumeContent($builder);
        $this->structuredExtractor->logParserStep((string) ($extraction['extraction_id'] ?? uniqid('extract_', true)), 'step_4_gemini_output', Arr::get($gemini, 'raw', $gemini));

        if (! $geminiHasContent) {
            $builder = $this->normalizer->toBuilderFormat((array) ($extraction['structured'] ?? ResumeSchema::empty()));
        }

        $builder = $this->validator->sanitizeBuilder($builder);
        $standard = $this->validator->sanitizeStandard($this->normalizer->fromBuilderFormat($builder));

        $this->structuredExtractor->logParserStep((string) ($extraction['extraction_id'] ?? uniqid('extract_', true)), 'step_5_final_normalized_json', [
            'standard' => $standard,
            'builder' => $builder,
        ]);

        return [
            'source'   => Arr::get($gemini, 'success') && $geminiHasContent ? 'gemini' : 'local',
            'standard' => $standard,
            'builder'  => $builder,
            'meta'     => [
                'extraction_id' => $extraction['extraction_id'] ?? null,
                'validation' => $extraction['validation'] ?? [],
            ],
            'message'  => Arr::get($gemini, 'message'),
        ];
    }

    private function geminiSectionInput(array $sections): array
    {
        return [
            'summary_section'        => trim((string) ($sections['summary'] ?? '')),
            'skills_section'         => trim((string) ($sections['skills'] ?? '')),
            'experience_section'     => trim((string) ($sections['experience'] ?? '')),
            'education_section'      => trim((string) ($sections['education'] ?? '')),
            'projects_section'       => trim((string) ($sections['projects'] ?? '')),
            'certifications_section' => trim((string) ($sections['certifications'] ?? '')),
            'languages_section'      => trim((string) ($sections['languages'] ?? '')),
            'achievements_section'   => trim((string) ($sections['achievements'] ?? '')),
        ];
    }

    private function extractWithGemini(array $sections, string $rawText): array
    {
        $inputJson = json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        $prompt = <<<PROMPT
You are a resume parsing engine. Return strict JSON only. No markdown. No explanations.

Extract every fact supported by the supplied resume sections. Use the raw text only to recover contact/header fields and context missing from section boundaries.

Required JSON shape:
{
  "improved_resume": {
    "name": "",
    "last_name": "",
    "designation": "",
    "job_title": "",
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

Rules:
- Preserve every experience entry.
- Experience periods must preserve formats such as Feb'15 - Present, Feb 2015 - Present, 2020 - Current, Jan 2021 - Dec 2023.
- Never return empty points when responsibility, achievement, or project-impact text exists for that job.
- Extract all bullet points as separate strings.
- Split skills into individual skills; do not include section labels.
- Keep education, certifications, projects, languages, and achievements separate.
- Do not invent facts.

SECTION_JSON:
{$inputJson}

RAW_TEXT_FOR_CONTACTS_AND_RECOVERY:
PROMPT;

        $prompt .= "\n" . mb_substr($rawText, 0, 9000);

        $result = $this->gemini->generateContent($prompt, [
            'maxOutputTokens'  => max(8192, (int) config('services.gemini.max_output_tokens', 8192)),
            'temperature'      => 0.05,
            'timeout'          => 90,
            'responseMimeType' => 'application/json',
        ]);

        if (! ($result['success'] ?? false)) {
            return ['success' => false, 'message' => $result['message'] ?? GeminiService::BUSY_MESSAGE, 'raw' => $result];
        }

        $decoded = $this->decodeJson((string) ($result['text'] ?? ''));
        $resume = is_array($decoded['improved_resume'] ?? null) ? $decoded['improved_resume'] : $decoded;

        return [
            'success' => is_array($resume) && $resume !== [],
            'builder' => is_array($resume) ? $resume : [],
            'raw' => $decoded ?: ['text' => $result['text'] ?? ''],
            'message' => $decoded ? null : 'Gemini returned invalid JSON.',
        ];
    }

    private function decodeJson(string $text): array
    {
        $candidate = trim(preg_replace('/^```(?:json)?|\s*```$/i', '', $text) ?? $text);
        $decoded = json_decode($candidate, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        $start = strpos($candidate, '{');
        $end = strrpos($candidate, '}');
        if ($start !== false && $end !== false && $end > $start) {
            $decoded = json_decode(substr($candidate, $start, $end - $start + 1), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    private function hasResumeContent(array $resume): bool
    {
        foreach (['name', 'email', 'mobile', 'summary'] as $key) {
            if (trim((string) ($resume[$key] ?? '')) !== '') {
                return true;
            }
        }

        foreach (['skills', 'experience', 'education', 'projects', 'certifications', 'languages'] as $key) {
            if (! empty($resume[$key]) && is_array($resume[$key])) {
                return true;
            }
        }

        return false;
    }
}
