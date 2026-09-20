<?php

namespace Database\Factories;

use App\Models\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shift>
 */
class ShiftFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Shift Pagi', 'Shift Siang', 'Shift Malam']),
            'start_time' => '08:00',
            'end_time' => '16:00',
            'is_active' => true,
        ];
    }
}
