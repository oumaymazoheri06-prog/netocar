<?php

namespace Database\Seeders;

use App\Models\agencies;
use App\Models\employees;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        agencies::all()->each(function ($agency) {
            employees::factory()->count(5)->create([
                'agency_id' => $agency->id,
            ]);

        });

    }
}
