<?php

namespace Tests\Feature;

use App\Models\agencies;
use App\Models\clients;
use App\Models\employees;
use App\Models\Payment;
use App\Models\reservations;
use App\Models\services;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_view_operational_analytics(): void
    {
        [$manager, $agency] = $this->managerWithAgency();
        $employee = employees::factory()->create([
            'agency_id' => $agency->id,
            'name' => 'Sara Washer',
            'job_title' => 'Detail Specialist',
        ]);
        $service = services::factory()->create([
            'agency_id' => $agency->id,
            'name' => 'Premium Wash',
            'price' => 120,
        ]);
        $client = clients::factory()->create([
            'agency_id' => $agency->id,
            'name' => 'Repeat Customer',
            'phone_number' => '0612345678',
        ]);

        $ticket = Ticket::create([
            'agency_id' => $agency->id,
            'service_id' => $service->id,
            'employee_id' => $employee->id,
            'vehicle_type' => 'car',
            'plate_number' => 'ANA-123',
            'status' => 'completed',
            'price' => 120,
        ]);
        $ticket->forceFill([
            'created_at' => now()->subMinutes(45),
            'updated_at' => now(),
            'started_at' => now()->subMinutes(45),
            'completed_at' => now(),
        ])->save();

        reservations::factory()->create([
            'agency_id' => $agency->id,
            'client_id' => $client->id,
            'service_id' => $service->id,
            'employee_id' => $employee->id,
            'reservation_date' => now()->addDay(),
            'status' => 'confirmed',
        ]);

        reservations::factory()->create([
            'agency_id' => $agency->id,
            'client_id' => $client->id,
            'service_id' => $service->id,
            'employee_id' => $employee->id,
            'reservation_date' => now()->addDays(2),
            'status' => 'cancelled',
        ]);

        $this->actingAs($manager)
            ->get(route('analytics.index'))
            ->assertOk()
            ->assertSee('Analyses opérationnelles')
            ->assertSee('Premium Wash')
            ->assertSee('Sara Washer')
            ->assertSee('Repeat Customer')
            ->assertSee('Réservations annulées')
            ->assertSee('45 min');
    }

    public function test_admin_can_view_unpaid_agencies(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [, $paidAgency] = $this->managerWithAgency(['name' => 'Paid Agency']);
        [, $unpaidAgency] = $this->managerWithAgency([
            'name' => 'Unpaid Agency',
            'license_status' => 'suspended',
        ]);

        Payment::create([
            'agency_id' => $paidAgency->id,
            'amount' => 500,
            'status' => 'completed',
            'payment_method' => 'cashpluss',
        ]);

        Payment::create([
            'agency_id' => $unpaidAgency->id,
            'amount' => 500,
            'status' => 'pending',
            'payment_method' => 'virement',
        ]);

        $this->actingAs($admin)
            ->get(route('analytics.index'))
            ->assertOk()
            ->assertSee('Agences non payées')
            ->assertSee('Unpaid Agency')
            ->assertDontSee('Paid Agency');
    }

    public function test_staff_cannot_view_analytics(): void
    {
        [, $agency] = $this->managerWithAgency();
        $staff = User::factory()->create([
            'role' => 'staff',
            'agency_id' => $agency->id,
        ]);

        employees::factory()->create([
            'agency_id' => $agency->id,
            'user_id' => $staff->id,
            'email' => $staff->email,
        ]);

        $this->actingAs($staff)
            ->get(route('analytics.index'))
            ->assertForbidden();
    }

    private function managerWithAgency(array $agencyAttributes = []): array
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $agency = agencies::factory()->create($agencyAttributes);

        $manager->update(['agency_id' => $agency->id]);

        return [$manager->refresh(), $agency];
    }
}
