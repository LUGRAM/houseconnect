<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\AppointmentStatus;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'property_id' => Property::factory(),
            'status' => AppointmentStatus::PENDING->value,
            'scheduled_at' => $this->faker->dateTimeBetween('+1 days', '+7 days'),
        ];
    }
}
