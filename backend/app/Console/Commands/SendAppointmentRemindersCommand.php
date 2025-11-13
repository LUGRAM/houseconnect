<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SendAppointmentReminders;
use Illuminate\Support\Facades\Bus; //

class SendAppointmentRemindersCommand extends Command
{
    protected $signature = 'appointments:send-reminders';
    protected $description = 'Envoie les rappels pour les rendez-vous des prochaines 24h';

    public function handle(): int
    {
        $this->info('Dispatching appointment reminder job...');

        Bus::dispatch(new SendAppointmentReminders());

        $this->info('Appointment reminders job dispatched.');
        return self::SUCCESS;
    }
}
