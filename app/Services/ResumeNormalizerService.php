<?php

namespace App\Services;

class ResumeNormalizerService
{
    /**
     * Map Affinda v3 (compact) resume payload to universal schema.
     */
    public function fromAffinda(array $affinda): array
    {
        $resume = ResumeSchema::empty();

        $resume['name']      = $this->affindaFirstName($affinda);
        $resume['last_name'] = $this->affindaLastName($affinda);
        $resume['designation'] = $this->scalar(
            $affinda['profession'] ?? $affinda['jobTitle'] ?? $affinda['headline'] ?? ''
        );

        $resume['email']  = $this->firstString(
            $affinda['email'] ?? $affinda['emails'] ?? []
        );
        $resume['mobile'] = $this->firstString(
            $affinda['phoneNumber'] ?? $affinda['phoneNumbers'] ?? $affinda['phoneNumberDetails'] ?? []
        );

        if ($resume['mobile'] === '' && is_array($affinda['phoneNumberDetails'] ?? null)) {
            foreach ($affinda['phoneNumberDetails'] as $phone) {
                if (! is_array($phone)) {
                    continue;
                }
                $resume['mobile'] = $this->scalar($phone['formattedNumber'] ?? $phone['rawText'] ?? $phone['nationalNumber'] ?? '');
                if ($resume['mobile'] !== '') {
                    break;
                }
            }
        }

        $resume['location'] = $this->formatLocation($affinda['location'] ?? $affinda['address'] ?? []);
        $resume['linkedin'] = $this->extractLinkedin($affinda);
        $resume['github']   = $this->extractGithub($affinda['website'] ?? $affinda['websites'] ?? []);
        $resume['website']  = $this->extractWebsite($affinda['website'] ?? $affinda['websites'] ?? []);
        $resume['summary']  = $this->affindaSummary($affinda);
        if ($resume['summary'] === '') {
            $rawText = is_string($affinda['rawText'] ?? null) ? $affinda['rawText'] : '';
            if ($rawText !== '') {
                $resume['summary'] = $this->summaryFromRawText($rawText);
            }
        }

        $resume['skills'] = $this->mapSkills(
            $affinda['skill'] ?? $affinda['skills'] ?? []
        );

        foreach ($this->listItems($affinda['workExperience'] ?? $affinda['work_experience'] ?? []) as $job) {
            if (! is_array($job)) {
                continue;
            }
            $entry = $this->mapExperience($job);
            if ($entry['company'] !== '' || $entry['role'] !== '' || ! empty($entry['points']) || $entry['description'] !== '') {
                $resume['experience'][] = $entry;
            }
        }

        foreach ($this->listItems($affinda['education'] ?? []) as $edu) {
            if (! is_array($edu)) {
                continue;
            }
            $entry = $this->mapEducation($edu);
            if (collect($entry)->except(['start_date', 'end_date', 'is_current'])->filter()->isNotEmpty()) {
                $resume['education'][] = $entry;
            }
        }

        foreach ($this->listItems($affinda['project'] ?? $affinda['projects'] ?? []) as $project) {
            if (! is_array($project)) {
                continue;
            }
            $entry = $this->mapProject($project);
            if ($entry['name'] !== '' || $entry['description'] !== '') {
                $resume['projects'][] = $entry;
            }
        }

        foreach ($this->listItems($affinda['certification'] ?? $affinda['certifications'] ?? []) as $cert) {
            $mapped = $this->mapNamedItem($cert);
            if ($mapped['name'] !== '') {
                $resume['certifications'][] = $mapped;
            }
        }

        $resume['languages'] = $this->mapLanguages(
            $affinda['language'] ?? $affinda['languages'] ?? []
        );

        foreach ($this->listItems($affinda['achievement'] ?? $affinda['achievements'] ?? $affinda['publication'] ?? $affinda['publications'] ?? []) as $item) {
            $mapped = $this->mapNamedItem($item);
            if ($mapped['name'] !== '') {
                $resume['achievements'][] = $mapped;
            }
        }

        if ($resume['designation'] === '' && ! empty($affinda['headline'])) {
            $resume['designation'] = $this->scalar($affinda['headline']);
        }

        return $resume;
    }

