<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Models\Payment;
use App\Observers\{UserObserver, PaymentObserver};
use App\Settings\SystemSettings;
use App\Observers\SystemSettingObserver;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Observers Eloquent
        User::observe(UserObserver::class);
        Payment::observe(PaymentObserver::class);

        // SystemSetting n’étant pas un modèle Eloquent,
        // on le gère via un hook global dans un event listener
        $this->registerSystemSettingObserver();
    }

    /**
     * Simulation d'un observer pour SystemSetting (non-Eloquent)
     */
    protected function registerSystemSettingObserver(): void
    {
        app()->resolving(SystemSettings::class, function ($setting) {
            // Exécution de logique custom quand la config est modifiée
            app(SystemSettingObserver::class)->updated($setting);
        });
    }
}
