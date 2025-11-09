<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Appointment;
use App\Services\Payments\CinetPayService;
use Carbon\Carbon;
use App\Enums\{AppointmentStatus,PaymentStatus};

class PaymentController extends Controller
{
    protected $cinetPayService;

    public function __construct(CinetPayService $cinetPayService)
    {
        $this->cinetPayService = $cinetPayService;
    }

    /**
     * Liste des paiements de l’utilisateur connecté
     */
    public function index()
    {
        $payments = Payment::where('user_id', Auth::id())
            ->with('property')
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data' => $payments,
        ]);
    }

    /**
     * Initiation d’un paiement via CinetPay
     */
    public function initiate(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:100',
            'property_id' => 'nullable|exists:properties,id',
        ]);

        // Vérification cohérence du montant si un bien est lié
        if (!empty($validated['property_id'])) {
            $property = Property::findOrFail($validated['property_id']);

            if ($validated['amount'] != $property->visit_price) {
                throw ValidationException::withMessages([
                    'amount' => 'Le montant ne correspond pas au tarif de visite de ce bien.',
                ]);
            }
        }

        // Création du paiement
        $payment = Payment::create([
            'user_id'      => Auth::id(),
            'property_id'  => $validated['property_id'] ?? null,
            'amount'       => $validated['amount'],
            'status'       => 'pending',
            'provider'     => 'cinetpay',
            'provider_ref' => null,
        ]);

        // Appel au service CinetPay
        $response = $this->cinetPayService->initiatePayment($payment);

        if (!isset($response['status']) || $response['status'] !== true) {
            $payment->update(['status' => 'failed']);
            return response()->json([
                'status' => false,
                'message' => 'Erreur lors de l’initiation du paiement.',
            ], 500);
        }

        $payment->update([
            'provider_ref' => $response['transaction_id'] ?? null,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Paiement initié avec succès.',
            'payment_id' => $payment->id,
            'amount' => $payment->amount,
            'transaction_ref' => $payment->provider_ref,
            'payment_url' => $response['payment_url'],
        ]);
    }

    /**
     * Webhook de confirmation CinetPay
     */
    public function webhook(Request $request)
    {
        $payload = $request->all();

        // Vérification signature HMAC SHA256
        $signature = $request->header('X-Signature');
        $secretKey = config('services.cinetpay.secret_key');
        $computed = hash_hmac('sha256', json_encode($payload), $secretKey);

        if ($signature !== $computed) {
            Log::warning('Webhook CinetPay: signature invalide.');
            return response()->json(['message' => 'Signature invalide'], 401);
        }

        $transactionId = $payload['transaction_id'] ?? null;
        if (! $transactionId) {
            return response()->json(['message' => 'Transaction ID manquant'], 400);
        }

        $payment = Payment::where('provider_ref', $transactionId)->first();
        if (! $payment) {
            return response()->json(['message' => 'Paiement introuvable'], 404);
        }

        // Mapping des statuts CinetPay → interne
        $status = strtolower($payload['status'] ?? '');
        $mappedStatus = match ($status) {
            'accepted', 'success' => 'success',
            'refused', 'failed'   => 'failed',
            'cancelled'           => 'cancelled',
            default               => 'pending',
        };

        $payment->update(['status' => $mappedStatus]);

        Log::info("Webhook reçu pour paiement #{$payment->id} — statut: {$mappedStatus}");

        // Si paiement réussi → confirmation du rendez-vous associé
        if ($mappedStatus === 'success' && $payment->property_id) {
            $appointment = Appointment::where('property_id', $payment->property_id)
                ->where('user_id', $payment->user_id)
                ->where('status', 'pending')
                ->whereNull('payment_id')
                ->first();

            if ($appointment) {
                $appointment->update([
                    'status' => 'confirmed',
                    'payment_id' => $payment->id,
                ]);
            }
        }

        return response()->json(['message' => 'Webhook traité avec succès'], 200);
    }

    /**
     * Détail d’un paiement
     */
    public function show($id)
    {
        $payment = Payment::where('id', $id)
            ->where('user_id', Auth::id())
            ->with(['property', 'invoice'])
            ->firstOrFail();

        return response()->json([
            'status' => true,
            'data' => $payment,
        ]);
    }

    /**
     * (Optionnel) Vue revenus bailleur
     */
    public function landlordPayments()
    {
        $payments = Payment::whereHas('property', function ($q) {
            $q->where('user_id', Auth::id());
        })
        ->with('property')
        ->latest()
        ->get();

        return response()->json([
            'status' => true,
            'data' => $payments,
        ]);
    }

    public function webhook2(Request $request)
    {
        $reference = $request->input('transaction_id');
        $payment = Payment::where('provider_ref', $reference)->first();

        if (! $payment) {
            return response()->json(['message' => 'Paiement introuvable'], 404);
        }

        $payment->update(['status' => PaymentStatus::SUCCESS]);

        // Mise à jour du rendez-vous
        $appointment = $payment->appointment;
        if ($appointment) {
            $appointment->update(['status' => AppointmentStatus::PAID]);
        }

        return response()->json(['message' => 'Paiement confirmé et mis en attente de validation.']);
    }

    public function confirmVisit($appointmentId)
    {
        $appointment = Appointment::findOrFail($appointmentId);

        if (Auth::id() !== $appointment->property->user_id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $appointment->update(['status' => AppointmentStatus::CONFIRMED]);

        return response()->json(['message' => 'Visite validée, paiement libéré.']);
    }

    public function cancelAppointment(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        $user = $request->user();

        $cancelledBy = $user->hasRole('bailleur') ? 'bailleur' : 'client';
        $appointment->update([
            'status' => AppointmentStatus::CANCELLED,
            'cancelled_by' => $cancelledBy,
        ]);

        $payment = $appointment->payment;

        app(\App\Services\RefundService::class)
            ->handleRefund($payment, "Annulation par {$cancelledBy}");

        return response()->json(['message' => 'Rendez-vous annulé et remboursement traité.']);
    }

}
