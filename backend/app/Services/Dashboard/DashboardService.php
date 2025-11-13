<?php

namespace App\Services\Dashboard;

use App\Models\{
    User,
    Property,
    Appointment,
    Payment,
    Expense,
    Invoice
};
use Illuminate\Support\Facades\{
    Auth,
    Cache,
    DB
};

class DashboardService
{

    /**
     * ---------------------------------------------------------------------
     * DASHBOARD CLIENT
     * ---------------------------------------------------------------------
     * Données centrées sur un utilisateur classique :
     * - Nombre de rendez-vous
     * - Derniers rendez-vous
     * - Total des paiements effectués
     * - Derniers paiements
     * - Factures récentes
     * ---------------------------------------------------------------------
     */
    public function clientSummary(): array
    {
        $user = Auth::user();

        if (! $user) {
            return [
                'role' => null,
                'appointments_count' => 0,
                'recent_appointments' => [],
                'payments_total' => 0.00,
                'recent_payments' => [],
                'recent_invoices' => [],
            ];
        }

        return Cache::remember("dashboard_client_{$user->id}", now()->addMinutes(2), function () use ($user) {

            $appointmentsCount = Appointment::where('user_id', $user->id)->count();

            $recentAppointments = Appointment::where('user_id', $user->id)
                ->latest()
                ->take(5)
                ->get();

            $paymentsTotal = Payment::where('user_id', $user->id)
                ->where('status', 'success')
                ->sum('amount');

            $recentPayments = Payment::where('user_id', $user->id)
                ->where('status', 'success')
                ->latest()
                ->take(5)
                ->get();

            $recentInvoices = Invoice::where('user_id', $user->id)
                ->latest()
                ->take(5)
                ->get();

            return [
                'role' => 'client',
                'appointments_count' => (int) $appointmentsCount,
                'recent_appointments' => $recentAppointments,
                'payments_total' => (float) number_format($paymentsTotal, 2, '.', ''),
                'recent_payments' => $recentPayments,
                'recent_invoices' => $recentInvoices,
            ];
        });
    }


    /**
     * ---------------------------------------------------------------------
     * DASHBOARD BAILLEUR
     * ---------------------------------------------------------------------
     * Données centrées sur le bailleur :
     * - Nombre de biens
     * - Nombre de rendez-vous liés aux biens
     * - Total des paiements perçus
     * - Total des dépenses
     * - Solde net
     * ---------------------------------------------------------------------
     */
    public function landlordSummary(): array
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

        return Cache::remember("dashboard_landlord_{$user->id}", now()->addMinutes(2), function () use ($user) {

            $propertyIds = Property::where('user_id', $user->id)->pluck('id');

            $propertiesCount = $propertyIds->count();

            $appointmentsCount = Appointment::whereIn('property_id', $propertyIds)->count();

            $totalPayments = Payment::whereHas('property', fn($q) =>
                    $q->where('user_id', $user->id)
                )
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
     * ---------------------------------------------------------------------
     * ADMIN DASHBOARD — VUE GLOBALE
     * ---------------------------------------------------------------------
     * - Statistiques globales
     * - Répartition utilisateurs
     * - Nombre total de biens
     * - Nombre total de rendez-vous
     * - Revenus globaux
     * - Dépenses globales
     * - Balance net
     * - Graphiques sur N jours
     * ---------------------------------------------------------------------
     */
    public function adminSummary(int $days = 30): array
    {
        $cacheKey = "dashboard_admin_{$days}d";

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($days) {

            $totalAdmins    = User::role('admin')->count();
            $totalBailleurs = User::role('bailleur')->count();
            $totalClients   = User::role('client')->count();

            $totalProperties   = Property::count();
            $totalAppointments = Appointment::count();

            $totalPayments = Payment::where('status', 'success')->sum('amount');
            $totalExpenses = Expense::sum('amount');

            $netBalance = $totalPayments - $totalExpenses;

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
                    'total_admins'    => (int) $totalAdmins,
                    'total_bailleurs' => (int) $totalBailleurs,
                    'total_clients'   => (int) $totalClients,
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
