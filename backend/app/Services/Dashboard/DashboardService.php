<?php

namespace App\Services\Dashboard;

use App\Models\{User, Property, Payment, Expense};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    /**
     * Résumé des statistiques du tableau de bord utilisateur.
     * 
     * @return array
     */
    public function summary(): array
    {
        $user = Auth::user();

        // Utilisation d’un cache court pour réduire les requêtes
        return Cache::remember("dashboard_summary_user_{$user->id}", now()->addMinutes(2), function () use ($user) {
            $properties = Property::where('user_id', $user->id)->count();

            $payments = Payment::where('user_id', $user->id)
                ->where('status', 'success')
                ->sum('amount');

            $expenses = Expense::whereHas('property', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->sum('amount');

            $balance = (float) $payments - (float) $expenses;

            // Casts explicites pour éviter les erreurs de typage dans les tests
            return [
                'properties' => (int) $properties,
                'payments'   => (float) number_format($payments, 2, '.', ''),
                'expenses'   => (float) number_format($expenses, 2, '.', ''),
                'balance'    => (float) number_format($balance, 2, '.', ''),
            ];
        });
    }

    /**
     * Statistiques globales (admin)
     * 
     * @return array
     */
    public function globalSummary(): array
    {
        return Cache::remember('dashboard_summary_global', now()->addMinutes(5), function () {
            $properties = Property::count();
            $payments   = Payment::where('status', 'success')->sum('amount');
            $expenses   = Expense::sum('amount');

            return [
                'total_users'     => (int) User::count(),
                'total_properties'=> (int) $properties,
                'total_payments'  => (float) $payments,
                'total_expenses'  => (float) $expenses,
                'revenue'         => (float) ($payments - $expenses),
            ];
        });
    }
}
