<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\{
    SendAppointmentReminders,
    GenerateMonthlyInvoices,
    MarkOverdueInvoices,
    CleanupOldNotifications,
    CleanupExpiredDeviceTokens
};


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ===============
//  JOBS PLANIFIÉS
// ===============

// Rappels de rendez-vous toutes les 30 minutes
Schedule::job(new SendAppointmentReminders)->everyThirtyMinutes();

// Génération de factures le 1er de chaque mois à minuit
Schedule::job(new GenerateMonthlyInvoices)->monthlyOn(1, '00:00');

// Marquer les factures en retard chaque jour à 6h
Schedule::job(new MarkOverdueInvoices)->dailyAt('06:00');

// Nettoyage des notifications et tokens expirés chaque semaine
Schedule::job(new CleanupOldNotifications)->weekly();
Schedule::job(new CleanupExpiredDeviceTokens)->weekly();
