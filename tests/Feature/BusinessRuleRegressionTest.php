<?php

namespace Tests\Feature;

use App\Livewire\Settings\DeleteUserForm;
use App\Models\agencies;
use App\Models\Branch;
use App\Models\clients;
use App\Models\employees;
use App\Models\Payment;
use App\Models\reservations;
use App\Models\services;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BusinessRuleRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_linked_manager_cannot_delete_the_account_or_the_agency(): void
    {
        [$manager, $agency] = $this->managerWithAgency();

        $this->actingAs($manager);

        Livewire::test(DeleteUserForm::class)
            ->set('password', 'password')
            ->call('deleteUser')
            ->assertHasErrors('password');

        $this->assertDatabaseHas('users', ['id' => $manager->id]);
        $this->assertDatabaseHas('agencies', ['id' => $agency->id, 'deleted_at' => null]);
    }

    public function test_an_employee_cannot_receive_overlapping_reservations(): void
    {
        [$manager, $agency] = $this->managerWithAgency();
        $employee = employees::factory()->create(['agency_id' => $agency->id]);
        $service = services::factory()->create(['agency_id' => $agency->id, 'duration_minutes' => 60]);
        $client = clients::factory()->create(['agency_id' => $agency->id]);
        $startsAt = now()->addDay()->setTime(10, 0);

        reservations::factory()->create([
            'agency_id' => $agency->id,
            'client_id' => $client->id,
            'employee_id' => $employee->id,
            'service_id' => $service->id,
            'reservation_date' => $startsAt,
            'duration_minutes' => 60,
            'status' => 'pending',
        ]);

        $this->actingAs($manager)
            ->from(route('reservations.create'))
            ->post(route('reservations.store'), [
                'client_name' => 'Second Client',
                'client_phone' => '0611111111',
                'employee_id' => $employee->id,
                'service_id' => $service->id,
                'vehicle_type' => 'car',
                'reservation_date' => $startsAt->copy()->addMinutes(30)->format('Y-m-d H:i:s'),
            ])
            ->assertRedirect(route('reservations.create'))
            ->assertSessionHasErrors('reservation_date');

        $this->assertSame(1, reservations::count());
    }

    public function test_reservation_changes_are_synchronized_and_cancellation_cancels_the_ticket(): void
    {
        [$manager, $agency] = $this->managerWithAgency();
        $client = clients::factory()->create(['agency_id' => $agency->id]);
        $employee = employees::factory()->create(['agency_id' => $agency->id]);
        $firstService = services::factory()->create(['agency_id' => $agency->id, 'price' => 80]);
        $secondService = services::factory()->create(['agency_id' => $agency->id, 'price' => 140]);
        $reservation = reservations::factory()->create([
            'agency_id' => $agency->id,
            'client_id' => $client->id,
            'employee_id' => $employee->id,
            'service_id' => $firstService->id,
            'vehicle_type' => 'car',
            'reservation_date' => now()->addDay()->setTime(11, 0),
            'status' => 'pending',
        ]);

        $payload = [
            'client_id' => $client->id,
            'employee_id' => $employee->id,
            'service_id' => $firstService->id,
            'vehicle_type' => 'car',
            'plate_number' => 'SYNC-1',
            'reservation_date' => $reservation->reservation_date->format('Y-m-d H:i:s'),
            'status' => 'confirmed',
        ];

        $this->actingAs($manager)->put(route('reservations.update', $reservation), $payload)->assertRedirect();
        $ticket = Ticket::where('reservation_id', $reservation->id)->firstOrFail();

        $payload['service_id'] = $secondService->id;
        $payload['plate_number'] = 'SYNC-2';
        $this->actingAs($manager)->put(route('reservations.update', $reservation), $payload)->assertRedirect();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'service_id' => $secondService->id,
            'plate_number' => 'SYNC-2',
            'price' => 140,
        ]);

        $payload['status'] = 'cancelled';
        $this->actingAs($manager)->put(route('reservations.update', $reservation), $payload)->assertRedirect();
        $this->assertSame('cancelled', $ticket->refresh()->status);
    }

    public function test_ticket_transitions_are_ordered_and_completion_has_real_timestamps(): void
    {
        [$manager, $agency] = $this->managerWithAgency();
        $service = services::factory()->create(['agency_id' => $agency->id]);
        $ticket = Ticket::create([
            'agency_id' => $agency->id,
            'service_id' => $service->id,
            'vehicle_type' => 'car',
            'status' => 'waiting',
            'price' => 100,
        ]);

        $this->actingAs($manager)
            ->put(route('tickets.update', $ticket), ['status' => 'completed'])
            ->assertSessionHasErrors('status');

        $this->actingAs($manager)->put(route('tickets.update', $ticket), ['status' => 'in_progress'])->assertRedirect();
        $this->actingAs($manager)->put(route('tickets.update', $ticket), ['status' => 'completed'])->assertRedirect();

        $ticket->refresh();
        $this->assertNotNull($ticket->started_at);
        $this->assertNotNull($ticket->completed_at);
    }

    public function test_completed_payment_is_idempotent_and_uses_its_plan_snapshot(): void
    {
        [$manager, $agency] = $this->managerWithAgency(['package' => 'premium']);
        $admin = User::factory()->create(['role' => 'admin']);
        $payment = Payment::create([
            'agency_id' => $agency->id,
            'plan' => 'basic',
            'billing_period' => 'yearly',
            'amount' => 300,
            'status' => 'pending',
            'payment_method' => 'virement',
        ]);

        $this->actingAs($admin)->put(route('payments.update', $payment), ['status' => 'completed'])->assertRedirect();
        $firstExpiry = $agency->refresh()->license_expires_at->toDateString();

        $this->actingAs($admin)->put(route('payments.update', $payment), ['status' => 'completed'])->assertRedirect();

        $this->assertSame($firstExpiry, $agency->refresh()->license_expires_at->toDateString());
        $this->assertSame('basic', $agency->package);
        $this->assertNotNull($payment->refresh()->processed_at);
    }

    public function test_confirmed_reservation_and_its_completed_ticket_are_not_double_counted(): void
    {
        [$manager, $agency] = $this->managerWithAgency();
        $client = clients::factory()->create(['agency_id' => $agency->id]);
        $service = services::factory()->create(['agency_id' => $agency->id, 'price' => 100]);
        $reservation = reservations::factory()->create([
            'agency_id' => $agency->id,
            'client_id' => $client->id,
            'service_id' => $service->id,
            'status' => 'confirmed',
        ]);
        Ticket::create([
            'agency_id' => $agency->id,
            'reservation_id' => $reservation->id,
            'client_id' => $client->id,
            'service_id' => $service->id,
            'vehicle_type' => 'car',
            'status' => 'completed',
            'price' => 100,
            'started_at' => now()->subHour(),
            'completed_at' => now(),
        ]);

        $this->actingAs($manager)
            ->get(route('analytics.index'))
            ->assertOk()
            ->assertViewHas('totalRevenue', fn ($revenue) => (float) $revenue === 100.0);
    }

    public function test_archiving_a_client_preserves_reservation_history(): void
    {
        [$manager, $agency] = $this->managerWithAgency();
        $client = clients::factory()->create(['agency_id' => $agency->id, 'name' => 'Historique Client']);
        $reservation = reservations::factory()->create(['agency_id' => $agency->id, 'client_id' => $client->id]);

        $this->actingAs($manager)->delete(route('clients.destroy', $client))->assertRedirect();

        $this->assertSoftDeleted('clients', ['id' => $client->id]);
        $this->assertDatabaseHas('reservations', ['id' => $reservation->id, 'client_id' => $client->id]);
        $this->assertSame('Historique Client', $reservation->fresh()->client->name);
    }

    public function test_inactive_branch_cannot_receive_new_records(): void
    {
        [$manager, $agency] = $this->managerWithAgency();
        $branch = Branch::factory()->create(['agency_id' => $agency->id, 'is_active' => false]);

        $this->actingAs($manager)
            ->post(route('clients.store'), [
                'name' => 'Client Inactif',
                'phone' => '0622222222',
                'branch_id' => $branch->id,
            ])
            ->assertSessionHasErrors('branch_id');
    }

    public function test_moving_a_reservation_cannot_bypass_the_monthly_quota(): void
    {
        config(['netocar.plans.basic.limits.reservations_per_month' => 1]);
        [$manager, $agency] = $this->managerWithAgency(['package' => 'basic']);
        $client = clients::factory()->create(['agency_id' => $agency->id]);
        $service = services::factory()->create(['agency_id' => $agency->id]);
        $targetDate = now()->addMonthsNoOverflow(2)->startOfMonth()->setTime(10, 0);

        reservations::factory()->create([
            'agency_id' => $agency->id,
            'client_id' => $client->id,
            'service_id' => $service->id,
            'employee_id' => null,
            'reservation_date' => $targetDate,
            'status' => 'pending',
        ]);
        $moving = reservations::factory()->create([
            'agency_id' => $agency->id,
            'client_id' => $client->id,
            'service_id' => $service->id,
            'employee_id' => null,
            'vehicle_type' => 'car',
            'reservation_date' => now()->addMonthNoOverflow()->startOfMonth()->setTime(10, 0),
            'status' => 'pending',
        ]);

        $this->actingAs($manager)
            ->put(route('reservations.update', $moving), [
                'client_id' => $client->id,
                'service_id' => $service->id,
                'vehicle_type' => 'car',
                'reservation_date' => $targetDate->format('Y-m-d H:i:s'),
                'status' => 'pending',
            ])
            ->assertSessionHasErrors('plan');
    }

    private function managerWithAgency(array $agencyAttributes = []): array
    {
        $manager = User::factory()->create([
            'role' => 'manager',
            'password' => 'password',
        ]);
        $agency = agencies::factory()->create($agencyAttributes);
        $manager->update(['agency_id' => $agency->id]);

        return [$manager->refresh(), $agency];
    }
}
