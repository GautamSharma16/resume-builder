<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            // ATS Templates
            ['name' => 'ATS Classic', 'category' => 'ats', 'type' => 'resume', 'html' => $this->atsClassic()],
            ['name' => 'ATS Modern', 'category' => 'ats', 'type' => 'resume', 'html' => $this->atsModern(), 'has_image' => true],
            ['name' => 'ATS Minimal', 'category' => 'ats', 'type' => 'resume', 'html' => $this->atsMinimal()],
            ['name' => 'ATS Professional', 'category' => 'ats', 'type' => 'resume', 'html' => $this->atsProfessional()],
            ['name' => 'ATS Clean', 'category' => 'ats', 'type' => 'resume', 'html' => $this->atsClean()],

            // Fresher Templates
            ['name' => 'Fresher Starter', 'category' => 'fresher', 'type' => 'resume', 'html' => $this->fresherStarter()],
            ['name' => 'Fresher Simple', 'category' => 'fresher', 'type' => 'resume', 'html' => $this->fresherSimple()],
            ['name' => 'Fresher Bright', 'category' => 'fresher', 'type' => 'resume', 'html' => $this->fresherBright(), 'has_image' => true],
            ['name' => 'Fresher Clean', 'category' => 'fresher', 'type' => 'resume', 'html' => $this->fresherClean()],
            ['name' => 'Fresher Bold', 'category' => 'fresher', 'type' => 'resume', 'html' => $this->fresherBold()],

            // Experienced Templates
            ['name' => 'Executive', 'category' => 'experienced', 'type' => 'resume', 'html' => $this->executive(), 'has_image' => true],
            ['name' => 'Senior Pro', 'category' => 'experienced', 'type' => 'resume', 'html' => $this->seniorPro(), 'has_image' => true],
            ['name' => 'Leadership', 'category' => 'experienced', 'type' => 'resume', 'html' => $this->leadership(), 'has_image' => true],
            ['name' => 'Advanced', 'category' => 'experienced', 'type' => 'resume', 'html' => $this->advanced()],
            ['name' => 'Master', 'category' => 'experienced', 'type' => 'resume', 'html' => $this->master()],

            // Word Templates
            ['name' => 'Word Blue', 'category' => 'word', 'type' => 'resume', 'html' => $this->wordBlue()],
            ['name' => 'Word Green', 'category' => 'word', 'type' => 'resume', 'html' => $this->wordGreen()],
            ['name' => 'Word Red', 'category' => 'word', 'type' => 'resume', 'html' => $this->wordRed()],
            ['name' => 'Word Gray', 'category' => 'word', 'type' => 'resume', 'html' => $this->wordGray()],
            ['name' => 'Word Purple', 'category' => 'word', 'type' => 'resume', 'html' => $this->wordPurple()],

            // Cover Letter Templates
            ['name' => 'Cover Letter Classic', 'category' => 'professional', 'type' => 'cover_letter', 'html' => $this->coverLetterClassic()],
            ['name' => 'Cover Letter Modern', 'category' => 'professional', 'type' => 'cover_letter', 'html' => $this->coverLetterModern()],
            ['name' => 'Cover Letter Clean', 'category' => 'professional', 'type' => 'cover_letter', 'html' => $this->coverLetterClean()],
        ];

        foreach ($templates as $template) {
            Template::updateOrCreate(
                ['name' => $template['name']],
                [...$template, 'is_active' => true, 'slug' => str($template['name'])->slug()]
            );
        }
    }

    private function atsClassic(): string
    {
        return <<<'HTML'
<div class="tpl-resume">
    <h1>{{name}}</h1>
    <p>{{email}} | {{mobile}} | {{location}}</p>
    <h2>Professional Summary</h2>
    <p>{{summary}}</p>
    <h2>Experience</h2>
    {{experience}}
    <h2>Skills</h2>
    {{skills}}
    <h2>Education</h2>
    {{education}}
</div>
HTML;
    }

    private function atsModern(): string
    {
        return <<<'HTML'
<div class="tpl-resume">
    <div style="display: flex; gap: 20px; align-items: center; border-bottom: 2px solid var(--primary, #000); padding-bottom: 8px; margin-bottom: 12px;">
        <div style="width: 60px; height: 60px; border-radius: 50%; overflow: hidden; flex-shrink: 0; background: #eee;">{{profile_image_tag}}</div>
        <div style="flex: 1;">
            <h1 style="margin: 0; font-size: 28px; color: var(--primary, #000);">{{name}}</h1>
            <p style="margin: 4px 0; font-size: 11px;">{{email}} • {{mobile}} • {{location}}</p>
        </div>
    </div>
    <h2>Summary</h2>
    <p>{{summary}}</p>
    <h2>Experience</h2>
    {{experience}}
    <h2>Skills</h2>
    {{skills}}
    <h2>Education</h2>
    {{education}}
</div>
HTML;
    }

    private function atsMinimal(): string
    {
        return <<<'HTML'
<div class="tpl-resume">
    <h1 style="font-size: 24px; margin: 0;">{{name}}</h1>
    <p style="margin: 2px 0; font-size: 10px;">{{email}} | {{mobile}}</p>
    <h2 style="margin-top: 10px;">Summary</h2>
    <p>{{summary}}</p>
    <h2>Experience</h2>
    {{experience}}
    <h2>Skills</h2>
    {{skills}}
    <h2>Education</h2>
    {{education}}
</div>
HTML;
    }

    private function atsProfessional(): string
    {
        return <<<'HTML'
<div class="tpl-resume" style="font-family: 'Courier New', monospace;">
    <div style="margin-bottom: 14px;">
        <h1 style="margin: 0 0 4px; font-size: 26px; letter-spacing: 1px;">{{name}}</h1>
        <p style="margin: 0; font-size: 11px; letter-spacing: 0.5px;">{{location}} • {{email}} • {{mobile}}</p>
    </div>
    <h2 style="margin: 12px 0 8px;">PROFESSIONAL SUMMARY</h2>
    <p>{{summary}}</p>
    <h2 style="margin: 12px 0 8px;">PROFESSIONAL EXPERIENCE</h2>
    {{experience}}
    <h2 style="margin: 12px 0 8px;">CORE COMPETENCIES</h2>
    {{skills}}
    <h2 style="margin: 12px 0 8px;">EDUCATION</h2>
    {{education}}
</div>
HTML;
    }

    private function atsClean(): string
    {
        return <<<'HTML'
<div class="tpl-resume">
    <header style="text-align: left; margin-bottom: 10px;">
        <h1 style="margin: 0; font-size: 22px;">{{name}}</h1>
        <p style="margin: 2px 0; color: #555; font-size: 11px;">{{email}} · {{mobile}} · {{location}}</p>
    </header>
    <h2>Profile</h2>
    <p>{{summary}}</p>
    <h2>Work Experience</h2>
    {{experience}}
    <h2>Technical Skills</h2>
    {{skills}}
    <h2>Academic Background</h2>
    {{education}}
</div>
HTML;
    }

    private function fresherStarter(): string
    {
        return <<<'HTML'
<div class="tpl-resume">
    <div style="text-align: center; margin-bottom: 12px; padding-bottom: 10px; border-bottom: 2px solid var(--primary, #4CAF50);">
        <h1 style="margin: 0; color: var(--primary, #2E7D32); font-size: 26px;">{{name}}</h1>
        <p style="margin: 4px 0; font-size: 11px;">{{email}} | {{mobile}} | {{location}}</p>
    </div>
    <h2 style="color: var(--primary, #2E7D32);">About Me</h2>
    <p>{{summary}}</p>
    <h2 style="color: #2E7D32;">Experience</h2>
    {{experience}}
    <h2 style="color: #2E7D32;">Skills</h2>
    {{skills}}
    <h2 style="color: #2E7D32;">Education</h2>
    {{education}}
</div>
HTML;
    }

    private function fresherSimple(): string
    {
        return <<<'HTML'
<div class="tpl-resume" style="background: #f9f9f9; padding: 35px;">
    <h1 style="margin: 0 0 8px; color: #1a5490; font-size: 24px;">{{name}}</h1>
    <p style="margin: 0 0 14px; font-size: 11px; color: #666;">{{email}} • {{mobile}} • {{location}}</p>
    <h2 style="color: #1a5490; border-bottom: 1px solid #1a5490;">Profile</h2>
    <p>{{summary}}</p>
    <h2 style="color: #1a5490; border-bottom: 1px solid #1a5490;">Experience</h2>
    {{experience}}
    <h2 style="color: #1a5490; border-bottom: 1px solid #1a5490;">Skills</h2>
    {{skills}}
    <h2 style="color: #1a5490; border-bottom: 1px solid #1a5490;">Education</h2>
    {{education}}
</div>
HTML;
    }

    private function fresherBright(): string
    {
        return <<<'HTML'
<div class="tpl-resume">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 24px 16px; margin: -38px -38px 14px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 12px;">
        <div style="width: 80px; height: 80px; border-radius: 50%; border: 3px solid rgba(255,255,255,0.3); overflow: hidden; background: rgba(255,255,255,0.1);">{{profile_image_tag}}</div>
        <div>
            <h1 style="margin: 0; font-size: 28px; color: white;">{{name}}</h1>
            <p style="margin: 4px 0; font-size: 11px; opacity: 0.9;">{{location}} | {{email}} | {{mobile}}</p>
        </div>
    </div>
    <h2 style="color: #667eea;">About</h2>
    <p>{{summary}}</p>
    <h2 style="color: #667eea;">Experience</h2>
    {{experience}}
    <h2 style="color: #667eea;">Skills</h2>
    {{skills}}
    <h2 style="color: #667eea;">Education</h2>
    {{education}}
</div>
HTML;
    }

    private function fresherClean(): string
    {
        return <<<'HTML'
<div class="tpl-resume">
    <h1 style="margin: 0 0 6px; font-size: 20px; color: #111;">{{name}}</h1>
    <p style="margin: 0 0 12px; font-size: 11px; color: #666;">{{email}} | {{mobile}} | {{location}}</p>
    <h2 style="background: #f0f0f0; padding: 6px 8px; margin: 10px 0 6px;">OBJECTIVE</h2>
    <p>{{summary}}</p>
    <h2 style="background: #f0f0f0; padding: 6px 8px; margin: 10px 0 6px;">EXPERIENCE</h2>
    {{experience}}
    <h2 style="background: #f0f0f0; padding: 6px 8px; margin: 10px 0 6px;">SKILLS</h2>
    {{skills}}
    <h2 style="background: #f0f0f0; padding: 6px 8px; margin: 10px 0 6px;">EDUCATION</h2>
    {{education}}
</div>
HTML;
    }

    private function fresherBold(): string
    {
        return <<<'HTML'
<div class="tpl-resume">
    <h1 style="margin: 0; font-size: 24px; font-weight: 900; letter-spacing: 2px;">{{name}}</h1>
    <p style="margin: 3px 0 12px; font-size: 11px;">{{email}} • {{mobile}} • {{location}}</p>
    <h2 style="text-transform: uppercase; font-weight: 900; font-size: 12px; margin: 10px 0 6px;">About</h2>
    <p>{{summary}}</p>
    <h2 style="text-transform: uppercase; font-weight: 900; font-size: 12px; margin: 10px 0 6px;">Experience</h2>
    {{experience}}
    <h2 style="text-transform: uppercase; font-weight: 900; font-size: 12px; margin: 10px 0 6px;">Skills</h2>
    {{skills}}
    <h2 style="text-transform: uppercase; font-weight: 900; font-size: 12px; margin: 10px 0 6px;">Education</h2>
    {{education}}
</div>
HTML;
    }

    private function executive(): string
    {
        return <<<'HTML'
<div class="tpl-resume">
    <div style="display: flex; gap: 25px; margin-bottom: 20px; align-items: flex-start;">
        <div style="width: 90px; height: 110px; border-radius: 4px; overflow: hidden; flex-shrink: 0; background: #f0f0f0;">{{profile_image_tag}}</div>
        <div style="flex: 1;">
            <h1 style="margin: 0 0 4px; font-size: 26px; font-weight: 900;">{{name}}</h1>
            <p style="margin: 0 0 8px; font-size: 11px;">{{location}} | {{email}} | {{mobile}}</p>
            <h2 style="margin-top: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px;">Executive Summary</h2>
            <p style="margin: 0;">{{summary}}</p>
        </div>
    </div>
    <div style="column-count: 2; column-gap: 20px;">
        <h2>Professional Experience</h2>
        {{experience}}
        <h2>Core Expertise</h2>
        {{skills}}
        <h2>Education</h2>
        {{education}}
    </div>
</div>
HTML;
    }

    private function seniorPro(): string
    {
        return <<<'HTML'
<div class="tpl-resume">
    <div style="display: flex; gap: 20px; margin-bottom: 12px; align-items: center;">
        <div style="width: 75px; height: 75px; border-radius: 8px; overflow: hidden; flex-shrink: 0; border: 2px solid #1976d2;">{{profile_image_tag}}</div>
        <div style="flex: 1; border-left: 4px solid #1976d2; padding-left: 14px;">
            <h1 style="margin: 0; font-size: 28px; color: #1976d2;">{{name}}</h1>
            <p style="margin: 4px 0; font-size: 11px; color: #666;">{{email}} • {{mobile}} • {{location}}</p>
        </div>
    </div>
    <h2 style="color: #1976d2; border-bottom: 2px solid #1976d2; padding-bottom: 4px;">EXECUTIVE PROFILE</h2>
    <p>{{summary}}</p>
    <h2 style="color: #1976d2; border-bottom: 2px solid #1976d2; padding-bottom: 4px; margin-top: 12px;">CAREER HISTORY</h2>
    {{experience}}
    <h2 style="color: #1976d2; border-bottom: 2px solid #1976d2; padding-bottom: 4px; margin-top: 12px;">EXPERTISE</h2>
    {{skills}}
    <h2 style="color: #1976d2; border-bottom: 2px solid #1976d2; padding-bottom: 4px; margin-top: 12px;">CREDENTIALS</h2>
    {{education}}
</div>
HTML;
    }

    private function leadership(): string
    {
        return <<<'HTML'
<div class="tpl-resume" style="background: #fafafa;">
    <header style="background: #333; color: white; padding: 24px 16px; margin: -38px -38px 14px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 15px;">
        <div style="width: 100px; height: 100px; border-radius: 50%; border: 4px solid rgba(255,255,255,0.2); overflow: hidden; background: rgba(255,255,255,0.1);">{{profile_image_tag}}</div>
        <div>
            <h1 style="margin: 0; font-size: 32px; color: white;">{{name}}</h1>
            <p style="margin: 4px 0; opacity: 0.9;">{{email}} | {{mobile}} | {{location}}</p>
        </div>
    </header>
    <h2 style="background: #333; color: white; padding: 6px 8px; margin: 12px -38px 8px; text-transform: uppercase;">Overview</h2>
    <p style="margin: 0 0 12px;">{{summary}}</p>
    <h2 style="background: #333; color: white; padding: 6px 8px; margin: 12px -38px 8px; text-transform: uppercase;">Experience</h2>
    {{experience}}
    <h2 style="background: #333; color: white; padding: 6px 8px; margin: 12px -38px 8px; text-transform: uppercase;">Skills</h2>
    {{skills}}
    <h2 style="background: #333; color: white; padding: 6px 8px; margin: 12px -38px 8px; text-transform: uppercase;">Education</h2>
    {{education}}
</div>
HTML;
    }

    private function advanced(): string
    {
        return <<<'HTML'
<div class="tpl-resume">
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="width: 70%;">
                <h1 style="margin: 0; font-size: 24px;">{{name}}</h1>
                <p style="margin: 2px 0; font-size: 11px;">{{email}} | {{mobile}}</p>
            </td>
            <td style="width: 30%; text-align: right; font-size: 10px;">
                {{location}}
            </td>
        </tr>
    </table>
    <div style="border-top: 2px solid #000; margin: 10px 0 10px; padding-top: 8px;">
        <h2>Professional Summary</h2>
        <p>{{summary}}</p>
        <h2>Professional Experience</h2>
        {{experience}}
        <h2>Skills</h2>
        {{skills}}
        <h2>Education</h2>
        {{education}}
    </div>
</div>
HTML;
    }

    private function master(): string
    {
        return <<<'HTML'
<div class="tpl-resume" style="line-height: 1.6;">
    <div style="margin-bottom: 12px; border-bottom: 1px solid #ccc; padding-bottom: 8px;">
        <h1 style="margin: 0; font-size: 26px; letter-spacing: 1px;">{{name}}</h1>
        <div style="display: flex; justify-content: space-between; font-size: 10px; color: #666;">
            <span>{{location}}</span>
            <span>{{email}}</span>
            <span>{{mobile}}</span>
        </div>
    </div>
    <h2 style="margin: 10px 0 6px; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Professional Profile</h2>
    <p style="margin: 0 0 10px;">{{summary}}</p>
    <h2 style="margin: 10px 0 6px; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Work Experience</h2>
    {{experience}}
    <h2 style="margin: 10px 0 6px; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Technical Competencies</h2>
    {{skills}}
    <h2 style="margin: 10px 0 6px; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Academic Qualifications</h2>
    {{education}}
</div>
HTML;
    }

    private function wordBlue(): string
    {
        return <<<'HTML'
<div class="tpl-resume" style="border-top: 8px solid #0066cc;">
    <h1 style="margin: 0 0 4px; font-size: 26px; color: #0066cc;">{{name}}</h1>
    <p style="margin: 0 0 12px; font-size: 11px; color: #666;">{{email}} • {{mobile}} • {{location}}</p>
    <h2 style="color: #0066cc; border-bottom: 2px solid #0066cc; padding-bottom: 4px;">Summary</h2>
    <p>{{summary}}</p>
    <h2 style="color: #0066cc; border-bottom: 2px solid #0066cc; padding-bottom: 4px; margin-top: 10px;">Experience</h2>
    {{experience}}
    <h2 style="color: #0066cc; border-bottom: 2px solid #0066cc; padding-bottom: 4px; margin-top: 10px;">Skills</h2>
    {{skills}}
    <h2 style="color: #0066cc; border-bottom: 2px solid #0066cc; padding-bottom: 4px; margin-top: 10px;">Education</h2>
    {{education}}
</div>
HTML;
    }

    private function wordGreen(): string
    {
        return <<<'HTML'
<div class="tpl-resume" style="border-top: 8px solid #008000;">
    <h1 style="margin: 0 0 4px; font-size: 26px; color: #008000;">{{name}}</h1>
    <p style="margin: 0 0 12px; font-size: 11px; color: #666;">{{email}} • {{mobile}} • {{location}}</p>
    <h2 style="color: #008000; border-bottom: 2px solid #008000; padding-bottom: 4px;">Summary</h2>
    <p>{{summary}}</p>
    <h2 style="color: #008000; border-bottom: 2px solid #008000; padding-bottom: 4px; margin-top: 10px;">Experience</h2>
    {{experience}}
    <h2 style="color: #008000; border-bottom: 2px solid #008000; padding-bottom: 4px; margin-top: 10px;">Skills</h2>
    {{skills}}
    <h2 style="color: #008000; border-bottom: 2px solid #008000; padding-bottom: 4px; margin-top: 10px;">Education</h2>
    {{education}}
</div>
HTML;
    }

    private function wordRed(): string
    {
        return <<<'HTML'
<div class="tpl-resume" style="border-top: 8px solid #cc0000;">
    <h1 style="margin: 0 0 4px; font-size: 26px; color: #cc0000;">{{name}}</h1>
    <p style="margin: 0 0 12px; font-size: 11px; color: #666;">{{email}} • {{mobile}} • {{location}}</p>
    <h2 style="color: #cc0000; border-bottom: 2px solid #cc0000; padding-bottom: 4px;">Summary</h2>
    <p>{{summary}}</p>
    <h2 style="color: #cc0000; border-bottom: 2px solid #cc0000; padding-bottom: 4px; margin-top: 10px;">Experience</h2>
    {{experience}}
    <h2 style="color: #cc0000; border-bottom: 2px solid #cc0000; padding-bottom: 4px; margin-top: 10px;">Skills</h2>
    {{skills}}
    <h2 style="color: #cc0000; border-bottom: 2px solid #cc0000; padding-bottom: 4px; margin-top: 10px;">Education</h2>
    {{education}}
</div>
HTML;
    }

    private function wordGray(): string
    {
        return <<<'HTML'
<div class="tpl-resume" style="border-top: 8px solid #666;">
    <h1 style="margin: 0 0 4px; font-size: 26px; color: #333;">{{name}}</h1>
    <p style="margin: 0 0 12px; font-size: 11px; color: #888;">{{email}} • {{mobile}} • {{location}}</p>
    <h2 style="color: #333; border-bottom: 2px solid #666; padding-bottom: 4px;">Summary</h2>
    <p>{{summary}}</p>
    <h2 style="color: #333; border-bottom: 2px solid #666; padding-bottom: 4px; margin-top: 10px;">Experience</h2>
    {{experience}}
    <h2 style="color: #333; border-bottom: 2px solid #666; padding-bottom: 4px; margin-top: 10px;">Skills</h2>
    {{skills}}
    <h2 style="color: #333; border-bottom: 2px solid #666; padding-bottom: 4px; margin-top: 10px;">Education</h2>
    {{education}}
</div>
HTML;
    }

    private function wordPurple(): string
    {
        return <<<'HTML'
<div class="tpl-resume" style="border-top: 8px solid #7030a0;">
    <h1 style="margin: 0 0 4px; font-size: 26px; color: #7030a0;">{{name}}</h1>
    <p style="margin: 0 0 12px; font-size: 11px; color: #666;">{{email}} • {{mobile}} • {{location}}</p>
    <h2 style="color: #7030a0; border-bottom: 2px solid #7030a0; padding-bottom: 4px;">Summary</h2>
    <p>{{summary}}</p>
    <h2 style="color: #7030a0; border-bottom: 2px solid #7030a0; padding-bottom: 4px; margin-top: 10px;">Experience</h2>
    {{experience}}
    <h2 style="color: #7030a0; border-bottom: 2px solid #7030a0; padding-bottom: 4px; margin-top: 10px;">Skills</h2>
    {{skills}}
    <h2 style="color: #7030a0; border-bottom: 2px solid #7030a0; padding-bottom: 4px; margin-top: 10px;">Education</h2>
    {{education}}
</div>
HTML;
    }

    private function coverLetterClassic(): string
    {
        return <<<'HTML'
<div class="tpl-cover" style="font-family: 'Georgia', serif; max-width: 800px; margin: 0 auto; padding: 40px; line-height: 1.6;">
    <p style="margin: 0 0 20px; color: #666;">{{location}}</p>
    <p style="margin: 0 0 30px; color: #666;">Dear Hiring Manager,</p>
    <p style="margin: 0 0 20px; text-align: justify;">{{body}}</p>
    <p style="margin: 0 0 30px;">Sincerely,</p>
    <p style="margin: 0; font-weight: bold;">{{name}}</p>
    <p style="margin: 5px 0 0; color: #666;">{{email}} | {{mobile}}</p>
</div>
HTML;
    }

    private function coverLetterModern(): string
    {
        return <<<'HTML'
<div class="tpl-cover" style="font-family: 'Segoe UI', system-ui, sans-serif; max-width: 800px; margin: 0 auto; padding: 60px 50px; line-height: 1.8; color: #1f2937;">
    <div style="margin-bottom: 30px;">
        <h1 style="margin: 0 0 8px; font-size: 36px; font-weight: 800; color: #0f172a; letter-spacing: -0.03em; text-transform: uppercase; line-height: 1.1;">{{name}}</h1>
        <p style="margin: 0; color: #64748b; font-size: 13px; font-weight: 500; letter-spacing: 0.05em; text-transform: uppercase;">{{email}} &bull; {{mobile}} &bull; {{location}}</p>
    </div>
    
    <div style="height: 2px; background: #0d9488; margin-bottom: 40px; opacity: 0.6;"></div>

    <div style="margin-bottom: 45px; border-left: 3px solid #0d9488; padding-left: 20px;">
        <p style="margin: 0 0 4px; color: #64748b; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Opportunity</p>
        <strong style="color: #0f172a; font-size: 18px; font-weight: 700;">{{job_role}} at {{company}}</strong>
    </div>

    <div style="margin-bottom: 25px; color: #1e293b; font-size: 15px; font-weight: 600;">Dear Hiring Manager,</div>
    
    <div style="margin-bottom: 60px; text-align: justify; color: #334155; font-size: 15px; line-height: 1.8; white-space: pre-wrap;">{{body}}</div>

    <div style="color: #1e293b; font-size: 15px;">
        <p style="margin-bottom: 8px; color: #64748b; font-size: 14px;">Sincerely,</p>
        <p style="font-weight: 800; color: #0f172a; font-size: 18px; margin: 0;">{{name}}</p>
    </div>
</div>
HTML;
    }

    private function coverLetterClean(): string
    {
        return <<<'HTML'
<div class="tpl-cover" style="max-width: 750px; margin: 0 auto; padding: 50px 40px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; font-size: 14px; line-height: 1.7; color: #2c3e50;">
    <div style="margin-bottom: 40px;">
        <h2 style="margin: 0 0 10px; font-size: 18px; font-weight: 600;">{{name}}</h2>
        <p style="margin: 0; color: #7f8c8d; font-size: 12px;">{{email}} | {{mobile}} | {{location}}</p>
    </div>
    <p style="margin: 0 0 25px;">Dear Hiring Manager,</p>
    <div style="margin: 0 0 30px; text-align: justify;">{{body}}</div>
    <div style="margin-top: 40px;">
        <p style="margin: 0 0 15px;">Regards,</p>
        <p style="margin: 0;">{{name}}</p>
    </div>
</div>
HTML;
    }
}
