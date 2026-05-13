<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach(\App\Models\Template::where('type', 'cover_letter')->get() as $t) {
    echo "ID: " . $t->id . " | Name: " . $t->name . "\n";
    echo "HTML: " . $t->html . "\n";
    echo "----------------------------------------\n";
}
