<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Appointment;
use App\Services\Notifications\NotificationService;

class SendAppointmentReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function handle(NotificationService $notificationService): void
    {
        $appointments = Appointment::needingReminder()->get();

        Log::info('Appointment reminders processing', ['count' => $appointments->count()]);

        foreach ($appointments as $appointment) {
            try {
                $notificationService->sendAppointmentReminder($appointment);

                Log::info(' Reminder sent', [
                    'appointment_id' => $appointment->id,
                    'client_id'      => $appointment->client_id,
                ]);
            } catch (\Throwable $e) {
                Log::error('Reminder failed', [
                    'appointment_id' => $appointment->id,
                    'error'          => $e->getMessage(),
                ]);
            }
        }
    }
}
