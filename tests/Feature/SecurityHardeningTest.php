<?php

namespace Tests\Feature;

use App\Models\agencies;
use App\Models\Branch;
use App\Models\clients;
use App\Models\Payment;
use App\Models\reservations;
use App\Models\services;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_unverified_user_cannot_open_operational_pages(): void
    {
        $user = User::factory()->unverified()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_registration_attempts_are_rate_limited(): void
    {
        RateLimiter::clear('registration:test@example.com|127.0.0.1');

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('register.store'), ['email' => 'test@example.com']);
        }

        $this->post(route('register.store'), ['email' => 'test@example.com'])
            ->assertTooManyRequests();
    }

    public function test_branch_capacity_blocks_an_unassigned_overlapping_reservation(): void
    {
        [$manager, $agency] = $this->managerWithAgency();
        $branch = Branch::factory()->create([
            'agency_id' => $agency->id,
            'simultaneous_capacity' => 1,
        ]);
        $service = services::factory()->create([
            'agency_id' => $agency->id,
            'branch_id' => $branch->id,
            'duration_minutes' => 60,
        ]);
        $client = clients::factory()->create([
            'agency_id' => $agency->id,
            'branch_id' => $branch->id,
        ]);
        $startsAt = now()->addDay()->setTime(10, 0);

        reservations::factory()->create([
            'agency_id' => $agency->id,
            'branch_id' => $branch->id,
            'client_id' => $client->id,
            'service_id' => $service->id,
            'employee_id' => null,
            'reservation_date' => $startsAt,
            'duration_minutes' => 60,
            'status' => 'pending',
        ]);

        $this->actingAs($manager)
            ->post(route('reservations.store'), [
                'client_id' => $client->id,
                'service_id' => $service->id,
                'branch_id' => $branch->id,
                'vehicle_type' => 'car',
                'reservation_date' => $startsAt->copy()->addMinutes(15)->format('Y-m-d H:i:s'),
            ])
            ->assertSessionHasErrors('reservation_date');
    }

    public function test_duplicate_pending_payment_is_rejected_without_orphaning_receipt(): void
    {
        Storage::fake('local');
        [$manager, $agency] = $this->managerWithAgency();

        Payment::create([
            'agency_id' => $agency->id,
            'plan' => $agency->package,
            'amount' => $agency->plan_amount,
            'status' => 'pending',
            'payment_method' => 'virement',
        ]);

        $this->actingAs($manager)
            ->post(route('payment.virement.store'), [
                'payment_method' => 'virement',
                'receipt_photo' => UploadedFile::fake()->image('receipt.jpg'),
            ])
            ->assertSessionHasErrors('payment_method');

        $this->assertSame([], Storage::disk('local')->allFiles('payments/receipts'));
    }

    public function test_web_responses_include_security_headers(): void
    {
        $this->get(route('home'))
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    private function managerWithAgency(): array
    {
        $agency = agencies::factory()->create([
            'license_status' => 'active',
            'license_expires_at' => now()->addYear(),
        ]);
        $manager = User::factory()->create([
            'role' => 'manager',
            'agency_id' => $agency->id,
        ]);

        return [$manager, $agency];
    }
}
