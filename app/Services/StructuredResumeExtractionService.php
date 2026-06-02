<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Orchestrate structured resume extraction.
 * Coordinates document extraction, section detection, and specialized parsers.
 * Produces highly structured JSON before AI processing.
 * Logs every step for debugging.
 */
class StructuredResumeExtractionService
{
    public function __construct(
        private readonly ResumeSectionDetectionService $sectionDetector,
        private readonly ExperienceParserService $experienceParser,
        private readonly StructuredSectionParserService $sectionParser,
    ) {}

    /**
     * Extract resume with full structure preservation
     * Returns structured JSON ready for AI enhancement
     */
    public function extractStructuredResume(string $rawText, ?string $documentType = null): array
    {
        $extractionId = uniqid('extract_', true);

        try {
            // Step 1: Prepare raw text
            Log::info("[$extractionId] Starting extraction", ['doc_type' => $documentType]);
            $prepared = $this->prepareText($rawText);
            $this->logExtraction($extractionId, 'step_1_prepared_text', $prepared);

            // Step 2: Detect sections
            $sections = $this->sectionDetector->detectAllSections($prepared);
            $this->logExtraction($extractionId, 'step_2_detected_sections', array_map(fn($s) => mb_strlen($s) > 0 ? substr($s, 0, 100) . '...' : '', $sections));

            // Step 3: Parse each section with specialized parsers
            $structured = $this->parseAllSections($sections, $extractionId);
            $this->logExtraction($extractionId, 'step_3_parsed_sections', array_map('count', $structured));

            // Step 4: Validate extracted data
            $validation = $this->validateExtraction($structured);
            $this->logExtraction($extractionId, 'step_4_validation', $validation);

            // Step 5: Prepare for AI
            $forAi = $this->prepareForAiEnhancement($structured);
            $this->logExtraction($extractionId, 'step_5_ai_input', $forAi);

            Log::info("[$extractionId] Extraction complete", [
                'validation' => $validation,
                'experience_count' => count($structured['experience'] ?? []),
                'skill_count' => count($structured['skills'] ?? []),
                'education_count' => count($structured['education'] ?? []),
            ]);

            return [
                'success'        => true,
                'extraction_id'  => $extractionId,
                'structured'     => $structured,
                'for_ai'         => $forAi,
                'validation'     => $validation,
            ];

        } catch (\Throwable $e) {
            Log::error("[$extractionId] Extraction failed: " . $e->getMessage());

            return [
                'success'   => false,
                'extraction_id' => $extractionId,
                'error'     => $e->getMessage(),
            ];
        }
    }

    /**
     * Prepare raw text for section detection
     */
    private function prepareText(string $text): string
    {
        // Normalize line endings
        $text = preg_replace('/\r\n|\r/', "\n", $text);

        // Replace tabs with spaces
        $text = str_replace("\t", "  ", $text);

        // Collapse multiple spaces on same line but preserve newlines
        $lines = explode("\n", $text);
        $lines = array_map(fn($l) => trim(preg_replace('/ {2,}/', ' ', $l)), $lines);
        $text = implode("\n", $lines);

        // Remove excessive blank lines
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
    }

    /**
     * Parse all detected sections with specialized parsers
     */
    private function parseAllSections(array $sections, string $extractionId): array
    {
        return [
            'summary'        => $sections['summary'] !== '' 
                ? trim($sections['summary'])
                : '',
            'skills'         => $this->sectionParser->parseSkillsSection($sections['skills']),
            'experience'     => $this->experienceParser->parseExperienceSection($sections['experience']),
            'education'      => $this->sectionParser->parseEducationSection($sections['education']),
            'projects'       => $this->sectionParser->parseProjectsSection($sections['projects']),
            'certifications' => $this->sectionParser->parseCertificationsSection($sections['certifications']),
            'languages'      => $this->parseLanguagesSection($sections['languages'] ?? ''),
            'achievements'   => $this->parseAchievementsSection($sections['achievements'] ?? ''),
        ];
    }

    /**
     * Parse languages section
     */
    private function parseLanguagesSection(string $text): array
    {
        if (trim($text) === '') {
            return [];
        }

        $languages = [];
        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                continue;
            }

