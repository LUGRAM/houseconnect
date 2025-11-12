<?php

namespace App\Services\Dashboard;

use App\Models\{User, Property, Appointment, Payment, Expense};
use Illuminate\Support\Facades\{Auth, Cache, DB};

class DashboardService
{
    /**
     * Résumé du tableau de bord pour un bailleur (utilisateur standard).
     */
    public function summary(): array
    {
        $user = Auth::user();

        if (! $user) {
            return [
                'role' => null,
                'properties_count'   => 0,
                'appointments_count' => 0,
                'total_payments'     => 0.00,
                'total_expenses'     => 0.00,
                'balance'            => 0.00,
            ];
        }

        return Cache::remember("dashboard_summary_user_{$user->id}", now()->addMinutes(2), function () use ($user) {
            $propertyIds = Property::where('user_id', $user->id)->pluck('id');
            $propertiesCount = $propertyIds->count();
            $appointmentsCount = Appointment::whereIn('property_id', $propertyIds)->count();

            $totalPayments = Payment::whereHas('property', fn($q) => $q->where('user_id', $user->id))
                ->where('status', 'success')
                ->sum('amount');

            $totalExpenses = Expense::where('user_id', $user->id)->sum('amount');
            $balance = $totalPayments - $totalExpenses;

            return [
                'role' => 'bailleur',
                'properties_count'   => (int) $propertiesCount,
                'appointments_count' => (int) $appointmentsCount,
                'total_payments'     => (float) number_format($totalPayments, 2, '.', ''),
                'total_expenses'     => (float) number_format($totalExpenses, 2, '.', ''),
                'balance'            => (float) number_format($balance, 2, '.', ''),
            ];
        });
    }

    /**
     * Résumé global du tableau de bord administrateur.
     *
     * @param int $days  Nombre de jours à inclure dans les statistiques (par défaut 30)
     */
    public function globalSummary(int $days = 30): array
    {
        // clé de cache dépend de la période
        $cacheKey = "dashboard_summary_global_{$days}d";

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($days) {
            $totalAdmins    = User::role('admin')->count();
            $totalBailleurs = User::role('bailleur')->count();
            $totalClients   = User::role('client')->count();

            $totalProperties   = Property::count();
            $totalAppointments = Appointment::count();
            $totalPayments     = Payment::where('status', 'success')->sum('amount');
            $totalExpenses     = Expense::sum('amount');
            $netBalance        = $totalPayments - $totalExpenses;

            // Période dynamique
            $recentPayments = Payment::select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(amount) as total')
                )
                ->where('status', 'success')
                ->where('created_at', '>=', now()->subDays($days))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $recentUsers = User::select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as total')
                )
                ->where('created_at', '>=', now()->subDays($days))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            return [
                'role' => 'admin',
                'period_days' => $days,
                'users' => [
                    'total_admins'   => (int) $totalAdmins,
                    'total_bailleurs'=> (int) $totalBailleurs,
                    'total_clients'  => (int) $totalClients,
                ],
                'properties' => [
                    'total' => (int) $totalProperties,
                ],
                'appointments' => [
                    'total' => (int) $totalAppointments,
                ],
                'payments' => [
                    'total_success' => (float) number_format($totalPayments, 2, '.', ''),
                ],
                'expenses' => [
                    'total' => (float) number_format($totalExpenses, 2, '.', ''),
                ],
                'balance' => [
                    'net' => (float) number_format($netBalance, 2, '.', ''),
                ],
                'charts' => [
                    'payments_last_period'      => $recentPayments,
                    'registrations_last_period' => $recentUsers,
                ],
            ];
        });
    }
}
