<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Property extends Model
{
    use HasFactory, LogsActivity;

    //Les attibuts assignables en masse.
    protected $fillable = [
        'title',
        'description',
        'price',
        'address',
        'city',
        'visit_price',
        'monthly_rent',
        'is_validated',
        'is_active',
        'user_id',
    ];

    //Les attributs castés automatiquement.
    protected $casts = [
        'is_validated' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'visit_price' => 'decimal:2',
        'monthly_rent' => 'decimal:2',
    ];

    //Configuration du logging d’activité (Spatie)
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'price', 'visit_price', 'is_validated', 'is_active'])
            ->useLogName('property')
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "La propriété a été {$eventName}");
    }

    //Relations Eloquent
    public function owner() { return $this->belongsTo(User::class, 'user_id'); }
    public function appointments() { return $this->hasMany(Appointment::class); }
    public function payments() { return $this->hasMany(Payment::class); }
    public function invoices() { return $this->hasMany(Invoice::class); }
    public function expenses() { return $this->hasMany(Expense::class); }

    // Boot du modèle pour valeurs par défaut.
    protected static function booted()
    {
    static::creating(function ($property) {
        $property->is_validated ??= false;
        $property->is_active ??= true;
    });
}

}
