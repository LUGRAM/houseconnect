<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupOldNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 120;

    public function handle(): void
    {
        $days = 90;

        $deleted = DB::table('notifications')
            ->where('created_at', '<', now()->subDays($days))
            ->whereNotNull('read_at')
            ->delete();

        Log::info('🧹 Old notifications cleaned', ['deleted' => $deleted]);
    }
}
