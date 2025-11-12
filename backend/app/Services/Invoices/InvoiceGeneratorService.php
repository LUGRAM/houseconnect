<?php

namespace App\Services\Invoices;

use App\Models\{Invoice, Payment, Property};
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class InvoiceGeneratorService
{
    /**
     * Génère une facture à partir d’un paiement.
     */
    public function generateFromPayment(Payment $payment): Invoice
    {
        $invoice = Invoice::firstOrCreate(
            ['payment_id' => $payment->id],
            [
                'user_id'   => $payment->user_id,
                'amount'    => $payment->amount,
                'status'    => 'paid',
                'issued_at' => now(),
                'due_date'  => now()->addDays(10),
            ]
        );

        Log::info('Invoice created manually from payment', [
            'invoice_id' => $invoice->id,
            'payment_id' => $payment->id,
        ]);

        return $invoice;
    }

    /**
     * Génère les factures mensuelles pour tous les biens actifs.
     */
    public function generateMonthlyInvoices(): int
    {
        $properties = Property::where('is_active', true)->get();
        $count = 0;

        foreach ($properties as $property) {
            Invoice::create([
                'property_id' => $property->id,
                'user_id'     => $property->user_id,
                'amount'      => $property->monthly_rent ?? 0,
                'status'      => 'pending',
                'issued_at'   => now(),
                'due_date'    => Carbon::now()->addDays(10),
            ]);
            $count++;
        }

        Log::info('Monthly invoices generated', ['count' => $count]);
        return $count;
    }

    /**
     * Marque les factures en retard.
     */
    public function markOverdueInvoices(): int
    {
        $count = Invoice::where('status', 'pending')
            ->where('due_date', '<', now())
            ->update(['status' => 'overdue']);

        Log::info('Overdue invoices marked', ['count' => $count]);
        return $count;
    }
}
