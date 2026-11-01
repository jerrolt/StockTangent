<?php

namespace Database\Factories;

use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\InventoryTransactionItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryTransactionItem>
 */
class InventoryTransactionItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'inventory_item_id' => InventoryItem::factory(),
            'transaction_id' => fn (array $attributes) => InventoryTransaction::factory()->create([
                'inventory_id' => InventoryItem::findOrFail($attributes['inventory_item_id'])->inventory_id,
            ])->id,
            'product_id' => fn (array $attributes) => InventoryItem::findOrFail($attributes['inventory_item_id'])->product_id,
            'store_id' => null,
            'quantity' => fake()->numberBetween(1, 20),
            'unit_price' => fake()->randomFloat(2, 1, 100),
            'notes' => fake()->sentence(),
        ];
    }
}
