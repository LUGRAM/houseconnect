<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Appointment;

class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'client', 'bailleur']);
    }

    public function view(User $user, Appointment $appointment): bool
    {
        return $user->hasRole('admin')
            || $appointment->user_id === $user->id
            || $appointment->property->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('client');
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return $user->hasRole('admin')
            || $appointment->user_id === $user->id;
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        return $user->hasRole('admin')
            || $appointment->user_id === $user->id;
    }

    public function validateVisit(User $user, Appointment $appointment): bool
    {
        return $user->hasRole('admin');
    }
}
