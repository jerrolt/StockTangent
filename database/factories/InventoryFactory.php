<?php

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inventory>
 */
class InventoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'organization_id' => null,
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
        ];
    }

    public function forOrganization(?Organization $organization = null): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'organization_id' => $organization?->id ?? Organization::factory(),
        ]);
    }
}
