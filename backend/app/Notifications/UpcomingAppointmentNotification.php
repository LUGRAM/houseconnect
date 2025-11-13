<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UpcomingAppointmentNotification extends Notification
{
    use Queueable;

    public function __construct(public $appointment)
    {}

    public function via($notifiable)
    {
        return ['database']; // tu peux ajouter 'mail', 'sms', FCM push, etc.
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Rappel de rendez-vous',
            'message' => "Vous avez un rendez-vous demain à {$this->appointment->scheduled_at->format('H:i')}.",
            'appointment_id' => $this->appointment->id,
            'scheduled_at' => $this->appointment->scheduled_at,
        ];
    }
}
