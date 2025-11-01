<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Expense;

class ExpensePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'client', 'bailleur']);
    }

    public function view(User $user, Expense $expense): bool
    {
        return $user->hasRole('admin')
            || $expense->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'client']);
    }

    public function update(User $user, Expense $expense): bool
    {
        return $user->hasRole('admin')
            || $expense->user_id === $user->id;
    }

    public function delete(User $user, Expense $expense): bool
    {
        return $user->hasRole('admin')
            || $expense->user_id === $user->id;
    }
}
