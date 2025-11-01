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
        'amount',
        'due_date',
        'status',
        'pdf_url',
    ];

    protected $casts = [
        'status' => InvoiceStatus::class,
        'due_date' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['amount', 'status', 'due_date'])
            ->useLogName('invoice')
            ->logOnlyDirty();
    }

    public function user() { return $this->belongsTo(User::class); }
    public function property() { return $this->belongsTo(Property::class); }
    public function payment() { return $this->belongsTo(Payment::class); }
}
