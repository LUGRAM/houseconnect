<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Refund;

class RefundPolicy
{
    /**
     * Seuls les admins peuvent approuver un remboursement.
     */
    public function approve(User $user, Refund $refund): bool
    {
        return $user->hasRole('admin');
    }
}
