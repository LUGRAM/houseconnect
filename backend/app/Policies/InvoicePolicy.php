<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Invoice;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'bailleur', 'client']);
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->hasRole('admin')
            || $invoice->user_id === $user->id
            || optional($invoice->property)->user_id === $user->id;
    }

    public function validate(User $user, Invoice $invoice): bool
    {
        return $user->hasRole('comptable') || $user->hasRole('admin');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->hasRole('admin');
    }
}
