<?php

namespace Database\Seeders;

use App\Models\agencies;
use App\Models\User;
use Illuminate\Database\Seeder;

class AgencySeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            $agency = agencies::factory()->create();

            if ($user->role === 'manager' && ! $user->agency_id) {
                $user->update(['agency_id' => $agency->id]);
            }
        }
    }
}
