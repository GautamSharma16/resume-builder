<?php

namespace App\Services;

use App\Models\Template;
use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;

class TemplateRenderService
{
    public function resumeSampleData(): array
    {
        return [
            'type' => 'resume',
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'mobile' => '+91 98765 43210',
            'location' => 'Bengaluru, India',
            'link' => 'https://example.com',
            'summary' => 'Senior Developer with 3+ years of experience building SaaS products, scalable Laravel APIs, and clean user workflows.',
            'linkedin' => 'linkedin.com/in/johndoe',
            'github' => 'github.com/johndoe',
            'skills' => ['React.js', 'Node.js', 'Express.js', 'MongoDB', 'REST APIs', 'JWT Authentication'],
            'experience' => [
                [
                    'role' => 'MERN Stack Developer Intern',
                    'company' => 'Companyvista Inc',
                    'duration' => 'Feb 2024 - Present',
                    'location' => 'On-site',
                    'link' => 'https://companyvista.com',
                    'highlights' => [
                        'Engineered full-stack features across the platform using MongoDB, Express.js, React.js, and Node.js.',
                        'Architected RESTful APIs powering core business workflows, reducing frontend-backend integration time and improving response consistency.',
                    ],
                ],
                [
                    'role' => 'Frontend Developer',
                    'company' => 'BrightTech',
                    'duration' => '2022 - 2024',
                    'location' => 'Remote',
                    'link' => 'https://brighttech.com',
                    'highlights' => [
                        'Delivered responsive React-based dashboards with reusable UI components.',
                        'Collaborated with backend teams to integrate APIs and improve data visibility.',
                    ],
                ],
            ],
            'education' => [
                [
                    'degree' => 'Master of Computer Applications (MCA)',
                    'institution' => 'Galgotia College of Engineering and Technology',
                    'duration' => '2024 - Present',
                    'cgpa' => '7.70',
                ],
                [
                    'degree' => 'B.Sc. Computer Science',
                    'institution' => 'Delhi University',
                    'duration' => '2021 - 2024',
                    'cgpa' => '7.85',
                ],
            ],
            'projects' => [
                [
                    'name' => 'TrimNet — URL Shortener',
                    'link' => 'https://trimnet.vercel.app',
                    'tech_stack' => 'React.js · Spring Boot · PostgreSQL',
                    'highlights' => [
                        'Designed and delivered a full-stack URL shortening system with RESTful APIs supporting link creation, intelligent redirection, and per-link usage analytics.',
                        'Built a responsive React.js frontend with seamless backend integration and robust server-side validation.',
                    ],
                ],
                [
                    'name' => 'AI Research Browser Extension',
                    'link' => 'https://github.com/GautamSharma16/Research-Assistant',
                    'tech_stack' => 'React.js · Spring Boot · Gemini API',
                    'highlights' => [
                        'Developed a browser extension using Google Gemini API to deliver real-time text summarization and context-aware question generation.',
                        'Engineered secure backend APIs for AI request routing and response optimization.',
                    ],
                ],
            ],
            'certifications' => [
                'AWS Certified Developer – Associate',
                'MongoDB Certified Developer',
                'Certified React.js Specialist',
            ],
            'social_links' => ['linkedin.com/in/johndoe', 'github.com/johndoe'],
        ];
    }

    public function coverLetterSampleData(?array $overrides = []): array
    {
        return array_merge([
            'type' => 'cover_letter',
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'mobile' => '+91 98765 43210',
            'location' => 'Bengaluru, India',
            'company' => 'Acme SaaS',
            'company_name' => 'Acme SaaS',
            'job_role' => 'Senior Laravel Developer',
            'skills' => 'Laravel, PHP, MySQL',
            'job_description' => "We are looking for a Senior Laravel Developer to join our team. You will be responsible for building scalable APIs, managing databases, and improving our SaaS product's performance.",
            'body' => "Dear Hiring Manager,\n\nI am excited to apply for the Senior Laravel Developer role at Acme SaaS. With 3+ years of experience building Laravel products, payment workflows, and database-driven applications, I can contribute quickly to your engineering team.\n\nMy work includes building production APIs, improving performance, and collaborating with product teams to ship clean user experiences. I would welcome the opportunity to bring the same focus and ownership to Acme SaaS.\n\nSincerely,\nJohn Doe",
        ], $overrides ?? []);
    }

