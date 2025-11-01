<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Property;

class PropertyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'bailleur', 'client']);
    }

    public function view(User $user, Property $property): bool
    {
        return $user->hasRole('admin')
            || $property->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'bailleur']);
    }

    public function update(User $user, Property $property): bool
    {
        return $user->hasRole('admin')
            || $property->user_id === $user->id;
    }

    public function delete(User $user, Property $property): bool
    {
        return $user->hasRole('admin')
            || $property->user_id === $user->id;
    }

    public function validateProperty(User $user, Property $property): bool
    {
        return $user->hasRole('admin');
    }
}
