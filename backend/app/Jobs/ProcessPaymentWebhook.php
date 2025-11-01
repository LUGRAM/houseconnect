<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Services\Payments\PaymentService;

class ProcessPaymentWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public int $timeout = 60;
    public array $backoff = [10, 30, 60];

    protected array $webhookData;

    public function __construct(array $webhookData)
    {
        $this->webhookData = $webhookData;
    }

    public function handle(PaymentService $paymentService): void
    {
        try {
            $paymentService->handleCinetpayWebhook($this->webhookData);

            Log::info('Webhook processed', [
                'transaction_id' => $this->webhookData['cpm_trans_id'] ?? 'unknown',
            ]);
        } catch (\Throwable $e) {
            Log::error('Webhook failed', [
                'data'  => $this->webhookData,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
