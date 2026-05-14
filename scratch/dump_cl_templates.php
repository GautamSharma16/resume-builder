<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$templates = \App\Models\Template::where('type', 'cover_letter')->limit(1)->get();
foreach ($templates as $t) {
    echo "ID: " . $t->id . "\n";
    echo "Name: " . $t->name . "\n";
    echo substr($t->html, 0, 500) . "...\n\n";
    
    $renderer = app(\App\Services\TemplateRenderService::class);
    $html = $renderer->renderCoverLetter($t);
    echo "RENDERED LENGTH: " . strlen((string)$html) . "\n";
    echo "RENDERED BEGINNING:\n" . substr((string)$html, 0, 500) . "\n";
}
