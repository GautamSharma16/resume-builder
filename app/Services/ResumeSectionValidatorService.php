<?php

namespace App\Services;

/**
 * Prevents cross-section pollution (experience bullets in education, etc.).
 */
class ResumeSectionValidatorService
{
    public function sanitizeStandard(array $resume): array
    {
        $resume['experience'] = $this->sanitizeExperienceList($resume['experience'] ?? [], $resume);
        $resume['education']    = $this->sanitizeEducationList($resume['education'] ?? [], $resume);
        $resume['projects']     = $this->sanitizeProjectList($resume['projects'] ?? []);
        $resume['skills']       = $this->sanitizeSkills($resume['skills'] ?? []);
        $resume['summary']      = $this->sanitizeSummary((string) ($resume['summary'] ?? ''));

        return $resume;
    }

    public function sanitizeBuilder(array $resume): array
    {
        $standard = $this->builderToStandardShape($resume);
        $standard = $this->sanitizeStandard($standard);

        return $this->standardToBuilderShape($standard, $resume);
    }

    /**
     * @param  array<string, mixed>  $resume
     * @return list<array>
     */
    private function sanitizeExperienceList(array $items, array &$resume): array
    {
        $clean = [];

        foreach ($items as $exp) {
            if (! is_array($exp)) {
                continue;
            }

            $exp = $this->splitMergedRoleField($exp, $resume);

            $company = trim((string) ($exp['company'] ?? ''));
            $role    = trim((string) ($exp['role'] ?? ''));
            $points  = $this->sanitizeExperiencePoints($exp['points'] ?? []);

            if ($this->looksLikeEducationOnlyBlob($company, $role, $points)) {
                $this->pushEducationFromMisplaced($resume, $company, $role, (string) ($exp['start_date'] ?? $exp['end_date'] ?? ''));

                continue;
            }

            if ($company === '' && $role === '' && $points === []) {
                continue;
            }

            if ($company === '' && $this->looksLikeProjectDump($role, $points)) {
                $this->pushProjectsFromDump($resume, $role, $points);

                continue;
            }

            $exp['company'] = $company;
            $exp['role']    = $role;
            $exp['points']  = $points;
            $exp['description'] = trim((string) ($exp['description'] ?? ''));

            $clean[] = $exp;
        }

        return array_values($clean);
    }

    /**
     * @param  array<string, mixed>  $resume
     * @return list<array>
     */
    private function sanitizeEducationList(array $items, array &$resume): array
    {
        $clean = [];

        foreach ($items as $edu) {
            if (! is_array($edu)) {
                continue;
            }

            $degree      = trim((string) ($edu['degree'] ?? ''));
            $field       = trim((string) ($edu['field'] ?? ''));
            $institution = trim((string) ($edu['institution'] ?? ''));

            if ($this->educationContainsExperienceBullet($degree, $field, $institution)) {
                $bullet = ltrim($degree, "-•* \t");
                if ($bullet === '' && $institution !== '') {
                    $bullet = $institution;
                }
                if ($bullet !== '') {
                    $resume['experience']   = is_array($resume['experience'] ?? null) ? $resume['experience'] : [];
                    $resume['experience'][] = [
                        'company'     => '',
                        'role'        => '',
                        'location'    => '',
                        'start_date'  => '',
                        'end_date'    => '',
                        'is_current'  => false,
                        'description' => '',
                        'points'      => [$bullet],
                    ];
                }

                continue;
            }

            if ($degree === '' && $field === '' && $institution === '') {
                continue;
            }

            $clean[] = [
                'degree'      => $degree,
                'field'       => $field,
                'institution' => $institution,
                'start_date'  => trim((string) ($edu['start_date'] ?? '')),
                'end_date'    => trim((string) ($edu['end_date'] ?? '')),
            ];
        }

        return array_values($clean);
    }

    /**
     * @return list<array>
     */
    private function sanitizeProjectList(array $items): array
    {
        $clean = [];

        foreach ($items as $project) {
            if (! is_array($project)) {
                continue;
            }

            $name = trim((string) ($project['name'] ?? ''));
            $institutionPattern = '/\b(university|college|institute|school|academy)\b/i';

            if ($name !== '' && preg_match($institutionPattern, $name) && ! preg_match('/\b(project|app|platform|system)\b/i', $name)) {
                continue;
            }

            if ($name === '' && trim((string) ($project['description'] ?? '')) === '') {
                continue;
            }

            $clean[] = $project;
        }

        return array_values($clean);
    }

    /**
     * @return list<string>
     */
    private function sanitizeSkills(array $skills): array
    {
        $out = [];
        foreach ($skills as $skill) {
            $s = trim((string) $skill);
            if ($s === '' || mb_strlen($s) > 120) {
                continue;
            }
            if (preg_match('/^(projects?|experience|education|interests?)$/i', $s)) {
                continue;
            }
            $out[] = $s;
        }

        return array_values(array_unique($out));
    }

