<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BusinessOwnerRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_page_loads_and_is_linked_from_login(): void
    {
        $this->get('/register')->assertOk()->assertSee('Create admin account')->assertSee('Step');
        $this->get('/login')->assertOk()->assertSee('Create admin account');
    }

    public function test_guest_can_create_owner_account_with_valid_otp(): void
    {
        Mail::fake();
        $email = 'new-owner@example.com';
        Cache::put('auth:email-otp:'.sha1($email), ['email' => $email, 'otp_hash' => Hash::make('1234')], 300);

        $response = $this->post('/register', [
            'business_type' => 'restaurant', 'name' => 'New Owner', 'phone' => '9876543210', 'email' => $email,
            'password' => 'password123', 'password_confirmation' => 'password123', 'otp' => '1234',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => $email, 'role' => 'owner', 'status' => 'active']);
        $this->assertDatabaseHas('businesses', ['name' => "New Owner's Business", 'type' => 'restaurant']);
        $this->assertNull(Cache::get('auth:email-otp:'.sha1($email)));
    }

    public function test_registration_rejects_invalid_otp(): void
    {
        $this->post('/register', [
            'business_type' => 'cafe', 'name' => 'New Owner', 'phone' => '9876543210', 'email' => 'owner@example.com',
            'password' => 'password123', 'password_confirmation' => 'password123', 'otp' => '9999',
        ])->assertSessionHasErrors('otp');

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('businesses', 0);
    }
}
