<?php

namespace App\Services;

/**
 * Parse education, skills, projects, and certifications sections.
 * Ensures complete extraction without mixing sections.
 */
class StructuredSectionParserService
{
    /**
     * Parse education section
     */
    public function parseEducationSection(string $text): array
    {
        if (trim($text) === '') {
            return [];
        }

        $educations = [];
        $entries = $this->splitEducationEntries($text);

        foreach ($entries as $entry) {
            $edu = $this->parseEducationEntry($entry);
            if ($this->isValidEducation($edu)) {
                $educations[] = $edu;
            }
        }

        return $educations;
    }

    /**
     * Split education text into individual entries
     */
    private function splitEducationEntries(string $text): array
    {
        $entries = [];
        $currentEntry = '';

        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Empty line or line starts with bullet = continuation
            if ($trimmed === '' || preg_match('/^[-•●▪◦→➤·\*]/u', $trimmed)) {
                $currentEntry .= $line . "\n";
            }
            // Looks like new education entry (degree or institution name)
            elseif ($this->isEducationHeaderLine($trimmed) && $currentEntry !== '') {
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
     * Detect education header line
     */
    private function isEducationHeaderLine(string $line): bool
    {
        // Degree patterns
        $degreePatterns = [
            '/^(B\.?Tech|B\.?E|M\.?Tech|M\.?E|B\.?S|M\.?S|B\.?A|M\.?A|M\.?B\.A|B\.?B\.A|B\.?Com|M\.?Com|B\.?Sc|M\.?Sc|Ph\.D|BCA|MCA|B\.?Ed|M\.?Ed|PGDM|Diploma|10th|12th|High School|Bachelor|Master|Associate|Diploma|Certificate)[\s\-,:]*/i',
            '/^[A-Z][a-zA-Z\s]+(?:University|College|Institute|School|Academy)/',
        ];

        foreach ($degreePatterns as $pattern) {
            if (preg_match($pattern, $line)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parse single education entry
     */
    private function parseEducationEntry(string $entry): array
    {
        $edu = [
            'degree'      => '',
            'field'       => '',
            'institution' => '',
            'start_date'  => '',
            'end_date'    => '',
        ];

        $lines = array_values(array_filter(array_map('trim', explode("\n", $entry))));

        if (empty($lines)) {
            return $edu;
        }

        // Parse degree and field from first line
        [$degree, $field] = $this->parseDegreeAndField($lines[0]);
        $edu['degree'] = $degree;
        $edu['field'] = $field;

        // Parse institution and dates from remaining lines
        for ($i = 1; $i < count($lines); $i++) {
            $line = $lines[$i];

            if ($line === '') {
                continue;
            }

            // Check if this line contains institution and/or dates
            if ($this->containsYear($line) && $edu['institution'] === '') {
                [$institution, $startDate, $endDate] = $this->parseInstitutionAndDates($line);
                if ($institution !== '') {
                    $edu['institution'] = $institution;
                }
                $edu['start_date'] = $startDate;
                $edu['end_date'] = $endDate;
            } elseif ($edu['institution'] === '' && !$this->isBulletLine($line)) {
                $edu['institution'] = $line;
            } elseif ($edu['field'] === '' && !$this->isBulletLine($line)) {
                $edu['field'] = $line;
            }
        }

        return $edu;
    }

    /**
     * Parse degree and field from text
     */
    private function parseDegreeAndField(string $line): array
    {
        $degree = '';
        $field = '';

        // Extract degree abbreviation
        if (preg_match('/(B\.?Tech|B\.?E|M\.?Tech|M\.?E|B\.?S|M\.?S|B\.?A|M\.?A|M\.?B\.A|B\.?B\.A|B\.?Com|M\.?Com|B\.?Sc|M\.?Sc|Ph\.D|BCA|MCA|B\.?Ed|M\.?Ed|PGDM|Diploma|10th|12th|Bachelor|Master|Associate|Certificate)/i', $line, $matches)) {
            $degree = $matches[0];

            // Extract field (usually after "in" or after degree)
            $afterDegree = substr($line, strlen($matches[0]));
            if (preg_match('/\s+(?:in|of|–|-)?\s*([A-Z][a-zA-Z\s&()-]+)/i', $afterDegree, $fieldMatches)) {
                $field = trim($fieldMatches[1]);
                // Remove trailing junk
                $field = preg_replace('/\s+(?:from|at|,)\s*.*/i', '', $field);
            }
        }

        return [trim($degree), trim($field)];
    }

    /**
     * Parse institution and dates
     */
    private function parseInstitutionAndDates(string $line): array
    {
        $institution = '';
        $startDate = '';
        $endDate = '';

        // Extract dates first
        if (preg_match('/(\d{4})\s*[-–—]\s*(\d{4})/i', $line, $matches)) {
            $startDate = $matches[1];
            $endDate = $matches[2];

            // What remains is institution
            $institution = trim(preg_replace('/\d{4}\s*[-–—]\s*\d{4}/i', '', $line));
        } else {
            $institution = $line;
        }

        return [trim($institution), trim($startDate), trim($endDate)];
    }

    /**
     * Check if education entry is valid
     */
    private function isValidEducation(array $edu): bool
    {
        return !empty($edu['degree']) || !empty($edu['institution']);
    }

    /**
     * Parse skills section
     */
    public function parseSkillsSection(string $text): array
    {
        if (trim($text) === '') {
            return [];
        }

        $skills = [];
        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                continue;
            }

            // Remove bullet points
            $trimmed = preg_replace('/^\s*[-•●▪◦→➤·\*]\s+/u', '', $trimmed);

            // Split by common separators (comma, semicolon, pipe, slash)
            $items = preg_split('/[,;|\/]/', $trimmed);

            foreach ($items as $item) {
                $skill = trim($item);
                if ($skill !== '' && mb_strlen($skill) > 1 && mb_strlen($skill) < 100) {
                    $skills[] = $skill;
                }
            }
        }

        // Remove duplicates and sort
        $skills = array_unique(array_map('trim', array_filter($skills)));
        return array_values($skills);
    }

    /**
     * Parse projects section
     */
    public function parseProjectsSection(string $text): array
    {
        if (trim($text) === '') {
            return [];
        }

        $projects = [];
        $entries = $this->splitProjectEntries($text);

        foreach ($entries as $entry) {
            $project = $this->parseProjectEntry($entry);
            if ($this->isValidProject($project)) {
                $projects[] = $project;
            }
        }

        return $projects;
    }

    /**
     * Split projects into individual entries
     */
    private function splitProjectEntries(string $text): array
    {
        $entries = [];
        $currentEntry = '';

        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '' || preg_match('/^[-•●▪◦→➤·\*]/u', $trimmed)) {
                $currentEntry .= $line . "\n";
            } elseif ($this->isProjectHeaderLine($trimmed) && $currentEntry !== '') {
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
     * Detect project header line
     */
    private function isProjectHeaderLine(string $line): bool
    {
        // Look for project name patterns
        if (preg_match('/^[A-Z][a-zA-Z0-9\s\-:()]+(?:\||–|:|,|\s*\()/i', $line) && mb_strlen($line) < 80) {
            // Should not look like an experience entry
            if (!preg_match('/(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec|\d{4})/i', $line)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parse single project entry
     */
    private function parseProjectEntry(string $entry): array
    {
        $project = [
            'name'       => '',
            'tech'       => '',
            'tech_stack' => '',
            'link'       => '',
            'description'=> '',
        ];

        $lines = array_values(array_filter(array_map('trim', explode("\n", $entry))));

        if (empty($lines)) {
            return $project;
        }

        // First line is usually the project name and tech stack
        [$name, $tech] = $this->parseProjectNameAndTech($lines[0]);
        $project['name'] = $name;
        $project['tech'] = $tech;
        $project['tech_stack'] = $tech;

        // Parse remaining lines for description and link
        $description = [];

        for ($i = 1; $i < count($lines); $i++) {
            $line = $lines[$i];

            if ($line === '') {
                continue;
            }

            // Check for URL
            if (preg_match('/(https?:\/\/[^\s]+|github\.com\/[^\s]+|gitlab\.com\/[^\s]+)/i', $line, $matches)) {
                $project['link'] = $matches[0];
            }

            // Remove bullet and add to description
            $line = preg_replace('/^\s*[-•●▪◦→➤·\*]\s+/u', '', $line);
            if ($line !== '') {
                $description[] = $line;
            }
        }

        $project['description'] = implode("\n", $description);

        return $project;
    }

    /**
     * Parse project name and tech stack
     */
    private function parseProjectNameAndTech(string $line): array
    {
        $name = '';
        $tech = '';

        // Check for pipe separator (Name | Tech Stack)
        if (preg_match('/^([^|]+)\|\s*(.+)$/', $line, $matches)) {
            $name = trim($matches[1]);
            $tech = trim($matches[2]);
        }
        // Check for parentheses (Name (Tech Stack))
        elseif (preg_match('/^([^\(]+)\(([^)]+)\)/', $line, $matches)) {
            $name = trim($matches[1]);
            $tech = trim($matches[2]);
        }
        // Check for colon (Name: Tech Stack)
        elseif (preg_match('/^([^:]+):\s*(.+)$/', $line, $matches)) {
            $name = trim($matches[1]);
            $tech = trim($matches[2]);
        }
        // Check for dash (Name – Tech Stack)
        elseif (preg_match('/^([^–-]+)\s*[–-]\s*(.+)$/', $line, $matches)) {
            $name = trim($matches[1]);
            $tech = trim($matches[2]);
        } else {
            $name = $line;
        }

        return [trim($name), trim($tech)];
    }

    /**
     * Check if project is valid
     */
    private function isValidProject(array $project): bool
    {
        return !empty($project['name']) || !empty($project['description']);
    }

    /**
     * Parse certifications section
     */
    public function parseCertificationsSection(string $text): array
    {
        if (trim($text) === '') {
            return [];
        }

        $certs = [];
        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                continue;
            }

            // Remove bullet points
            $trimmed = preg_replace('/^\s*[-•●▪◦→➤·\*]\s+/u', '', $trimmed);

            if ($trimmed !== '') {
                // Extract just the certification name (remove issuer/dates if on same line)
                [$certName] = preg_split('/\s*[-–—,]\s+(?:from|by|issued|awarded)/i', $trimmed, 2);
                $certName = trim($certName);

                if ($certName !== '' && mb_strlen($certName) > 2 && mb_strlen($certName) < 200) {
                    $certs[] = ['name' => $certName, 'description' => ''];
                }
            }
        }

        return array_values($certs);
    }

    /**
     * Check if line is a bullet
     */
    private function isBulletLine(string $line): bool
    {
        return preg_match('/^\s*[-•●▪◦→➤·\*]\s+/u', $line);
    }

    /**
     * Check if line contains a year
     */
    private function containsYear(string $line): bool
    {
        return preg_match('/\d{4}/', $line);
    }
}
