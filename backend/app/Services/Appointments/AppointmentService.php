<?php

namespace App\Services\Appointments;

use App\Models\Appointment;
use App\Models\Property;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class AppointmentService
{
    public function create(int $propertyId, string $scheduledAt): Appointment
    {
        $property = Property::findOrFail($propertyId);

        if (! $property->is_validated) {
            throw new Exception("Ce bien n’est pas encore validé par l’administration.");
        }

        $appointment = Appointment::create([
            'user_id'     => Auth::id(),
            'property_id' => $property->id,
            'scheduled_at'=> Carbon::parse($scheduledAt),
            'status'      => 'pending',
        ]);

        Log::info('Rendez-vous créé', [
            'user_id' => Auth::id(),
            'property_id' => $propertyId,
        ]);

        return $appointment;
    }

    public function cancel(int $appointmentId): void
    {
        $appointment = Appointment::where('user_id', Auth::id())->findOrFail($appointmentId);

        $appointment->update(['status' => 'cancelled']);

        Log::info('Rendez-vous annulé', ['appointment_id' => $appointmentId]);
    }
}
