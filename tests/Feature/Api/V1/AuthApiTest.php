<?php

namespace Tests\Feature\Api\V1;

use App\Models\Business;
use App\Models\User;

class AuthApiTest extends ApiTestCase
{
    public function test_register_creates_business_user_and_token(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Akhil',
            'email' => 'akhil@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'business_name' => 'Everything Easy Cafe',
            'business_type' => 'restaurant',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.business.name', 'Everything Easy Cafe');

        $this->assertDatabaseHas('businesses', ['name' => 'Everything Easy Cafe']);
        $this->assertDatabaseHas('users', ['email' => 'akhil@example.com', 'role' => 'owner']);
    }

    public function test_login_returns_token_and_me_requires_authentication(): void
    {
        [$business, $user] = $this->createBusinessUser('login@example.com');

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $login->assertOk()->assertJsonPath('data.business.id', $business->id);
        $token = $login->json('data.access_token');

        $this->getJson('/api/v1/auth/me')->assertUnauthorized();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id);
    }

    public function test_auth_login_returns_token_for_active_staff(): void
    {
        [$business] = $this->createBusinessUser('staff-login-owner@example.com');
        $staff = User::create([
            'business_id' => $business->id,
            'name' => 'Rahul Waiter',
            'email' => 'staff-login@example.com',
            'password' => 'password123',
            'role' => 'waiter',
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'staff-login@example.com',
            'password' => 'password123',
            'device_name' => 'Waiter App',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Logged in successfully')
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.user.id', $staff->id)
            ->assertJsonPath('data.user.role', 'waiter')
            ->assertJsonPath('data.business.id', $business->id)
            ->assertJsonStructure(['data' => ['access_token']]);

        $token = $response->json('data.access_token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.user.id', $staff->id);
    }

    public function test_auth_login_rejects_inactive_staff(): void
    {
        [$business] = $this->createBusinessUser('inactive-staff-owner@example.com');
        User::create([
            'business_id' => $business->id,
            'name' => 'Inactive Waiter',
            'email' => 'inactive-staff-login@example.com',
            'password' => 'password123',
            'role' => 'waiter',
            'status' => 'inactive',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'inactive-staff-login@example.com',
            'password' => 'password123',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Invalid credentials');
    }
}
