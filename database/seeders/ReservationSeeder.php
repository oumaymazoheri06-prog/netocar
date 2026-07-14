<?php

namespace Database\Seeders;

use App\Models\clients;
use App\Models\reservations;
use Illuminate\Database\Seeder;

class ReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        clients::all()->each(function ($client) {
            $agency = $client->agency;
            $service = $agency->services()->inRandomOrder()->first();
            $employee = $agency->employee()->inRandomOrder()->first();

            reservations::factory()->create([
                'client_id' => $client->id,
                'service_id' => $service->id,
                'agency_id' => $agency->id,
                'employee_id' => $employee->id,
            ]);

        });
    }
}
