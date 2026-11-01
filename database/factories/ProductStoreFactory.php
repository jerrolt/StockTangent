<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductStore;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductStore>
 */
class ProductStoreFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'store_id' => Store::factory(),
            'price' => fake()->randomFloat(2, 1, 100),
            'currency' => 'USD',
            'observed_at' => now(),
        ];
    }
}
