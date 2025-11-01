<?php

namespace App\Observers;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLogObserver
{
    /**
     * Événement lors de la création d’un modèle.
     */
    public function created(Model $model): void
    {
        $this->logActivity('created', $model, null, $model->getAttributes());
    }

    /**
     * Événement lors de la mise à jour d’un modèle.
     */
    public function updated(Model $model): void
    {
        $this->logActivity('updated', $model, $model->getOriginal(), $model->getChanges());
    }

    /**
     * Événement lors de la suppression d’un modèle.
     */
    public function deleted(Model $model): void
    {
        $this->logActivity('deleted', $model, $model->getAttributes());
    }

    /**
     * Enregistre une activité dans la base.
     */
    protected function logActivity(string $action, Model $model, ?array $oldValues = null, ?array $newValues = null): void
    {
        // Ignore ActivityLog pour éviter les boucles infinies
        if ($model instanceof ActivityLog) {
            return;
        }

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'model_type'  => get_class($model),
            'model_id'    => $model->getKey(),
            'action'      => $action,
            'old_values'  => $oldValues,
            'new_values'  => $newValues,
        ]);
    }
}
