<?php

namespace App\Observers;

use App\Settings\SystemSetting;
use Illuminate\Support\Facades\Log;

class SystemSettingObserver
{
    /**
     * Empêche la modification d’un paramètre verrouillé.
     */
    public function updating(SystemSetting $setting): void
    {
        if ($setting->locked ?? false) {
            throw new \Exception("Impossible de modifier le paramètre verrouillé : {$setting->group}");
        }
    }

    /**
     * Journalise chaque mise à jour de configuration.
     */
    public function updated(SystemSetting $setting): void
    {
        Log::info('Paramètre système modifié', [
            'group' => $setting->group,
            'values' => $setting->toArray(),
        ]);
    }
}
