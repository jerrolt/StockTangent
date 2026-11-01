<?php

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\InventoryItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryItem>
 */
class InventoryItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'inventory_id' => Inventory::factory(),
            'product_id' => Product::factory(),
            'quantity' => fake()->numberBetween(0, 100),
            'reorder_level' => fake()->numberBetween(5, 15),
        ];
    }
}
