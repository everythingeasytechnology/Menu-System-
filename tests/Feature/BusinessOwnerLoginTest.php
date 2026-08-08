<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessOwnerLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_loads_for_guests(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Business Owner Login');
    }

    public function test_business_owner_can_login_and_reach_dashboard(): void
    {
        User::create([
            'name' => 'Owner',
            'email' => 'owner-login@example.com',
            'password' => 'password123',
            'role' => 'owner',
            'status' => 'active',
        ]);

        $this->post('/login', [
            'email' => 'owner-login@example.com',
            'password' => 'password123',
        ])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();

        $this->get('/')->assertOk();
    }

    public function test_non_owner_staff_cannot_login_to_owner_dashboard(): void
    {
        User::create([
            'name' => 'Waiter',
            'email' => 'waiter-login@example.com',
            'password' => 'password123',
            'role' => 'waiter',
            'status' => 'active',
        ]);

        $this->post('/login', [
            'email' => 'waiter-login@example.com',
            'password' => 'password123',
        ])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_guest_dashboard_request_redirects_to_login(): void
    {
        $this->get('/')
            ->assertRedirect(route('login'));
    }
}
