<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\{Payment, Refund};
use App\Services\RefundService;

class RefundController extends Controller
{
    protected RefundService $refundService;

    public function __construct(RefundService $refundService)
    {
        $this->refundService = $refundService;
    }

    /**
     * 📌 Client ou bailleur — crée une demande de remboursement
     */
    public function requestRefund(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'reason'     => 'required|string|min:5|max:255',
        ]);

        $payment = Payment::find($request->payment_id);

        // Vérification : le paiement appartient bien à l’utilisateur
        if ($payment->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Paiement non autorisé pour remboursement.',
            ], 403);
        }

        // Déjà remboursé ?
        if ($payment->refunded) {
            return response()->json([
                'success' => false,
                'message' => 'Ce paiement a déjà été remboursé.',
            ], 400);
        }

        // Enregistrement de la demande
        $refund = Refund::create([
            'payment_id' => $payment->id,
            'amount'     => $payment->amount, // ou calcul partiel si applicable
            'reason'     => $request->reason,
            'status'     => 'pending',
            'requested_by' => $request->user()->id,
        ]);

        Log::info("Demande de remboursement créée", [
            'refund_id' => $refund->id,
            'user_id' => $request->user()->id,
            'payment_id' => $payment->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Demande de remboursement enregistrée.',
            'data'    => $refund,
        ]);
    }

    /**
     * 📌 Admin — approuve et exécute un remboursement
     */
    public function approve(Request $request, Refund $refund)
    {
        $this->authorize('approve-refund'); // policy ou gate admin-only

        if ($refund->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Ce remboursement a déjà été traité.',
            ], 400);
        }

        $payment = $refund->payment;

        try {
            $this->refundService->handleRefund(
                $payment,
                $refund->reason,
                $refund->amount
            );

            $refund->update([
                'status' => 'approved',
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Remboursement effectué avec succès.',
                'data'    => $refund,
            ]);

        } catch (\Exception $e) {
            Log::error("Erreur remboursement : " . $e->getMessage(), [
                'refund_id' => $refund->id,
            ]);

            $refund->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du remboursement.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin — liste tous les remboursements
     */
    public function index()
    {
        $refunds = Refund::with(['payment.user'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $refunds,
        ]);
    }
}
