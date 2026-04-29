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
            ['Basic', 'basic', 4900, 1, 3, false],
            ['Silver', 'silver', 14900, 3, 10, true],
            ['Gold', 'gold', 29900, null, null, true],
        ];

        foreach ($plans as [$name, $slug, $price, $resumeLimit, $coverLetterLimit, $aiEnabled]) {
            Plan::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'price_paise' => $price,
                    'resume_limit' => $resumeLimit,
                    'cover_letter_limit' => $coverLetterLimit,
                    'ai_enabled' => $aiEnabled,
                    'is_active' => true,
                ]
            );
        }

        Template::updateOrCreate(
            ['slug' => 'classic-ats-resume'],
            [
                'type' => 'resume',
                'name' => 'Classic ATS Resume',
                'category' => 'professional',
                'html' => '<section><h1>{{name}}</h1><p>{{summary}}</p><h2>Skills</h2><p>{{skills}}</p><h2>Experience</h2>{{experience}}<h2>Education</h2>{{education}}</section>',
                'is_active' => true,
            ]
        );

        Template::updateOrCreate(
            ['slug' => 'clean-cover-letter'],
            [
                'type' => 'cover_letter',
                'name' => 'Clean Cover Letter',
                'category' => 'professional',
                'html' => '<section><h1>{{name}}</h1><p>{{body}}</p></section>',
                'is_active' => true,
            ]
        );

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

        Article::updateOrCreate(
            ['slug' => 'how-to-answer-tell-me-about-yourself'],
            [
                'author_id' => $author?->id,
                'title' => 'How to Answer Tell Me About Yourself',
                'excerpt' => 'A practical structure for a confident interview opening.',
                'body' => 'Start with your current role, connect your most relevant achievements, and close with why the target role fits your next step.',
                'is_published' => true,
                'published_at' => now(),
            ]
        );
    }
}
