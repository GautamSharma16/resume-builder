<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Template;
use App\Services\TemplateRenderService;

$renderer = app(TemplateRenderService::class);
$templates = Template::where('type', 'resume')->limit(5)->get();

foreach ($templates as $template) {
    echo "Template: " . $template->name . "\n";
    $html = (string) $renderer->renderResume($template);
    if (strpos($html, 'John Doe') !== false) {
        echo "Contains 'John Doe'\n";
    } else {
        echo "Does NOT contain 'John Doe'\n";
        // Print first 200 chars to see what's there
        echo substr(strip_tags($html), 0, 200) . "...\n";
    }
    echo "-------------------\n";
}
