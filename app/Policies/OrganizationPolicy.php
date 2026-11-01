<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    public function view(User $user, Organization $organization): bool
    {
        return $organization->users()->whereKey($user->id)->exists();
    }

    public function update(User $user, Organization $organization): bool
    {
        return $this->view($user, $organization);
    }

    public function delete(User $user, Organization $organization): bool
    {
        return $this->update($user, $organization);
    }

    public function restore(User $user, Organization $organization): bool
    {
        return $this->update($user, $organization);
    }
}
