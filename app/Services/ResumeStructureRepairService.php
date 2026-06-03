<?php

namespace App\Services;

class ResumeStructureRepairService
{
    /**
     * AFFINDA DISABLED
     * Affinda-only cleanup: never replace Affinda sections with regex/rawText parsers.
     */
    public function repairAffinda(array $resume): array
    {
        $summary = trim((string) ($resume['summary'] ?? ''));
        if ($summary !== '' && preg_match('/\b(CONTACT|EDUCATION|SKILLS?|PROJECTS?|EXPERIENCE)\b/i', $summary)) {
            $resume['summary'] = '';
        }

        $resume = $this->stripGarbageExperience($resume);
        $resume = $this->fixSkillLikeRoles($resume);
        $resume = $this->splitMergedRolesOnExperience($resume);

        $resume['skills'] = array_values(array_unique(array_filter(array_map(
            fn ($s) => trim((string) $s),
            $resume['skills'] ?? []
        ), fn ($s) => $s !== '' && mb_strlen($s) <= 120)));

        return $resume;
    }

    /**
     * Fallback path only (Gemini/local) — may enrich empty sections from raw text.
     */
    public function repairFallback(array $resume, string $rawText = ''): array
    {
        $resume = $this->repairAffinda($resume);

        if ($rawText !== '' && $this->shouldEnrichFromRawText($resume)) {
            $resume = $this->enrichFromRawText($resume, $rawText);
        }

        return $resume;
    }

    private function shouldEnrichFromRawText(array $resume): bool
    {
        $validJobs = 0;
        foreach ($resume['experience'] ?? [] as $exp) {
            if (! is_array($exp)) {
                continue;
            }
            if (trim((string) ($exp['company'] ?? '')) !== '') {
                $validJobs++;
            }
        }

        return $validJobs === 0;
    }

    private function splitMergedRolesOnExperience(array $resume): array
    {
        foreach ($resume['experience'] ?? [] as &$exp) {
            if (! is_array($exp)) {
                continue;
            }
            $role = trim((string) ($exp['role'] ?? ''));
            if ($role === '' || trim((string) ($exp['company'] ?? '')) !== '') {
                continue;
            }
            if (preg_match(
                '/^([A-Z][A-Z\s]{3,40})\s+((?:Senior\s+|Junior\s+|Lead\s+)?[A-Za-z0-9][A-Za-z0-9\s\/\-]{3,80}(?:Developer|Engineer|Manager|Analyst|Designer|Tester|Architect|Consultant|Intern|Stack).*)$/u',
                $role,
                $m
            )) {
                $exp['role'] = trim($m[2]);
            }
        }
        unset($exp);

        return $resume;
    }

    private function stripGarbageExperience(array $resume): array
    {
        $clean   = [];
        $skills  = $resume['skills'] ?? [];
        $projects = $resume['projects'] ?? [];

        foreach ($resume['experience'] ?? [] as $exp) {
            if (! is_array($exp)) {
                continue;
            }

            $blob = strtolower(implode(' ', array_filter([
                (string) ($exp['role'] ?? ''),
                (string) ($exp['company'] ?? ''),
                (string) ($exp['description'] ?? ''),
                implode(' ', $exp['points'] ?? []),
            ])));

            if ($this->isSectionDumpBlob($blob)) {
                $split = $this->splitInlineSections(implode("\n", array_filter([
                    (string) ($exp['description'] ?? ''),
                    implode("\n", $exp['points'] ?? []),
                    (string) ($exp['role'] ?? ''),
                ])));
                $skills   = array_merge($skills, $split['skills'] ?? []);
                $projects = array_merge($projects, $split['projects'] ?? []);
                continue;
            }

            if ($this->looksLikeSkillNotRole((string) ($exp['role'] ?? '')) && empty($exp['company'])) {
                $skills[] = (string) $exp['role'];
                $exp['role'] = '';
            }

            $points = is_array($exp['points'] ?? null) ? $exp['points'] : [];
            $cleanPoints = [];
            foreach ($points as $point) {
                $point = trim((string) $point);
                if ($point === '') {
                    continue;
                }
                if ($this->isSectionDumpBlob(strtolower($point))) {
                    $split = $this->splitInlineSections($point);
                    $skills   = array_merge($skills, $split['skills'] ?? []);
                    $projects = array_merge($projects, $split['projects'] ?? []);
                    continue;
                }
                $cleanPoints[] = $point;
            }
            $exp['points'] = $cleanPoints;

            if (trim((string) ($exp['company'] ?? '')) !== ''
                || trim((string) ($exp['role'] ?? '')) !== ''
                || ! empty($cleanPoints)) {
                $clean[] = $exp;
            }
        }

        $resume['experience'] = $clean;
        $resume['skills']     = $skills;
        $resume['projects']   = $projects;

        return $resume;
    }