    /**
     * Convert universal schema to Resume Builder / template payload.
     */
    public function toBuilderFormat(array $standard): array
    {
        $firstName = trim((string) ($standard['name'] ?? ''));
        $lastName  = trim((string) ($standard['last_name'] ?? ''));
        if ($lastName === '' && str_contains($firstName, ' ')) {
            $parts     = preg_split('/\s+/', $firstName, 2) ?: [];
            $firstName = $parts[0] ?? $firstName;
            $lastName  = $parts[1] ?? '';
        }

        $designation = trim((string) ($standard['designation'] ?? ''));
        if ($designation === '') {
            foreach ($standard['experience'] ?? [] as $job) {
                $role = trim((string) ($job['role'] ?? ''));
                if ($role !== '' && ! $this->looksLikeSkillNotRole($role)) {
                    $designation = $role;
                    break;
                }
            }
        }

        $experience = [];
        foreach ($standard['experience'] ?? [] as $job) {
            if (! is_array($job)) {
                continue;
            }
            $points = $job['points'] ?? [];
            if (empty($points) && ! empty($job['description'])) {
                $points = preg_split('/\R+/', (string) $job['description']) ?: [];
            }
            $points = array_values(array_filter(array_map(fn ($p) => $this->scalar($p), is_array($points) ? $points : [$points])));

            $experience[] = [
                'company' => $this->scalar($job['company'] ?? ''),
                'role'    => $this->scalar($job['role'] ?? ''),
                'period'  => $this->formatPeriod(
                    $job['start_date'] ?? '',
                    $job['end_date'] ?? '',
                    (bool) ($job['is_current'] ?? false)
                ),
                'points'  => $points,
            ];
        }

        $education = [];
        foreach ($standard['education'] ?? [] as $edu) {
            if (! is_array($edu)) {
                continue;
            }
            $education[] = [
                'degree'      => $this->scalar($edu['degree'] ?? ''),
                'stream'      => $this->scalar($edu['field'] ?? ''),
                'institution' => $this->scalar($edu['institution'] ?? ''),
                'year'        => $this->formatPeriod(
                    $edu['start_date'] ?? '',
                    $edu['end_date'] ?? '',
                    false
                ),
            ];
        }

        $projects = [];
        foreach ($standard['projects'] ?? [] as $project) {
            if (! is_array($project)) {
                continue;
            }
            $projects[] = [
                'name'        => $this->scalar($project['name'] ?? ''),
                'tech_stack'  => $this->scalar($project['tech_stack'] ?? ''),
                'link'        => $this->scalar($project['link'] ?? ''),
                'description' => $this->scalar($project['description'] ?? ''),
            ];
        }

        $social = array_values(array_filter([
            $standard['linkedin'] ?? '',
            $standard['github'] ?? '',
            $standard['website'] ?? '',
        ]));

        return [
            'name'             => $firstName,
            'last_name'        => $lastName,
            'designation'      => $designation,
            'job_title'        => $designation,
            'desired_job_role' => '',
            'email'            => $this->scalar($standard['email'] ?? ''),
            'mobile'           => $this->scalar($standard['mobile'] ?? ''),
            'location'         => $this->scalar($standard['location'] ?? ''),
            'linkedin'         => $this->scalar($standard['linkedin'] ?? ''),
            'github'           => $this->scalar($standard['github'] ?? ''),
            'portfolio'        => $this->scalar($standard['website'] ?? ''),
            'link'             => $this->scalar($standard['website'] ?? ''),
            'social_links'     => $social,
            'summary'          => $this->scalar($standard['summary'] ?? ''),
            'skills'           => array_values(array_filter(array_map(fn ($s) => $this->scalar($s), $standard['skills'] ?? []))),
            'experience'       => $experience,
            'education'        => $education,
            'projects'         => $projects,
            'certifications'   => array_values(array_filter(array_map(fn ($c) => $this->mapNamedItem($c), $standard['certifications'] ?? []))),
            'languages'        => array_values(array_filter(array_map(fn ($l) => $this->mapLanguageItem($l), $standard['languages'] ?? []))),
            'achievements'     => array_values(array_filter(array_map(fn ($a) => $this->mapNamedItem($a), $standard['achievements'] ?? []))),
        ];
    }

