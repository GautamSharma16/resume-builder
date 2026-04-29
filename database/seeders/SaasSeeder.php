<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Plan;
use App\Models\RolePermission;
use App\Models\Template;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SaasSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            ['Basic', 'basic', 29900, 1, 14, false],
            ['Silver', 'silver', 59900, 3, 45, true],
            ['Gold', 'gold', 149900, null, 365, true],
        ];

        foreach ($plans as [$name, $slug, $price, $downloadsAllowed, $durationDays, $aiEnabled]) {
            Plan::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'price_paise' => $price,
                    'downloads_allowed' => $downloadsAllowed,
                    'duration_days' => $durationDays,
                    'resume_limit' => $downloadsAllowed,
                    'cover_letter_limit' => $downloadsAllowed,
                    'ai_enabled' => $aiEnabled,
                    'is_active' => true,
                ]
            );
        }

        $resumeCategories = [
            'ats' => 'ATS Resume',
            'fresher' => 'Fresher Resume',
            'experienced' => 'Resume for Experienced',
            'word' => 'MS Word Resume',
        ];

        foreach ($resumeCategories as $category => $label) {
            for ($i = 1; $i <= 10; $i++) {
                Template::updateOrCreate(
                    ['slug' => Str::slug($label.' '.$i)],
                    [
                        'type' => 'resume',
                        'name' => $label.' '.$i,
                        'category' => $category,
                        'html' => $this->resumeTemplateHtml($category, $i),
                        'is_active' => true,
                    ]
                );
            }
        }

        foreach ([
            ['Clean Cover Letter', 'professional'],
            ['Modern Cover Letter', 'modern'],
            ['Executive Cover Letter', 'executive'],
            ['Fresher Cover Letter', 'fresher'],
            ['Career Change Cover Letter', 'career-change'],
            ['Minimal Cover Letter', 'minimal'],
        ] as [$name, $category]) {
            Template::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'type' => 'cover_letter',
                    'name' => $name,
                    'category' => $category,
                    'html' => $this->coverLetterTemplateHtml($category),
                    'is_active' => true,
                ]
            );
        }

        $permissions = [
            'super_admin' => ['templates.manage', 'articles.manage', 'users.manage', 'pricing.manage'],
            'developer' => ['templates.manage', 'pricing.manage'],
            'seo' => ['articles.manage'],
            'article_writer' => ['articles.manage'],
            'admin' => ['templates.manage', 'articles.manage', 'users.manage', 'pricing.manage'],
        ];

        foreach ($permissions as $role => $items) {
            foreach ($items as $permission) {
                RolePermission::updateOrCreate(compact('role', 'permission'));
            }
        }

        $author = User::where('role', 'article_writer')->first() ?? User::first();

        foreach ([
            ['how-to-answer-tell-me-about-yourself', 'Preparation', 'How to Answer Tell Me About Yourself', 'A practical structure for a confident interview opening.', 'Start with your current role, connect your most relevant achievements, and close with why the target role fits your next step.'],
            ['fresher-interview-checklist', 'Freshers', 'Fresher Interview Checklist', 'Simple preparation steps before your first interview.', 'Revise your projects, prepare a short introduction, practice common HR questions, and keep examples ready for teamwork, learning ability, and problem solving.'],
            ['experienced-salary-negotiation', 'Experienced', 'Salary Negotiation for Experienced Candidates', 'How to discuss compensation with confidence.', 'Anchor your expected salary with market research, current responsibilities, measurable achievements, and the scope of the role you are interviewing for.'],
            ['final-round-preparation', 'Preparation', 'Final Round Preparation', 'What to polish before the last conversation.', 'Review the job description, prepare business-impact examples, ask thoughtful questions, and be ready to explain why this company and role fit your next step.'],
        ] as [$slug, $category, $title, $excerpt, $body]) {
            Article::updateOrCreate(
                ['slug' => $slug],
                [
                    'author_id' => $author?->id,
                    'category' => $category,
                    'title' => $title,
                    'excerpt' => $excerpt,
                    'body' => $body,
                    'is_published' => true,
                    'published_at' => now(),
                ]
            );
        }
    }

    private function resumeTemplateHtml(string $category, int $index): string
    {
        $palettes = [
            ['#111827', '#0f766e', '#f8fafc'],
            ['#172554', '#2563eb', '#eff6ff'],
            ['#27272a', '#ca8a04', '#fefce8'],
            ['#312e81', '#7c3aed', '#f5f3ff'],
            ['#064e3b', '#059669', '#ecfdf5'],
            ['#701a75', '#c026d3', '#fdf4ff'],
            ['#7f1d1d', '#dc2626', '#fef2f2'],
            ['#1f2937', '#64748b', '#f1f5f9'],
            ['#164e63', '#0891b2', '#ecfeff'],
            ['#3f3f46', '#18181b', '#fafafa'],
        ];
        [$dark, $accent, $soft] = $palettes[$index - 1];
        $font = $category === 'word' ? 'Georgia, Times New Roman, serif' : 'Inter, Arial, sans-serif';
        $dense = $category === 'ats';
        $fresh = $category === 'fresher';
        $experienced = $category === 'experienced';

        $variants = [
            1 => "<div class=\"tpl-resume\" style=\"font-family:$font;color:$dark\"><header class=\"tpl-topline\" style=\"border-top:8px solid $accent\"><h1>{{name}}</h1><p>{{email}} | {{mobile}} | {{location}}</p><p>{{social_links}}</p></header><section><h2>Summary</h2><p>{{summary}}</p></section><section><h2>Skills</h2><div class=\"tpl-badges\">{{skills}}</div></section><section><h2>Experience</h2>{{experience}}</section><section><h2>Projects</h2>{{projects}}</section><section><h2>Education</h2>{{education}}</section></div>",
            2 => "<div class=\"tpl-resume tpl-two\" style=\"font-family:$font;color:$dark\"><aside style=\"background:$dark;color:white\"><h1>{{name}}</h1><p>{{email}}</p><p>{{mobile}}</p><p>{{location}}</p><h2>Skills</h2><div class=\"tpl-badges\">{{skills}}</div><h2>Links</h2><p>{{social_links}}</p></aside><main><section><h2>Profile</h2><p>{{summary}}</p></section><section><h2>Experience</h2>{{experience}}</section><section><h2>Projects</h2>{{projects}}</section><section><h2>Education</h2>{{education}}</section></main></div>",
            3 => "<div class=\"tpl-resume\" style=\"font-family:$font;color:$dark\"><header class=\"tpl-centered\"><h1>{{name}}</h1><p>{{email}} | {{mobile}} | {{location}} | {{social_links}}</p></header><div class=\"tpl-rule\" style=\"background:$accent\"></div><section><h2>Professional Summary</h2><p>{{summary}}</p></section><div class=\"tpl-cols\"><section><h2>Skills</h2><div class=\"tpl-badges\">{{skills}}</div><h2>Education</h2>{{education}}</section><section><h2>Experience</h2>{{experience}}<h2>Projects</h2>{{projects}}</section></div></div>",
            4 => "<div class=\"tpl-resume tpl-carded\" style=\"font-family:$font;color:$dark;background:$soft\"><header style=\"background:$dark;color:white\"><h1>{{name}}</h1><p>{{email}} | {{mobile}} | {{location}}</p><p>{{social_links}}</p></header><section><h2>Summary</h2><p>{{summary}}</p></section><section><h2>Core Skills</h2><div class=\"tpl-badges\">{{skills}}</div></section><section><h2>Work Experience</h2>{{experience}}</section><section><h2>Selected Projects</h2>{{projects}}</section><section><h2>Education</h2>{{education}}</section></div>",
            5 => "<div class=\"tpl-resume tpl-leftline\" style=\"font-family:$font;color:$dark;border-left:10px solid $accent\"><header><h1>{{name}}</h1><p>{{email}} | {{mobile}} | {{location}}</p></header><section><h2>About</h2><p>{{summary}}</p></section><section><h2>Technical Skills</h2><div class=\"tpl-badges\">{{skills}}</div></section><section><h2>Career History</h2>{{experience}}</section><section><h2>Projects</h2>{{projects}}</section><section><h2>Education</h2>{{education}}</section><footer>{{social_links}}</footer></div>",
            6 => "<div class=\"tpl-resume tpl-band\" style=\"font-family:$font;color:$dark\"><header style=\"background:$accent;color:white\"><h1>{{name}}</h1><p>{{email}} | {{mobile}} | {{location}}</p></header><section><h2>Summary</h2><p>{{summary}}</p></section><section class=\"tpl-panel\"><h2>Skills</h2><div class=\"tpl-badges\">{{skills}}</div></section><section><h2>Experience</h2>{{experience}}</section><section><h2>Projects</h2>{{projects}}</section><section><h2>Education</h2>{{education}}</section><p class=\"tpl-links\">{{social_links}}</p></div>",
            7 => "<div class=\"tpl-resume tpl-grid\" style=\"font-family:$font;color:$dark\"><header><div><h1>{{name}}</h1><p>Senior Developer</p></div><div><p>{{email}}</p><p>{{mobile}}</p><p>{{location}}</p></div></header><section class=\"span\"><h2>Summary</h2><p>{{summary}}</p></section><section><h2>Skills</h2><div class=\"tpl-badges\">{{skills}}</div></section><section><h2>Education</h2>{{education}}</section><section class=\"span\"><h2>Experience</h2>{{experience}}</section><section class=\"span\"><h2>Projects</h2>{{projects}}</section><footer>{{social_links}}</footer></div>",
            8 => "<div class=\"tpl-resume tpl-minimal\" style=\"font-family:$font;color:$dark\"><header><h1>{{name}}</h1><p>{{email}} / {{mobile}} / {{location}}</p><p>{{social_links}}</p></header><section><h2>SUMMARY</h2><p>{{summary}}</p></section><section><h2>SKILLS</h2><div class=\"tpl-badges\">{{skills}}</div></section><section><h2>EXPERIENCE</h2>{{experience}}</section><section><h2>PROJECTS</h2>{{projects}}</section><section><h2>EDUCATION</h2>{{education}}</section></div>",
            9 => "<div class=\"tpl-resume tpl-accentbox\" style=\"font-family:$font;color:$dark\"><header><div style=\"background:$accent\"></div><h1>{{name}}</h1><p>{{email}} | {{mobile}} | {{location}}</p><p>{{social_links}}</p></header><section><h2>Summary</h2><p>{{summary}}</p></section><div class=\"tpl-cols\"><section><h2>Skills</h2><div class=\"tpl-badges\">{{skills}}</div><h2>Projects</h2>{{projects}}</section><section><h2>Experience</h2>{{experience}}<h2>Education</h2>{{education}}</section></div></div>",
            10 => "<div class=\"tpl-resume tpl-executive\" style=\"font-family:$font;color:$dark\"><header><h1>{{name}}</h1><p>{{email}} | {{mobile}} | {{location}} | {{social_links}}</p></header><section class=\"tpl-highlight\" style=\"border-color:$accent\"><h2>Professional Profile</h2><p>{{summary}}</p></section><section><h2>Skills</h2><div class=\"tpl-badges\">{{skills}}</div></section><section><h2>Experience</h2>{{experience}}</section><section><h2>Project Portfolio</h2>{{projects}}</section><section><h2>Education</h2>{{education}}</section></div>",
        ];

        $html = $variants[$index];

        if ($dense) {
            $html = str_replace('tpl-resume', 'tpl-resume tpl-dense', $html);
        }

        if ($fresh) {
            $html = str_replace('<section><h2>Experience</h2>{{experience}}</section>', '<section><h2>Projects & Internships</h2>{{projects}}</section><section><h2>Academic Experience</h2>{{experience}}</section>', $html);
        }

        if ($experienced) {
            $html = str_replace('<section><h2>Summary</h2>', '<section><h2>Leadership Summary</h2>', $html);
        }

        return $html;
    }

    private function coverLetterTemplateHtml(string $category): string
    {
        return match ($category) {
            'modern' => '<div class="tpl-cover tpl-cover-modern"><header><h1>{{name}}</h1><p>{{email}} | {{mobile}} | {{location}}</p></header><aside>{{job_role}} at {{company}}</aside><main>{{body}}</main></div>',
            'executive' => '<div class="tpl-cover tpl-cover-executive"><header><p>{{email}} | {{mobile}}</p><h1>{{name}}</h1><h2>{{job_role}}</h2></header><main>{{body}}</main><footer>{{location}}</footer></div>',
            'fresher' => '<div class="tpl-cover tpl-cover-fresher"><header><h1>{{name}}</h1><p>{{email}} | {{mobile}}</p></header><section><h2>Application for {{job_role}}</h2><p class="tpl-company">{{company}}</p>{{body}}</section></div>',
            'career-change' => '<div class="tpl-cover tpl-cover-switch"><header><h1>{{name}}</h1><p>{{location}}</p></header><main><p class="tpl-kicker">{{job_role}} | {{company}}</p>{{body}}</main><footer>{{email}} | {{mobile}}</footer></div>',
            'minimal' => '<div class="tpl-cover tpl-cover-minimal"><header><h1>{{name}}</h1><p>{{email}} | {{mobile}} | {{location}}</p></header><main>{{body}}</main></div>',
            default => '<div class="tpl-cover tpl-cover-clean"><header><h1>{{name}}</h1><p>{{email}} | {{mobile}} | {{location}}</p></header><main><p class="tpl-kicker">{{company}} - {{job_role}}</p>{{body}}</main></div>',
        };
    }
}
