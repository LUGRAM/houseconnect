<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';
}
