<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SendAppointmentReminders;

class SendAppointmentRemindersCommand extends Command
{
    protected $signature = 'appointments:send-reminders';
    protected $description = 'Send reminders for upcoming appointments';

    public function handle(): int
    {
        $this->info('Dispatching appointment reminder job...');
        SendAppointmentReminders::dispatch();
        $this->info('Appointment reminders job dispatched.');
        return self::SUCCESS;
    }
}
