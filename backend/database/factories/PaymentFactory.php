<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\PaymentStatus;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'property_id' => Property::factory(),
            'amount' => $this->faker->numberBetween(5000, 500000),
            'type' => $this->faker->randomElement(['visit', 'rent']),
            'status' => $this->faker->randomElement([
                PaymentStatus::PENDING->value,
                PaymentStatus::SUCCESS->value,
                PaymentStatus::FAILED->value,
            ]),
            'provider' => 'cinetpay',
            'provider_ref' => strtoupper($this->faker->bothify('TXN-#######')),
            'hmac_signature' => hash('sha256', uniqid()),
            'fees' => $this->faker->randomFloat(2, 0, 500),
        ];
    }
}
