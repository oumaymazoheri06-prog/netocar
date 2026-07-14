<?php

namespace Tests\Feature;

use App\Models\agencies;
use App\Models\Branch;
use App\Models\clients;
use App\Models\employees;
use App\Models\reservations;
use App\Models\services;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchSupportTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_create_update_and_view_branches_for_their_agency(): void
    {
        [$manager, $agency] = $this->managerWithAgency();

        $this->actingAs($manager)
            ->post(route('branches.store'), [
                'name' => 'Downtown Wash',
                'code' => 'dw-1',
                'address' => '12 Main Avenue',
                'phone_number' => '0612345678',
                'is_active' => '1',
            ])
            ->assertRedirect(route('branches.index'));

        $branch = Branch::where('agency_id', $agency->id)->firstOrFail();

        $this->assertSame('DW-1', $branch->code);

        $this->actingAs($manager)
            ->put(route('branches.update', $branch), [
                'name' => 'Downtown Wash Updated',
                'code' => 'dw-2',
                'address' => '15 Main Avenue',
                'phone_number' => '0699999999',
                'is_active' => '0',
            ])
            ->assertRedirect(route('branches.index'));

        $this->actingAs($manager)
            ->get(route('branches.index'))
            ->assertOk()
            ->assertSee('Downtown Wash Updated')
            ->assertSee('Inactive');

        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'agency_id' => $agency->id,
            'name' => 'Downtown Wash Updated',
            'code' => 'DW-2',
            'is_active' => false,
        ]);
    }

    public function test_manager_can_assign_and_filter_clients_by_branch(): void
    {
        [$manager, $agency] = $this->managerWithAgency();
        $branch = Branch::factory()->create(['agency_id' => $agency->id, 'name' => 'North Site']);
        $otherBranch = Branch::factory()->create(['agency_id' => $agency->id, 'name' => 'South Site']);

        clients::factory()->create([
            'agency_id' => $agency->id,
            'branch_id' => $branch->id,
            'name' => 'North Client',
        ]);
        clients::factory()->create([
            'agency_id' => $agency->id,
            'branch_id' => $otherBranch->id,
            'name' => 'South Client',
        ]);

        $this->actingAs($manager)
            ->get(route('clients.index', ['branch_id' => $branch->id]))
            ->assertOk()
            ->assertSee('North Client')
            ->assertSee('North Site')
            ->assertDontSee('South Client');
    }

    public function test_reservation_ticket_created_from_confirmation_keeps_branch(): void
    {
        [$manager, $agency] = $this->managerWithAgency();
        $branch = Branch::factory()->create(['agency_id' => $agency->id]);
        $client = clients::factory()->create(['agency_id' => $agency->id, 'branch_id' => $branch->id]);
        $employee = employees::factory()->create(['agency_id' => $agency->id, 'branch_id' => $branch->id]);
        $service = services::factory()->create([
            'agency_id' => $agency->id,
            'branch_id' => $branch->id,
            'price' => 90,
        ]);
        $reservation = reservations::factory()->create([
            'agency_id' => $agency->id,
            'branch_id' => $branch->id,
            'client_id' => $client->id,
            'employee_id' => $employee->id,
            'service_id' => $service->id,
            'reservation_date' => now()->addDay()->setTime(10, 0),
            'status' => 'pending',
        ]);

        $this->actingAs($manager)
            ->put(route('reservations.update', $reservation), [
                'client_id' => $client->id,
                'employee_id' => $employee->id,
                'service_id' => $service->id,
                'branch_id' => $branch->id,
                'vehicle_type' => 'car',
                'plate_number' => 'BR-123',
                'reservation_date' => now()->addDay()->setTime(10, 0)->format('Y-m-d H:i:s'),
                'status' => 'confirmed',
            ])
            ->assertRedirect(route('reservations.index'));

        $ticket = Ticket::where('reservation_id', $reservation->id)->firstOrFail();

        $this->assertSame($branch->id, $reservation->refresh()->branch_id);
        $this->assertSame($branch->id, $ticket->branch_id);
    }

    public function test_manager_cannot_use_branch_from_another_agency(): void
    {
        [$manager, $agency] = $this->managerWithAgency();
        [, $otherAgency] = $this->managerWithAgency();
        $otherBranch = Branch::factory()->create(['agency_id' => $otherAgency->id]);

        $this->actingAs($manager)
            ->from(route('clients.create'))
            ->post(route('clients.store'), [
                'name' => 'Wrong Branch Client',
                'email' => 'wrong-branch@example.com',
                'phone' => '0612345678',
                'branch_id' => $otherBranch->id,
            ])
            ->assertRedirect(route('clients.create'))
            ->assertSessionHasErrors('branch_id');

        $this->assertDatabaseMissing('clients', [
            'agency_id' => $agency->id,
            'email' => 'wrong-branch@example.com',
        ]);
    }

    public function test_branch_with_linked_records_cannot_be_deleted(): void
    {
        [$manager, $agency] = $this->managerWithAgency();
        $branch = Branch::factory()->create(['agency_id' => $agency->id]);

        employees::factory()->create([
            'agency_id' => $agency->id,
            'branch_id' => $branch->id,
        ]);

        $this->actingAs($manager)
            ->delete(route('branches.destroy', $branch))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
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
