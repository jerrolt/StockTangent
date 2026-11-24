<?php

namespace App\Policies;

use App\Models\Inventory;
use App\Models\User;

class InventoryPolicy
{
    public function view(User $user, Inventory $inventory): bool
    {
        return Inventory::withTrashed()->visibleTo($user)->whereKey($inventory->id)->exists();
    }

    public function update(User $user, Inventory $inventory): bool
    {
        return $this->owns($user, $inventory)
            || $inventory->users()->whereKey($user->id)->wherePivotIn('permission', ['admin', 'editor'])->exists();
    }

    public function delete(User $user, Inventory $inventory): bool
    {
        return $this->owns($user, $inventory)
            || $inventory->users()->whereKey($user->id)->wherePivot('permission', 'admin')->exists();
    }

    public function restore(User $user, Inventory $inventory): bool
    {
        return $this->delete($user, $inventory);
    }

    private function owns(User $user, Inventory $inventory): bool
    {
        return $inventory->user_id === $user->id
            || ($inventory->organization_id !== null && $user->organizations()->withTrashed()->whereKey($inventory->organization_id)->exists());
    }
}
