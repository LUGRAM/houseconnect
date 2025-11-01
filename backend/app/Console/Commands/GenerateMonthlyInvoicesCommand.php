<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\GenerateMonthlyInvoices;

class GenerateMonthlyInvoicesCommand extends Command
{
    protected $signature = 'invoices:generate-monthly';
    protected $description = 'Generate monthly rent invoices for all active properties';

    public function handle(): int
    {
        $this->info('Generating monthly invoices...');
        GenerateMonthlyInvoices::dispatch();
        $this->info('Monthly invoice job dispatched.');
        return self::SUCCESS;
    }
}
