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
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'mobile' => '+91 98765 43210',
            'location' => 'Bengaluru, India',
            'summary' => 'Senior Developer with 3+ years of experience building SaaS products, scalable Laravel APIs, and clean user workflows.',
            'skills' => ['Laravel', 'PHP', 'MySQL', 'Tailwind CSS', 'REST APIs', 'Git'],
            'experience' => [
                ['role' => 'Senior Developer', 'company' => 'Acme SaaS', 'period' => '2022 - Present', 'points' => ['Built subscription and payment workflows in Laravel.', 'Improved application performance by 35%.']],
                ['role' => 'PHP Developer', 'company' => 'BrightTech', 'period' => '2020 - 2022', 'points' => ['Delivered CRM features with MySQL reporting.', 'Integrated third-party APIs for operations teams.']],
            ],
            'education' => ['B.Tech in Computer Science, Delhi Technical University', 'Certified Laravel Developer'],
            'projects' => ['Resume Builder SaaS with Razorpay downloads', 'ATS score analyzer using Gemini API'],
            'social_links' => ['linkedin.com/in/johndoe', 'github.com/johndoe'],
        ];
    }

    public function coverLetterSampleData(?array $overrides = []): array
    {
        return array_merge([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'mobile' => '+91 98765 43210',
            'location' => 'Bengaluru, India',
            'company' => 'Acme SaaS',
            'company_name' => 'Acme SaaS',
            'job_role' => 'Senior Laravel Developer',
            'skills' => 'Laravel, PHP, MySQL',
            'body' => "Dear Hiring Manager,\n\nI am excited to apply for the Senior Laravel Developer role at Acme SaaS. With 3+ years of experience building Laravel products, payment workflows, and database-driven applications, I can contribute quickly to your engineering team.\n\nMy work includes building production APIs, improving performance, and collaborating with product teams to ship clean user experiences. I would welcome the opportunity to bring the same focus and ownership to Acme SaaS.\n\nSincerely,\nJohn Doe",
        ], $overrides ?? []);
    }

    public function renderResume(Template $template, ?array $data = null): HtmlString
    {
        return new HtmlString($this->render($template->html ?: '', $this->normalizeResume($data ?: $this->resumeSampleData())));
    }

    public function renderCoverLetter(Template $template, ?array $data = null): HtmlString
    {
        return new HtmlString($this->render($template->html ?: '', $this->normalizeCoverLetter($data ?: $this->coverLetterSampleData())));
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

        return $html;
    }

    private function normalizeResume(array $data): array
    {
        return [
            'name' => e((string) Arr::get($data, 'name', '')),
            'email' => e((string) Arr::get($data, 'email', '')),
            'mobile' => e((string) Arr::get($data, 'mobile', Arr::get($data, 'contact', ''))),
            'location' => e((string) Arr::get($data, 'location', Arr::get($data, 'address', ''))),
            'summary' => e((string) Arr::get($data, 'summary', '')),
            'skills' => $this->badges(Arr::get($data, 'skills', [])),
            'experience' => $this->experience(Arr::get($data, 'experience', [])),
            'education' => $this->list(Arr::get($data, 'education', [])),
            'projects' => $this->list(Arr::get($data, 'projects', [])),
            'social_links' => $this->inline(Arr::get($data, 'social_links', [])),
        ];
    }

    private function normalizeCoverLetter(array $data): array
    {
        return [
            'name' => e((string) Arr::get($data, 'name', '')),
            'email' => e((string) Arr::get($data, 'email', '')),
            'mobile' => e((string) Arr::get($data, 'mobile', '')),
            'location' => e((string) Arr::get($data, 'location', '')),
            'company' => e((string) Arr::get($data, 'company', Arr::get($data, 'company_name', ''))),
            'company_name' => e((string) Arr::get($data, 'company_name', Arr::get($data, 'company', ''))),
            'job_role' => e((string) Arr::get($data, 'job_role', '')),
            'skills' => e((string) Arr::get($data, 'skills', '')),
            'body' => nl2br(e((string) Arr::get($data, 'body', ''))),
        ];
    }

    private function badges(array|string $items): string
    {
        $items = is_array($items) ? $items : explode(',', $items);

        return collect($items)->filter()->map(fn ($item) => '<span class="tpl-badge">'.e((string) $item).'</span>')->join('');
    }

    private function list(array|string $items): string
    {
        $items = is_array($items) ? $items : explode("\n", $items);

        return '<ul>'.collect($items)->filter()->map(fn ($item) => '<li>'.e(is_array($item) ? implode(' - ', $item) : (string) $item).'</li>')->join('').'</ul>';
    }

    private function inline(array|string $items): string
    {
        $items = is_array($items) ? $items : explode(',', $items);

        return collect($items)->filter()->map(fn ($item) => e((string) $item))->join(' | ');
    }

    private function experience(array|string $items): string
    {
        if (! is_array($items)) {
            return '<p>'.e($items).'</p>';
        }

        return collect($items)->map(function ($item) {
            if (! is_array($item)) {
                return '<div class="tpl-role"><p>'.e((string) $item).'</p></div>';
            }

            $points = $this->list($item['points'] ?? []);

            return '<div class="tpl-role"><div class="tpl-role-head"><strong>'.e((string) ($item['role'] ?? '')).'</strong><span>'.e((string) ($item['period'] ?? '')).'</span></div><p>'.e((string) ($item['company'] ?? '')).'</p>'.$points.'</div>';
        })->join('');
    }
}
