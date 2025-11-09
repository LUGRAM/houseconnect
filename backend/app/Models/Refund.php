<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Refund extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'payment_id',
        'amount',
        'reason',
        'status',
        'requested_by',
        'approved_by',
        'requested_at',
        'approved_at',
        'error_message',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    public function payment() { return $this->belongsTo(Payment::class); }
    public function requester() { return $this->belongsTo(User::class, 'requested_by'); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }

    /*
    |--------------------------------------------------------------------------
    | Logs d’activité
    |--------------------------------------------------------------------------
    */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'amount', 'reason'])
            ->useLogName('refund')
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Remboursement {$eventName}");
    }

    /*
    |--------------------------------------------------------------------------
    | Méthodes utilitaires
    |--------------------------------------------------------------------------
    */
    public function approve(int $adminId): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $adminId,
            'approved_at' => now(),
        ]);
    }

    public function fail(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $error,
        ]);
    }
}
