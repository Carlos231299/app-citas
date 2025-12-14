<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ensure Admin User Exists
        if (!User::where('email', 'cbastidas52@gmail.com')->exists()) {
            User::create([
                'name' => 'Carlos Bastidas',
                'email' => 'cbastidas52@gmail.com',
                'password' => Hash::make('Admin2025*'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optional: Do not delete to preserve data safety
    }
};
