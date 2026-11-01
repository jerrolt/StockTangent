<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'sku' => fake()->unique()->bothify('ST-????-####'),
            'barcode' => fake()->unique()->ean13(),
            'qrcode' => fake()->unique()->uuid(),
            'unit_of_measure' => fake()->randomElement(['each', 'box', 'kg', 'liter']),
            'is_active' => true,
        ];
    }
}
