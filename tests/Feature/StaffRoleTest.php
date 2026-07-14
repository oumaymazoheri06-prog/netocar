<?php

namespace Tests\Feature;

use App\Models\agencies;
use App\Models\clients;
use App\Models\employees;
use App\Models\reservations;
use App\Models\services;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_only_sees_assigned_tickets_and_reservations(): void
    {
        [$staffUser, $staffEmployee, $agency] = $this->staffWithAgency();
        $otherEmployee = employees::factory()->create(['agency_id' => $agency->id]);

        $assignedTicket = $this->ticketForEmployee($agency, $staffEmployee, 'Assigned Wash');
        $otherTicket = $this->ticketForEmployee($agency, $otherEmployee, 'Other Wash');

        $assignedReservation = $this->reservationForEmployee($agency, $staffEmployee, 'Assigned Booking');
        $otherReservation = $this->reservationForEmployee($agency, $otherEmployee, 'Other Booking');

        $this->actingAs($staffUser)
            ->get(route('tickets.index'))
            ->assertOk()
            ->assertSee($assignedTicket->service->name)
            ->assertDontSee($otherTicket->service->name);

        $this->actingAs($staffUser)
            ->get(route('tickets.show', $otherTicket))
            ->assertForbidden();

        $this->actingAs($staffUser)
            ->get(route('reservations.index'))
            ->assertOk()
            ->assertSee($assignedReservation->client->name)
            ->assertDontSee($otherReservation->client->name);

        $this->actingAs($staffUser)
            ->get(route('reservations.show', $otherReservation))
            ->assertForbidden();
    }

    public function test_staff_can_update_only_the_status_of_an_assigned_ticket(): void
    {
        [$staffUser, $staffEmployee, $agency] = $this->staffWithAgency();
        $otherEmployee = employees::factory()->create(['agency_id' => $agency->id]);

        $assignedTicket = $this->ticketForEmployee($agency, $staffEmployee, 'Assigned Status Wash');
        $otherTicket = $this->ticketForEmployee($agency, $otherEmployee, 'Other Status Wash');

        $this->actingAs($staffUser)
            ->put(route('tickets.update', $assignedTicket), [
                'status' => 'in_progress',
                'employee_id' => $otherEmployee->id,
            ])
            ->assertRedirect(route('tickets.index'));

        $assignedTicket->refresh();

        $this->assertSame('in_progress', $assignedTicket->status);
        $this->assertSame($staffEmployee->id, $assignedTicket->employee_id);

        $this->actingAs($staffUser)
            ->from(route('tickets.index'))
            ->put(route('tickets.update', $assignedTicket), [
                'status' => 'cancelled',
            ])
            ->assertRedirect(route('tickets.index'))
            ->assertSessionHasErrors('status');

        $this->assertNotSame('cancelled', $assignedTicket->refresh()->status);

        $this->actingAs($staffUser)
            ->put(route('tickets.update', $otherTicket), [
                'status' => 'in_progress',
            ])
            ->assertForbidden();
    }

    public function test_staff_dashboard_renders_assigned_work(): void
    {
        [$staffUser, $staffEmployee, $agency] = $this->staffWithAgency();

        $ticket = $this->ticketForEmployee($agency, $staffEmployee, 'Dashboard Wash');
        $reservation = $this->reservationForEmployee($agency, $staffEmployee, 'Dashboard Booking');

        $this->actingAs($staffUser)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee($ticket->service->name)
            ->assertSee($reservation->client->name)
            ->assertSee('Mes travaux assignés');
    }

    public function test_manager_can_create_employee_with_staff_login_account(): void
    {
        [$manager, $agency] = $this->managerWithAgency();

        $this->actingAs($manager)
            ->post(route('employees.store'), [
                'name' => 'Nadia Staff',
                'email' => 'nadia.staff@example.com',
                'phone_number' => '0612345678',
                'job_title' => 'Wash Specialist',
                'salary' => 4500,
                'create_staff_account' => '1',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ])
            ->assertRedirect(route('employees.index'));

        $user = User::where('email', 'nadia.staff@example.com')->firstOrFail();

        $this->assertSame('staff', $user->role);
        $this->assertSame($agency->id, $user->agency_id);

        $this->assertDatabaseHas('employees', [
            'agency_id' => $agency->id,
            'user_id' => $user->id,
            'email' => 'nadia.staff@example.com',
        ]);
    }

    public function test_staff_cannot_open_manager_only_pages(): void
    {
        [$staffUser] = $this->staffWithAgency();

        $this->actingAs($staffUser)
            ->get(route('employees.index'))
            ->assertForbidden();

        $this->actingAs($staffUser)
            ->get(route('tickets.create'))
            ->assertForbidden();
    }

    private function managerWithAgency(): array
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $agency = agencies::factory()->create();

        $manager->update(['agency_id' => $agency->id]);

        return [$manager->refresh(), $agency];
    }

    private function staffWithAgency(): array
    {
        [, $agency] = $this->managerWithAgency();

        $staffUser = User::factory()->create([
            'role' => 'staff',
            'agency_id' => $agency->id,
            'email' => 'staff-'.uniqid().'@example.com',
        ]);

        $staffEmployee = employees::factory()->create([
            'agency_id' => $agency->id,
            'user_id' => $staffUser->id,
            'email' => $staffUser->email,
        ]);

        return [$staffUser->refresh(), $staffEmployee, $agency];
    }

    private function ticketForEmployee(agencies $agency, employees $employee, string $serviceName): Ticket
    {
        $service = services::factory()->create([
            'agency_id' => $agency->id,
            'name' => $serviceName,
        ]);

        return Ticket::create([
            'agency_id' => $agency->id,
            'service_id' => $service->id,
            'employee_id' => $employee->id,
            'vehicle_type' => 'car',
            'plate_number' => strtoupper(substr($serviceName, 0, 3)).'-123',
            'status' => 'waiting',
            'price' => $service->price,
        ]);
    }

    private function reservationForEmployee(agencies $agency, employees $employee, string $clientName): reservations
    {
        $client = clients::factory()->create([
            'agency_id' => $agency->id,
            'name' => $clientName,
        ]);

        $service = services::factory()->create([
            'agency_id' => $agency->id,
        ]);

        return reservations::factory()->create([
            'agency_id' => $agency->id,
            'client_id' => $client->id,
            'service_id' => $service->id,
            'employee_id' => $employee->id,
            'reservation_date' => now()->addDay(),
            'status' => 'pending',
        ]);
    }
}
