<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;


class Appointment extends Model
{
    use HasFactory, LogsActivity;

    // Les attributs assignables en masse.
    protected $fillable = [
        'user_id',
        'property_id',
        'payment_id',
        'scheduled_at',
        'status',
        'reminder_sent_at',
    ];

    //Les attributs castés automatiquement.
    protected $casts = [
        'scheduled_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'status' => AppointmentStatus::class,
    ];

    //Configuration du logging d’activité (Spatie).
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'scheduled_at'])
            ->useLogName('appointment')
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Le rendez-vous a été {$eventName}");
    }

    //Relations Eloquent.
    public function client(){return $this->belongsTo(User::class, 'user_id');}
    public function property(){return $this->belongsTo(Property::class, 'property_id');}
    public function payment(){return $this->belongsTo(Payment::class, 'payment_id');}

    //Scopes Eloquent.
    public function scopeUpcoming(Builder $query): Builder
    {
        $start = now();
        $end = (clone $start)->addDay();

        return $query
            ->whereNull('reminder_sent_at')
            ->where('status', AppointmentStatus::CONFIRMED)
            ->whereBetween('scheduled_at', [$start, $end]);
    }


    //Méthode utilitaire : marquer comme rappelé.
    public function markAsReminded(): void{$this->update(['reminder_sent_at' => now()]);}

    //Boot du modèle : statut par défaut.
    protected static function booted()
    {
        static::creating(function ($appointment) {
            if (is_null($appointment->status)) {
                $appointment->status = AppointmentStatus::PENDING;
            }
        });
    }
}
