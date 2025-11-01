<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class UserObserver
{
    /**
     * Lorsqu’un utilisateur est créé.
     */
    public function created(User $user): void
    {
        // Attribution du rôle par défaut
        if (! $user->hasAnyRole(['admin', 'bailleur', 'client'])) {
            $user->assignRole('client');
        }

        Log::info('Nouvel utilisateur créé', [
            'id' => $user->id,
            'email' => $user->email,
            'role' => $user->getRoleNames()->first(),
        ]);
    }

    /**
     * Lorsqu’un utilisateur est supprimé.
     */
    public function deleted(User $user): void
    {
        Log::warning('Utilisateur supprimé', [
            'id' => $user->id,
            'email' => $user->email,
        ]);
    }
}