    private function fixSkillLikeRoles(array $resume): array
    {
        $skills = $resume['skills'] ?? [];

        foreach ($resume['experience'] ?? [] as &$exp) {
            if (! is_array($exp)) {
                continue;
            }
            $role = trim((string) ($exp['role'] ?? ''));
            if ($role !== '' && $this->looksLikeSkillNotRole($role)) {
                $skills[] = $role;
                $exp['role'] = '';
            }
        }
        unset($exp);

        $resume['skills'] = $skills;

        return $resume;
    }

    private function enrichFromRawText(array $resume, string $rawText): array
    {
        $sections = $this->detectSections($rawText);

        if (empty($resume['skills']) && ! empty($sections['skills'])) {
            $resume['skills'] = $this->parseSkillLines($sections['skills']);
        }

        if ($this->experienceNeedsRepair($resume['experience'] ?? []) && ! empty($sections['experience'])) {
            $resume['experience'] = $this->parseExperienceSection($sections['experience']);
        }

        if ($this->experienceNeedsRepair($resume['experience'] ?? [])) {
            $resume['experience'] = $this->parseExperienceFromRawFallback($rawText);
        }

        if (empty($resume['projects']) && ! empty($sections['projects'])) {
            $resume['projects'] = $this->parseProjectsSection($sections['projects']);
        }

        if (empty($resume['projects'])) {
            $resume['projects'] = $this->parseProjectsFromRawFallback($rawText);
        }

        if (empty($resume['education']) && ! empty($sections['education'])) {
            $resume['education'] = $this->parseEducationSection($sections['education']);
        }

        if (trim((string) ($resume['summary'] ?? '')) === '' && ! empty($sections['summary'])) {
            $resume['summary'] = trim($sections['summary']);
        }

        if (trim((string) ($resume['designation'] ?? '')) === '') {
            foreach ($resume['experience'] ?? [] as $job) {
                $role = trim((string) ($job['role'] ?? ''));
                if ($role !== '' && ! $this->looksLikeSkillNotRole($role)) {
                    $resume['designation'] = $role;
                    break;
                }
            }
        }

        if (trim((string) ($resume['name'] ?? '')) === '') {
            $headerName = $this->detectNameFromText($rawText);
            if ($headerName !== '') {
                $parts = preg_split('/\s+/', $headerName, 2) ?: [];
                $resume['name']      = $parts[0] ?? $headerName;
                $resume['last_name'] = $resume['last_name'] ?? ($parts[1] ?? '');
            }
        }

        return $resume;
    }

    private function experienceNeedsRepair(array $experience): bool
    {
        if ($experience === []) {
            return true;
        }

        foreach ($experience as $exp) {
            if (! is_array($exp)) {
                return true;
            }
            $company = trim((string) ($exp['company'] ?? ''));
            $role    = trim((string) ($exp['role'] ?? ''));
            if ($company === '' && ($role === '' || $this->looksLikeSkillNotRole($role))) {
                return true;
            }
        }

        return false;
    }

    private function isSectionDumpBlob(string $blob): bool
    {
        if ($blob === '') {
            return false;
        }

        $markers = 0;
        foreach (['projects', 'interests', 'skills', 'education', 'experience'] as $word) {
            if (str_contains($blob, $word)) {
                $markers++;
            }
        }

        return $markers >= 2
            || preg_match('/\bprojects?\s*:/i', $blob)
            || preg_match('/\binterests?\s*:/i', $blob)
            || (preg_match('/\bprojects?\b/i', $blob) && preg_match('/\binterests?\b/i', $blob));
    }

