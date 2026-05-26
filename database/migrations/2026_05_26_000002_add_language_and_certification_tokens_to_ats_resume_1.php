<?php

use App\Models\Template;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Template::query()
            ->where('type', 'resume')
            ->where('name', 'ATS Resume 1')
            ->get()
            ->each(function (Template $template): void {
                $html = (string) $template->html;

                if (str_contains($html, '{{languages}}') && (str_contains($html, '{{certifications}}') || str_contains($html, '{{certificates}}'))) {
                    return;
                }

                $sections = '';

                if (! str_contains($html, '{{certifications}}') && ! str_contains($html, '{{certificates}}')) {
                    $sections .= '<section><h2>Certifications</h2>{{certifications}}</section>';
                }

                if (! str_contains($html, '{{languages}}')) {
                    $sections .= '<section><h2>Languages</h2>{{languages}}</section>';
                }

                $template->forceFill([
                    'html' => str_contains($html, '</div>')
                        ? preg_replace('/<\/div>\s*$/', $sections.'</div>', $html, 1)
                        : $html.$sections,
                ])->save();
            });
    }

    public function down(): void
    {
        Template::query()
            ->where('type', 'resume')
            ->where('name', 'ATS Resume 1')
            ->get()
            ->each(function (Template $template): void {
                $html = (string) $template->html;
                $html = str_replace('<section><h2>Certifications</h2>{{certifications}}</section>', '', $html);
                $html = str_replace('<section><h2>Languages</h2>{{languages}}</section>', '', $html);

                $template->forceFill(['html' => $html])->save();
            });
    }
};
