<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Enums\ExpenseCategory;

class Expense extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'property_id',
        'category',
        'amount',
        'date',
        'notes',
    ];

    // Les attributs castés automatiquement.
    protected $casts = [
        'category' => ExpenseCategory::class,
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    // Configuration du logging d’activité (Spatie).
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['category', 'amount', 'date'])
            ->useLogName('expense')
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "La dépense a été {$eventName}");
    }

    // Relations Eloquent.
    public function property() { return $this->belongsTo(Property::class); }
    public function user() { return $this->belongsTo(User::class); }

}
