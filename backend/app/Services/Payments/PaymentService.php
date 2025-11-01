<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function handleCinetpayWebhook(array $payload): void
    {
        $transactionId = $payload['cpm_trans_id'] ?? null;

        if (! $transactionId) {
            Log::warning('Webhook ignored: missing transaction ID', $payload);
            return;
        }

        $payment = Payment::where('provider_ref', $transactionId)->first();

        if (! $payment) {
            Log::warning('Payment not found for webhook', ['transaction_id' => $transactionId]);
            return;
        }

        $status = $payload['cpm_result'] ?? 'success';
        $payment->update(['status' => $status]);

        Log::info('Payment updated via webhook', [
            'id' => $payment->id,
            'status' => $status,
        ]);
    }
}
