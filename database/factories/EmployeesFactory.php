<?php

namespace Database\Factories;

use App\Models\agencies;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\employees>
 */
class EmployeesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone_number' => fake()->phoneNumber(),
            'agency_id' => agencies::factory(),
            'job_title' => fake()->randomElement(['Manager', 'Salesperson', 'Technician', 'Customer Service']),
            'salary' => fake()->randomFloat(2, 1500, 10000),
        ];
    }
}
