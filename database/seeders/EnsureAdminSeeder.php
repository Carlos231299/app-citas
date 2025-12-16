<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class EnsureAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = 'admin@barberiajr.com';

        $user = User::where('email', $email)->first();

        if ($user) {
            // Promover si ya existe
            $user->role = 'admin';
            $user->save();
            $this->command->info("Usuario {$email} promovido a ADMIN.");
        } else {
            // Crear si no existe
            User::create([
                'name' => 'Administrador JR',
                'email' => $email,
                'password' => Hash::make('password123'), // Contraseña temporal
                'role' => 'admin',
            ]);
            $this->command->info("Usuario {$email} creado como ADMIN.");
        }
    }
}
