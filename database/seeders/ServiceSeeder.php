<?php

namespace Database\Seeders;

use App\Models\agencies;
use App\Models\services;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        agencies::all()->each(function ($agency) {
            services::factory()->count(5)->create([
                'agency_id' => $agency->id,
            ]);
        });
    }
}
