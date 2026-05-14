<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach(\App\Models\Template::where('type', 'resume')->get() as $t) {
    echo "ID: " . $t->id . " | Name: " . $t->name . "\n";
    echo "HTML: " . substr($t->html, 0, 500) . "...\n";
    echo "----------------------------------------\n";
}
