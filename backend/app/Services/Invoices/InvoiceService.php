<?php

namespace App\Services\Invoices;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class InvoiceService
{
    public function generateForPayment(Payment $payment): Invoice
    {
        $invoice = Invoice::create([
            'user_id'    => $payment->user_id,
            'payment_id' => $payment->id,
            'amount'     => $payment->amount,
            'status'     => 'paid',
            'issued_at'  => now(),
        ]);

        Log::info('Facture générée', ['invoice_id' => $invoice->id]);

        return $invoice;
    }

    public function list(): array
    {
        return Invoice::where('user_id', Auth::id())
            ->with('payment')
            ->latest()
            ->get()
            ->toArray();
    }
}
