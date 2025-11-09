<?php

namespace App\Observers;

use App\Models\{Payment, Invoice};
use App\Notifications\PaymentSuccessNotification;
use Illuminate\Support\Facades\{Log, Notification};

class PaymentObserver
{
    /**
     * Lorsqu’un paiement est mis à jour.
     */
    public function updated(Payment $payment): void
    {
        // Paiement validé
        if ($payment->wasChanged('status') && $payment->status === 'success') {

            // 1️Création automatique de la facture
            Invoice::firstOrCreate(
                ['payment_id' => $payment->id],
                [
                    'user_id' => $payment->user_id,
                    'amount'  => $payment->amount,
                    'status'  => 'paid',
                ]
            );

            // 2️Confirmation automatique du rendez-vous lié (si applicable)
            if ($payment->appointment_id && $payment->appointment) {
                $payment->appointment->update(['status' => 'confirmed']);

                Log::info('Rendez-vous confirmé automatiquement après paiement', [
                    'appointment_id' => $payment->appointment_id,
                ]);
            }

            // 3️Notification du client
            if ($payment->user) {
                Notification::send($payment->user, new PaymentSuccessNotification($payment));
            }

            // 4️Journalisation
            Log::info('Paiement confirmé et facture générée', [
                'payment_id' => $payment->id,
                'user_id'    => $payment->user_id,
            ]);
        }

        // Paiement échoué
        if ($payment->wasChanged('status') && $payment->status === 'failed') {
            // Si le paiement était lié à un rendez-vous → repasser le statut à “pending”
            if ($payment->appointment_id && $payment->appointment) {
                $payment->appointment->update(['status' => 'pending']);

                Log::warning('Paiement échoué, rendez-vous repassé en attente', [
                    'appointment_id' => $payment->appointment_id,
                ]);
            }

            Log::warning('Échec de paiement', [
                'payment_id' => $payment->id,
                'user_id'    => $payment->user_id,
            ]);
        }

        // 🧾 (Optionnel) Gestion d’un remboursement
        if ($payment->wasChanged('status') && $payment->status === 'refunded') {
            if ($payment->appointment_id && $payment->appointment) {
                $payment->appointment->update(['status' => 'cancelled']);

                Log::info('Paiement remboursé, rendez-vous annulé', [
                    'appointment_id' => $payment->appointment_id,
                ]);
            }
        }
    }
}
