<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\InvoiceStatus;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'property_id' => Property::factory(),
            'amount' => $this->faker->numberBetween(80000, 400000),
            'due_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'status' => $this->faker->randomElement([
                InvoiceStatus::UNPAID->value,
                InvoiceStatus::PAID->value,
                InvoiceStatus::OVERDUE->value,
            ]),
        ];
    }
}
