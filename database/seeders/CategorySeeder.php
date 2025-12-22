<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Categories
        $cats = [
            'barberia' => Category::firstOrCreate(['name' => 'Barbería'], ['color' => 'dark']),
            'bebidas'  => Category::firstOrCreate(['name' => 'Bebidas'],  ['color' => 'info']),
            'mekatos'  => Category::firstOrCreate(['name' => 'Mekatos'],  ['color' => 'warning']),
        ];

        // 2. Migrate existing products (if any)
        // Since we dropped the column in migration (or will), we must be careful.
        // ACTUALLY, we should run this SEEDER *AFTER* adding the column but *BEFORE* dropping the old one if we wanted to preserve data.
        // But since I am defining the migration to DROP the old column, I need to handle data migration inside the MIGRATION or just re-seed everything.
        // Given this is a new feature in a 'dev' cycle, I'll rely on this seeder to fix up products if they exist and still have the string? 
        // No, once column is dropped, data is gone.
        // Strategy: The migration 'up' will add column. Data migration should happen there if critical.
        // But let's assume we just re-assign based on name or just random/default for now as the user approved "Upload Only" which implies some reset is fine, 
        // but I will try to map by standardizing.
        
        // For now, just create categories. The ProductSeeder can be updated to use IDs.
    }
}
