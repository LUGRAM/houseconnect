<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Services\Invoices\InvoiceGeneratorService;

class MarkOverdueInvoices implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 180;

    public function handle(InvoiceGeneratorService $invoiceService): void
    {
        Log::info("Checking for overdue invoices");

        try {
            $count = $invoiceService->markOverdueInvoices();
            
            Log::info("Overdue invoices marked", ['count' => $count]);

        } catch (\Exception $e) {
            Log::error("Failed to mark overdue invoices", [
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
}