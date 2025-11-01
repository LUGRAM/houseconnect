<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Services\Invoices\InvoiceGeneratorService;

class GenerateMonthlyInvoices implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 300;

    public function handle(InvoiceGeneratorService $invoiceService): void
    {
        Log::info('Starting monthly invoice generation');

        try {
            $count = $invoiceService->generateMonthlyInvoices();

            Log::info('Monthly invoices generated', ['count' => $count]);
        } catch (\Throwable $e) {
            Log::error('Invoice generation failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
