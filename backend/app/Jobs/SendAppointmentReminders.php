<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Notifications\UpcomingAppointmentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendAppointmentReminders implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    /**
     * Nombre de tentatives max du job
     */
    public $tries = 3;

    /**
     * Timeout (secondes)
     */
    public $timeout = 20;

    public function __construct()
    {
        //
    }

    /**
     * Exécution du job
     */
    public function handle(): void
    {
        Log::info('Starting appointment reminder job...');

        Appointment::upcoming() // utilise ton scope
            ->with('user')
            ->chunk(50, function ($appointments) {

                foreach ($appointments as $appointment) {

                    if (! $appointment->user) {
                        continue;
                    }

                    // Envoi notification
                    $appointment->user->notify(
                        new UpcomingAppointmentNotification($appointment)
                    );

                    // Sécurise l’envoi unique
                    $appointment->update([
                        'reminder_sent_at' => now()
                    ]);

                    Log::info("Reminder sent for appointment #{$appointment->id}");
                }
            });

        Log::info('Appointment reminder job completed.');
    }
}
