<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleFactory extends Factory
{
    public function definition(): array
    {
        return [
            // customer_id akan diisi oleh seeder
            'license_plate' => 'L ' . fake()->numberBetween(1000, 9999) . ' ' . fake()->lexify('??'),
            'brand' => fake()->randomElement(['Honda', 'Toyota', 'Yamaha', 'Suzuki', 'Daihatsu', 'Mitsubishi']),
            'model' => fake()->randomElement(['Avanza', 'Beat', 'Vario', 'Innova', 'Xenia', 'NMAX', 'Pajero']),
            'year' => fake()->numberBetween(2010, 2025),
        ];
    }
}