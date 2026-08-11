<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_login_and_open_admin_dashboard(): void
    {
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => 'password123',
            'role' => 'superadmin',
            'status' => 'active',
        ]);

        $this->post('/login', [
            'email' => 'superadmin@example.com',
            'password' => 'password123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->get('/admin')
            ->assertOk()
            ->assertSee('Business Control Center')
            ->assertSee('Create Business');
    }

    public function test_owner_cannot_open_superadmin_section(): void
    {
        $this->signInBusinessOwner('owner-admin-block@example.com');

        $this->get('/admin')
            ->assertRedirect(route('dashboard'));
    }

    public function test_superadmin_can_create_business_with_owner_login(): void
    {
        $superadmin = User::create([
            'name' => 'Super Admin',
            'email' => 'create-business-super@example.com',
            'password' => 'password123',
            'role' => 'superadmin',
            'status' => 'active',
        ]);

        $this->actingAs($superadmin);

        $this->post(route('admin.businesses.store'), [
            'business_name' => 'Admin Created Cafe',
            'business_type' => 'restaurant',
            'business_status' => 'active',
            'business_email' => 'cafe@example.com',
            'business_phone' => '9999999999',
            'city' => 'Dehradun',
            'state' => 'Uttarakhand',
            'country' => 'India',
            'owner_name' => 'Cafe Owner',
            'owner_email' => 'cafe-owner@example.com',
            'owner_phone' => '8888888888',
            'owner_password' => 'password123',
            'owner_password_confirmation' => 'password123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('businesses', [
            'name' => 'Admin Created Cafe',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'cafe-owner@example.com',
            'role' => 'owner',
            'status' => 'active',
        ]);
    }

    public function test_superadmin_can_suspend_business_and_owner_access_is_blocked(): void
    {
        $superadmin = User::create([
            'name' => 'Super Admin',
            'email' => 'suspend-business-super@example.com',
            'password' => 'password123',
            'role' => 'superadmin',
            'status' => 'active',
        ]);

        $owner = User::create([
            'name' => 'Owner',
            'email' => 'suspended-owner@example.com',
            'password' => 'password123',
            'role' => 'owner',
            'status' => 'active',
        ]);

        $business = Business::create([
            'owner_user_id' => $owner->id,
            'name' => 'Suspend Me',
            'type' => 'restaurant',
            'country' => 'India',
            'timezone' => 'Asia/Kolkata',
            'status' => 'active',
        ]);

        $owner->update(['business_id' => $business->id]);

        $this->actingAs($superadmin)
            ->put(route('admin.businesses.update', $business), [
                'name' => 'Suspend Me',
                'type' => 'restaurant',
                'status' => 'suspended',
                'email' => 'suspend@example.com',
                'phone' => null,
                'city' => null,
                'state' => null,
                'country' => 'India',
            ])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'status' => 'suspended',
        ]);

        $this->actingAs($owner)
            ->get('/')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
