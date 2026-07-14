<?php

namespace Database\Factories;

use App\Models\agencies;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Branch>
 */
class BranchFactory extends Factory
{
    protected $model = Branch::class;

    public function definition(): array
    {
        return [
            'agency_id' => agencies::factory(),
            'name' => fake()->city().' Site',
            'code' => strtoupper(fake()->unique()->bothify('BR-####')),
            'address' => fake()->address(),
            'phone_number' => fake()->phoneNumber(),
            'simultaneous_capacity' => 2,
            'is_active' => true,
        ];
    }
}
