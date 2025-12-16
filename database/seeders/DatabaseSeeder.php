<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Service;
use App\Models\Barber;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin User (Check if exists first)
        User::firstOrCreate(
            ['email' => 'admin@barberiajr.com'],
            [
                'name' => 'Administrador JR',
                'username' => 'admin', // Default Username
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        // Reset Services and Barbers to prevent duplicates
        \App\Models\Service::truncate();
        \App\Models\Barber::truncate();

        // Services (Bootstrap Icons)
        $services = [
            ['name' => 'Corte', 'price' => 15000, 'icon' => 'scissors'],
            ['name' => 'Corte + Barba', 'price' => 20000, 'icon' => 'person-badge'],
            ['name' => 'Corte Niño', 'price' => 12000, 'icon' => 'emoji-smile'],
            ['name' => 'Barba', 'price' => 10000, 'icon' => 'bezier2'],
            ['name' => 'Corte + Cejas', 'price' => 17000, 'icon' => 'eye'],
            ['name' => 'Barba + Cerquillo', 'price' => 12000, 'icon' => 'brush'],
            ['name' => 'Mascarilla + Masaje', 'price' => 15000, 'icon' => 'droplet'],
            ['name' => 'Otro servicio', 'price' => 0, 'icon' => 'stars'],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }

        // Custom User Requested
        User::create([
            'name' => 'Carlos Bastidas',
            'username' => 'Carlos23',
            'email' => 'cbastidas52@gmail.com',
            'password' => bcrypt('password'),
            'role' => 'standard', // Changed to standard to test barber role
        ]);

        // Create Barber Profile for Carlos23
        \App\Models\Barber::create([
            'name' => 'Carlos Bastidas',
            'whatsapp_number' => '+573001234567',
            'is_active' => true,
            'user_id' => \App\Models\User::where('username', 'Carlos23')->first()->id,
        ]);

        // NO Default Barber (Clean State requested)
        // Barber::create([ ... ]);
    }
}
