<?php

namespace App\Services\Refunds;

use App\Models\Payment;
use App\Models\Appointment;
use App\Models\Refund;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Enums\AppointmentStatus;
use Carbon\Carbon;
use Exception;

class RefundService
{
    /**
     * Applique la logique Airbnb-like de remboursement.
     */
    public function handleRefund(Payment $payment, string $reason, ?float $forcedAmount = null)
    {
        if ($payment->status === PaymentStatus::REFUNDED) {
            throw new Exception('Paiement déjà remboursé.');
        }

        $amount = $forcedAmount ?? $this->calculateRefundAmount($payment);
        if ($amount <= 0) {
            return null; // Aucun remboursement applicable
        }

        // Simule appel au prestataire de paiement
        // $this->refundViaGateway($payment, $amount);

        // Crée un Refund logiquement
        $refund = Refund::create([
            'payment_id' => $payment->id,
            'amount' => $amount,
            'reason' => $reason,
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        $payment->update([
            'status' => PaymentStatus::REFUNDED,
        ]);

        return $refund;
    }

    /**
     * Calcule le montant remboursable selon la politique Airbnb adaptée.
     */
    private function calculateRefundAmount(Payment $payment): float
    {
        if ($payment->type === PaymentType::VISIT->value && $payment->appointment) {
            $appointment = $payment->appointment;
            $hoursBefore = Carbon::now()->diffInHours($appointment->scheduled_at, false);

            // Annulé par bailleur → remboursement total
            if ($appointment->cancelled_by === 'bailleur') {
                return $payment->amount;
            }

            // Annulé par client → politique de délai
            if ($appointment->cancelled_by === 'client') {
                if ($hoursBefore >= 24) {
                    return $payment->amount * 0.8; // 80%
                }
                return 0; // Non remboursé < 24h
            }
        }

        // Remboursements de loyer (rent)
        if ($payment->type === PaymentType::RENT->value) {
            return $payment->amount * 0.5; // 50 % par défaut si bail annulé avant entrée
        }

        return 0.0;
    }
}