    private function affindaFirstName(array $affinda): string
    {
        $candidate = $affinda['candidateName'] ?? [];
        if (is_array($candidate)) {
            $first = $this->scalar($candidate['candidateNameFirst'] ?? $candidate['first'] ?? $candidate['given'] ?? '');
            if ($first !== '') {
                return $first;
            }
        }

        $name = $affinda['name'] ?? [];
        if (is_array($name)) {
            return $this->scalar($name['first'] ?? $name['given'] ?? '');
        }

        $parts = preg_split('/\s+/', $this->scalar($name), 2) ?: [];

        return $parts[0] ?? '';
    }

    private function affindaLastName(array $affinda): string
    {
        $candidate = $affinda['candidateName'] ?? [];
        if (is_array($candidate)) {
            $last = $this->scalar($candidate['candidateNameFamily'] ?? $candidate['last'] ?? $candidate['family'] ?? '');
            if ($last !== '') {
                return $last;
            }
        }

        $name = $affinda['name'] ?? [];
        if (is_array($name)) {
            $last = $this->scalar($name['last'] ?? $name['family'] ?? '');
            if ($last !== '') {
                return $last;
            }
            if (! empty($name['raw'])) {
                $parts = preg_split('/\s+/', $this->scalar($name['raw']), 2) ?: [];

                return $parts[1] ?? '';
            }
        }

        $parts = preg_split('/\s+/', $this->scalar($name), 2) ?: [];

        return $parts[1] ?? '';
    }

    private function affindaSummary(array $affinda): string
    {
        foreach (['summary', 'objective', 'profile', 'professionalSummary', 'careerObjective'] as $key) {
            $value = $affinda[$key] ?? null;
            $text  = $this->scalar($value);
            if ($text !== '' && mb_strlen($text) > 15 && ! $this->looksLikeSkillListSummary($text)) {
                return $text;
            }
        }

        return '';
    }

    public function extractSummaryFromRawText(string $text): string
    {
        return $this->summaryFromRawText($text);
    }

