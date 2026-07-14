<?php

namespace Tests\Feature;

use App\Models\agencies;
use App\Models\employees;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanEnforcementTest extends TestCase
{
    use RefreshDatabase;

    public function test_basic_plan_blocks_employee_creation_after_limit(): void
    {
        [$manager, $agency] = $this->managerWithAgency('basic');
        $employeeLimit = config('netocar.plans.basic.limits.employees');

        employees::factory()
            ->count($employeeLimit)
            ->create(['agency_id' => $agency->id, 'phone_number' => '0611111111']);

        $this->actingAs($manager)
            ->post(route('employees.store'), [
                'name' => 'Extra Employee',
                'email' => 'extra.employee@example.com',
                'phone_number' => '0622222222',
                'job_title' => 'Technician',
                'salary' => 3500,
            ])
            ->assertSessionHasErrors('plan');

        $this->assertDatabaseMissing('employees', [
            'agency_id' => $agency->id,
            'email' => 'extra.employee@example.com',
        ]);
    }

    public function test_payment_amount_uses_configured_yearly_plan_price(): void
    {
        [$manager, $agency] = $this->managerWithAgency('standard');

        Payment::create([
            'agency_id' => $agency->id,
            'amount' => $agency->plan_amount,
            'status' => 'pending',
            'payment_method' => 'virement',
        ]);

        $this->assertDatabaseHas('payments', [
            'agency_id' => $agency->id,
            'amount' => config('netocar.plans.standard.price_yearly_mad'),
        ]);
    }

    private function managerWithAgency(string $package): array
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $agency = agencies::factory()->create([
            'package' => $package,
        ]);

        $manager->update(['agency_id' => $agency->id]);
        $manager->refresh();

        return [$manager, $agency];
    }
}
