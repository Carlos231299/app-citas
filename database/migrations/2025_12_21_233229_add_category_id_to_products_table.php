<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('stock')->constrained('categories')->nullOnDelete();
            // We drop the old column. Data loss is acceptable/planned for this reset.
            $table->dropColumn('category');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('category')->nullable();
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
};
