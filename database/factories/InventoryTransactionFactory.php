<?php

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryTransaction>
 */
class InventoryTransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'inventory_id' => Inventory::factory(),
            'user_id' => fn (array $attributes) => Inventory::findOrFail($attributes['inventory_id'])->user_id
                ?? User::factory()->create()->id,
            'type' => 'purchase',
            'reference_number' => fake()->unique()->bothify('PO-########'),
            'notes' => fake()->sentence(),
        ];
    }
}
