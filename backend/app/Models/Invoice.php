<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\InvoiceStatus;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'property_id',
        'payment_id',
        'issued_at',
        'amount',
        'due_date',
        'status',
        'pdf_url',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'due_date' => 'datetime',
        'amount' => 'decimal:2',
        'status' => InvoiceStatus::class,
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['amount', 'status', 'due_date'])
            ->useLogName('invoice')
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "La facture a été {$eventName}");
    }

    public function user() { return $this->belongsTo(User::class); }
    public function property() { return $this->belongsTo(Property::class); }
    public function payment() { return $this->belongsTo(Payment::class); }

    public function scopePaid($query) { return $query->where('status', InvoiceStatus::PAID); }
    public function scopeUnpaid($query) { return $query->where('status', InvoiceStatus::UNPAID); }
    public function scopeOverdue($query) { return $query->where('status', InvoiceStatus::OVERDUE); }

    protected static function booted()
    {
        static::creating(function ($invoice) {
            $invoice->issued_at ??= now();
            $invoice->due_date ??= now()->addDays(30);
            $invoice->status ??= InvoiceStatus::UNPAID;
        });
    }
}
