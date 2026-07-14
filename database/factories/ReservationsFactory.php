<?php

namespace Database\Factories;

use App\Models\agencies;
use App\Models\clients;
use App\Models\employees;
use App\Models\services;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\reservations>
 */
class ReservationsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'agency_id' => agencies::factory(),
            'client_id' => clients::factory(),
            'service_id' => services::factory(),
            'employee_id' => employees::factory(),
            'reservation_date' => fake()->dateTimeBetween('-1 month', '+1 month'),
            'status' => fake()->randomElement(['pending', 'confirmed', 'cancelled']),
        ];
    }
}
