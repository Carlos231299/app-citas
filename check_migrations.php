<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$migs = \Illuminate\Support\Facades\DB::table('migrations')->orderBy('id', 'desc')->take(5)->get();
foreach($migs as $m) {
    echo "Migration: {$m->migration} (Batch: {$m->batch})\n";
}