    /**
     * @return array{skills:array,projects:array}
     */
    private function splitInlineSections(string $text): array
    {
        $skills   = [];
        $projects = [];

        if (preg_match('/\bskills?\s*:?\s*(.+?)(?=\b(?:projects?|interests?|experience|education)\b|$)/is', $text, $m)) {
            $skills = $this->parseSkillLines($m[1]);
        } elseif (preg_match('/\b(?:technical\s+skills?|technologies)\s*:?\s*(.+?)(?=\b(?:projects?|interests?|experience)\b|$)/is', $text, $m)) {
            $skills = $this->parseSkillLines($m[1]);
        }

        if (preg_match('/\bprojects?\s*:?\s*(.+?)(?=\b(?:interests?|experience|education|skills?)\b|$)/is', $text, $m)) {
            $projects = $this->parseProjectsSection($m[1]);
        }

        return ['skills' => $skills, 'projects' => $projects];
    }

    /**
     * @return array<string,string>
     */
    private function detectSections(string $text): array
    {
        $headingRegex = '/^(?:([A-Z][A-Z\s&]{2,58})|([A-Z][a-z]+(?:\s+[A-Z][a-z]+){0,4}):?)\s*$/m';
        preg_match_all($headingRegex, $text, $matches, PREG_OFFSET_CAPTURE);

        $headings = [];
        foreach ($matches[0] as $match) {
            $headings[] = ['label' => trim($match[0]), 'offset' => $match[1]];
        }

        $canonicalMap = [
            'summary'    => ['summary', 'professional summary', 'profile', 'objective', 'about'],
            'skills'     => ['skills', 'technical skills', 'core skills', 'technologies', 'tech stack'],
            'experience' => ['experience', 'work experience', 'professional experience', 'employment', 'work history'],
            'education'  => ['education', 'academics', 'qualification'],
            'projects'   => ['projects', 'project', 'portfolio', 'personal projects'],
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

            if (! $canonical) {
                continue;
            }

            $start = $heading['offset'] + mb_strlen($heading['label']);
            $end   = $headings[$idx + 1]['offset'] ?? $textLen;
            $body  = trim(mb_substr($text, $start, $end - $start));

            if (! isset($sections[$canonical])) {
                $sections[$canonical] = $body;
            }
        }

        return $sections;
    }

    /**
     * @return list<string>
     */
    private function parseSkillLines(string $text): array
    {
        $text = preg_replace('/\binterests?\s*:.*$/is', '', $text) ?? $text;
        $parts = preg_split('/[\n,;|•]+/', $text) ?: [];

        return array_values(array_unique(array_filter(array_map(function ($p) {
            $p = trim(preg_replace('/^\s*[-*]\s*/', '', $p) ?? '');
            if ($p === '' || mb_strlen($p) > 80) {
                return '';
            }
            if (preg_match('/^(interests?|projects?|experience)$/i', $p)) {
                return '';
            }

            return $p;
        }, $parts))));
    }

    /**
     * @return list<array>
     */
    private function parseExperienceSection(string $text): array
    {
        $lines   = array_values(array_filter(array_map('trim', preg_split('/\R/', $text))));
        $entries = [];
        $current = null;

        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            if ($this->isCompanyLine($line)) {
                if ($current) {
                    $entries[] = $current;
                }
                $current = [
                    'company'     => $line,
                    'role'        => '',
                    'location'    => '',
                    'start_date'  => '',
                    'end_date'    => '',
                    'is_current'  => false,
                    'description' => '',
                    'points'      => [],
                ];
                continue;
            }

            if (preg_match('/\b(?:jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec).*\d{4}\b.*(?:present|now|current)/i', $line)
                || preg_match('/\d{4}\s*[-–]\s*(?:present|now)/i', $line)) {
                if ($current) {
                    $current['is_current'] = (bool) preg_match('/\b(present|now|current)\b/i', $line);
                    [$start, $end] = $this->parseDateRangeString($line);
                    $current['start_date'] = $start;
                    $current['end_date']   = $end;
                    continue;
                }
            }

            if ($current && $current['role'] === '' && $this->isRoleLine($line)) {
                $current['role'] = $line;
                continue;
            }

            if (preg_match('/^\s*[-*•]\s+/u', $line)) {
                if (! $current) {
                    $current = ['company' => '', 'role' => '', 'location' => '', 'start_date' => '', 'end_date' => '', 'is_current' => false, 'description' => '', 'points' => []];
                }
                $current['points'][] = trim(preg_replace('/^\s*[-*•]\s+/u', '', $line));
                continue;
            }

            if (! $current) {
                if (preg_match('/\b(?:jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec).*\d{4}/i', $line)) {
                    $current = ['company' => '', 'role' => '', 'location' => '', 'start_date' => '', 'end_date' => '', 'is_current' => preg_match('/\b(present|now)\b/i', $line), 'description' => '', 'points' => []];
                    [$start, $end] = $this->parseDateRangeString($line);
                    $current['start_date'] = $start;
                    $current['end_date']   = $end;
                    continue;
                }
            }

            if ($current && mb_strlen($line) > 20 && empty($current['points'])) {
                $current['description'] = trim(($current['description'] ?? '').' '.$line);
            }
        }

