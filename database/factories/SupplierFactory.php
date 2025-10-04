<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'phone_number' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'contact_person' => fake()->name(),
        ];
    }
}