    private function sanitizeSummary(string $summary): string
    {
        if ($summary === '') {
            return '';
        }
        if (preg_match('/\b(CONTACT|EDUCATION|SKILLS?|PROJECTS?|EXPERIENCE)\b/i', $summary)) {
            return '';
        }
        if (preg_match('/^\s*[-•*]\s*(developed|implemented|built)\b/i', $summary)) {
            return '';
        }

        return $summary;
    }

    /**
     * @param  array<string, mixed>  $exp
     * @param  array<string, mixed>  $resume
     * @return array<string, mixed>
     */
    private function splitMergedRoleField(array $exp, array &$resume): array
    {
        $role    = trim((string) ($exp['role'] ?? ''));
        $company = trim((string) ($exp['company'] ?? ''));

        if ($company !== '' || $role === '') {
            return $exp;
        }

        if (preg_match(
            '/^([A-Z][A-Za-z]+(?:\s+[A-Z][A-Za-z]+){0,3})\s+((?:Senior\s+|Junior\s+|Lead\s+)?(?:MERN|MEAN|Full[\s-]*Stack|Software|Frontend|Front[\s-]*End|Backend|Back[\s-]*End|Web|Mobile|Data|DevOps|QA|UI\/UX|Product|Project|Business|HR|Sales|Marketing|Node\.?js|React|Java|Python|PHP|\.NET).{3,90})$/iu',
            $role,
            $m
        )) {
            $exp['role'] = trim($m[2]);

            return $exp;
        }

        if (preg_match(
            '/^([A-Z][A-Z\s]{4,40})\s+((?:Senior\s+|Junior\s+)?[A-Za-z][A-Za-z\s\/\-]{4,60}(?:Developer|Engineer|Manager|Analyst|Designer|Tester|Architect|Consultant|Intern|Administrator|Officer|Executive))$/u',
            $role,
            $m
        )) {
            $maybeName = trim($m[1]);
            $maybeRole = trim($m[2]);
            if ($this->looksLikePersonNameToken($maybeName)) {
                if (trim((string) ($resume['name'] ?? '')) === '') {
                    $parts           = preg_split('/\s+/', $maybeName, 2) ?: [];
                    $resume['name']  = $parts[0] ?? $maybeName;
                    $resume['last_name'] = trim((string) ($resume['last_name'] ?? '')) ?: ($parts[1] ?? '');
                }
                $exp['role'] = $maybeRole;
            }
        }

        return $exp;
    }

    private function educationContainsExperienceBullet(string $degree, string $field, string $institution): bool
    {
        $hay = strtolower(implode(' ', array_filter([$degree, $field, $institution])));

        if ($hay === '') {
            return false;
        }

        if (preg_match('/^\s*[-•*]\s*/', $degree)) {
            return true;
        }

        if (preg_match('/\b(developed|implemented|built|designed|created|maintained|deployed|optimized|led|managed)\b/i', $hay)
            && ! preg_match('/\b(b\.?tech|m\.?tech|bachelor|master|bsc|msc|mba|bca|mca|diploma|ph\.?d|degree|university|college|school|institute|cgpa|gpa|12th|10th|intermediate|matriculation)\b/i', $hay)) {
            return true;
        }

        if (preg_match('/\b(responsible for|accountable for|key deliverables|market exploration|sales targets|territory|achieved targets|managed a team|handling clients)\b/i', $hay)
            && ! preg_match('/\b(b\.?tech|m\.?tech|bachelor|master|bsc|msc|mba|bca|mca|diploma|university|college|school|institute|cgpa|gpa|12th|10th)\b/i', $hay)) {
            return true;
        }

        if ($institution !== '' && $degree === '' && $field === ''
            && preg_match('/\b(responsible|sales|manager|deliverables|exploration|development|services|clients|revenue)\b/i', $institution)
            && ! preg_match('/\b(university|college|school|institute|academy)\b/i', $institution)) {
            return true;
        }

        return false;
    }

    /**
     * @param  list<mixed>  $points
     * @return list<string>
     */
    private function sanitizeExperiencePoints(array $points): array
    {
        $clean = [];
        foreach ($points as $point) {
            $p = trim((string) $point);
            if ($p === '') {
                continue;
            }
            if (preg_match('/^\s*(projects?|education|skills?|interests?)\s*:?\s*$/i', $p)) {
                continue;
            }
            if ($this->educationContainsExperienceBullet($p, '', '')) {
                continue;
            }
            $clean[] = $p;
        }

        return $clean;
    }

    private function looksLikeEducationOnlyBlob(string $company, string $role, array $points): bool
    {
        $hay = strtolower(implode(' ', array_filter([$company, $role, ...$points])));

        return $hay !== ''
            && preg_match('/\b(b\.?tech|bachelor|master|bsc|msc|mba|university|college|diploma|cgpa|gpa)\b/i', $hay)
            && ! preg_match('/\b(developer|engineer|manager|ltd|limited|pvt|inc|solutions|systems)\b/i', $hay);
    }

