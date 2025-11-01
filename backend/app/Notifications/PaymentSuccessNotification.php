<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use App\Models\Payment;

class PaymentSuccessNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Payment $payment;

    /**
     * Crée une nouvelle instance de notification.
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    /**
     * Canaux de notification.
     */
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Message email.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Paiement confirmé - HouseConnect')
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Votre paiement d’un montant de **{$this->payment->amount} XAF** a été confirmé.")
            ->line('Détails du paiement :')
            ->line("Référence : {$this->payment->provider_ref}")
            ->line("Date : " . $this->payment->created_at->format('d/m/Y H:i'))
            ->action('Voir ma facture', url("/payments/{$this->payment->id}"))
            ->line('Merci d’avoir utilisé HouseConnect.');
    }

    /**
     * Enregistrement en base.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'payment_id'   => $this->payment->id,
            'amount'       => $this->payment->amount,
            'provider'     => $this->payment->provider,
            'provider_ref' => $this->payment->provider_ref,
            'status'       => $this->payment->status,
            'message'      => 'Votre paiement a été confirmé.',
        ];
    }

    /**
     * (Optionnel) Message pour broadcast ou temps réel (si Pusher/WebSocket est activé).
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }

    /**
     * Identifiant unique.
     */
    public function id(): string
    {
        return 'payment-' . $this->payment->id;
    }
}