        if ($current) {
            $entries[] = $current;
        }

        return array_values(array_filter($entries, function (array $e): bool {
            $company = trim((string) ($e['company'] ?? ''));
            $role    = trim((string) ($e['role'] ?? ''));

            if ($company !== '') {
                return true;
            }

            return $role !== '' && ! $this->looksLikeSkillNotRole($role);
        }));
    }

    /**
     * @return list<array>
     */
    private function parseProjectsSection(string $text): array
    {
        $projects = [];
        $chunks   = preg_split('/(?=\b[A-Z][A-Za-z0-9]+(?:Share|Money|App|Platform|System)?\s*:)/', $text) ?: [$text];

        foreach ($chunks as $chunk) {
            $chunk = trim($chunk);
            if ($chunk === '') {
                continue;
            }

            if (! preg_match('/^([^:]+):\s*(.*)$/s', $chunk, $m)) {
                continue;
            }

            $name = trim($m[1]);
            $body = trim($m[2]);
            if ($name === '' || preg_match('/^projects?$/i', $name)) {
                continue;
            }

            $tech  = '';
            $desc  = $body;
            if (preg_match('/technologies?\s*used\s*:?\s*([^.]+)/i', $body, $tm)) {
                $tech = trim($tm[1]);
            }
            if (preg_match('/key\s+contributions?\s*:?\s*(.+)$/is', $body, $cm)) {
                $desc = trim($cm[1]);
            }

            $projects[] = [
                'name'        => $name,
                'tech_stack'  => $tech,
                'description' => $desc,
                'link'        => '',
            ];
        }

        if ($projects !== []) {
            return $projects;
        }

        return array_map(fn ($line) => [
            'name'        => $line,
            'tech_stack'  => '',
            'description' => '',
            'link'        => '',
        ], array_filter(array_map('trim', preg_split('/\R+/', $text))));
    }

    /**
     * @return list<array>
     */
    private function parseEducationSection(string $text): array
    {
        $rows = [];
        foreach (array_filter(array_map('trim', preg_split('/\R+/', $text))) as $line) {
            if ($line === '') {
                continue;
            }
            $year = '';
            if (preg_match('/\b((?:19|20)\d{2}(?:\s*[-–]\s*(?:\d{4}|present))?)\b/i', $line, $m)) {
                $year = $m[1];
            }
            $rows[] = [
                'degree'      => $line,
                'field'       => '',
                'institution' => '',
                'start_date'  => '',
                'end_date'    => $year,
            ];
        }

        return $rows;
    }

    /**
     * @return list<array>
     */
    private function parseProjectsFromRawFallback(string $rawText): array
    {
        $projects = [];

        if (preg_match_all(
            '/\b([A-Z][A-Za-z0-9]+(?:Share|Money|Hub|App|Platform)?)\s*:\s*([^.]+\.)(?:\s*Technologies?\s*Used\s*:?\s*([^.]+)\.?)?(?:\s*Key\s*Contributions?\s*:?\s*(.+?))?(?=\b[A-Z][A-Za-z0-9]+(?:Share|Money|Hub|App)?\s*:|$)/is',
            $rawText,
            $matches,
            PREG_SET_ORDER
        )) {
            foreach ($matches as $m) {
                $projects[] = [
                    'name'        => trim($m[1]),
                    'tech_stack'  => trim($m[3] ?? ''),
                    'description' => trim($m[4] ?? $m[2] ?? ''),
                    'link'        => '',
                ];
            }
        }

        return $projects;
    }

    private function isCompanyLine(string $line): bool
    {
        return (bool) preg_match(
            '/\b(LTD|LIMITED|PVT|INC|LLC|CORP|SOLUTIONS|SYSTEMS|TECHNOLOGIES)\b/i',
            $line
        ) || (
            (bool) preg_match('/^[A-Z][A-Za-z0-9\s&.\'-]{4,}$/u', $line)
            && ! preg_match('/\b(developer|engineer|skills?|projects?)\b/i', $line)
        );
    }

    private function isRoleLine(string $line): bool
    {
        return (bool) preg_match(
            '/\b(developer|engineer|manager|analyst|designer|tester|intern|lead|consultant|specialist)\b/i',
            $line
        );
    }

    private function looksLikeSkillNotRole(string $role): bool
    {
        if ($role === '') {
            return false;
        }
        if (preg_match('/\b(developer|engineer|manager|analyst|designer|tester|intern|lead|consultant|specialist|officer|executive)\b/i', $role)) {
            return false;
        }
        if (preg_match('/\b(react\s*native|jasper|android\s*studio|strapi|html|css|javascript|sql|java|cms|reporting\s*tool)\b/i', $role)) {
            return true;
        }
        if (preg_match('/\([^)]*tool[^)]*\)/i', $role)) {
            return true;
        }

        return mb_strlen($role) < 45 && ! preg_match('/\s{2,}/', $role) && substr_count($role, ' ') >= 1
            && ! preg_match('/\b(and|of|for|at)\b/i', $role);
    }

    /**
     * @return array{0:string,1:string}
     */
    private function parseDateRangeString(string $range): array
    {
        $range = trim($range);
        if (preg_match('/^(.+?)\s*[–\-—to]+\s*(.+)$/iu', $range, $m)) {
            return [trim($m[1]), trim($m[2])];
        }

        return [$range, ''];
    }

    /**
     * @return list<array>
     */
    private function parseExperienceFromRawFallback(string $rawText): array
    {
        if (preg_match(
            '/\b((?:jan(?:uary)?|feb(?:ruary)?|mar(?:ch)?|apr(?:il)?|may|jun(?:e)?|jul(?:y)?|aug(?:ust)?|sep(?:t(?:ember)?)?|oct(?:ober)?|nov(?:ember)?|dec(?:ember)?)\s*\d{4}\s*[-–—]\s*(?:present|now|current))\b[^A-Z]{0,40}\b([A-Z][A-Za-z0-9\s&.\'-]{3,60}(?:Solutions|Systems|Technologies|Services|Software|Ltd|Limited|INC|Corp))\b[:\s-]*([A-Za-z][^\n]{8,120})/iu',
            $rawText,
            $m
        )) {
            return [[
                'company'     => trim($m[2]),
                'role'        => trim(preg_replace('/\s{2,}/', ' ', $m[3])),
                'location'    => '',
                'start_date'  => trim($m[1]),
                'end_date'    => '',
                'is_current'  => true,
                'description' => '',
                'points'      => [],
            ]];
        }

        if (preg_match(
            '/\b([A-Z][A-Za-z0-9\s&.\'-]{3,60}(?:Solutions|Systems|Technologies|Services|Software|Ltd|Limited))\b[:\s-]*((?:[A-Za-z]+\s+){1,6}(?:Developer|Engineer|Manager|Analyst|Tester|Consultant)[^\n.]{0,80})/iu',
            $rawText,
            $m
        )) {
            $period = '';
            if (preg_match('/\b((?:jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)[a-z]*\s*\d{4}\s*[-–—]\s*(?:present|now|current))/iu', $rawText, $pm)) {
                $period = $pm[1];
            }

            return [[
                'company'     => trim($m[1]),
                'role'        => trim($m[2]),
                'location'    => '',
                'start_date'  => $period,
                'end_date'    => '',
                'is_current'  => (bool) preg_match('/\b(present|now|current)\b/i', $period),
                'description' => '',
                'points'      => [],
            ]];
        }

        return [];
    }

    private function detectNameFromText(string $text): string
    {
        $lines = array_values(array_filter(array_map('trim', preg_split('/\R+/', $text))));
        foreach (array_slice($lines, 0, 6) as $line) {
            if (preg_match('/^[A-Z][A-Za-z]+(?:\s+[A-Z][A-Za-z]+){1,3}$/u', $line)
                && ! preg_match('/\b(developer|engineer|skills?|education|contact)\b/i', $line)) {
                return $line;
            }
        }

        return '';
    }
}
