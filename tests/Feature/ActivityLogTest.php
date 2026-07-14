<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\agencies;
use App\Models\clients;
use App\Models\employees;
use App\Models\Payment;
use App\Models\reservations;
use App\Models\services;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_status_change_is_audited_with_actor_and_agency(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [, $agency] = $this->managerWithAgency();

        $payment = Payment::create([
            'agency_id' => $agency->id,
            'amount' => 500,
            'status' => 'pending',
            'payment_method' => 'cashpluss',
        ]);

        $this->actingAs($admin)
            ->put(route('payments.update', $payment), [
                'status' => 'completed',
            ])
            ->assertRedirect(route('payments.index'));

        $log = ActivityLog::where('action', 'payment.status_changed')->firstOrFail();

        $this->assertSame($admin->id, $log->user_id);
        $this->assertSame($agency->id, $log->agency_id);
        $this->assertSame($agency->name, $log->agency_name);
        $this->assertSame('pending', data_get($log->changes, 'status.from'));
        $this->assertSame('completed', data_get($log->changes, 'status.to'));
    }

    public function test_client_update_is_audited_and_manager_activity_view_is_agency_scoped(): void
    {
        [$manager, $agency] = $this->managerWithAgency();
        [, $otherAgency] = $this->managerWithAgency();

        $client = clients::factory()->create([
            'agency_id' => $agency->id,
            'name' => 'Before Client',
            'phone_number' => '0612345678',
        ]);

        ActivityLog::create([
            'agency_id' => $otherAgency->id,
            'agency_name' => $otherAgency->name,
            'user_id' => $manager->id,
            'user_name' => $manager->name,
            'user_role' => 'manager',
            'action' => 'client.updated',
            'subject_type' => 'clients',
            'subject_id' => 999,
            'subject_label' => 'Other Agency Client',
        ]);

        $this->actingAs($manager)
            ->put(route('clients.update', $client), [
                'name' => 'After Client',
                'email' => $client->email,
                'phone' => $client->phone_number,
            ])
            ->assertRedirect(route('clients.index'));

        $log = ActivityLog::where('agency_id', $agency->id)
            ->where('action', 'client.updated')
            ->firstOrFail();

        $this->assertSame('Before Client', data_get($log->changes, 'name.from'));
        $this->assertSame('After Client', data_get($log->changes, 'name.to'));

        $this->actingAs($manager)
            ->get(route('activity-logs.index'))
            ->assertOk()
            ->assertSee('After Client')
            ->assertDontSee('Other Agency Client');
    }

    public function test_reservation_delete_is_audited_before_the_record_is_removed(): void
    {
        [$manager, $agency] = $this->managerWithAgency();
        $employee = employees::factory()->create(['agency_id' => $agency->id]);
        $client = clients::factory()->create(['agency_id' => $agency->id]);
        $service = services::factory()->create(['agency_id' => $agency->id]);
        $reservation = reservations::factory()->create([
            'agency_id' => $agency->id,
            'client_id' => $client->id,
            'employee_id' => $employee->id,
            'service_id' => $service->id,
            'plate_number' => 'AUD-123',
            'status' => 'pending',
        ]);

        $this->actingAs($manager)
            ->delete(route('reservations.destroy', $reservation))
            ->assertRedirect(route('reservations.index'));

        $this->assertSoftDeleted('reservations', ['id' => $reservation->id]);

        $log = ActivityLog::where('action', 'reservation.deleted')->firstOrFail();

        $this->assertSame($reservation->id, $log->subject_id);
        $this->assertSame($agency->id, $log->agency_id);
        $this->assertSame('AUD-123', data_get($log->metadata, 'plate_number'));
        $this->assertSame('pending', data_get($log->metadata, 'status'));
    }

    public function test_staff_cannot_access_activity_log(): void
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
            ->get(route('activity-logs.index'))
            ->assertForbidden();
    }

    private function managerWithAgency(): array
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $agency = agencies::factory()->create();

        $manager->update(['agency_id' => $agency->id]);

        return [$manager->refresh(), $agency];
    }
}