    private function looksLikeProjectDump(string $role, array $points): bool
    {
        $blob = strtolower($role.' '.implode(' ', $points));

        return str_contains($blob, 'projects') && (str_contains($blob, 'technologies used') || str_contains($blob, 'key contributions'));
    }

    /**
     * @param  array<string, mixed>  $resume
     */
    private function pushEducationFromMisplaced(array &$resume, string $company, string $role, string $year): void
    {
        $resume['education']   = is_array($resume['education'] ?? null) ? $resume['education'] : [];
        $resume['education'][] = [
            'degree'      => $role ?: $company,
            'field'       => '',
            'institution' => $company ?: '',
            'start_date'  => '',
            'end_date'    => $year,
        ];
    }

    /**
     * @param  array<string, mixed>  $resume
     */
    private function pushProjectsFromDump(array &$resume, string $role, array $points): void
    {
        $resume['projects']   = is_array($resume['projects'] ?? null) ? $resume['projects'] : [];
        $resume['projects'][] = [
            'name'        => $role ?: 'Project',
            'tech_stack'  => '',
            'description' => implode("\n", $points),
            'link'        => '',
        ];
    }

    private function looksLikePersonNameToken(string $value): bool
    {
        return (bool) preg_match('/^[A-Z][A-Za-z]+(?:\s+[A-Z][A-Za-z]+){0,3}$/u', trim($value))
            && ! preg_match('/\b(developer|engineer|stack|software|mern|mean)\b/i', $value);
    }

    /**
     * @param  array<string, mixed>  $builder
     * @return array<string, mixed>
     */
    private function builderToStandardShape(array $builder): array
    {
        $experience = [];
        foreach ($builder['experience'] ?? [] as $exp) {
            if (! is_array($exp)) {
                continue;
            }
            $period = trim((string) ($exp['period'] ?? ''));
            [$start, $end, $current] = $this->parsePeriod($period);
            $experience[] = [
                'company'     => trim((string) ($exp['company'] ?? '')),
                'role'        => trim((string) ($exp['role'] ?? '')),
                'location'    => '',
                'start_date'  => $start,
                'end_date'    => $end,
                'is_current'  => $current,
                'description' => '',
                'points'      => is_array($exp['points'] ?? null) ? $exp['points'] : [],
            ];
        }

        $education = [];
        foreach ($builder['education'] ?? [] as $edu) {
            if (! is_array($edu)) {
                continue;
            }
            $education[] = [
                'degree'      => trim((string) ($edu['degree'] ?? '')),
                'field'       => trim((string) ($edu['stream'] ?? $edu['field'] ?? '')),
                'institution' => trim((string) ($edu['institution'] ?? '')),
                'start_date'  => '',
                'end_date'    => trim((string) ($edu['year'] ?? '')),
            ];
        }

        return [
            'name'           => trim((string) ($builder['name'] ?? '')),
            'last_name'      => trim((string) ($builder['last_name'] ?? '')),
            'designation'    => trim((string) ($builder['designation'] ?? $builder['job_title'] ?? '')),
            'email'          => trim((string) ($builder['email'] ?? '')),
            'mobile'         => trim((string) ($builder['mobile'] ?? '')),
            'location'       => trim((string) ($builder['location'] ?? '')),
            'linkedin'       => trim((string) ($builder['linkedin'] ?? '')),
            'github'         => trim((string) ($builder['github'] ?? '')),
            'website'        => trim((string) ($builder['portfolio'] ?? $builder['link'] ?? '')),
            'summary'        => trim((string) ($builder['summary'] ?? '')),
            'skills'         => $builder['skills'] ?? [],
            'experience'     => $experience,
            'education'      => $education,
            'projects'       => $builder['projects'] ?? [],
            'certifications' => $builder['certifications'] ?? [],
            'languages'      => $builder['languages'] ?? [],
            'achievements'   => $builder['achievements'] ?? [],
        ];
    }

    /**
     * @return array{0:string,1:string,2:bool}
     */
    private function parsePeriod(string $period): array
    {
        $period = trim($period);
        if ($period === '') {
            return ['', '', false];
        }
        $current = (bool) preg_match('/\b(present|now|current)\b/i', $period);
        if (preg_match('/^(.+?)\s*[–\-—to]+\s*(.+)$/iu', $period, $m)) {
            return [trim($m[1]), trim($m[2]), $current || preg_match('/\b(present|now)\b/i', $m[2])];
        }

        return [$period, '', $current];
    }

    /**
     * @param  array<string, mixed>  $original
     * @return array<string, mixed>
     */
    private function standardToBuilderShape(array $standard, array $original): array
    {
        $normalizer = app(ResumeNormalizerService::class);
        $builder    = $normalizer->toBuilderFormat($standard);

        $builder['desired_job_role'] = $original['desired_job_role'] ?? '';
        $builder['social_links']     = $original['social_links'] ?? $builder['social_links'] ?? [];

        return $builder;
    }
}
