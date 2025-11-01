<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\ExpenseCategory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Expense extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'property_id',
        'user_id',
        'category',
        'amount',
        'date',
        'note',
    ];

    protected $casts = [
        'category' => ExpenseCategory::class,
        'date' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['category', 'amount', 'date'])
            ->useLogName('expense')
            ->logOnlyDirty();
    }

    public function property() { return $this->belongsTo(Property::class); }
    public function user() { return $this->belongsTo(User::class); }
}
