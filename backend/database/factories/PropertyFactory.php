<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Property;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
{
    protected $model = Property::class;
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => ucfirst($this->faker->words(3, true)),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->numberBetween(100000, 500000),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->randomElement(['Libreville', 'Port-Gentil', 'Franceville']),
            'visit_price' => 5000,
            'is_validated' => $this->faker->boolean(80),
        ];
    }
}
