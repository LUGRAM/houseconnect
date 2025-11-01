<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\MarkOverdueInvoices;

class MarkOverdueInvoicesCommand extends Command
{
    protected $signature = 'invoices:mark-overdue';
    protected $description = 'Mark invoices as overdue if due date passed';

    public function handle(): int
    {
        $this->info('Checking overdue invoices...');
        MarkOverdueInvoices::dispatch();
        $this->info('✅ Overdue invoices job dispatched.');
        return self::SUCCESS;
    }
}
