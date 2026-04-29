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
            ['name' => 'ATS Modern', 'category' => 'ats', 'type' => 'resume', 'html' => $this->atsModern()],
            ['name' => 'ATS Minimal', 'category' => 'ats', 'type' => 'resume', 'html' => $this->atsMinimal()],
            ['name' => 'ATS Professional', 'category' => 'ats', 'type' => 'resume', 'html' => $this->atsProfessional()],
            ['name' => 'ATS Clean', 'category' => 'ats', 'type' => 'resume', 'html' => $this->atsClean()],

            // Fresher Templates
            ['name' => 'Fresher Starter', 'category' => 'fresher', 'type' => 'resume', 'html' => $this->fresherStarter()],
            ['name' => 'Fresher Simple', 'category' => 'fresher', 'type' => 'resume', 'html' => $this->fresherSimple()],
            ['name' => 'Fresher Bright', 'category' => 'fresher', 'type' => 'resume', 'html' => $this->fresherBright()],
            ['name' => 'Fresher Clean', 'category' => 'fresher', 'type' => 'resume', 'html' => $this->fresherClean()],
            ['name' => 'Fresher Bold', 'category' => 'fresher', 'type' => 'resume', 'html' => $this->fresherBold()],

            // Experienced Templates
            ['name' => 'Executive', 'category' => 'experienced', 'type' => 'resume', 'html' => $this->executive()],
            ['name' => 'Senior Pro', 'category' => 'experienced', 'type' => 'resume', 'html' => $this->seniorPro()],
            ['name' => 'Leadership', 'category' => 'experienced', 'type' => 'resume', 'html' => $this->leadership()],
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
    <div style="border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 12px;">
        <h1 style="margin: 0; font-size: 28px;">{{name}}</h1>
        <p style="margin: 4px 0; font-size: 11px;">{{email}} • {{mobile}} • {{location}}</p>
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
    <div style="text-align: center; margin-bottom: 12px; padding-bottom: 10px; border-bottom: 2px solid #4CAF50;">
        <h1 style="margin: 0; color: #2E7D32; font-size: 26px;">{{name}}</h1>
        <p style="margin: 4px 0; font-size: 11px;">{{email}} | {{mobile}} | {{location}}</p>
    </div>
    <h2 style="color: #2E7D32;">About Me</h2>
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
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 16px; margin: -38px -38px 14px; text-align: center;">
        <h1 style="margin: 0; font-size: 28px;">{{name}}</h1>
        <p style="margin: 4px 0; font-size: 11px;">{{location}} | {{email}} | {{mobile}}</p>
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
<div class="tpl-resume" style="column-count: 2; column-gap: 20px;">
    <h1 style="column-span: all; margin: 0 0 4px; font-size: 26px; font-weight: 900;">{{name}}</h1>
    <p style="column-span: all; margin: 0 0 14px; font-size: 11px;">{{location}} | {{email}} | {{mobile}}</p>
    <h2 style="column-span: all;">Executive Summary</h2>
    <p style="column-span: all;">{{summary}}</p>
    <h2>Professional Experience</h2>
    {{experience}}
    <h2>Core Expertise</h2>
    {{skills}}
    <h2>Education</h2>
    {{education}}
</div>
HTML;
    }

    private function seniorPro(): string
    {
        return <<<'HTML'
<div class="tpl-resume">
    <div style="border-left: 4px solid #1976d2; padding-left: 14px; margin-bottom: 12px;">
        <h1 style="margin: 0; font-size: 28px; color: #1976d2;">{{name}}</h1>
        <p style="margin: 4px 0; font-size: 11px; color: #666;">{{email}} • {{mobile}} • {{location}}</p>
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
    <header style="background: #333; color: white; padding: 16px; margin: -38px -38px 14px; text-align: center;">
        <h1 style="margin: 0; font-size: 32px;">{{name}}</h1>
        <p style="margin: 4px 0;">{{email}} | {{mobile}} | {{location}}</p>
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
<div class="tpl-cover" style="font-family: 'Segoe UI', sans-serif; max-width: 800px; margin: 0 auto; padding: 40px; line-height: 1.8; color: #333;">
    <div style="border-top: 4px solid #0f766e; padding-top: 20px; margin-bottom: 30px;">
        <h1 style="margin: 0 0 5px; font-size: 24px; color: #0f766e;">{{name}}</h1>
        <p style="margin: 0; color: #666; font-size: 13px;">{{email}} • {{mobile}} • {{location}}</p>
    </div>
    <p style="margin: 0 0 20px; color: #666; font-size: 14px;">Dear Hiring Manager,</p>
    <div style="margin: 0 0 30px; text-align: justify; line-height: 1.7;">{{body}}</div>
    <div style="margin-top: 30px;">
        <p style="margin: 0 0 10px;">Best regards,</p>
        <p style="margin: 0; font-weight: 600; color: #0f766e;">{{name}}</p>
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
