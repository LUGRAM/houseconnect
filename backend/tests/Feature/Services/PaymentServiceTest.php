<?php

namespace Tests\Feature\Services;

use App\Models\{Payment};
use App\Services\Payments\CinetPayService;
use Illuminate\Support\Facades\Http;
use Tests\TestHelper;

/** @var \Tests\TestCase $this */
/** @var \App\Models\User $user */

beforeEach(function () {

    /** @var TestHelper $this */
    $this->loginAsNewUser();
});

it('initie un paiement via CinetPayService', function () {
    Http::fake([
        'https://api-checkout.cinetpay.com/v2/payment' => Http::response([
            'code' => '201',
            'message' => 'CREATED',
            'data' => [
                'payment_url' => 'https://checkout.cinetpay.com/pay/test123',
                'transaction_id' => 'HC-TEST123'
            ],
        ], 200),
    ]);

    $payment = Payment::factory()->create([
        'user_id' => $this->user->id,
        'amount' => 1500,
    ]);

    $service = app(CinetPayService::class);
    $response = $service->initiatePayment($payment);

    expect($response['data']['transaction_id'])->toBe('HC-TEST123')
        ->and($payment->refresh()->provider_ref)->toBe('HC-TEST123')
        ->and($payment->payment_url)->toContain('cinetpay');
});
