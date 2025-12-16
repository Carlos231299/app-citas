<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add user_id to barbers
        Schema::table('barbers', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->unique()->after('id');
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); 
            // Optional: Foreign key constraints might complicate things if users are deleted but barbers kept, or vice versa. 
            // For now, loose coupling or manual handling is fine, but FK is better for integrity.
            // Let's add FK.
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        // 2. Add 2FA fields to users
        Schema::table('users', function (Blueprint $table) {
            $table->string('two_factor_code')->nullable()->after('password');
            $table->dateTime('two_factor_expires_at')->nullable()->after('two_factor_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barbers', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['two_factor_code', 'two_factor_expires_at']);
        });
    }
};
