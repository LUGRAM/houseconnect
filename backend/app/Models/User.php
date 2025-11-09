<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, LogsActivity;

    // Les attributs assignables en masse.
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'email_verified_at',
        'phone_verified_at',
    ];

    //Les attributs cachés dans les tableaux et JSON.
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Les attributs castés automatiquement.
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
    ];

    // Configuration du logging d’activité (Spatie)
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'phone'])
            ->useLogName('user')
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "L'utilisateur a été {$eventName}");
    }

    // Relations Eloquent
    public function properties() { return $this->hasMany(Property::class); }
    public function appointments() { return $this->hasMany(Appointment::class); }
    public function payments() { return $this->hasMany(Payment::class); }
    public function invoices() { return $this->hasMany(Invoice::class); }
    public function expenses() { return $this->hasMany(Expense::class); }


    // Mutateur pour hacher le mot de passe automatiquement.
    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = bcrypt($value);
        }
    }

}
