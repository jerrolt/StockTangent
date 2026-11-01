<?php

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\InventoryUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryUser>
 */
class InventoryUserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'inventory_id' => Inventory::factory(),
            'user_id' => User::factory(),
            'permission' => fake()->randomElement(['admin', 'editor', 'viewer']),
        ];
    }
}
