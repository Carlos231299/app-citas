<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$services = \App\Models\Service::all();
foreach ($services as $service) {
    echo "{$service->id}: {$service->name} ({$service->price})" . PHP_EOL;
}
