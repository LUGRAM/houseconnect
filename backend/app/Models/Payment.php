<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

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
        'payment_url',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fees' => 'decimal:2',
        'status' => PaymentStatus::class,
        'type' => PaymentType::class,
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'amount', 'fees', 'provider'])
            ->useLogName('payment')
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Le paiement a été {$eventName}");
    }

    public function user()       { return $this->belongsTo(User::class); }
    public function property()   { return $this->belongsTo(Property::class); }
    public function invoice()    { return $this->hasOne(Invoice::class); }
    public function appointment(){ return $this->hasOne(Appointment::class, 'payment_id'); }

    public function scopeSuccessful(Builder $query): Builder { return $query->where('status', PaymentStatus::SUCCESS); }
    public function scopePending(Builder $query): Builder    { return $query->where('status', PaymentStatus::PENDING); }
    public function scopeFailed(Builder $query): Builder     { return $query->where('status', PaymentStatus::FAILED); }
    public function scopeOfType(Builder $query, PaymentType $type): Builder { return $query->where('type', $type); }
    public function scopeRecent(Builder $query): Builder     { return $query->orderByDesc('created_at'); }

    public function isSuccessful(): bool
    {
        return $this->status === PaymentStatus::SUCCESS;
    }

    protected static function booted()
    {
        static::creating(function ($payment) {
            if (is_null($payment->status)) {
                $payment->status = PaymentStatus::PENDING;
            }
        });
    }
}
