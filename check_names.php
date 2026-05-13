<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\Template;
use App\Services\TemplateRenderService;

$renderer = app(TemplateRenderService::class);
$templates = Template::all();

foreach ($templates as $template) {
    $rendered = (string) $renderer->renderResume($template);
    // Find the name in the rendered HTML
    if (preg_match('/John Doe/i', $rendered)) {
        echo "Template {$template->id} ({$template->name}): Contains John Doe\n";
    } else {
        // Output a snippet of the rendered HTML to see what's there
        $snippet = strip_tags($rendered);
        $snippet = substr(trim($snippet), 0, 50);
        echo "Template {$template->id} ({$template->name}): DOES NOT CONTAIN John Doe. Snippet: [{$snippet}]\n";
    }
}
