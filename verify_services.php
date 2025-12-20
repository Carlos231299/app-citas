<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Service;

echo "Verifying Service Order...\n";

// Emulate what the Controller does
$services = Service::orderBy('sort_order')->get();

foreach($services as $service) {
    echo "Order: {$service->sort_order} | {$service->name} ({$service->price})\n";
}
