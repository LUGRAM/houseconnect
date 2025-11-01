<?php

namespace App\Observers;

use App\Models\Payment;
use App\Models\Invoice;
use App\Notifications\PaymentSuccessNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class PaymentObserver
{
    /**
     * Lorsqu’un paiement est mis à jour.
     */
    public function updated(Payment $payment): void
    {
        // Si le statut passe à "success"
        if ($payment->wasChanged('status') && $payment->status === 'success') {

            // Création automatique d'une facture
            Invoice::firstOrCreate([
                'payment_id' => $payment->id,
            ], [
                'user_id'    => $payment->user_id,
                'amount'     => $payment->amount,
                'status'     => 'paid',
            ]);

            // Envoi de notification au client
            if ($payment->user) {
                Notification::send($payment->user, new PaymentSuccessNotification($payment));
            }

            Log::info('Paiement confirmé et facture générée', [
                'payment_id' => $payment->id,
                'user_id'    => $payment->user_id,
            ]);
        }

        // Si le statut passe à "failed"
        if ($payment->wasChanged('status') && $payment->status === 'failed') {
            Log::warning('Échec de paiement', [
                'payment_id' => $payment->id,
                'user_id'    => $payment->user_id,
            ]);
        }
    }
}
