<?php

namespace App\Services;

/**
 * Parse experience entries with high accuracy.
 * Extracts: company, role, dates, location, and all bullet points.
 * Handles various date formats and company/role presentation styles.
 */
class ExperienceParserService
{
    /**
     * Parse experience section into individual jobs
     */
    public function parseExperienceSection(string $text): array
    {
        if (trim($text) === '') {
            return [];
        }

        $jobs = [];
        $entries = $this->splitExperienceEntries($text);

        foreach ($entries as $entry) {
            $job = $this->parseExperienceEntry($entry);
            if ($this->isValidJob($job)) {
                $jobs[] = $job;
            }
        }

        return $jobs;
    }

    /**
     * Split experience text into individual job entries
     */
    private function splitExperienceEntries(string $text): array
    {
        // Split by common company/role patterns
        $entries = [];
        $currentEntry = '';

        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Check if this looks like a new job entry (company name or company + location)
            if ($this->isJobHeaderLine($trimmed) && $currentEntry !== '') {
                $entries[] = $currentEntry;
                $currentEntry = $line . "\n";
            } else {
                $currentEntry .= $line . "\n";
            }
        }

        if ($currentEntry !== '') {
            $entries[] = $currentEntry;
        }

        return array_filter(array_map('trim', $entries));
    }

    /**
     * Detect if line is a job header (company name)
     */
    private function isJobHeaderLine(string $line): bool
    {
        if ($line === '' || mb_strlen($line) < 3) {
            return false;
        }

        // All uppercase or Title Case (likely company name)
        if (preg_match('/^[A-Z][A-Z\s\.\-,&()]*$/', $line) && mb_strlen($line) > 3) {
            return true;
        }

        // Company name patterns
        if (preg_match('/^[A-Z][a-zA-Z0-9\s\.\-,&()]+\s+(Ltd|Inc|Corp|LLC|LLP|GMBH|AG|PLC|Company|Group|Solutions|Services|Systems|Pvt|Private|Limited|Organization|Org|Bank|Hospital|University|School)\.*$/i', $line)) {
            return true;
        }

        // Company + city pattern
        if (preg_match('/^[A-Z][a-zA-Z0-9\s\.\-,&()]+,\s+[A-Z][a-zA-Z\s\-]*$/i', $line)) {
            return true;
        }

        return false;
    }

    /**
     * Parse a single experience entry
     */
    private function parseExperienceEntry(string $entry): array
    {
        $lines = array_values(array_filter(array_map('trim', explode("\n", $entry))));

        if (empty($lines)) {
            return $this->emptyJob();
        }

        $job = $this->emptyJob();

        // First line is usually company name
        $job['company'] = $this->extractCompanyName($lines[0]);

        // Look for role and dates in subsequent lines
        $roleFound = false;
        $dateFound = false;
        $bullets = [];

        for ($i = 1; $i < count($lines); $i++) {
            $line = $lines[$i];

            // Skip empty lines
            if ($line === '') {
                continue;
            }

            // Check if this is a role/title line
            if (!$roleFound && $this->isRoleLine($line)) {
                $job['role'] = $this->extractRoleFromLine($line);
                $roleFound = true;
                continue;
            }

            // Check if this line contains dates
            if (!$dateFound && $this->containsDateRange($line)) {
                [$startDate, $endDate, $isCurrent] = $this->extractDateRange($line);
                $job['start_date'] = $startDate;
                $job['end_date'] = $endDate;
                $job['is_current'] = $isCurrent;
                $dateFound = true;

                // Extract role from same line if not found yet
                if (!$roleFound) {
                    $role = $this->extractRoleFromDateLine($line);
                    if ($role !== '') {
                        $job['role'] = $role;
                        $roleFound = true;
                    }
                }
                continue;
            }

            // If it looks like a bullet/responsibility
            if ($this->isBulletLine($line) || (!$roleFound && !$dateFound && $i === 1)) {
                $bullets[] = $this->cleanBulletPoint($line);
            }
        }

        // Clean and set bullets
        $job['points'] = array_values(array_filter($bullets));
        $job['description'] = implode("\n", $job['points']);

        return $job;
    }

    /**
     * Empty job template
     */
    private function emptyJob(): array
    {
        return [
            'company'     => '',
            'role'        => '',
            'location'    => '',
            'start_date'  => '',
            'end_date'    => '',
            'is_current'  => false,
            'description' => '',
            'points'      => [],
        ];
    }

    /**
     * Extract company name from text
     */
    private function extractCompanyName(string $line): string
    {
        // Remove location in parentheses if present
        $line = preg_replace('/\s*\([^)]*\)\s*$/', '', $line);

        // Remove city/state if after comma
        if (strpos($line, ',') !== false) {
            [$company] = explode(',', $line, 2);
            return trim($company);
        }

        return trim($line);
    }

    /**
     * Detect if line is a job role/title
     */
    private function isRoleLine(string $line): bool
    {
        // Skip lines with dates
        if ($this->containsDateRange($line)) {
            return false;
        }

        // Job title patterns
        $titlePatterns = [
            '/^(Senior|Junior|Lead|Principal|Manager|Associate|Intern|Consultant|Specialist|Engineer|Developer|Designer|Analyst|Officer|Director|Head|Chief|VP|Vice President|President|Coordinator|Officer|Lead|Supervisor|Administrator)/i',
            '/^[A-Z][a-z]+\s+(Engineer|Developer|Manager|Lead|Specialist|Analyst|Officer|Consultant|Designer|Architect|Director)/i',
        ];

        foreach ($titlePatterns as $pattern) {
            if (preg_match($pattern, $line)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract role from a line
     */
    private function extractRoleFromLine(string $line): string
    {
        // Remove dates if present
        $line = preg_replace('/\d{4}|Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec|Present|Current/i', '', $line);
        return trim(preg_replace('/\s+/', ' ', $line));
    }

    /**
     * Extract role from a line that contains date range
     */
    private function extractRoleFromDateLine(string $line): string
    {
        // Remove date patterns
        $line = preg_replace('/[\(\[]?(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec|January|February|March|April|May|June|July|August|September|October|November|December)[\'\s]*\d{2,4}[\)\]]*\s*[-–—]\s*(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec|January|February|March|April|May|June|July|August|September|October|November|December)?[\'\s]*\d{0,4}|Present|Current/i', '', $line);
        $line = preg_replace('/\d{1,2}[\'\s]*\d{4}\s*[-–—]\s*\d{1,2}[\'\s]*\d{4}/', '', $line);
        $line = preg_replace('/\d{4}\s*[-–—]\s*(?:\d{4}|Present|Current)/i', '', $line);

        $line = trim(preg_replace('/\s+/', ' ', $line));

        return mb_strlen($line) < 100 ? $line : '';
    }

    /**
     * Check if line contains a date range
     */
    private function containsDateRange(string $line): bool
    {
        // Various date range patterns
        $datePatterns = [
            '/(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec|January|February|March|April|May|June|July|August|September|October|November|December)[\'\s]*\d{0,4}\s*[-–—]\s*(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec|January|February|March|April|May|June|July|August|September|October|November|December)?[\'\s]*\d{0,4}|Present|Current/i',
            '/\d{1,2}[\'\s]*\d{4}\s*[-–—]\s*\d{1,2}[\'\s]*\d{4}/',
            '/\d{4}\s*[-–—]\s*(?:\d{4}|Present|Current)/i',
            '/(?:Q[1-4]\s*)?\d{4}\s*[-–—]\s*(?:Q[1-4]\s*)?\d{4}/',
        ];

        foreach ($datePatterns as $pattern) {
            if (preg_match($pattern, $line)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract date range from a line
     */
    private function extractDateRange(string $line): array
    {
        $startDate = '';
        $endDate = '';
        $isCurrent = false;

        // Full month/year pattern: Jan 2020 - Dec 2021
        if (preg_match('/(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec|January|February|March|April|May|June|July|August|September|October|November|December)[\'\s]*(\d{4})\s*[-–—]\s*(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec|January|February|March|April|May|June|July|August|September|October|November|December)?[\'\s]*(\d{4}|Present|Current)/i', $line, $matches)) {
            $startDate = 'Jan ' . $matches[1];
            $endDate = $matches[2] ?? '';
            $isCurrent = preg_match('/Present|Current/i', $endDate);
            if ($isCurrent) $endDate = '';
        } 
        // Year only pattern: 2020 - 2021
        elseif (preg_match('/(\d{4})\s*[-–—]\s*((?:\d{4})|Present|Current)/i', $line, $matches)) {
            $startDate = $matches[1];
            $endDate = $matches[2] ?? '';
            $isCurrent = preg_match('/Present|Current/i', $endDate);
            if ($isCurrent) $endDate = '';
        }

        // Also capture dates from different formats
        if ($startDate === '' && preg_match('/(\d{1,2})[\'\s]*(\d{4})\s*[-–—]\s*(\d{1,2})[\'\s]*(\d{4})/i', $line, $matches)) {
            $startDate = $matches[1] . '/' . $matches[2];
            $endDate = $matches[3] . '/' . $matches[4];
        }

        return [$startDate, $endDate, $isCurrent];
    }

    /**
     * Check if line is a bullet point
     */
    private function isBulletLine(string $line): bool
    {
        return preg_match('/^\s*[-•●▪◦→➤·\*]\s+/u', $line);
    }

    /**
     * Clean a bullet point by removing the bullet marker
     */
    private function cleanBulletPoint(string $line): string
    {
        // Remove bullet marker
        $line = preg_replace('/^\s*[-•●▪◦→➤·\*]\s+/u', '', $line);
        return trim($line);
    }

    /**
     * Check if job entry is valid (has minimum required fields)
     */
    private function isValidJob(array $job): bool
    {
        return !empty($job['company']) || !empty($job['role']) || !empty($job['points']);
    }
}
