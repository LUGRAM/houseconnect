<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasApiTokens, HasRoles, Notifiable, LogsActivity, HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'phone'])
            ->useLogName('user')
            ->logOnlyDirty();
    }

    // Relations
    public function properties() { return $this->hasMany(Property::class); }
    public function appointments() { return $this->hasMany(Appointment::class); }
    public function payments() { return $this->hasMany(Payment::class); }
    public function invoices() { return $this->hasMany(Invoice::class); }
    public function expenses() { return $this->hasMany(Expense::class); }
}
