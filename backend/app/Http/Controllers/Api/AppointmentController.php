<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Appointment, Property};
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    /**
     * Liste les rendez-vous du client connecté.
     */
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

    /**
     * Crée un rendez-vous pour le client connecté.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id'  => 'required|exists:properties,id',
            'scheduled_at' => 'required|date|after:now',
        ]);

        // Vérifie que le bien est validé avant d'autoriser un rendez-vous
        $property = Property::where('id', $validated['property_id'])
            ->where('is_validated', true)
            ->first();

        if (! $property) {
            return response()->json([
                'status'  => false,
                'message' => 'Impossible de prendre rendez-vous sur un bien non validé.',
            ], 422);
        }

        $appointment = Appointment::create([
            'user_id'     => Auth::id(),
            'property_id' => $property->id,
            'scheduled_at'=> $validated['scheduled_at'],
            'status'      => 'pending',
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Rendez-vous créé. Paiement requis avant validation.',
            'data'    => [
                'appointment' => $appointment,
                'visit_price' => $property->visit_price ?? 0,
            ],
        ]);
    }

    /**
     * Annule un rendez-vous du client connecté.
     */
    public function cancel($id)
    {
        $appointment = Appointment::where('user_id', Auth::id())->findOrFail($id);

        if ($appointment->status === 'cancelled') {
            return response()->json([
                'status'  => false,
                'message' => 'Ce rendez-vous est déjà annulé.',
            ], 400);
        }

        $appointment->update(['status' => 'cancelled']);

        return response()->json([
            'status'  => true,
            'message' => 'Rendez-vous annulé.',
        ]);
    }

    /**
     * Liste des rendez-vous sur les propriétés du bailleur connecté.
     * (optionnel, usage côté propriétaire)
     */
    public function owned()
    {
        $user = Auth::user();

        $appointments = Appointment::with('property')
            ->whereHas('property', fn($q) => $q->where('user_id', $user->id))
            ->latest()
            ->get();

        return response()->json([
            'status'  => true,
            'message' => 'Rendez-vous sur vos biens récupérés.',
            'data'    => $appointments,
        ]);
    }

     /**
     * Afficher le détail d’un rendez-vous
     */
    public function show(Appointment $appointment)
    {
        $user = Auth::user();

        // Vérifie si l’utilisateur est le client du RDV
        $isClient = $appointment->user_id === $user->id;

        // Vérifie si l’utilisateur est le bailleur du bien
        $isBailleur = $appointment->property && $appointment->property->user_id === $user->id;

        if (!($isClient || $isBailleur)) {
            return response()->json([
                'status'  => false,
                'message' => 'Accès non autorisé à ce rendez-vous.',
            ], 403);
        }

        // Charger les relations utiles (property, payment)
        $appointment->load(['property', 'payment', 'client']);

        return response()->json([
            'status'  => true,
            'message' => 'Détail du rendez-vous récupéré avec succès.',
            'data'    => $appointment,
        ]);
    }
}
