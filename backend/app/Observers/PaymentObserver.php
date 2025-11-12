<?php

namespace App\Observers;

use App\Models\Payment;
use App\Notifications\PaymentSuccessNotification;
use Illuminate\Support\Facades\{Log, Notification};

class PaymentObserver
{
    /**
     * Déclenché lorsqu’un paiement est mis à jour.
     */
    public function updated(Payment $payment): void
    {
        // Vérifie uniquement si le statut a changé
        if (! $payment->wasChanged('status')) {
            return;
        }

        // Paiement validé
        if ($payment->status === 'success') {

            // 1. Confirmation automatique du rendez-vous lié (si applicable)
            if ($payment->appointment()->exists()) {
                $appointment = $payment->appointment;
                $appointment->update(['status' => 'confirmed']);

                Log::info('Rendez-vous confirmé automatiquement après paiement', [
                    'appointment_id' => $appointment->id,
                    'payment_id'     => $payment->id,
                ]);
            }

            // 2. Notification du client
            if ($payment->user) {
                Notification::send($payment->user, new PaymentSuccessNotification($payment));
            }

            // 3. Journalisation
            Log::info('Paiement confirmé', [
                'payment_id' => $payment->id,
                'user_id'    => $payment->user_id,
            ]);
        }

        // Paiement échoué
        elseif ($payment->status === 'failed') {
            if ($payment->appointment()->exists()) {
                $appointment = $payment->appointment;
                $appointment->update(['status' => 'pending']);

                Log::warning('Paiement échoué, rendez-vous repassé en attente', [
                    'appointment_id' => $appointment->id,
                    'payment_id'     => $payment->id,
                ]);
            }

            Log::warning('Échec de paiement', [
                'payment_id' => $payment->id,
                'user_id'    => $payment->user_id,
            ]);
        }

        // Paiement remboursé 
        elseif ($payment->status === 'refunded') {
            if ($payment->appointment()->exists()) {
                $appointment = $payment->appointment;
                $appointment->update(['status' => 'cancelled']);

                Log::info('Paiement remboursé, rendez-vous annulé', [
                    'appointment_id' => $appointment->id,
                    'payment_id'     => $payment->id,
                ]);
            }
        }
    }
}