    private function summaryFromRawText(string $text): string
    {
        $patterns = [
            '/\b(?:professional\s+)?summary\s*:?\s*\R?\s*(.+?)(?=\R\s*(?:skills?|technical\s+skills?|experience|work\s+experience|education|projects?|certifications?|languages?|achievements?)\b)/is',
            '/\b(?:career\s+)?objective\s*:?\s*\R?\s*(.+?)(?=\R\s*(?:skills?|experience|education|projects?)\b)/is',
            '/\b(?:profile|about\s+me)\s*:?\s*\R?\s*(.+?)(?=\R\s*(?:skills?|experience|education|projects?)\b)/is',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $m)) {
                $summary = trim(preg_replace('/\s+/', ' ', $m[1]) ?? '');
                if (mb_strlen($summary) > 20 && ! $this->looksLikeSkillListSummary($summary)) {
                    return $summary;
                }
            }
        }

        $lines = array_values(array_filter(array_map('trim', preg_split('/\R+/', $text))));
        $capture = false;
        $parts   = [];

        foreach ($lines as $line) {
            if (preg_match('/^\s*(?:professional\s+)?summary\s*:?\s*$/i', $line)
                || preg_match('/^\s*(?:career\s+)?objective\s*:?\s*$/i', $line)
                || preg_match('/^\s*profile\s*:?\s*$/i', $line)) {
                $capture = true;
                $inline = trim(preg_replace('/^\s*(?:professional\s+)?summary\s*:?\s*/i', '', $line));
                if ($inline !== '' && mb_strlen($inline) > 15) {
                    $parts[] = $inline;
                }
                continue;
            }

            if ($capture) {
                if (preg_match('/^\s*(skills?|experience|education|projects?|certifications?|languages?|work)\b/i', $line)) {
                    break;
                }
                if (preg_match('/@|https?:\/\/|^\+?\d[\d\s().-]{7,}$/', $line)) {
                    break;
                }
                if (mb_strlen($line) > 25) {
                    $parts[] = $line;
                }
                if (mb_strlen(implode(' ', $parts)) > 280) {
                    break;
                }
            }
        }

        $summary = trim(implode(' ', $parts));

        return ($summary !== '' && ! $this->looksLikeSkillListSummary($summary)) ? $summary : '';
    }

    private function looksLikeSkillListSummary(string $text): bool
    {
        if (mb_strlen($text) > 400) {
            return false;
        }
        if (preg_match('/\b(CONTACT|EDUCATION|SKILLS?|PROJECTS?|EXPERIENCE)\b/i', $text)) {
            return true;
        }

        return substr_count($text, ',') >= 4 && ! preg_match('/\b(I am|I have|years? of|experience in|seeking|passionate)\b/i', $text);
    }

    private function extractLinkedin(array $affinda): string
    {
        $direct = $this->scalar($affinda['linkedin'] ?? '');
        if ($direct !== '') {
            return $direct;
        }

        foreach ($this->websiteList($affinda['website'] ?? $affinda['websites'] ?? []) as $url) {
            if (preg_match('/linkedin\.com/i', $url)) {
                return $url;
            }
        }

        return '';
    }

    private function mapExperience(array $job): array
    {
        $company = $this->scalar(
            $job['workExperienceOrganization']
            ?? $job['organization']
            ?? $job['company']
            ?? $job['employer']
            ?? ''
        );

        $role = $this->scalar(
            $job['jobTitle']
            ?? $job['job_title']
            ?? $job['title']
            ?? (is_array($job['occupation'] ?? null) ? ($job['occupation']['jobTitle'] ?? $job['occupation']['label'] ?? '') : ($job['occupation'] ?? ''))
        );

        if ($this->looksLikeSkillNotRole($role)) {
            $role = '';
        }

        $dateRange = $this->scalar(
            $job['workExperienceDateRange']
            ?? $job['dateRange']
            ?? $job['dates']
            ?? ''
        );

        $dates = is_array($job['dates'] ?? null) ? $job['dates'] : [];
        $start = $this->scalar($dates['startDate'] ?? $dates['start'] ?? '');
        $end   = $this->scalar($dates['endDate'] ?? $dates['end'] ?? '');
        $isCurrent = (bool) ($dates['isCurrent'] ?? false);

        if ($dateRange !== '' && ($start === '' && $end === '')) {
            [$start, $end, $isCurrent] = $this->parseDateRangeString($dateRange);
        }

        $description = $this->scalar(
            $job['jobDescription']
            ?? $job['workExperienceDescription']
            ?? $job['description']
            ?? ''
        );
        $points = $description !== '' ? preg_split('/\R+/', $description) ?: [] : [];
        $points = array_values(array_filter(array_map(fn ($p) => trim($this->scalar($p)), $points)));

        return [
            'company'     => $company,
            'role'        => $role,
            'location'    => $this->formatLocation($job['workExperienceLocation'] ?? $job['location'] ?? []),
            'start_date'  => $start,
            'end_date'    => $end,
            'is_current'  => $isCurrent,
            'description' => $description,
            'points'      => $points,
        ];
    }

  /**
     * @return array{0:string,1:string,2:bool}
     */
    private function parseDateRangeString(string $range): array
    {
        $range = trim($range);
        $isCurrent = (bool) preg_match('/\b(now|present|current)\b/i', $range);

        if (preg_match('/^(.+?)\s*[–\-—to]+\s*(.+)$/iu', $range, $m)) {
            $start = trim($m[1]);
            $end   = trim($m[2]);
            if (preg_match('/\b(now|present|current)\b/i', $end)) {
                $isCurrent = true;
                $end       = '';
            }

            return [$start, $end, $isCurrent];
        }

        return [$range, '', $isCurrent];
    }

    private function mapEducation(array $edu): array
    {
        $dates = is_array($edu['dates'] ?? null) ? $edu['dates'] : [];
        $dateRange = $this->scalar($edu['educationDateRange'] ?? $edu['dateRange'] ?? '');

        $start = $this->scalar($dates['startDate'] ?? $dates['start'] ?? '');
        $end   = $this->scalar($dates['endDate'] ?? $dates['end'] ?? '');

        if ($dateRange !== '' && $start === '' && $end === '') {
            [$start, $end] = array_slice($this->parseDateRangeString($dateRange), 0, 2);
        }

        $major = $edu['educationMajor'] ?? $edu['major'] ?? $edu['field'] ?? '';
        if (is_array($major)) {
            $major = implode(', ', array_filter(array_map(fn ($m) => $this->scalar($m), $major)));
        }

        $level = '';
        if (is_array($edu['educationLevel'] ?? null)) {
            $level = $this->scalar($edu['educationLevel']['label'] ?? $edu['educationLevel']['value'] ?? '');
        }

        $degree = $this->scalar(
            $edu['educationAccreditation']
            ?? $edu['accreditation']
            ?? $edu['degree']
            ?? $edu['studyType']
            ?? ''
        );

        if ($degree === '' && $level !== '') {
            $degree = $level;
        }

        return [
            'degree'      => $degree,
            'field'       => $this->scalar($major),
            'institution' => $this->scalar(
                $edu['educationOrganization']
                ?? $edu['organization']
                ?? $edu['institution']
                ?? $edu['school']
                ?? ''
            ),
            'start_date'  => $start,
            'end_date'    => $end,
        ];
    }

    private function mapProject(array $project): array
    {
        $dateRange = $this->scalar($project['projectDateRange'] ?? $project['dateRange'] ?? '');

        return [
            'name'        => $this->scalar($project['projectName'] ?? $project['name'] ?? $project['title'] ?? ''),
            'tech_stack'  => $this->scalar($project['technologies'] ?? $project['tech_stack'] ?? ''),
            'description' => $this->scalar($project['projectDescription'] ?? $project['description'] ?? ''),
            'link'        => $this->scalar($project['projectUrl'] ?? $project['url'] ?? $project['link'] ?? ''),
            'date'        => $dateRange,
        ];
    }

    private function mapSkills(mixed $skills): array
    {
        if (! is_array($skills)) {
            return $this->scalar($skills) !== '' ? [$this->scalar($skills)] : [];
        }

        $out = [];
        foreach ($skills as $skill) {
            if (is_string($skill) || is_numeric($skill)) {
                $value = $this->scalar($skill);
            } elseif (is_array($skill)) {
                $value = $this->scalar($skill['name'] ?? $skill['parsed'] ?? $skill['raw'] ?? $skill['value'] ?? '');
            } else {
                $value = '';
            }
            if ($value !== '' && mb_strlen($value) <= 120) {
                $out[] = $value;
            }
        }

        return array_values(array_unique($out));
    }

    private function mapLanguages(mixed $languages): array
    {
        if (! is_array($languages)) {
            return $this->scalar($languages) !== '' ? [['name' => $this->scalar($languages), 'level' => '']] : [];
        }

        $out = [];
        foreach ($this->listItems($languages) as $lang) {
            if (is_string($lang) || is_numeric($lang)) {
                $out[] = ['name' => $this->scalar($lang), 'level' => ''];
                continue;
            }
            if (! is_array($lang)) {
                continue;
            }
            $out[] = [
                'name'  => $this->scalar($lang['language'] ?? $lang['name'] ?? ''),
                'level' => $this->scalar($lang['level'] ?? $lang['proficiency'] ?? $lang['fluency'] ?? ''),
            ];
        }

        return $out;
    }

    private function mapNamedItem(mixed $item): array
    {
        if (is_string($item) || is_numeric($item)) {
            return ['name' => $this->scalar($item), 'description' => ''];
        }
        if (! is_array($item)) {
            return ['name' => '', 'description' => ''];
        }

        return [
            'name'        => $this->scalar($item['name'] ?? $item['title'] ?? $item['publicationName'] ?? ''),
            'description' => $this->scalar($item['description'] ?? $item['details'] ?? $item['summary'] ?? ''),
        ];
    }

    private function mapLanguageItem(mixed $item): array
    {
        if (is_string($item) || is_numeric($item)) {
            return ['name' => $this->scalar($item), 'level' => ''];
        }
        if (! is_array($item)) {
            return ['name' => '', 'level' => ''];
        }

        return [
            'name'  => $this->scalar($item['name'] ?? $item['language'] ?? ''),
            'level' => $this->scalar($item['level'] ?? ''),
        ];
    }

    /**
     * @return list<mixed>
     */
    private function listItems(mixed $value): array
    {
        if ($value === null) {
            return [];
        }
        if (! is_array($value)) {
            return [$value];
        }
        if ($value === []) {
            return [];
        }
        if (array_is_list($value)) {
            return $value;
        }

        return [$value];
    }

    private function formatLocation(mixed $location): string
    {
        if (is_string($location) || is_numeric($location)) {
            return $this->scalar($location);
        }
        if (! is_array($location)) {
            return '';
        }

        if (! empty($location['formatted'])) {
            return $this->scalar($location['formatted']);
        }

        $parts = array_filter([
            $this->scalar($location['city'] ?? ''),
            $this->scalar($location['state'] ?? $location['stateCode'] ?? ''),
            $this->scalar($location['country'] ?? $location['countryCode'] ?? ''),
        ]);

        return implode(', ', $parts);
    }

    private function formatPeriod(string $start, string $end, bool $isCurrent): string
    {
        $start = trim($start);
        $end   = trim($end);

        if ($start === '' && $end === '') {
            return '';
        }

        if ($isCurrent || strtolower($end) === 'present' || strtolower($end) === 'now') {
            return trim($start.' – Present', ' –');
        }

        if ($start !== '' && $end !== '') {
            return "{$start} – {$end}";
        }

        return $start !== '' ? $start : $end;
    }

    private function extractGithub(mixed $websites): string
    {
        foreach ($this->websiteList($websites) as $url) {
            if (preg_match('/github\.com/i', $url)) {
                return $url;
            }
        }

        return '';
    }

    private function extractWebsite(mixed $websites): string
    {
        foreach ($this->websiteList($websites) as $url) {
            if (! preg_match('/linkedin\.com|github\.com/i', $url)) {
                return $url;
            }
        }

        return '';
    }

    /**
     * @return list<string>
     */
    private function websiteList(mixed $websites): array
    {
        if (! is_array($websites)) {
            $one = $this->scalar($websites);

            return $one !== '' ? [$one] : [];
        }

        $urls = [];
        foreach ($websites as $w) {
            if (is_array($w)) {
                $url = $this->scalar($w['url'] ?? $w['value'] ?? $w['parsed'] ?? '');
            } else {
                $url = $this->scalar($w);
            }
            if ($url !== '') {
                $urls[] = $url;
            }
        }

        return array_values(array_unique($urls));
    }

    private function firstString(mixed $values): string
    {
        if (! is_array($values)) {
            return $this->scalar($values);
        }

        foreach ($values as $value) {
            $text = $this->scalar($value);
            if ($text !== '') {
                return $text;
            }
        }

        return '';
    }

    private function scalar(mixed $value): string
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
            return trim($value);
        }
        if (is_array($value)) {
            if ($value === []) {
                return '';
            }
            foreach (['parsed', 'raw', 'formatted', 'formattedNumber', 'rawText', 'value', 'name', 'text', 'content'] as $key) {
                if (isset($value[$key]) && ! is_array($value[$key])) {
                    return trim((string) $value[$key]);
                }
            }
            $parts = [];
            foreach ($value as $item) {
                $part = $this->scalar($item);
                if ($part !== '') {
                    $parts[] = $part;
                }
            }

            return trim(implode(' ', $parts));
        }

        return trim((string) $value);
    }

    private function looksLikeSkillNotRole(string $role): bool
    {
        if ($role === '') {
            return false;
        }
        if (preg_match('/\b(developer|engineer|manager|analyst|designer|tester|intern|lead|consultant|specialist|officer|executive)\b/i', $role)) {
            return false;
        }
        if (preg_match('/\b(react\s*native|jasper|android\s*studio|strapi|html|css|javascript|sql|java|cms|reporting)\b/i', $role)) {
            return true;
        }
        if (preg_match('/\([^)]*tool[^)]*\)/i', $role)) {
            return true;
        }

        return false;
    }
}
