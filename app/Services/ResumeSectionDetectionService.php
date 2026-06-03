<?php

namespace App\Services;

/**
 * Detect and extract resume sections with high accuracy.
 * Handles numerous heading variations and prevents section mixing.
 * Returns structured sections for downstream processing.
 */
class ResumeSectionDetectionService
{
    /**
     * Detect all major sections in resume text
     */
    public function detectAllSections(string $text): array
    {
        $sections = array_fill_keys([
            'summary',
            'skills',
            'experience',
            'education',
            'projects',
            'certifications',
            'languages',
            'achievements',
        ], '');

        $current = null;
        foreach (preg_split('/\R/', str_replace(["\r\n", "\r"], "\n", $text)) ?: [] as $line) {
            $heading = $this->sectionKeyForHeading($line);
            if ($heading !== null) {
                $current = $heading;
                continue;
            }

            if ($current !== null) {
                $sections[$current] .= rtrim($line) . "\n";
            }
        }

        return array_map(fn ($section) => trim($section), $sections);
    }

    private function sectionKeyForHeading(string $line): ?string
    {
        $heading = trim(preg_replace('/\s+/', ' ', $line) ?? '');
        $heading = trim($heading, " \t\n\r\0\x0B:-–—");

        if ($heading === '' || mb_strlen($heading) > 45) {
            return null;
        }

        $key = strtolower($heading);
        $key = preg_replace('/[^a-z0-9 ]+/', '', $key) ?? $key;
        $key = trim(preg_replace('/\s+/', ' ', $key) ?? $key);

        return match ($key) {
            'summary', 'professional summary', 'profile', 'professional profile', 'objective', 'career objective', 'about me', 'about', 'synopsis' => 'summary',
            'skills', 'skill', 'technical skills', 'core skills', 'core competencies', 'competencies', 'expertise', 'technologies', 'technology stack' => 'skills',
            'experience', 'work experience', 'professional experience', 'employment history', 'work history', 'career history', 'employment', 'internships', 'positions', 'roles' => 'experience',
            'education', 'academics', 'academic background', 'qualifications', 'academic qualifications' => 'education',
            'projects', 'project', 'portfolio', 'personal projects', 'academic projects' => 'projects',
            'certifications', 'certificates', 'licenses', 'credentials', 'professional certifications' => 'certifications',
            'languages', 'language', 'linguistic skills' => 'languages',
            'achievements', 'awards', 'honors', 'honours', 'accomplishments', 'publications', 'key achievements' => 'achievements',
            default => null,
        };
    }

