<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\PaymentStatus;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'property_id',
        'amount',
        'type',
        'status',
        'provider',
        'provider_ref',
        'hmac_signature',
        'fees',
    ];

    protected $casts = [
        'status' => PaymentStatus::class,
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['amount', 'status', 'type', 'provider'])
            ->useLogName('payment')
            ->logOnlyDirty();
    }

    public function user() { return $this->belongsTo(User::class); }
    public function property() { return $this->belongsTo(Property::class); }
    public function invoice() { return $this->hasOne(Invoice::class); }
}
