<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\DeviceToken;

class CleanupExpiredDeviceTokens implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 60;

    public function handle(): void
    {
        $days = 60;

        $deleted = DeviceToken::query()
            ->where('last_used_at', '<', now()->subDays($days))
            ->orWhereNull('last_used_at')
            ->where('created_at', '<', now()->subDays($days))
            ->delete();

        Log::info('🧹 Expired device tokens cleaned', ['deleted' => $deleted]);
    }
}
