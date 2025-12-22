<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('barbers', 'lunch_work_start')) {
            Schema::table('barbers', function (Blueprint $table) {
                $table->date('lunch_work_start')->nullable();
                $table->date('lunch_work_end')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::table('barbers', function (Blueprint $table) {
            $table->dropColumn(['lunch_work_start', 'lunch_work_end']);
        });
    }
};
