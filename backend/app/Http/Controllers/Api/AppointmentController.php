<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Property;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::with('property')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return response()->json([
            'status'  => true,
            'message' => 'Rendez-vous récupérés.',
            'data'    => $appointments,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id'  => 'required|exists:properties,id',
            'scheduled_at' => 'required|date|after:now',
        ]);

        $property = Property::findOrFail($validated['property_id']);

        $appointment = Appointment::create([
            'user_id' => Auth::id(),
            'property_id' => $property->id,
            'scheduled_at' => $validated['scheduled_at'],
            'status' => 'pending',
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Rendez-vous créé. Paiement requis avant validation.',
            'data'    => $appointment,
        ]);
    }

    public function cancel($id)
    {
        $appointment = Appointment::where('user_id', Auth::id())->findOrFail($id);

        $appointment->update(['status' => 'cancelled']);

        return response()->json([
            'status'  => true,
            'message' => 'Rendez-vous annulé.',
        ]);
    }
}