            // Remove bullet
            $trimmed = preg_replace('/^\s*[-•●▪◦→➤·\*]\s+/u', '', $trimmed);

            // Parse language and proficiency
            [$lang, $level] = $this->parseLanguageAndLevel($trimmed);

            if ($lang !== '') {
                $languages[] = [
                    'name'  => $lang,
                    'level' => $level,
                ];
            }
        }

        return $languages;
    }

    /**
     * Parse language and proficiency level
     */
    private function parseLanguageAndLevel(string $text): array
    {
        $level = '';

        // Check for proficiency level
        if (preg_match('/(Fluent|Native|Bilingual|Proficient|Intermediate|Basic|Elementary|Advanced|Beginner)/i', $text, $matches)) {
            $level = $matches[1];
            $text = preg_replace('/' . preg_quote($matches[1]) . '/i', '', $text);
        }

        // Split by separators
        if (preg_match('/^([A-Z][a-z]+)\s*[-–—:,]\s*(.+)$/', trim($text), $matches)) {
            $lang = $matches[1];
            if ($level === '') {
                $level = trim($matches[2]);
            }
        } else {
            $lang = trim($text);
        }

        return [trim($lang), trim($level)];
    }

    /**
     * Parse achievements section
     */
    private function parseAchievementsSection(string $text): array
    {
        if (trim($text) === '') {
            return [];
        }

        $achievements = [];
        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                continue;
            }

            // Remove bullet
            $trimmed = preg_replace('/^\s*[-•●▪◦→➤·\*]\s+/u', '', $trimmed);

            if ($trimmed !== '') {
                $achievements[] = [
                    'name'        => $trimmed,
                    'description' => '',
                ];
            }
        }

        return $achievements;
    }

    /**
     * Validate extraction completeness
     */
    private function validateExtraction(array $structured): array
    {
        $validation = [
            'has_summary'      => !empty(trim($structured['summary'] ?? '')),
            'has_skills'       => count($structured['skills'] ?? []) > 0,
            'has_experience'   => count($structured['experience'] ?? []) > 0,
            'has_education'    => count($structured['education'] ?? []) > 0,
            'has_projects'     => count($structured['projects'] ?? []) > 0,
            'confidence_score' => $this->calculateConfidenceScore($structured),
        ];

        // Flag issues for AI to handle
        $validation['needs_ai_pass'] = 
            !$validation['has_experience'] ||
            !$validation['has_education'] ||
            $validation['confidence_score'] < 60;

        return $validation;
    }

    /**
     * Calculate confidence score based on extraction completeness
     */
    private function calculateConfidenceScore(array $structured): int
    {
        $score = 0;

        // Presence of key sections
        if (!empty(trim($structured['summary'] ?? ''))) $score += 10;
        if (count($structured['skills'] ?? []) >= 5) $score += 15;
        elseif (count($structured['skills'] ?? []) > 0) $score += 10;

        if (count($structured['experience'] ?? []) >= 3) $score += 25;
        elseif (count($structured['experience'] ?? []) >= 1) $score += 15;

        // Experience quality
        foreach ($structured['experience'] ?? [] as $job) {
            if (!empty($job['company']) && !empty($job['role'])) $score += 5;
            if (!empty($job['start_date'])) $score += 3;
            if (count($job['points'] ?? []) >= 3) $score += 5;
        }

        if (count($structured['education'] ?? []) >= 1) $score += 15;
        if (count($structured['projects'] ?? []) >= 1) $score += 10;
        if (count($structured['certifications'] ?? []) >= 1) $score += 5;
        if (count($structured['languages'] ?? []) >= 1) $score += 5;

        return min(100, max(0, $score));
    }

    /**
     * Prepare structured data for AI enhancement
     */
    private function prepareForAiEnhancement(array $structured): array
    {
        return [
            'summary_section'        => $structured['summary'] ?? '',
            'skills_section'         => implode(", ", $structured['skills'] ?? []),
            'experience_section'     => $this->formatExperienceForAi($structured['experience'] ?? []),
            'education_section'      => $this->formatEducationForAi($structured['education'] ?? []),
            'projects_section'       => $this->formatProjectsForAi($structured['projects'] ?? []),
            'certifications_section' => $this->formatCertificationsForAi($structured['certifications'] ?? []),
            'languages_section'      => $this->formatLanguagesForAi($structured['languages'] ?? []),
            'achievements_section'   => $this->formatAchievementsForAi($structured['achievements'] ?? []),
        ];
    }

    /**
     * Format experience for AI input
     */
    private function formatExperienceForAi(array $experience): string
    {
        if (empty($experience)) {
            return '';
        }

        $lines = [];
        foreach ($experience as $job) {
            $lines[] = "Company: " . ($job['company'] ?? '');
            $lines[] = "Role: " . ($job['role'] ?? '');
            if (!empty($job['start_date'])) {
                $lines[] = "Period: " . $job['start_date'] . 
                    (empty($job['end_date']) && !($job['is_current'] ?? false) ? '' : 
                    ' - ' . ($job['end_date'] ?? 'Present'));
            }
            if (!empty($job['points'])) {
                $lines[] = "Responsibilities:";
                foreach ($job['points'] as $point) {
                    $lines[] = "  - " . $point;
                }
            }
            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    /**
     * Format education for AI input
     */
    private function formatEducationForAi(array $education): string
    {
        if (empty($education)) {
            return '';
        }

        $lines = [];
        foreach ($education as $edu) {
            $lines[] = "Degree: " . ($edu['degree'] ?? '');
            if (!empty($edu['field'])) {
                $lines[] = "Field: " . $edu['field'];
            }
            $lines[] = "Institution: " . ($edu['institution'] ?? '');
            if (!empty($edu['start_date']) || !empty($edu['end_date'])) {
                $lines[] = "Year: " . ($edu['start_date'] ?? '') . ' - ' . ($edu['end_date'] ?? '');
            }
            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    /**
     * Format projects for AI input
     */
    private function formatProjectsForAi(array $projects): string
    {
        if (empty($projects)) {
            return '';
        }

        $lines = [];
        foreach ($projects as $proj) {
            $lines[] = "Project: " . ($proj['name'] ?? '');
            if (!empty($proj['tech_stack'])) {
                $lines[] = "Tech Stack: " . $proj['tech_stack'];
            }
            if (!empty($proj['link'])) {
                $lines[] = "Link: " . $proj['link'];
            }
            if (!empty($proj['description'])) {
                $lines[] = "Description: " . $proj['description'];
            }
            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    /**
     * Format certifications for AI input
     */
    private function formatCertificationsForAi(array $certs): string
    {
        if (empty($certs)) {
            return '';
        }

        $lines = [];
        foreach ($certs as $cert) {
            $lines[] = "- " . ($cert['name'] ?? '');
        }

        return implode("\n", $lines);
    }

    /**
     * Format languages for AI input
     */
    private function formatLanguagesForAi(array $languages): string
    {
        if (empty($languages)) {
            return '';
        }

        $lines = [];
        foreach ($languages as $lang) {
            $line = $lang['name'] ?? '';
            if (!empty($lang['level'])) {
                $line .= ' (' . $lang['level'] . ')';
            }
            $lines[] = "- " . $line;
        }

        return implode("\n", $lines);
    }

    /**
     * Format achievements for AI input
     */
    private function formatAchievementsForAi(array $achievements): string
    {
        if (empty($achievements)) {
            return '';
        }

        $lines = [];
        foreach ($achievements as $ach) {
            $lines[] = "- " . ($ach['name'] ?? '');
        }

        return implode("\n", $lines);
    }

    /**
     * Log extraction step
     */
    private function logExtraction(string $extractionId, string $step, $data): void
    {
        $logPath = storage_path('logs/resume-parser.log');
        $logDir = dirname($logPath);

        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $timestamp = now()->format('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [$extractionId] $step\n";
        $logEntry .= json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        $logEntry .= str_repeat("-", 80) . "\n\n";

        @file_put_contents($logPath, $logEntry, FILE_APPEND);
    }
}