    /**
     * Extract single section using pattern matching
     */
    private function extractSection(string $text, array $patterns): string
    {
        // Find the best matching pattern
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                // Extract content until next major section
                $start = $matches[0];
                $content = $matches[1] ?? '';

                // Find where this section ends (next major heading)
                $endPattern = '/^(?:SUMMARY|PROFILE|OBJECTIVE|SKILLS|EXPERIENCE|EDUCATION|PROJECTS|CERTIFICATIONS|LANGUAGES|ACHIEVEMENTS|AWARDS|PUBLICATIONS|CONTACT|REFERENCES)\s*[:–—\-]?$/im';
                $remainder = substr($text, strpos($text, $start) + strlen($start));

                if (preg_match($endPattern, $remainder, $endMatches)) {
                    $endPos = strpos($remainder, $endMatches[0]);
                    $content = substr($remainder, 0, $endPos);
                } else {
                    $content = $remainder;
                }

                return trim($content);
            }
        }

        return '';
    }

    /**
     * Summary section patterns
     */
    private function summaryPatterns(): array
    {
        return [
            '/^\s*(PROFESSIONAL\s+SUMMARY|SUMMARY|PROFESSIONAL\s+PROFILE|PROFILE|CAREER\s+OBJECTIVE|OBJECTIVE|ABOUT\s+ME|ABOUT|SYNOPSIS)\s*[:–—\-]?\s*\n(.*?)(?=^[A-Z\s]+[:–—\-]?$)/im',
            '/^\s*(PROFESSIONAL\s+SUMMARY|SUMMARY|PROFESSIONAL\s+PROFILE|PROFILE|CAREER\s+OBJECTIVE|OBJECTIVE|ABOUT\s+ME|ABOUT|SYNOPSIS)\s*[:–—\-]?\s*$(.*?)(?=^[A-Z\s]+[:–—\-]?$)/im',
        ];
    }

    /**
     * Skills section patterns
     */
    private function skillsPatterns(): array
    {
        return [
            '/^\s*(TECHNICAL\s+SKILLS|CORE\s+SKILLS|SKILLS|COMPETENCIES|CORE\s+COMPETENCIES|EXPERTISE|TECHNOLOGIES)\s*[:–—\-]?\s*\n(.*?)(?=^[A-Z\s]+[:–—\-]?$)/im',
            '/^\s*(TECHNICAL\s+SKILLS|CORE\s+SKILLS|SKILLS|COMPETENCIES|CORE\s+COMPETENCIES|EXPERTISE|TECHNOLOGIES)\s*[:–—\-]?\s*$(.*?)(?=^[A-Z\s]+[:–—\-]?$)/im',
        ];
    }

    /**
     * Experience section patterns
     */
    private function experiencePatterns(): array
    {
        return [
            '/^\s*(WORK\s+EXPERIENCE|PROFESSIONAL\s+EXPERIENCE|EMPLOYMENT\s+HISTORY|EXPERIENCE|INTERNSHIPS|POSITIONS|ROLES)\s*[:–—\-]?\s*\n(.*?)(?=^[A-Z\s]+[:–—\-]?$)/im',
            '/^\s*(WORK\s+EXPERIENCE|PROFESSIONAL\s+EXPERIENCE|EMPLOYMENT\s+HISTORY|EXPERIENCE|INTERNSHIPS|POSITIONS|ROLES)\s*[:–—\-]?\s*$(.*?)(?=^[A-Z\s]+[:–—\-]?$)/im',
        ];
    }

    /**
     * Education section patterns
     */
    private function educationPatterns(): array
    {
        return [
            '/^\s*(EDUCATION|ACADEMICS|ACADEMIC\s+BACKGROUND|QUALIFICATIONS|ACADEMIC\s+QUALIFICATIONS)\s*[:–—\-]?\s*\n(.*?)(?=^[A-Z\s]+[:–—\-]?$)/im',
            '/^\s*(EDUCATION|ACADEMICS|ACADEMIC\s+BACKGROUND|QUALIFICATIONS|ACADEMIC\s+QUALIFICATIONS)\s*[:–—\-]?\s*$(.*?)(?=^[A-Z\s]+[:–—\-]?$)/im',
        ];
    }

    /**
     * Projects section patterns
     */
    private function projectsPatterns(): array
    {
        return [
            '/^\s*(PROJECTS|PROJECT|PORTFOLIO|PERSONAL\s+PROJECTS|ACADEMIC\s+PROJECTS)\s*[:–—\-]?\s*\n(.*?)(?=^[A-Z\s]+[:–—\-]?$)/im',
            '/^\s*(PROJECTS|PROJECT|PORTFOLIO|PERSONAL\s+PROJECTS|ACADEMIC\s+PROJECTS)\s*[:–—\-]?\s*$(.*?)(?=^[A-Z\s]+[:–—\-]?$)/im',
        ];
    }

    /**
     * Certifications section patterns
     */
    private function certificationsPatterns(): array
    {
        return [
            '/^\s*(CERTIFICATIONS|CERTIFICATES|LICENSES|CREDENTIALS|PROFESSIONAL\s+CERTIFICATIONS)\s*[:–—\-]?\s*\n(.*?)(?=^[A-Z\s]+[:–—\-]?$)/im',
            '/^\s*(CERTIFICATIONS|CERTIFICATES|LICENSES|CREDENTIALS|PROFESSIONAL\s+CERTIFICATIONS)\s*[:–—\-]?\s*$(.*?)(?=^[A-Z\s]+[:–—\-]?$)/im',
        ];
    }

    /**
     * Languages section patterns
     */
    private function languagesPatterns(): array
    {
        return [
            '/^\s*(LANGUAGES|LANGUAGE|LINGUISTIC\s+SKILLS)\s*[:–—\-]?\s*\n(.*?)(?=^[A-Z\s]+[:–—\-]?$)/im',
            '/^\s*(LANGUAGES|LANGUAGE|LINGUISTIC\s+SKILLS)\s*[:–—\-]?\s*$(.*?)(?=^[A-Z\s]+[:–—\-]?$)/im',
        ];
    }

    /**
     * Achievements section patterns
     */
    private function achievementsPatterns(): array
    {
        return [
            '/^\s*(ACHIEVEMENTS|AWARDS|HONORS|HONOURS|ACCOMPLISHMENTS|PUBLICATIONS|KEY\s+ACHIEVEMENTS)\s*[:–—\-]?\s*\n(.*?)(?=^[A-Z\s]+[:–—\-]?$)/im',
            '/^\s*(ACHIEVEMENTS|AWARDS|HONORS|HONOURS|ACCOMPLISHMENTS|PUBLICATIONS|KEY\s+ACHIEVEMENTS)\s*[:–—\-]?\s*$(.*?)(?=^[A-Z\s]+[:–—\-]?$)/im',
        ];
    }

    /**
     * Normalize text for section detection
     */
    private function normalizeText(string $text): string
    {
        // Normalize line endings
        $text = preg_replace('/\r\n|\r/', "\n", $text);

        // Ensure section headings are on their own line
        $headings = [
            'summary', 'professional summary', 'profile', 'objective', 'career objective', 'about', 'about me',
            'experience', 'work experience', 'work history', 'employment', 'professional experience', 'internships',
            'education', 'academics', 'academic background', 'qualifications',
            'projects', 'project', 'portfolio', 'personal projects', 'academic projects',
            'skills', 'technical skills', 'core skills', 'competencies', 'expertise', 'technologies',
            'certifications', 'certificates', 'licenses', 'credentials',
            'languages', 'language',
            'achievements', 'awards', 'honors', 'honours', 'accomplishments', 'publications',
            'contact', 'positions', 'roles',
        ];

        foreach ($headings as $h) {
            $text = preg_replace('/\b' . preg_quote($h, '/') . '\b[\s:\-]*/i', "\n" . strtoupper($h) . "\n", $text);
        }

        // Clean up multiple newlines
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return $text;
    }

    /**
     * Get clean section text without heading
     */
    public function getSectionContent(string $sectionText): string
    {
        // Remove the heading line if present
        $lines = explode("\n", trim($sectionText));

        // Skip first line if it's a heading
        if (count($lines) > 0 && preg_match('/^[A-Z\s\-:]+$/', trim($lines[0]))) {
            array_shift($lines);
        }

        return trim(implode("\n", $lines));
    }
}
