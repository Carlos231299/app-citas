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
        Schema::table('barbers', function (Blueprint $table) {
            $table->date('extra_time_start')->nullable()->after('special_mode');
            $table->date('extra_time_end')->nullable()->after('extra_time_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barbers', function (Blueprint $table) {
            $table->dropColumn(['extra_time_start', 'extra_time_end']);
        });
    }
};
