<?php

namespace Tests\Feature;

use App\Models\agencies;
use App\Models\clients;
use App\Models\employees;
use App\Models\services;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationClientWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_reservation_automatically_creates_a_client(): void
    {
        [$manager, $agency] = $this->managerWithAgency();
        $employee = employees::factory()->create(['agency_id' => $agency->id]);
        $service = services::factory()->create(['agency_id' => $agency->id]);

        $this->actingAs($manager)
            ->post(route('reservations.store'), [
                'client_name' => 'Nadia Amrani',
                'client_phone' => '06 12 34 56 78',
                'client_email' => '',
                'employee_id' => $employee->id,
                'service_id' => $service->id,
                'vehicle_type' => 'car',
                'plate_number' => '12345-A-6',
                'reservation_date' => now()->addDay()->format('Y-m-d H:i:s'),
            ])
            ->assertRedirect(route('reservations.index'));

        $client = clients::where('agency_id', $agency->id)
            ->where('phone_number', '06 12 34 56 78')
            ->firstOrFail();

        $this->assertNull($client->email);
        $this->assertDatabaseHas('reservations', [
            'agency_id' => $agency->id,
            'client_id' => $client->id,
            'status' => 'pending',
        ]);
    }

    public function test_creating_a_reservation_reuses_a_client_with_the_same_phone(): void
    {
        [$manager, $agency] = $this->managerWithAgency();
        $client = clients::factory()->create([
            'agency_id' => $agency->id,
            'name' => 'Ancien nom',
            'phone_number' => '+212 6 12 34 56 78',
        ]);
        $employee = employees::factory()->create(['agency_id' => $agency->id]);
        $service = services::factory()->create(['agency_id' => $agency->id]);

        $this->actingAs($manager)
            ->post(route('reservations.store'), [
                'client_name' => 'Nadia Amrani',
                'client_phone' => '+212612345678',
                'client_email' => 'nadia@example.com',
                'employee_id' => $employee->id,
                'service_id' => $service->id,
                'vehicle_type' => 'car',
                'reservation_date' => now()->addDay()->format('Y-m-d H:i:s'),
            ])
            ->assertRedirect(route('reservations.index'));

        $this->assertSame(1, clients::where('agency_id', $agency->id)->count());
        $this->assertDatabaseHas('reservations', ['client_id' => $client->id]);
        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'name' => 'Ancien nom',
        ]);
    }

    private function managerWithAgency(): array
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $agency = agencies::factory()->create();

        $manager->update(['agency_id' => $agency->id]);

        return [$manager->refresh(), $agency];
    }
}
