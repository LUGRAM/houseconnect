<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, User $target): bool
    {
        return $user->id === $target->id || $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, User $target): bool
    {
        // un admin peut tout modifier, un utilisateur seulement son profil
        return $user->hasRole('admin') || $user->id === $target->id;
    }

    public function delete(User $user, User $target): bool
    {
        // interdit la suppression de soi-même
        return $user->hasRole('admin') && $user->id !== $target->id;
    }
}
