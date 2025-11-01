<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Payment;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'client', 'bailleur']);
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->hasRole('admin')
            || $payment->user_id === $user->id
            || optional($payment->property)->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('client');
    }

    public function update(User $user, Payment $payment): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $user->hasRole('admin');
    }
}
