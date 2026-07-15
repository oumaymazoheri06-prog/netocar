<?php

namespace Database\Factories;

use App\Models\agencies;
use App\Models\clients;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\clients>
 */
class ClientsFactory extends Factory
{
    protected $model = clients::class;
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

        ];
    }
}
