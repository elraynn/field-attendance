<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveRequest>
 */
class LeaveRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('now', '+1 month');

        return [
            'employee_id' => Employee::factory(),
            'type' => fake()->randomElement(['izin', 'cuti']),
            'start_date' => $start->format('Y-m-d'),
            'end_date' => fake()->dateTimeBetween($start, (clone $start)->modify('+3 days'))->format('Y-m-d'),
            'reason' => fake()->sentence(),
            'status' => 'diajukan',
        ];
    }
}
