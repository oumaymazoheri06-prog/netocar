<?php

namespace Database\Seeders;

use App\Models\agencies;
use App\Models\clients;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        agencies::all()->each(function ($agency) {

            clients::factory()->count(5)->create([
                'agency_id' => $agency->id,
            ]);
        });
    }
}
