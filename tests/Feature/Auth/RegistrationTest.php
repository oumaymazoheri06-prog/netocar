<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk();
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'agency_name' => 'Neto Wash',
            'agency_phone' => '0612345678',
            'agency_address' => 'Casablanca',
            'package' => 'basic',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('agencies', [
            'name' => 'Neto Wash',
            'package' => 'basic',
            'license_status' => 'suspended',
        ]);
        $this->assertNotNull(auth()->user()->agency_id);
    }
}
