<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'property_id',
        'payment_id',
        'status',
        'scheduled_at',
    ];

    protected $casts = [
        'status' => AppointmentStatus::class,
        'scheduled_at' => 'datetime',
    ];

    public function client() { return $this->belongsTo(User::class, 'user_id'); }
    public function property() { return $this->belongsTo(Property::class); }
    public function payment() { return $this->belongsTo(Payment::class); }
}
