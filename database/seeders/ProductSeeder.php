<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure categories exist first (should be run after CategorySeeder, or we create them here)
        $barberia = \App\Models\Category::firstOrCreate(['name' => 'Barbería'], ['color' => 'dark']);
        $bebidas = \App\Models\Category::firstOrCreate(['name' => 'Bebidas'], ['color' => 'info']);
        $mekatos = \App\Models\Category::firstOrCreate(['name' => 'Mekatos'], ['color' => 'warning']);

        $products = [
            // Barbería
            [
                'name' => 'Cera Matte',
                'description' => 'Cera de fijación fuerte efecto mate.',
                'price' => 25000,
                'stock' => 10,
                'category_id' => $barberia->id,
                'image' => null,
            ],
            [
                'name' => 'Gel EgO',
                'description' => 'Gel para cabello hombre.',
                'price' => 12000,
                'stock' => 15,
                'category_id' => $barberia->id,
                'image' => null,
            ],
            // Bebidas
            [
                'name' => 'Coca Cola 400ml',
                'description' => 'Gaseosa refrescante.',
                'price' => 4000,
                'stock' => 24,
                'category_id' => $bebidas->id,
                'image' => null,
            ],
            [
                'name' => 'Agua Cristal',
                'description' => 'Botella de agua sin gas.',
                'price' => 3000,
                'stock' => 20,
                'category_id' => $bebidas->id,
                'image' => null,
            ],
            // Mekatos
            [
                'name' => 'Papas Margarita Pollo',
                'description' => 'Paquete personal.',
                'price' => 3500,
                'stock' => 12,
                'category_id' => $mekatos->id,
                'image' => null,
            ],
            [
                'name' => 'Chocoramo',
                'description' => 'Ponqué cubierto de chocolate.',
                'price' => 2500,
                'stock' => 30,
                'category_id' => $mekatos->id,
                'image' => null,
            ],
        ];

        foreach ($products as $p) {
            Product::updateOrCreate(
                ['name' => $p['name']], // Match by name
                $p // Update fields including category_id
            );
        }
    }
}
