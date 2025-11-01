<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'model_type',
        'model_id',
        'action',
        'old_values',
        'new_values',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public static function logActivity(string $action, Model $model, ?array $oldValues = null, ?array $newValues = null): void
    {
        self::create([
            'user_id'     => Auth::id(),
            'model_type'  => get_class($model),
            'model_id'    => $model->getKey(),
            'action'      => $action,
            'old_values'  => $oldValues,
            'new_values'  => $newValues,
        ]);
    }
}
