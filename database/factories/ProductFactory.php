<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $purchasePrice = fake()->numberBetween(25000, 1000000);
        $sellingPrice = round($purchasePrice * 1.25);

        return [
            'category_id' => Category::whereNotIn('name', ['Jasa Servis Rutin', 'Jasa Perbaikan'])->inRandomOrder()->first()->id,
            'sku' => fake()->unique()->ean8(),
            'name' => 'Produk ' . fake()->words(2, true),
            'purchase_price' => $purchasePrice,
            'selling_price' => $sellingPrice,
            'stock' => fake()->numberBetween(10, 100),
            'unit' => fake()->randomElement(['pcs', 'botol', 'set', 'liter']),
            'storage_location' => 'Rak ' . fake()->randomElement(['A', 'B', 'C']) . '-' . fake()->numberBetween(1, 5),
        ];
    }
}