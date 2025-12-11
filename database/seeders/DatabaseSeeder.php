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
                'password' => bcrypt('password'),
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

        // Default Barber
        Barber::create([
            'name' => 'Javier Mosquera',
            'is_active' => true,
            'avatar' => 'default.png'
        ]);
    }
}
