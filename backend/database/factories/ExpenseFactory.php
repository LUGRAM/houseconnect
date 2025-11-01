<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\ExpenseCategory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'user_id' => User::factory(),
            'category' => $this->faker->randomElement([
                ExpenseCategory::WATER->value,
                ExpenseCategory::ELECTRICITY->value,
                ExpenseCategory::MAINTENANCE->value,
                ExpenseCategory::OTHER->value,
            ]),
            'amount' => $this->faker->numberBetween(1000, 20000),
            'date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'note' => $this->faker->sentence(),
        ];
    }
}
