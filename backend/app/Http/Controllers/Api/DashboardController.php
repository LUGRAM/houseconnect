<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Property;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\Appointment;

class DashboardController extends Controller
{
    public function summary()
    {
        $user = Auth::user();

        $propertiesCount = Property::where('user_id', $user->id)->count();
        $totalPayments = Payment::where('user_id', $user->id)->sum('amount');
        $totalExpenses = Expense::where('user_id', $user->id)->sum('amount');
        $appointmentsCount = Appointment::where('user_id', $user->id)->count();

        return response()->json([
            'status'  => true,
            'message' => 'Tableau de bord utilisateur.',
            'data'    => [
                'properties_count' => $propertiesCount,
                'appointments_count' => $appointmentsCount,
                'total_payments' => $totalPayments,
                'total_expenses' => $totalExpenses,
                'balance' => $totalPayments - $totalExpenses,
            ],
        ]);
    }
}
