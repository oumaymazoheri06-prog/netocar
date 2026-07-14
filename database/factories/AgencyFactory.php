<?php

namespace Database\Factories;

use App\Models\agencies;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\agencies>
 */
class AgencyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = agencies::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'address' => fake()->address(),
            'phone_number' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'package' => fake()->randomElement(['basic', 'standard', 'premium']),
            'license_status' => 'active',
            'license_expires_at' => now()->addYearNoOverflow()->toDateString(),
            'activated_at' => now(),
        ];
    }
}
