<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Service;

echo "Initializing Sort Order...\n";

$services = Service::all();

// Default Mapping based on ID (preserving original order mostly)
$map = [
    1 => 10,   // Corte
    2 => 20,   // Corte + Barba
    3 => 30,   // Corte Niño
    4 => 40,   // Barba
    5 => 50,   // Corte + Cejas
    6 => 60,   // Barba + Cerquillo
    7 => 70,   // Mascarilla + Masaje
    8 => 900,  // Otro servicio (Last)
];

foreach($services as $service) {
    if(isset($map[$service->id])) {
        $service->sort_order = $map[$service->id];
        $service->save();
        echo "Updated {$service->name} -> Order: {$service->sort_order}\n";
    }
}

// Add Cerquillos
$cerquillos = Service::firstOrNew(['name' => 'Cerquillos']);
$cerquillos->price = 5000;
$cerquillos->icon = 'scissors'; // Default icon
$cerquillos->sort_order = 800; // Before 900
$cerquillos->save();

echo "Added/Updated Cerquillos -> Order: 800\n";
