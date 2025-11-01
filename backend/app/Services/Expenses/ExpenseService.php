<?php

namespace App\Services\Expenses;

use App\Models\Expense;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ExpenseService
{
    public function list(): array
    {
        return Expense::where('user_id', Auth::id())
            ->with('property')
            ->latest()
            ->get()
            ->toArray();
    }

    public function create(array $data): Expense
    {
        $data['user_id'] = Auth::id();
        $expense = Expense::create($data);

        Log::info('Nouvelle dépense ajoutée', ['expense_id' => $expense->id]);

        return $expense;
    }

    public function update(Expense $expense, array $data): Expense
    {
        if ($expense->user_id !== Auth::id()) {
            abort(403, 'Accès refusé.');
        }

        $expense->update($data);

        return $expense;
    }

    public function delete(Expense $expense): void
    {
        if ($expense->user_id !== Auth::id()) {
            abort(403, 'Accès refusé.');
        }

        $expense->delete();
    }
}
