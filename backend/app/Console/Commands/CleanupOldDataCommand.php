<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\{CleanupOldNotifications, CleanupExpiredDeviceTokens};

class CleanupOldDataCommand extends Command
{
    protected $signature = 'cleanup:old-data';
    protected $description = 'Cleanup old notifications and expired device tokens';

    public function handle(): int
    {
        $this->info('Cleaning up old data...');
        CleanupOldNotifications::dispatch();
        CleanupExpiredDeviceTokens::dispatch();
        $this->info('Cleanup jobs dispatched.');
        return self::SUCCESS;
    }
}