    public function renderResume(Template $template, ?array $data = null): HtmlString
    {
        $data = $data ?: $this->resumeSampleData();
        $html = $template->html ?: '';

        $accentColor = $this->resumeAccentColor($data);
        if ($this->shouldRenderWithBlade($html)) {
            return new HtmlString($this->resumeAccentStyle($accentColor).$this->renderBlade($html, $this->bladeRenderDataForResume($data)));
        }

        return new HtmlString($this->render($html, $this->normalizeResume($data)));
    }

    public function renderCoverLetter(Template $template, ?array $data = null): HtmlString
    {
        $data = $data ?: $this->coverLetterSampleData();
        $html = $template->html ?: '';

        if ($this->shouldRenderWithBlade($html)) {
            return new HtmlString($this->renderBlade($html, $this->bladeRenderDataForCoverLetter($data)));
        }

        return new HtmlString($this->render($html, $this->normalizeCoverLetter($data)));
    }

    private function render(string $html, array $data): string
    {
        $originalHtml = $html;
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $html = str_replace('{{'.$key.'}}', $value, $html);
                $html = str_replace('[['.$key.']]', $value, $html);
            }
        }

        // Some stored resume templates don't include a {{projects}} placeholder.
        // Ensure projects are still visible in both preview and PDF output.
        $hasProjectsToken = str_contains($originalHtml, '{{projects}}') || str_contains($originalHtml, '[[projects]]');
        if (! $hasProjectsToken && str_contains($originalHtml, 'tpl-resume') && ! empty($data['projects'])) {
            $projectsSection = '<h2>Projects</h2>'.$data['projects'];
            $pos = strrpos($html, '</div>');
            if ($pos !== false) {
                $html = substr($html, 0, $pos).$projectsSection.substr($html, $pos);
            } else {
                $html .= $projectsSection;
            }
        }

        $html = $this->resumeAccentStyle($this->resumeAccentColor($data)).$html;

        return $html;
    }

    private function resumeAccentStyle(string $color): string
    {
        if (! preg_match('/^#[0-9a-f]{6}$/i', $color)) {
            return '';
        }

        $primaryColor = $color;

        return '<style>
            :root, .tpl-resume { --primary: '.$primaryColor.'; }
            .tpl-resume {
                border-color: var(--primary) !important;
                border-top-color: var(--primary) !important;
                border-right-color: var(--primary) !important;
                border-bottom-color: var(--primary) !important;
                border-left-color: var(--primary) !important;
            }
            .tpl-resume h1,
            .tpl-resume h2,
            .tpl-resume h3,
            .tpl-resume a,
            .tpl-role-head strong {
                color: var(--primary) !important;
                border-color: var(--primary) !important;
            }
            .tpl-badge {
                background: var(--primary) !important;
                border-color: var(--primary) !important;
                color: #fff !important;
            }
            .tpl-rule,
            .tpl-accentbox header > div,
            .tpl-two aside,
            .tpl-carded header,
            .tpl-band header,
            .tpl-resume > header[style*="background"],
            .tpl-resume h2[style*="background"] {
                background: var(--primary) !important;
                color: #fff !important;
            }
            .tpl-rule *,
            .tpl-accentbox header > div *,
            .tpl-two aside *,
            .tpl-carded header *,
            .tpl-band header *,
            .tpl-resume > header[style*="background"] *,
            .tpl-resume h2[style*="background"] {
                color: #fff !important;
                border-color: rgba(255,255,255,0.45) !important;
            }
        </style>';
    }

    private function resumeAccentColor(array $data): string
    {
        $color = $this->text(Arr::get($data, 'primary_color', ''));
        if (! preg_match('/^#[0-9a-f]{6}$/i', $color)) {
            return '';
        }

        $customized = filter_var(Arr::get($data, 'primary_color_customized', $color !== '#2563eb'), FILTER_VALIDATE_BOOLEAN);

        return $customized ? $color : '';
    }

    private function normalizeResume(array $data): array
    {
        return [
            'name' => e($this->text(Arr::get($data, 'name', ''))),
            'email' => e($this->text(Arr::get($data, 'email', ''))),
            'mobile' => e($this->text(Arr::get($data, 'mobile', Arr::get($data, 'contact', '')))),
            'location' => e($this->text(Arr::get($data, 'location', Arr::get($data, 'address', '')))),
            'summary' => e($this->text(Arr::get($data, 'summary', ''))),
            'skills' => $this->badges(Arr::get($data, 'skills', [])),
            'experience' => $this->experience(Arr::get($data, 'experience', [])),
            'education' => $this->list(Arr::get($data, 'education', [])),
            'projects' => $this->projectList(Arr::get($data, 'projects', [])),
            'social_links' => $this->inline(Arr::get($data, 'social_links', [])),
            'primary_color' => $this->text(Arr::get($data, 'primary_color', '')),
            'primary_color_customized' => filter_var(Arr::get($data, 'primary_color_customized', false), FILTER_VALIDATE_BOOLEAN),
        ];
    }

    private function normalizeCoverLetter(array $data): array
    {
        return [
            'name' => e($this->text(Arr::get($data, 'name', ''))),
            'email' => e($this->text(Arr::get($data, 'email', ''))),
            'mobile' => e($this->text(Arr::get($data, 'mobile', ''))),
            'location' => e($this->text(Arr::get($data, 'location', ''))),
            'company' => e($this->text(Arr::get($data, 'company', Arr::get($data, 'company_name', '')))),
            'company_name' => e($this->text(Arr::get($data, 'company_name', Arr::get($data, 'company', '')))),
            'job_role' => e($this->text(Arr::get($data, 'job_role', ''))),
            'skills' => e($this->text(Arr::get($data, 'skills', ''))),
            'body' => nl2br(e($this->text(Arr::get($data, 'body', '')))),
        ];
    }

    private function shouldRenderWithBlade(string $html): bool
    {
        return preg_match('/\{\{\s*\$resume|\{\{\s*\$coverLetter|\@foreach\s*\(\s*\$resume|\@foreach\s*\(\s*\$coverLetter|\@if\s*\(\s*\$resume|\@if\s*\(\s*\$coverLetter/i', $html) === 1;
    }

    private function renderBlade(string $html, array $data): string
    {
        try {
            return \Illuminate\Support\Facades\Blade::render($html, $data);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Template Blade render failed: ' . $e->getMessage(), ['html' => str($html)->limit(200)]);
            return '<div style="background:#fee2e2;padding:1rem;color:#991b1b;font-family:sans-serif;margin:0 20px 20px;">Preview Blade render failed: ' . e($e->getMessage()) . '</div>' . $html;
        }
    }

    private function bladeRenderDataForResume(array $data): array
    {
        $resume = [
            'type' => 'resume',
            'name' => $this->text(Arr::get($data, 'name', '')),
            'email' => $this->text(Arr::get($data, 'email', '')),
            'mobile' => $this->text(Arr::get($data, 'mobile', Arr::get($data, 'contact', ''))),
            'location' => $this->text(Arr::get($data, 'location', Arr::get($data, 'address', ''))),
            'summary' => $this->text(Arr::get($data, 'summary', '')),
            'linkedin' => $this->text(Arr::get($data, 'linkedin', '')),
            'github' => $this->text(Arr::get($data, 'github', '')),
            'tech_stack' => $this->text(Arr::get($data, 'tech_stack', '')),
            'skills' => $this->normalizeBladeArray(Arr::get($data, 'skills', [])),
            'experience' => $this->normalizeBladeArray(Arr::get($data, 'experience', [])),
            'education' => $this->normalizeBladeArray(Arr::get($data, 'education', [])),
            'projects' => $this->normalizeBladeArray(Arr::get($data, 'projects', [])),
            'certifications' => $this->normalizeBladeArray(Arr::get($data, 'certifications', [])),
            'social_links' => $this->normalizeBladeArray(Arr::get($data, 'social_links', [])),
            'link' => $this->text(Arr::get($data, 'link', '')),
            'contact' => $this->text(Arr::get($data, 'contact', '')),
            'address' => $this->text(Arr::get($data, 'address', '')),
            'primary_color' => $this->text(Arr::get($data, 'primary_color', '')),
            'primary_color_customized' => filter_var(Arr::get($data, 'primary_color_customized', false), FILTER_VALIDATE_BOOLEAN),
        ];

        return array_merge(['resume' => $resume], $resume);
    }

    private function bladeRenderDataForCoverLetter(array $data): array
    {
        $coverLetter = [
            'type' => 'cover_letter',
            'name' => $this->text(Arr::get($data, 'name', '')),
            'email' => $this->text(Arr::get($data, 'email', '')),
            'mobile' => $this->text(Arr::get($data, 'mobile', '')),
            'location' => $this->text(Arr::get($data, 'location', '')),
            'company' => $this->text(Arr::get($data, 'company', Arr::get($data, 'company_name', ''))),
            'company_name' => $this->text(Arr::get($data, 'company_name', Arr::get($data, 'company', ''))),
            'job_role' => $this->text(Arr::get($data, 'job_role', '')),
            'skills' => $this->text(Arr::get($data, 'skills', '')),
            'body' => $this->text(Arr::get($data, 'body', '')),
        ];

        return array_merge(['coverLetter' => $coverLetter], $coverLetter);
    }

    private function normalizeBladeArray(array|string|null $items): array
    {
        $items ??= [];

        if (! is_array($items)) {
            $items = array_filter(array_map('trim', explode("\n", (string) $items)));
        }

        return array_values(collect($items)->map(function ($item) {
            if (is_array($item)) {
                return collect($item)->map(function ($value) {
                    return is_array($value) ? $this->normalizeBladeArray($value) : $this->text($value);
                })->filter()->all();
            }

            return $this->text($item);
        })->filter()->all());
    }

    private function badges(array|string|null $items): string
    {
        $items ??= [];
        $items = is_array($items) ? $items : explode(',', $items);

        return collect($items)->map(fn ($item) => $this->text($item))->filter()->map(fn ($item) => '<span class="tpl-badge">'.e($item).'</span>')->join('');
    }

    private function list(array|string|null $items): string
    {
        $items ??= [];
        $items = is_array($items) ? $items : explode("\n", $items);

        return '<ul>'.collect($items)->map(fn ($item) => $this->text($item))->filter()->map(fn ($item) => '<li>'.e($item).'</li>')->join('').'</ul>';
    }

    private function projectList(array|string|null $items): string
    {
        $items ??= [];
        $items = is_array($items) ? $items : explode("\n", $items);

        return '<ul>'.collect($items)->map(function ($item) {
            if (! is_array($item)) {
                $name = $this->text($item);

                return $name === '' ? '' : '<li>'.e($name).'</li>';
            }

            $name = $this->text($item['name'] ?? '');
            $description = $this->text($item['description'] ?? '');

            if ($name === '' && $description === '') {
                return '';
            }

            $title = $name !== '' ? '<strong>'.e($name).'</strong>' : '';
            $body = $description !== '' ? '<span class="tpl-description">'.e($description).'</span>' : '';

            return '<li>'.$title.$body.'</li>';
        })->filter()->join('').'</ul>';
    }

    private function inline(array|string|null $items): string
    {
        $items ??= [];
        $items = is_array($items) ? $items : explode(',', $items);

        return collect($items)->map(fn ($item) => $this->text($item))->filter()->map(fn ($item) => e($item))->join(' | ');
    }

    private function experience(array|string|null $items): string
    {
        $items ??= [];
        if (! is_array($items)) {
            return '<p>'.e($items).'</p>';
        }

        return collect($items)->map(function ($item) {
            if (! is_array($item)) {
                return '<div class="tpl-role"><p>'.e($this->text($item)).'</p></div>';
            }

            $points = $this->list($item['points'] ?? []);

            return '<div class="tpl-role"><div class="tpl-role-head"><strong>'.e($this->text($item['role'] ?? '')).'</strong><span>'.e($this->text($item['period'] ?? '')).'</span></div><p>'.e($this->text($item['company'] ?? '')).'</p>'.$points.'</div>';
        })->join('');
    }

    private function text(mixed $value): string
    {
        if (is_array($value)) {
            return collect($value)
                ->flatten()
                ->map(fn ($part) => is_scalar($part) ? trim((string) $part) : '')
                ->filter()
                ->join(' - ');
        }

        return trim((string) ($value ?? ''));
    }
}
