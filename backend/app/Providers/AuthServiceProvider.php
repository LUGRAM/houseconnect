<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Mapping des models vers leurs policies.
     */
    protected $policies = [
        \App\Models\Property::class    => \App\Policies\PropertyPolicy::class,
        \App\Models\Expense::class     => \App\Policies\ExpensePolicy::class,
        \App\Models\Appointment::class => \App\Policies\AppointmentPolicy::class,
        \App\Models\Payment::class     => \App\Policies\PaymentPolicy::class,
        \App\Models\Invoice::class     => \App\Policies\InvoicePolicy::class,
        \App\Models\User::class => \App\Policies\UserPolicy::class,
        \App\Settings\SystemSetting::class => \App\Policies\SystemSettingPolicy::class,
        \Illuminate\Notifications\DatabaseNotification::class => \App\Policies\NotificationPolicy::class,

    ];

    /**
     * Enregistrement des policies.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
