<?php

namespace Tests\Feature;

use App\Models\agencies;
use App\Models\employees;
use App\Models\Payment;
use App\Models\services;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgencyIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_only_sees_tickets_from_their_agency(): void
    {
        [$manager, $agency] = $this->managerWithAgency();
        [, $otherAgency] = $this->managerWithAgency();

        $ownTicket = $this->ticketForAgency($agency, 'OWN-123');
        $otherTicket = $this->ticketForAgency($otherAgency, 'OTHER-456');

        $this->actingAs($manager)
            ->get(route('tickets.index'))
            ->assertOk()
            ->assertSee($ownTicket->service->name)
            ->assertDontSee($otherTicket->service->name);

        $this->actingAs($manager)
            ->get(route('tickets.show', $otherTicket))
            ->assertNotFound();
    }

    public function test_manager_only_sees_payments_from_their_agency(): void
    {
        [$manager, $agency] = $this->managerWithAgency();
        [, $otherAgency] = $this->managerWithAgency();

        $ownPayment = Payment::create([
            'agency_id' => $agency->id,
            'amount' => 111,
            'status' => 'pending',
            'payment_method' => 'cashpluss',
        ]);

        $otherPayment = Payment::create([
            'agency_id' => $otherAgency->id,
            'amount' => 999,
            'status' => 'pending',
            'payment_method' => 'virement',
        ]);

        $this->actingAs($manager)
            ->get(route('payments.index'))
            ->assertOk()
            ->assertSee('MAD '.number_format($ownPayment->amount, 2))
            ->assertDontSee('MAD '.number_format($otherPayment->amount, 2));

        $this->actingAs($manager)
            ->get(route('payments.show', $otherPayment))
            ->assertNotFound();
    }

    private function managerWithAgency(): array
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $agency = agencies::factory()->create();

        $manager->update(['agency_id' => $agency->id]);
        $manager->refresh();

        return [$manager, $agency];
    }

    private function ticketForAgency(agencies $agency, string $plateNumber): Ticket
    {
        $service = services::factory()->create([
            'agency_id' => $agency->id,
            'name' => 'Service '.$plateNumber,
        ]);
        $employee = employees::factory()->create(['agency_id' => $agency->id]);

        return Ticket::create([
            'agency_id' => $agency->id,
            'service_id' => $service->id,
            'employee_id' => $employee->id,
            'vehicle_type' => 'car',
            'plate_number' => $plateNumber,
            'status' => 'waiting',
            'price' => $service->price,
        ]);
    }
}
