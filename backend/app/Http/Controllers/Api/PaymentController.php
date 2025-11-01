<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use Illuminate\Support\Facades\{Auth, Log};
use App\Services\Payments\CinetPayService;
use App\Enums\PaymentStatus;
use Exception;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::where('user_id', Auth::id())
            ->with('property')
            ->latest()
            ->get();

        return response()->json([
            'status'  => true,
            'message' => 'Liste des paiements récupérée.',
            'data'    => $payments,
        ]);
    }

    public function initiate(Request $request, CinetPayService $cinetpay)
    {
        $validated = $request->validate([
            'amount'      => 'required|numeric|min:100',
            'property_id' => 'nullable|integer|exists:properties,id',
        ]);

        $payment = Payment::create([
            'user_id'     => Auth::id(),
            'property_id' => $validated['property_id'] ?? null,
            'amount'      => $validated['amount'],
            'status'      => PaymentStatus::PENDING->value,
            'provider'    => 'cinetpay',
        ]);

        try {
            $response = $cinetpay->initiatePayment($payment);
        } catch (Exception $e) {
            Log::error('Erreur CinetPay', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Erreur CinetPay : '.$e->getMessage()], 500);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Paiement initié avec succès.',
            'data'    => [
                'payment_id'   => $payment->id,
                'amount'       => $payment->amount,
                'provider_ref' => $payment->provider_ref,
                'payment_url'  => $payment->payment_url,
            ],
        ]);
    }

    public function webhook(Request $request)
    {
        // Vérification HMAC basique
        $secret = config('services.cinetpay.api_key');
        $payload = json_encode($request->all());
        $received = $request->header('x-signature');
        $computed = hash_hmac('sha256', $payload, $secret);

        if ($received !== $computed) {
            Log::warning('Webhook signature invalide', ['payload' => $payload]);
            return response()->json(['status' => false, 'message' => 'Signature HMAC invalide'], 401);
        }

        $transactionId = $request->input('transaction_id');
        if (! $transactionId) {
            return response()->json(['status' => false, 'message' => 'Transaction ID manquant.'], 400);
        }

        $payment = Payment::where('provider_ref', $transactionId)->first();
        if (! $payment) {
            return response()->json(['status' => false, 'message' => 'Paiement non trouvé.'], 404);
        }

        $status = strtolower($request->input('status', 'pending'));
        $mapped = match ($status) {
            'accepted', 'success' => PaymentStatus::SUCCESS->value,
            'refused', 'failed'   => PaymentStatus::FAILED->value,
            'cancelled'           => PaymentStatus::CANCELLED->value,
            default               => PaymentStatus::PENDING->value,
        };

        $payment->update(['status' => $mapped]);
        Log::info('Webhook CinetPay traité', ['transaction_id' => $transactionId, 'status' => $mapped]);

        return response()->json(['status' => true, 'message' => 'Webhook traité.']);
    }

    public function show($id)
    {
        $payment = Payment::where('user_id', Auth::id())
            ->with('property')
            ->findOrFail($id);

        return response()->json([
            'status'  => true,
            'message' => 'Détails du paiement récupérés.',
            'data'    => $payment,
        ]);
    }
}
