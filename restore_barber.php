<?php

use App\Models\User;
use App\Models\Barber;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$name = 'Javier Mosquera';
$user = User::where('name', 'LIKE', "%$name%")->first();

if (!$user) {
    echo "User '$name' NOT FOUND.\n";
    exit(1);
}

echo "Found User: {$user->name} (ID: {$user->id})\n";

// Check if Barber exists
$barber = Barber::where('user_id', $user->id)->first();

if ($barber) {
    echo "Barber profile already exists for this user.\n";
} else {
    Barber::create([
        'name' => $user->name,
        'user_id' => $user->id,
        'is_active' => true,
        // Add random phone if needed or leave null if nullable
        'whatsapp_number' => '3000000000', 


    ]);
    echo "✅ Barber profile created successfully for {$user->name}.\n";
}
