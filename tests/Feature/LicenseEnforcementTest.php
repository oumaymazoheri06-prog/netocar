<?php

namespace Tests\Feature;

use App\Models\agencies;
use App\Models\employees;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicenseEnforcementTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_manager_is_redirected_to_billing_and_can_still_open_payments(): void
    {
        [$manager] = $this->managerWithAgency([
            'license_status' => 'active',
            'license_expires_at' => now()->subDay()->toDateString(),
        ]);

        $this->actingAs($manager)
            ->get(route('clients.index'))
            ->assertRedirect(route('agency-billing.edit'))
            ->assertSessionHas('license_error');

        $this->actingAs($manager)
            ->get(route('agency-billing.edit'))
            ->assertOk();

        $this->actingAs($manager)
            ->get(route('payments.index'))
            ->assertOk();
    }

    public function test_suspended_staff_is_blocked_from_operational_pages(): void
    {
        [, $agency] = $this->managerWithAgency([
            'license_status' => 'suspended',
            'license_expires_at' => now()->addMonth()->toDateString(),
        ]);

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
            ->get(route('tickets.index'))
            ->assertForbidden();
    }

    public function test_completed_payment_renews_agency_license_for_one_year(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [, $agency] = $this->managerWithAgency([
            'license_status' => 'suspended',
            'license_expires_at' => now()->subMonth()->toDateString(),
        ]);

        $payment = Payment::create([
            'agency_id' => $agency->id,
            'amount' => $agency->plan_amount,
            'status' => 'pending',
            'payment_method' => 'virement',
        ]);

        $this->actingAs($admin)
            ->put(route('payments.update', $payment), [
                'status' => 'completed',
            ])
            ->assertRedirect(route('payments.index'));

        $agency->refresh();

        $this->assertSame('active', $agency->license_status);
        $this->assertTrue($agency->hasActiveLicense());
        $this->assertSame(now()->addYearNoOverflow()->toDateString(), $agency->license_expires_at->toDateString());

        $this->assertDatabaseHas('activity_logs', [
            'agency_id' => $agency->id,
            'action' => 'agency.license_renewed',
        ]);
    }

    private function managerWithAgency(array $agencyOverrides = []): array
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $agency = agencies::factory()->create($agencyOverrides);

        $manager->update(['agency_id' => $agency->id]);

        return [$manager->refresh(), $agency];
    }
}
