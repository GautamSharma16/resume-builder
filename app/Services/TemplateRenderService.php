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
            'name' => 'James Smith',
            'email' => 'james.smith@example.com',
            'mobile' => '+1 (555) 123-4567',
            'location' => 'Austin, Texas, USA',
            'link' => 'https://jamessmith.dev',
            'summary' => 'Senior Full Stack Developer with 6+ years of experience building reliable web applications and SaaS products. Skilled in Laravel, React, APIs, and cloud-ready architecture.',
            'linkedin' => 'linkedin.com/in/jamessmith',
            'github' => 'github.com/jamessmith',
            'skills' => ['Laravel', 'PHP', 'React.js', 'MySQL', 'AWS', 'Docker', 'REST APIs'],
            'experience' => [
                [
                    'role' => 'Senior Full Stack Developer',
                    'company' => 'TechNova Solutions',
                    'duration' => '2023 - Present',
                    'highlights' => [
                        'Built core SaaS features across frontend, backend, and API layers.',
                        'Improved application performance and mentored junior developers.',
                    ],
                ],
            ],
            'education' => [
                [
                    'degree' => 'Master of Science in Computer Science',
                    'institution' => 'University of Texas',
                    'duration' => '2018 - 2020',
                ],
                [
                    'degree' => 'Bachelor of Computer Science',
                    'institution' => 'State University',
                    'duration' => '2014 - 2018',
                ],
            ],
            'projects' => [
                [
                    'name' => 'CRM Platform',
                    'tech_stack' => 'Laravel, React.js, MySQL, AWS',
                    'highlights' => [
                        'Built dashboards, role-based workflows, and REST API integrations.',
                    ],
                ],
            ],
            'certifications' => [
                'AWS Certified Solutions Architect',
            ],
            'certificates' => [
                'AWS Certified Solutions Architect',
            ],
            'languages' => [
                ['name' => 'English', 'level' => 'Native'],
            ],
            'additional_information' => [
                'Open Source Contributor',
            ],
            'achievements' => [
                'Top Performer Award 2023',
            ],
            'social_links' => ['linkedin.com/in/jamessmith', 'github.com/jamessmith'],
        ];

        return [
            'type' => 'resume',
            'name' => 'James Smith',
            'email' => 'james.smith@example.com',
            'mobile' => '+1 (555) 123-4567',
            'location' => 'Austin, Texas, USA',
            'link' => 'https://jamessmith.dev',
            'summary' => 'Results-driven Senior Full Stack Developer with 6+ years of experience building scalable web applications, cloud solutions, and enterprise SaaS platforms. Skilled in Laravel, React, AWS, and modern software architecture.',
            'linkedin' => 'linkedin.com/in/jamessmith',
            'github' => 'github.com/jamessmith',
            'skills' => ['Laravel', 'PHP', 'MySQL', 'React.js', 'Next.js', 'TypeScript', 'Node.js', 'AWS', 'Docker', 'Git', 'REST APIs'],
            'experience' => [
                [
                    'role' => 'Senior Full Stack Developer',
                    'company' => 'TechNova Solutions',
                    'duration' => '2023 - Present',
                    'location' => 'On-site',
                    'link' => 'https://companyvista.com',
                    'highlights' => [
                        'Engineered full-stack features across the platform using MongoDB, Express.js, React.js, and Node.js.',
                        'Architected RESTful APIs powering core business workflows, reducing frontend-backend integration time and improving response consistency.',
                    ],
                ],
                [
                    'role' => 'Full Stack Developer',
                    'company' => 'DigitalCraft Inc.',
                    'duration' => '2020 - 2023',
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
                    'degree' => 'Master of Science in Computer Science',
                    'institution' => 'University of Texas',
                    'duration' => '2018 - 2020',
                    'cgpa' => '7.70',
                ],
                [
                    'degree' => 'Bachelor of Computer Science',
                    'institution' => 'State University',
                    'duration' => '2014 - 2018',
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
                'Google Cloud Associate Engineer',
                'Meta Frontend Professional Certificate',
            ],
            'certificates' => [
                'AWS Certified Developer - Associate',
                'Google Cloud Associate Engineer',
                'Meta Frontend Professional Certificate',
            ],
            'languages' => [
                ['name' => 'English', 'level' => 'Native'],
                ['name' => 'Spanish', 'level' => 'Professional'],
            ],
            'additional_information' => [
                'Open Source Contributor',
                'Tech Conference Speaker',
                'Mentor for Junior Developers',
            ],
            'achievements' => [
                'Winner - National Hackathon 2024',
                'Top Performer Award 2023',
                'Built platform serving 100K+ users',
            ],
            'social_links' => ['linkedin.com/in/jamessmith', 'github.com/jamessmith'],
        ];
    }


    public function coverLetterSampleData(?array $overrides = []): array
    {
        return array_merge([
            'type' => 'cover_letter',
            'name' => 'James Smith',
            'email' => 'james.smith@example.com',
            'mobile' => '+1 (555) 123-4567',
            'location' => 'Austin, Texas, USA',
            'company' => 'Acme SaaS',
            'company_name' => 'Acme SaaS',
            'job_role' => 'Senior Laravel Developer',
            'skills' => 'Laravel, React.js, MySQL, AWS',
            'job_description' => "We are looking for a Senior Laravel Developer to join our team. You will be responsible for building scalable APIs, managing databases, and improving our SaaS product's performance.",
            'body' => "Dear Hiring Manager,\n\nI am excited to apply for the Senior Laravel Developer position at Acme SaaS.\n\nWith over 6 years of experience building scalable web applications, RESTful APIs, and cloud-based solutions, I have successfully delivered high-performance products across multiple industries.\n\nMy expertise includes Laravel, React.js, MySQL, AWS, and modern software architecture. I am passionate about building clean, maintainable code and collaborating with cross-functional teams to create exceptional user experiences.\n\nI would welcome the opportunity to contribute my technical expertise and problem-solving skills to your organization.\n\nSincerely,\n\nJames Smith",
            'primary_color' => '#2563eb',
            'primary_color_customized' => false,
        ], $overrides ?? []);

        return array_merge([
            'type' => 'cover_letter',
            'name' => 'James Smith',
            'email' => 'james.smith@example.com',
            'mobile' => '+1 (555) 123-4567',
            'location' => 'Austin, Texas, USA',
            'company' => 'Acme SaaS',
            'company_name' => 'Acme SaaS',
            'job_role' => 'Senior Laravel Developer',
            'skills' => 'Laravel, React.js, MySQL, AWS',
            'job_description' => "We are looking for a Senior Laravel Developer to join our team. You will be responsible for building scalable APIs, managing databases, and improving our SaaS product's performance.",
            'body' => "Dear Hiring Manager,\n\nI am excited to apply for the Senior Laravel Developer position at Acme SaaS.\n\nWith over 6 years of experience building scalable web applications, RESTful APIs, and cloud-based solutions, I have successfully delivered high-performance products across multiple industries.\n\nMy expertise includes Laravel, React.js, MySQL, AWS, and modern software architecture. I am passionate about building clean, maintainable code and collaborating with cross-functional teams to create exceptional user experiences.\n\nI would welcome the opportunity to contribute my technical expertise and problem-solving skills to your organization.\n\nSincerely,\n\nJames Smith",
            'primary_color' => '#2563eb',
            'primary_color_customized' => false,
        ], $overrides ?? []);
    }

    public function renderResume(Template $template, ?array $data = null, bool $allowInjection = true): HtmlString
    {
        $data = $data ?: $this->resumeSampleData();
        $html = $template->html ?: '';
        $accentColor = $this->resumeAccentColor($data);

        if ($this->shouldRenderWithBlade($html)) {
            return new HtmlString($this->withScopedAccent($this->renderBlade($html, $this->bladeRenderDataForResume($data)), $accentColor));
        }

        if (! $this->containsResumePlaceholders($html)) {
            $html = $this->editableResumeTemplateHtml();
        }

        // Resolve CSS variables for DOMPDF
        if ($accentColor) {
            // Use custom accent color
            $html = str_replace('var(--primary, #2563eb)', $accentColor, $html);
            $html = str_replace('var(--primary)', $accentColor, $html);
            $html = preg_replace('/var\(--primary[^)]*\)/', $accentColor, $html);
        } else {
            // Resolve to the default values specified in the var() calls
            // e.g. var(--primary, #hex) -> #hex
            $html = preg_replace_callback('/var\(--primary,\s*([^)]+)\)/', function ($matches) {
                return trim($matches[1]);
            }, $html);
            // If no default is provided, fallback to the standard blue
            $html = str_replace('var(--primary)', '#2563eb', $html);
        }

        return new HtmlString($this->render($html, $this->normalizeResume($data), $allowInjection));
    }

    public function containsResumePlaceholders(string $html): bool
    {
        return preg_match('/\{\{\s*(?:name|last_name|job_title|designation|email|mobile|location|contact|address|summary|skills|experience|education|projects|certifications|certificates|languages|additional_information|achievements|social_links|linkedin|portfolio|link|profile_image|profile_image_tag|photo)\s*\}\}|\[\[\s*(?:name|last_name|job_title|designation|email|mobile|location|contact|address|summary|skills|experience|education|projects|certifications|certificates|languages|additional_information|achievements|social_links|linkedin|portfolio|link|profile_image|profile_image_tag|photo)\s*\]\]/i', $html) === 1
            || preg_match('/\{\{#if\s+[a-z0-9_.]+\s*\}\}/i', $html) === 1
            || $this->shouldRenderWithBlade($html);
    }

    public function editableResumeTemplateHtml(): string
    {
        return <<<'HTML'
<div class="tpl-resume tpl-uploaded-editable" style="font-family: Inter, Arial, sans-serif; color:#172033; padding:42px; line-height:1.45;">
    <table style="width:100%; border-bottom:3px solid var(--primary, #2563eb); padding-bottom:16px; margin-bottom:22px; border-collapse: collapse;">
        <tr>
            <td style="vertical-align: top; text-align: left;">
                <h1 style="margin:0 0 6px; font-size:30px; letter-spacing:.04em; text-transform:uppercase; color:var(--primary, #2563eb); text-align: left;">{{name}}</h1>
                <p style="margin:0 0 4px; font-size:14px; color:#334155; text-align: left;">{{job_title}}</p>
                <p style="margin:0; font-size:12px; color:#475569; text-align: left;">{{email}} | {{mobile}} | {{location}}</p>
                <p style="margin:4px 0 0; font-size:12px; color:#475569; text-align: left;">{{social_links}}</p>
            </td>
            <td style="width: 100px; vertical-align: top; text-align: right;">{{profile_image_tag}}</td>
        </tr>
    </table>
    <section><h2>Professional Summary</h2><div style="margin-bottom:7px">{{summary}}</div></section>
    <section><h2>Skills</h2><div class="tpl-badges">{{skills}}</div></section>
    <section><h2>Experience</h2>{{experience}}</section>
    <section><h2>Projects</h2>{{projects}}</section>
    <section><h2>Education</h2>{{education}}</section>
    <section><h2>Certifications</h2>{{certifications}}</section>
    <section><h2>Achievements</h2>{{achievements}}</section>
    <section><h2>Languages</h2>{{languages}}</section>
    
</div>
HTML;
    }

    public function containsCoverLetterPlaceholders(string $html): bool
    {
        return preg_match('/\{\{\s*(?:name|email|mobile|location|company|company_name|job_role|skills|body)\s*\}\}|\[\[\s*(?:name|email|mobile|location|company|company_name|job_role|skills|body)\s*\]\]/i', $html) === 1
            || preg_match('/\{\{\s*\$coverLetter|\@foreach\s*\(\s*\$coverLetter|\@if\s*\(\s*\$coverLetter/i', $html) === 1;
    }

    public function editableCoverLetterTemplateHtml(): string
    {
        return <<<'HTML'
<div class="tpl-cover tpl-cover-clean" style="font-family: Inter, Arial, sans-serif; color:#0f172a; padding:42px; line-height:1.6;">
    <header style="margin-bottom: 24px;">
        <h1 style="margin:0 0 6px; font-size: 28px; color:#0f172a;">{{name}}</h1>
        <p style="margin:0; font-size:13px; color:#475569;">{{email}} | {{mobile}} | {{location}}</p>
    </header>

    <section style="margin: 14px 0 20px;">
        <p style="margin:0 0 4px;"><strong>Applying for:</strong> {{job_role}}</p>
        <p style="margin:0;"><strong>Company:</strong> {{company_name}}</p>
    </section>

    <main style="margin-top: 18px;">
        {{body}}
    </main>
</div>
HTML;
    }

    public function renderCoverLetter(Template $template, ?array $data = null): HtmlString
    {
        $data = $data ?: $this->coverLetterSampleData();
        $html = $template->html ?: '';

        if ($this->shouldRenderWithBlade($html)) {
            $accentColor = $this->resumeAccentColor($data);
            return new HtmlString($this->withScopedAccent($this->renderBlade($html, $this->bladeRenderDataForCoverLetter($data)), $accentColor));
        }

        if (! $this->containsCoverLetterPlaceholders($html)) {
            $html = $this->editableCoverLetterTemplateHtml();
        }

        $accentColor = $this->resumeAccentColor($data);
        if ($accentColor) {
            $html = str_replace(['var(--primary, #2563eb)', 'var(--primary)'], $accentColor, $html);
            $html = preg_replace('/var\(--primary[^)]*\)/', $accentColor, $html);
        } else {
            $html = preg_replace_callback('/var\(--primary,\s*([^)]+)\)/', fn($m) => trim($m[1]), $html);
            $html = str_replace('var(--primary)', '#2563eb', $html);
        }

        return new HtmlString($this->render($html, $this->normalizeCoverLetter($data)));
    }

    private function render(string $html, array $data, bool $allowInjection = true): string
    {
        $originalHtml = $html;
        $html = $this->renderHandlebarsIfBlocks($html, $data);

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $quotedKey = preg_quote($key, '/');
                $html = preg_replace_callback('/\{\{\s*'.$quotedKey.'\s*\}\}/', fn () => $value, $html);
                $html = preg_replace_callback('/\[\[\s*'.$quotedKey.'\s*\]\]/', fn () => $value, $html);
            }
        }
        
        // Auto-fix: If user has an image, replace common placeholders in the HTML
        $profileImage = Arr::get($data, 'profile_image_url');
        if ($profileImage) {
            $html = preg_replace('/src=["\']https?:\/\/(?:i\.pravatar\.cc|via\.placeholder\.com|placehold\.co|placehold\.it|avatar\.iran\.liara\.run|ui-avatars\.com)\/[^"\']*["\']/i', 'src="'.$profileImage.'"', $html);
            
            // Also try to update any img tag with id profile-pic or profile-img if it hasn't been updated yet
            if (!str_contains($html, $profileImage)) {
                $html = preg_replace('/(id=["\'](?:profile-pic|profile-img|cv-img|cv-profile-img|user-photo|user-avatar)["\'][^>]*src=["\'])([^"\']*)(["\'])/i', '$1'.$profileImage.'$3', $html);
            }

            // Auto-replace base64 images or blob urls (often generated by PDF to HTML conversions)
            if (!str_contains($html, $profileImage)) {
                $html = preg_replace('/src=["\'](data:image\/[^;]+;base64,[^"\']+|blob:[^"\']+)["\']/i', 'src="'.$profileImage.'"', $html);
            }
        }

        // Dynamic Section Visibility: If a section is empty, remove its header and token
        foreach (['projects', 'certifications', 'certificates', 'languages', 'additional_information', 'achievements', 'experience', 'education'] as $key) {
            $val = $data[$key] ?? '';
            if (empty($val) || $val === '<ul></ul>' || $val === '""' || $val === 'null') {
                // Regex to find a section header followed by the token.
                // We use a non-greedy match and limit the content between them to be safe.
                $re = '/<(h2|h3|h4|div)[^>]*>[^<]*?' . preg_quote($key, '/') . '[^<]*?<\/\1>\s*(?:<[^>]+>\s*)*?(\{\{\s*' . preg_quote($key, '/') . '\s*\}\}|\[\[' . preg_quote($key, '/') . '\]\])/i';
                $html = preg_replace($re, '', $html);
                
                // Also just remove the token if it's still there
                $html = str_replace(['{{'.$key.'}}', '[['.$key.']]'], '', $html);
            }
        }

        if ($allowInjection) {
            // Ensure visible optional sections are present when the selected template omits their tokens.
            // We also check if a section header with the title already exists to avoid duplicates.
            $this->ensureSectionVisible($html, $data, 'projects', 'Projects');
            $this->ensureSectionVisible($html, $data, 'certifications', 'Certifications');
            $this->ensureSectionVisible($html, $data, 'achievements', 'Achievements');
            $this->ensureSectionVisible($html, $data, 'languages', 'Languages');
            $this->ensureSectionVisible($html, $data, 'additional_information', 'Additional Information');
        }

        $html = preg_replace('/<section[^>]*>\s*<h[1-6][^>]*>\s*(Professional Summary|Summary)\s*<\/h[1-6]>\s*(?:<div[^>]*>\s*)?<\/section>/i', '', $html);
        $html = preg_replace('/<section[^>]*>\s*<h[1-6][^>]*>\s*(Experience|Education|Projects|Certifications|Certificates|Languages|Achievements|Additional Information)\s*<\/h[1-6]>\s*(?:<ul[^>]*>\s*<\/ul>|<div[^>]*>\s*<\/div>|<p[^>]*>\s*<\/p>|)\s*<\/section>/i', '', $html);
        $html = preg_replace('/<h[1-6][^>]*>\s*(Experience|Education|Projects|Certifications|Certificates|Languages|Achievements|Additional Information)\s*<\/h[1-6]>\s*(?:<ul[^>]*>\s*<\/ul>|<div[^>]*>\s*<\/div>|<p[^>]*>\s*<\/p>)/i', '', $html);

        $html = $this->withScopedAccent($html, $this->resumeAccentColor($data));

        return $html;
    }

    private function withScopedAccent(string $html, string $color): string
    {
        if (! preg_match('/^#[0-9a-f]{6}$/i', $color)) {
            return $html;
        }

        $scope = 'tpl-accent-'.substr(md5($color.'|'.$html), 0, 12);

        return $this->resumeAccentStyle($color, '.'.$scope).'<div class="'.$scope.'">'.$html.'</div>';
    }

    private function ensureSectionVisible(string &$html, array $data, string $key, string $title): void
    {
        $token = '{{'.$key.'}}';
        $altToken = '[['.$key.']]';
        if (str_contains($html, $token) || str_contains($html, $altToken)) {
            return;
        }

        // Check if the title already exists as a header or bold text (case-insensitive)
        if (preg_match('/<(h2|h3|h4|div|strong|b)[^>]*>[^<]*?' . preg_quote($title, '/') . '[^<]*?<\/\1>/i', $html)) {
            return;
        }

        if (empty($data[$key]) || $data[$key] === '<ul></ul>' || $data[$key] === '' || $data[$key] === '""') {
            return;
        }

        if (! str_contains($html, 'tpl-resume')) {
            return;
        }

        $section = '<h2>'.$title.'</h2>'.$data[$key];
        $pos = strrpos($html, '</div>');
        if ($pos !== false) {
            $html = substr($html, 0, $pos).$section.substr($html, $pos);
        } else {
            $html .= $section;
        }
    }

    private function resumeAccentStyle(string $color, string $scope = ''): string
    {
        if (! preg_match('/^#[0-9a-f]{6}$/i', $color)) {
            return '';
        }

        $primaryColor = $color;
        $prefix = $scope !== '' ? $scope.' ' : '';

        return '<style>
            '.$prefix.'.tpl-resume, '.$prefix.'.tpl-cover, '.$scope.' { --primary: '.$primaryColor.'; }
            '.$prefix.'.tpl-resume h2, '.$prefix.'.tpl-cover h2 {
                border-color: var(--primary) !important;
                color: var(--primary) !important;
            }
            '.$prefix.'.tpl-resume h1,
            '.$prefix.'.tpl-resume h3,
            '.$prefix.'.tpl-resume a,
            '.$prefix.'.tpl-cover h1,
            '.$prefix.'.tpl-cover h3,
            '.$prefix.'.tpl-cover a,
            '.$prefix.'.tpl-role-head strong {
                color: var(--primary) !important;
            }

            '.$prefix.'.tpl-badge {
                background: var(--primary) !important;
                border-color: var(--primary) !important;
                color: #fff !important;
            }
            '.$prefix.'.tpl-rule,
            '.$prefix.'.tpl-accentbox header > div,
            '.$prefix.'.tpl-two aside,
            '.$prefix.'.tpl-carded header,
            '.$prefix.'.tpl-band header,
            '.$prefix.'.tpl-resume > header[style*="background"],
            '.$prefix.'.tpl-resume h2[style*="background"],
            '.$prefix.'.tpl-cover > header[style*="background"],
            '.$prefix.'.tpl-cover-modern header,
            '.$prefix.'.tpl-cover-executive h1 {
                background: var(--primary) !important;
                color: #fff !important;
            }
            '.$prefix.'.tpl-cover-executive h1 { padding: 10px; }
            '.$prefix.'.tpl-rule *,
            '.$prefix.'.tpl-accentbox header > div *,
            '.$prefix.'.tpl-two aside *,
            '.$prefix.'.tpl-carded header *,
            '.$prefix.'.tpl-band header *,
            '.$prefix.'.tpl-resume > header[style*="background"] *,
            '.$prefix.'.tpl-resume h2[style*="background"],
            '.$prefix.'.tpl-cover > header[style*="background"] *,
            '.$prefix.'.tpl-cover-modern header * {
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
        $firstName = $this->text(Arr::get($data, 'name', ''));
        $lastName = $this->text(Arr::get($data, 'last_name', ''));
        $fullName = trim($firstName.' '.$lastName) ?: $firstName;
        $additionalInformation = Arr::get($data, 'additional_information', Arr::get($data, 'additionalInformation', []));

        $rawImage = Arr::get($data, 'profile_image', '');
        $profileImage = $rawImage;
        if (empty($profileImage) || $profileImage === 'null' || $profileImage === '""') {
            $profileImage = 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&h=150&fit=crop&crop=face';
        }

        return [
            'name' => e($fullName),
            'last_name' => e($lastName),
            'job_title' => e($this->text(Arr::get($data, 'job_title', ''))),
            'designation' => e($this->text(Arr::get($data, 'designation', ''))),
            'email' => e($this->text(Arr::get($data, 'email', ''))),
            'mobile' => e($this->text(Arr::get($data, 'mobile', Arr::get($data, 'contact', '')))),
            'location' => e($this->text(Arr::get($data, 'location', Arr::get($data, 'address', '')))),
            'summary' => preg_match('/<[a-z][\s\S]*>/i', Arr::get($data, 'summary', '')) ? $this->text(Arr::get($data, 'summary', '')) : $this->rich($this->text(Arr::get($data, 'summary', ''))),
            'linkedin' => e($this->text(Arr::get($data, 'linkedin', ''))),
            'portfolio' => e($this->text(Arr::get($data, 'portfolio', Arr::get($data, 'link', Arr::get($data, 'social_links.0', ''))))),
            'link' => e($this->text(Arr::get($data, 'link', Arr::get($data, 'portfolio', '')))),
            'skills' => $this->badges(Arr::get($data, 'skills', [])),
            'experience' => $this->experience(Arr::get($data, 'experience', [])),
            'education' => $this->educationList(Arr::get($data, 'education', [])),
            'projects' => $this->projectList(Arr::get($data, 'projects', [])),
            'social_links' => $this->inline(Arr::get($data, 'social_links', [])),
            'certifications' => $this->list(Arr::get($data, 'certifications', Arr::get($data, 'certificates', []))),
            'certificates' => $this->list(Arr::get($data, 'certifications', Arr::get($data, 'certificates', []))),
            'languages' => $this->languageList(Arr::get($data, 'languages', Arr::get($data, 'language', Arr::get($data, 'language_skills', Arr::get($data, 'language_proficiency', []))))),
            'additional_information' => $this->list($additionalInformation),
            'achievements' => $this->list(Arr::get($data, 'achievements', [])),
            'primary_color' => $this->text(Arr::get($data, 'primary_color', '')),
            'primary_color_customized' => filter_var(Arr::get($data, 'primary_color_customized', false), FILTER_VALIDATE_BOOLEAN),
            'profile_image' => $profileImage,
            'profile_image_url' => $profileImage,
            'profile_image_tag' => '<img src="'.$profileImage.'" class="tpl-profile-img" style="width:100%; height:100%; object-fit:cover;">',
            'photo' => $profileImage,
        ];
    }

    private function normalizeCoverLetter(array $data): array
    {
        $body = $this->formatCoverLetterBody($this->text(Arr::get($data, 'body', '')));

        return [
            'name' => e($this->text(Arr::get($data, 'name', ''))),
            'email' => e($this->text(Arr::get($data, 'email', ''))),
            'mobile' => e($this->text(Arr::get($data, 'mobile', ''))),
            'location' => e($this->text(Arr::get($data, 'location', ''))),
            'company' => e($this->text(Arr::get($data, 'company', Arr::get($data, 'company_name', '')))),
            'company_name' => e($this->text(Arr::get($data, 'company_name', Arr::get($data, 'company', '')))),
            'job_role' => e($this->text(Arr::get($data, 'job_role', ''))),
            'skills' => e($this->text(Arr::get($data, 'skills', ''))),
            'body' => $body,
            'primary_color' => $this->text(Arr::get($data, 'primary_color', '')),
            'primary_color_customized' => filter_var(Arr::get($data, 'primary_color_customized', false), FILTER_VALIDATE_BOOLEAN),
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
        $profileImage = $this->text(Arr::get($data, 'profile_image', ''));
        if (empty($profileImage) || $profileImage === 'null' || $profileImage === '""') {
            $profileImage = 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&h=150&fit=crop&crop=face';
        }

        $resume = [
            'type' => 'resume',
            'name' => trim($this->text(Arr::get($data, 'name', '')).' '.$this->text(Arr::get($data, 'last_name', ''))) ?: $this->text(Arr::get($data, 'name', '')),
            'last_name' => $this->text(Arr::get($data, 'last_name', '')),
            'job_title' => $this->text(Arr::get($data, 'job_title', '')),
            'designation' => $this->text(Arr::get($data, 'designation', '')),
            'email' => $this->text(Arr::get($data, 'email', '')),
            'mobile' => $this->text(Arr::get($data, 'mobile', Arr::get($data, 'contact', ''))),
            'location' => $this->text(Arr::get($data, 'location', Arr::get($data, 'address', ''))),
            'summary' => $this->text(Arr::get($data, 'summary', '')),
            'linkedin' => $this->text(Arr::get($data, 'linkedin', '')),
            'portfolio' => $this->text(Arr::get($data, 'portfolio', Arr::get($data, 'link', Arr::get($data, 'social_links.0', '')))),
            'github' => $this->text(Arr::get($data, 'github', '')),
            'tech_stack' => $this->text(Arr::get($data, 'tech_stack', '')),
            'skills' => $this->normalizeBladeArray(Arr::get($data, 'skills', [])),
            'experience' => $this->normalizeBladeArray(Arr::get($data, 'experience', [])),
            'education' => $this->normalizeBladeArray(Arr::get($data, 'education', [])),
            'projects' => $this->normalizeBladeArray(Arr::get($data, 'projects', [])),
            'certifications' => $this->normalizeBladeArray(Arr::get($data, 'certifications', Arr::get($data, 'certificates', []))),
            'certificates' => $this->normalizeBladeArray(Arr::get($data, 'certifications', Arr::get($data, 'certificates', []))),
            'languages' => $this->normalizeBladeArray(Arr::get($data, 'languages', Arr::get($data, 'language', Arr::get($data, 'language_skills', Arr::get($data, 'language_proficiency', []))))),
            'additional_information' => $this->normalizeBladeArray(Arr::get($data, 'additional_information', Arr::get($data, 'additionalInformation', []))),
            'achievements' => $this->normalizeBladeArray(Arr::get($data, 'achievements', [])),
            'social_links' => $this->normalizeBladeArray(Arr::get($data, 'social_links', [])),
            'link' => $this->text(Arr::get($data, 'link', '')),
            'contact' => $this->text(Arr::get($data, 'contact', '')),
            'address' => $this->text(Arr::get($data, 'address', '')),
            'primary_color' => $this->text(Arr::get($data, 'primary_color', '')),
            'primary_color_customized' => filter_var(Arr::get($data, 'primary_color_customized', false), FILTER_VALIDATE_BOOLEAN),
            'profile_image' => $profileImage,
            'profile_image_url' => $profileImage,
            'photo' => $profileImage,
        ];

        return array_merge(['resume' => $resume], $resume);
    }

    private function renderHandlebarsIfBlocks(string $html, array $data): string
    {
        $pattern = '/\{\{#if\s+([a-z0-9_.]+)\s*\}\}(?:(?!\{\{#if).)*?(?:\{\{else\}\}(?:(?!\{\{#if).)*?)?\{\{\/if\}\}/is';

        // Handle multiple blocks safely
        while (preg_match($pattern, $html)) {
            $html = preg_replace_callback(
                '/\{\{#if\s+([a-z0-9_.]+)\s*\}\}(.*?)(?:\{\{else\}\}(.*?))?\{\{\/if\}\}/is',
                function ($matches) use ($data) {
                    $key = trim($matches[1] ?? '');
                    $truthyPart = $matches[2] ?? '';
                    $elsePart = $matches[3] ?? '';
                    $value = Arr::get($data, $key);
                    $isTruthy = ! ($value === null || $value === '' || $value === false || $value === 'null');

                    return $isTruthy ? $truthyPart : $elsePart;
                },
                $html
            );
        }

        return $html;
    }

    private function bladeRenderDataForCoverLetter(array $data): array
    {
        $body = $this->formatCoverLetterBody($this->text(Arr::get($data, 'body', '')));

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
            'body' => $body,
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

        return collect($items)
            ->map(fn ($item) => $this->text($item))
            ->filter()
            ->values()
            ->map(fn ($item) => '<span class="tpl-badge">'.$this->rich($item).'</span>')
            ->join('');
    }

    private function list(array|string|null $items): string
    {
        $items ??= [];
        $items = is_array($items) ? $items : explode("\n", $items);
        $normalized = collect($items)->map(function ($item) {
            if (is_array($item)) {
                if (array_key_exists('description', $item)) {
                    $name = $this->text($item['name'] ?? $item['title'] ?? $item['label'] ?? '');
                    $description = $this->text($item['description']);

                    return trim($name.($description !== '' ? ' - '.$description : ''));
                }

                if (array_key_exists('points', $item)) {
                    return $this->text($item['points']);
                }

                return $this->text($item);
            }

            return $this->text($item);
        })->filter()->values();

        if ($normalized->isEmpty()) {
            return '';
        }

        $first = $normalized->first();
        if ($normalized->count() === 1 && is_string($first) && preg_match('/<[a-z][\s\S]*>/i', $first)) {
            return $first;
        }

        return '<ul>'.$normalized->map(fn ($item) => '<li>'.$this->rich($item).'</li>')->join('').'</ul>';
    }

    private function educationList(array|string|null $items): string
    {
        $items ??= [];
        $items = is_array($items) ? $items : explode("\n", $items);

        $normalized = collect($items)->map(function ($item) {
            if (! is_array($item)) {
                $text = $this->text($item);

                return $text === '' ? null : '<li>'.$this->rich($text).'</li>';
            }

            $degree = $this->text($item['degree'] ?? $item['course'] ?? '');
            $stream = $this->text($item['stream'] ?? $item['field'] ?? $item['specialization'] ?? '');
            $institution = $this->text($item['institution'] ?? $item['school'] ?? $item['university'] ?? $item['college'] ?? '');
            $year = $this->text($item['year'] ?? $item['duration'] ?? $item['period'] ?? '');
            $cgpa = $this->text($item['cgpa'] ?? '');

            $title = collect([$degree, $stream])->filter()->join(' - ');
            $meta = collect([$institution, $cgpa !== '' ? 'CGPA: '.$cgpa : '', $year])->filter()->join(', ');

            if ($title === '' && $meta === '') {
                return null;
            }

            return '<li>'
                .($title !== '' ? '<strong>'.e($title).'</strong>' : '')
                .($meta !== '' ? '<span class="tpl-description">'.e($meta).'</span>' : '')
                .'</li>';
        })->filter()->values();

        return $normalized->isEmpty() ? '' : '<ul>'.$normalized->join('').'</ul>';
    }

    private function languageList(array|string|null $items): string
    {
        $items ??= [];
        $items = is_array($items) ? $items : explode(',', $items);

        $normalized = collect($items)->map(function ($item) {
            if (is_array($item)) {
                $name = $this->text($item['name'] ?? $item['language'] ?? '');
                $level = $this->text($item['level'] ?? $item['proficiency'] ?? '');

                return trim($name.($level !== '' ? ' - '.$level : ''));
            }

            return $this->text($item);
        })->filter()->values();

        return $normalized->isEmpty()
            ? ''
            : '<ul>'.$normalized->map(fn ($item) => '<li>'.$this->rich($item).'</li>')->join('').'</ul>';
    }

    private function rich(string $v): string
    {
        $v = e($v);
        // Convert Markdown-style bold and underline to HTML (matching JS logic)
        $v = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $v);
        $v = preg_replace('/__(.*?)__/', '<u>$1</u>', $v);
        
        // Also allow specific common tags if they were escaped
        $v = preg_replace('/&lt;(\/?)(strong|b|em|i|u)&gt;/i', '<$1$2>', $v);
        
        return $v;
    }

    private function projectList(array|string|null $items): string
    {
        $items ??= [];
        $items = is_array($items) ? $items : explode("\n", $items);

        $rendered = collect($items)->map(function ($item) {
            if (! is_array($item)) {
                $name = $this->text($item);

                return $name === '' ? '' : '<li>'.e($name).'</li>';
            }

            $name = $this->text($item['name'] ?? '');
            $tech = $this->text($item['tech_stack'] ?? $item['tech'] ?? '');
            $link = $this->text($item['link'] ?? $item['url'] ?? '');
            $description = '';
            if (array_key_exists('description', $item)) {
                $description = $this->text($item['description']);
            } elseif (array_key_exists('highlights', $item)) {
                $description = $this->text($item['highlights']);
            } elseif (array_key_exists('points', $item)) {
                $description = $this->text($item['points']);
            }

            if ($name === '' && $tech === '' && $link === '' && $description === '') {
                return '';
            }

            $title = $name !== ''
                ? ($link !== '' ? '<strong><a href="'.e($link).'" target="_blank" rel="noopener">'.e($name).'</a></strong>' : '<strong>'.e($name).'</strong>')
                : '';
            $metaParts = array_values(array_filter([$tech, $link]));
            $meta = ! empty($metaParts) ? '<span class="tpl-description">'.e(implode(' | ', $metaParts)).'</span>' : '';
            $body = $description !== ''
                ? (preg_match('/<[a-z][\s\S]*>/i', $description) ? '<span class="tpl-description">'.$description.'</span>' : '<span class="tpl-description">'.$this->rich($description).'</span>')
                : '';

            return '<li>'.$title.$meta.$body.'</li>';
        })->filter()->join('');

        return $rendered === '' ? '' : '<ul>'.$rendered.'</ul>';
    }

    private function inline(array|string|null $items): string
    {
        $items ??= [];
        $items = is_array($items) ? $items : explode(',', $items);

        return collect($items)->map(fn ($item) => $this->text($item))->filter()->map(fn ($item) => $this->rich($item))->join(' | ');
    }

    private function experience(array|string|null $items): string
    {
        $items ??= [];
        if (! is_array($items)) {
            $text = $this->text($items);

            return $text === '' ? '' : '<p>'.e($text).'</p>';
        }

        return collect($items)->map(function ($item) {
            if (! is_array($item)) {
                $text = $this->text($item);

                return $text === '' ? '' : '<div class="tpl-role"><p>'.e($text).'</p></div>';
            }

            $role = $this->text($item['role'] ?? $item['title'] ?? $item['position'] ?? '');
            $period = $this->text($item['period'] ?? $item['duration'] ?? $item['dates'] ?? '');
            $company = $this->text($item['company'] ?? $item['organization'] ?? '');
            $points = $this->list($item['points'] ?? $item['highlights'] ?? []);

            if ($role === '' && $period === '' && $company === '' && $points === '') {
                return '';
            }

            return '<div class="tpl-role"><div class="tpl-role-head"><strong>'.e($role).'</strong><span>'.e($period).'</span></div><p>'.e($company).'</p>'.$points.'</div>';
        })->filter()->join('');
    }

    private function text(mixed $value): string
    {
        if (is_array($value)) {
            return collect($value)
                ->flatten()
                ->map(fn ($part) => is_scalar($part) ? $this->cleanTextMarkers((string) $part) : '')
                ->filter()
                ->join(' - ');
        }

        return $this->cleanTextMarkers((string) ($value ?? ''));
    }

    private function cleanTextMarkers(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        $lines = preg_split('/\R/', $value) ?: [];
        $lines = array_values(array_filter(array_map('trim', $lines), function (string $line): bool {
            return ! preg_match('/^(?:[-*_•·]+|\x{2022})$/u', $line);
        }));

        return trim(implode("\n", $lines));
    }

    private function formatCoverLetterBody(string $body): string
    {
        $body = trim($body);
        if ($body === '') {
            return '';
        }

        $body = preg_replace('/(Sincerely,)(?!\s*(?:\R|<br\b|<\/p>))\s*/i', "$1\n", $body) ?? $body;
        $body = preg_replace('/(Sincerely,)(?:&nbsp;|\x{00a0})+/iu', "$1\n", $body) ?? $body;

        // If body already contains HTML (from rich editor), keep it.
        if (preg_match('/<[a-z][\s\S]*>/i', $body)) {
            return preg_replace('/(Sincerely,)\s*\R\s*/i', '$1<br>', $body) ?? $body;
        }

        // Convert plain text into paragraph blocks, preserving blank-line spacing.
        $paragraphs = preg_split('/\R{2,}/', $body) ?: [];
        $paragraphs = array_values(array_filter(array_map('trim', $paragraphs), fn ($p) => $p !== ''));

        if (empty($paragraphs)) {
            return nl2br(e($body));
        }

        return implode('', array_map(
            fn ($p) => '<p>'.nl2br(e($p)).'</p>',
            $paragraphs
        ));
    }
}
