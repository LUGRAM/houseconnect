<?php

namespace App\Services\Notifications;

use App\Models\{Appointment, User, DeviceToken};
use Illuminate\Support\Facades\{Log, Mail, Http};
use App\Mail\AppointmentReminderMail;

class NotificationService
{
    /**
     * Envoie un rappel de rendez-vous
     */
    public function sendAppointmentReminder(Appointment $appointment): void
    {
        $user = $appointment->user ?? $appointment->client ?? null;
        if (! $user) {
            Log::warning('Appointment without user', ['appointment_id' => $appointment->id]);
            return;
        }

        // 1️⃣ Push Notification
        $this->sendPushNotification(
            $user,
            'Rappel de rendez-vous',
            "Vous avez un rendez-vous prévu le " . $appointment->scheduled_at->format('d/m/Y H:i')
        );

        // 2️⃣ Mail Notification
        if ($user->email) {
            Mail::to($user->email)->queue(new AppointmentReminderMail($appointment));
        }

        // 3️⃣ Log fallback
        Log::info('Appointment reminder sent', [
            'appointment_id' => $appointment->id,
            'user_id' => $user->id,
            'user_email' => $user->email,
        ]);
    }

    /**
     * Envoie une notification push à l’utilisateur
     */
    public function sendPushNotification(User $user, string $title, string $message): void
    {
        $tokens = DeviceToken::where('user_id', $user->id)->pluck('token')->filter()->toArray();

        if (empty($tokens)) {
            Log::info('No device tokens for user', ['user_id' => $user->id]);
            return;
        }

        $provider = config('notifications.provider', 'onesignal');

        match ($provider) {
            'onesignal' => $this->sendViaOneSignal($tokens, $title, $message),
            'fcm'       => $this->sendViaFirebase($tokens, $title, $message),
            default     => Log::warning('Unknown notification provider', ['provider' => $provider]),
        };
    }

    /**
     * Envoi via OneSignal API
     */
    protected function sendViaOneSignal(array $tokens, string $title, string $message): void
    {
        $appId = config('services.onesignal.app_id');
        $apiKey = config('services.onesignal.api_key');

        if (! $appId || ! $apiKey) {
            Log::warning('OneSignal config missing');
            return;
        }

        $response = Http::withHeaders([
            'Authorization' => "Basic {$apiKey}",
            'Content-Type'  => 'application/json',
        ])->post('https://onesignal.com/api/v1/notifications', [
            'app_id' => $appId,
            'include_player_ids' => $tokens,
            'headings' => ['en' => $title],
            'contents' => ['en' => $message],
        ]);

        Log::info('OneSignal push sent', ['response' => $response->json()]);
    }

    /**
     * Envoi via Firebase Cloud Messaging
     */
    protected function sendViaFirebase(array $tokens, string $title, string $message): void
    {
        $serverKey = config('services.fcm.server_key');

        if (! $serverKey) {
            Log::warning('Firebase server key missing');
            return;
        }

        $response = Http::withHeaders([
            'Authorization' => "key={$serverKey}",
            'Content-Type'  => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', [
            'registration_ids' => $tokens,
            'notification' => [
                'title' => $title,
                'body'  => $message,
            ],
        ]);

        Log::info('Firebase push sent', ['response' => $response->json()]);
    }
}
