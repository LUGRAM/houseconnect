<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Property extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'price',
        'address',
        'city',
        'visit_price',
        'is_validated',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'price', 'visit_price', 'is_validated'])
            ->useLogName('property')
            ->logOnlyDirty();
    }

    public function owner() { return $this->belongsTo(User::class, 'user_id'); }
    public function appointments() { return $this->hasMany(Appointment::class); }
    public function payments() { return $this->hasMany(Payment::class); }
    public function invoices() { return $this->hasMany(Invoice::class); }
    public function expenses() { return $this->hasMany(Expense::class); }
}
