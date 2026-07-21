<?php

namespace Tests\Unit;

use App\Models\Template;
use App\Services\TemplateRenderService;
use Tests\TestCase;

class TemplateRenderServiceTest extends TestCase
{
    public function test_it_hides_empty_sections_in_rendered_resume(): void
    {
        $service = new TemplateRenderService();
        $template = new Template([
            'html' => '<div class="tpl-resume"><section><h2>Summary</h2><p>{{summary}}</p></section><section><h2>Skills</h2><div>{{skills}}</div></section><section><h2>Projects</h2>{{projects}}</section><section><h2>Languages</h2>{{languages}}</section><section><h2>Education</h2>{{education}}</section></div>',
        ]);

        $html = (string) $service->renderResume($template, [
            'name' => 'Gautam Sharma',
            'summary' => '   ',
            'skills' => [' ', null],
            'projects' => [['name' => ' ', 'description' => '']],
            'languages' => [['name' => "\t", 'level' => '']],
            'education' => [['degree' => '', 'institution' => null]],
        ]);

        $this->assertStringNotContainsString('<h2>Summary</h2>', $html);
        $this->assertStringNotContainsString('<h2>Skills</h2>', $html);
        $this->assertStringNotContainsString('<h2>Projects</h2>', $html);
        $this->assertStringNotContainsString('<h2>Languages</h2>', $html);
        $this->assertStringNotContainsString('<h2>Education</h2>', $html);
    }

    public function test_it_keeps_only_sections_with_meaningful_data(): void
    {
        $service = new TemplateRenderService();
        $template = new Template([
            'html' => '<div class="tpl-resume"><section><h2>Skills</h2>{{skills}}</section><section><h2>Projects</h2>{{projects}}</section><section><h2>Certifications</h2>{{certifications}}</section></div>',
        ]);

        $html = (string) $service->renderResume($template, [
            'name' => 'Gautam Sharma',
            'skills' => ['Laravel', ' '],
            'projects' => [],
            'certifications' => [['name' => '', 'description' => '']],
        ]);

        $this->assertStringContainsString('<h2>Skills</h2>', $html);
        $this->assertStringContainsString('Laravel', $html);
        $this->assertStringNotContainsString('<h2>Projects</h2>', $html);
        $this->assertStringNotContainsString('<h2>Certifications</h2>', $html);
    }

    public function test_it_hides_empty_blade_sections_after_rendering(): void
    {
        $service = new TemplateRenderService();
        $template = new Template([
            'html' => '<div class="tpl-resume"><section><h2>Achievements</h2><ul>@foreach($resume["achievements"] as $achievement)<li>{{ $achievement["name"] ?? $achievement }}</li>@endforeach</ul></section><section><h2>Experience</h2>@foreach($resume["experience"] as $item)<p>{{ $item["role"] }}</p>@endforeach</section></div>',
        ]);

        $html = (string) $service->renderResume($template, [
            'name' => 'Gautam Sharma',
            'achievements' => [['name' => ' ', 'description' => null]],
            'experience' => [['role' => 'Developer', 'company' => '', 'points' => []]],
        ]);

        $this->assertStringNotContainsString('<h2>Achievements</h2>', $html);
        $this->assertStringContainsString('<h2>Experience</h2>', $html);
        $this->assertStringContainsString('Developer', $html);
    }

    public function test_it_formats_education_consistently_with_structured_fields(): void
    {
        $service = new TemplateRenderService();

        $html = (string) $service->renderResume(new Template(), [
            'name' => 'Gautam Sharma',
            'education' => [
                [
                    'degree' => 'Master of Computer Applications (MCA)',
                    'institution' => 'Galgotia College of Engineering and Technology',
                    'year' => '2024 - Present',
                    'cgpa' => '7.70',
                ],
            ],
        ]);

        $this->assertStringContainsString('<strong>Master of Computer Applications (MCA)</strong>', $html);
        $this->assertStringContainsString('Galgotia College of Engineering and Technology, CGPA: 7.70, 2024 - Present', $html);
        $this->assertStringNotContainsString('<ul></ul>', $html);
    }

    public function test_it_uses_duration_when_experience_period_is_missing(): void
    {
        $service = new TemplateRenderService();

        $html = (string) $service->renderResume(new Template(), [
            'name' => 'Gautam Sharma',
            'experience' => [
                [
                    'role' => 'MERN Stack Developer Intern',
                    'company' => 'Companyvista Inc',
                    'duration' => 'Feb 2024 - Present',
                    'points' => ['Built resume features.'],
                ],
            ],
        ]);

        $this->assertStringContainsString('Feb 2024 - Present', $html);
        $this->assertStringContainsString('Built resume features.', $html);
    }
}
