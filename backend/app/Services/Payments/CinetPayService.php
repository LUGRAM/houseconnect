<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Payment;
use Exception;

class CinetPayService
{
    protected string $apiKey;
    protected string $siteId;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.cinetpay.api_key');
        $this->siteId = config('services.cinetpay.site_id');
        $this->baseUrl = config('services.cinetpay.base_url', 'https://api-checkout.cinetpay.com/v2');
    }

    public function initiatePayment(Payment $payment): array
    {
        $transactionId = 'HC-' . uniqid();

        $payload = [
            'apikey'         => $this->apiKey,
            'site_id'        => $this->siteId,
            'transaction_id' => $transactionId,
            'amount'         => $payment->amount,
            'currency'       => 'XAF',
            'description'    => 'Paiement visite HouseConnect',
            'notify_url'     => route('api.cinetpay.webhook'),
            'return_url'     => route('api.payment.return'),
            'customer_id'    => $payment->user_id,
            'customer_name'  => $payment->user->name,
            'customer_email' => $payment->user->email,
            'customer_phone_number' => $payment->user->phone,
            'channels'       => 'MOBILE_MONEY',
        ];

        $signature = hash_hmac('sha256', json_encode($payload), $this->apiKey);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-Signature'  => $signature,
        ])->post("{$this->baseUrl}/payment", $payload);

        if ($response->failed()) {
            Log::error('Erreur lors de l’init CinetPay', ['response' => $response->body()]);
            throw new Exception('Erreur lors de l’initialisation du paiement.');
        }

        $data = $response->json();

        // Sauvegarde des informations de transaction dans la base
        $payment->update([
            'provider_ref'    => $data['data']['transaction_id'] ?? $transactionId,
            'payment_url'     => $data['data']['payment_url'] ?? null,
            'hmac_signature'  => $signature,
        ]);

        Log::info('Paiement CinetPay initialisé', [
            'payment_id'   => $payment->id,
            'provider_ref' => $payment->provider_ref,
        ]);

        return $data;
    }

     //Vérifie l’état d’un paiement CinetPa
    public function verifyTransaction(string $transactionId): array
    {
        $response = Http::get("{$this->baseUrl}/payment/check", [
            'apikey'         => $this->apiKey,
            'site_id'        => $this->siteId,
            'transaction_id' => $transactionId,
        ]);

        if ($response->failed()) {
            Log::error('CinetPay Verify Failed', ['response' => $response->body()]);
            throw new Exception('Impossible de vérifier la transaction.');
        }

        return $response->json();
    }
}
