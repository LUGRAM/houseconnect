<?php

namespace App\Policies;

use App\Models\User;

class SystemSettingPolicy
{
    public function view(User $user): bool
    {
        // tout utilisateur authentifié peut lire, seul l’admin modifie
        return $user->exists;
    }

    public function update(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function reset(User $user): bool
    {
        // facultatif : seul un super-admin (ou admin unique)
        return $user->hasRole('admin');
    }
}
