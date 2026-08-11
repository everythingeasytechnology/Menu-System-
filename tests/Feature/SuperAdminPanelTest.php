<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\PresetFoodImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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
            ->assertSee('ServiceOS Admin')
            ->assertSee('Business Overview')
            ->assertSee('Orders and Sales')
            ->assertSee('View Businesses')
            ->assertSee('Create Business')
            ->assertSee('Menu Images');
    }

    public function test_owner_cannot_open_superadmin_section(): void
    {
        $this->signInBusinessOwner('owner-admin-block@example.com');

        $this->get('/admin')
            ->assertRedirect(route('dashboard'));
    }

    public function test_admin_dashboard_shows_business_owners_not_staff_users(): void
    {
        $superadmin = User::create([
            'name' => 'Super Admin',
            'email' => 'owners-only-super@example.com',
            'password' => 'password123',
            'role' => 'superadmin',
            'status' => 'active',
        ]);

        $owner = User::create([
            'name' => 'Visible Owner',
            'email' => 'visible-owner@example.com',
            'password' => 'password123',
            'role' => 'owner',
            'status' => 'active',
        ]);

        $business = Business::create([
            'owner_user_id' => $owner->id,
            'name' => 'Owner Only Cafe',
            'type' => 'restaurant',
            'country' => 'India',
            'timezone' => 'Asia/Kolkata',
            'status' => 'active',
        ]);

        $owner->update(['business_id' => $business->id]);

        User::create([
            'business_id' => $business->id,
            'name' => 'Hidden Waiter',
            'email' => 'hidden-waiter@example.com',
            'password' => 'password123',
            'role' => 'waiter',
            'status' => 'active',
        ]);

        $this->actingAs($superadmin);

        $this->get('/admin')
            ->assertOk()
            ->assertSee('Business Overview')
            ->assertDontSee('Owner Only Cafe')
            ->assertDontSee('Visible Owner')
            ->assertDontSee('Hidden Waiter');

        $this->get(route('admin.businesses.index'))
            ->assertOk()
            ->assertSee('Owner Only Cafe')
            ->assertSee('Visible Owner')
            ->assertSee('Edit')
            ->assertDontSee('Hidden Waiter')
            ->assertDontSee('hidden-waiter@example.com')
            ->assertDontSee('Control platform users');

        $this->get(route('admin.businesses.edit', $business))
            ->assertOk()
            ->assertSee('Edit Business Details')
            ->assertSee('Owner Only Cafe')
            ->assertSee('Visible Owner');
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
        ])->assertRedirect(route('admin.businesses.index'));

        $this->assertDatabaseHas('businesses', [
            'name' => 'Admin Created Cafe',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'cafe-owner@example.com',
            'role' => 'owner',
            'status' => 'active',
        ]);

        $this->get(route('admin.businesses.create'))
            ->assertOk()
            ->assertSee('Business Details')
            ->assertSee('Owner Login');
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
            ->assertRedirect(route('admin.businesses.edit', $business));

        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'status' => 'suspended',
        ]);

        $this->actingAs($owner)
            ->get('/')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_superadmin_can_manage_menu_image_library(): void
    {
        $superadmin = User::create([
            'name' => 'Super Admin',
            'email' => 'menu-images-super@example.com',
            'password' => 'password123',
            'role' => 'superadmin',
            'status' => 'active',
        ]);

        $this->actingAs($superadmin);

        $this->get(route('admin.menu-images.index'))
            ->assertOk()
            ->assertSee('Menu Item Images')
            ->assertSee('Image Library')
            ->assertSee('Upload');

        $this->post(route('admin.menu-images.store'), [
            'name' => 'Paneer Tikka',
            'tags' => 'paneer, starter, veg',
            'image' => UploadedFile::fake()->image('paneer.jpg', 320, 240),
        ])->assertRedirect(route('admin.menu-images.index'));

        $image = PresetFoodImage::where('name', 'Paneer Tikka')->firstOrFail();

        $this->assertStringStartsWith('storage/menu_items/', $image->image_path);
        $this->assertDatabaseHas('preset_food_images', [
            'name' => 'Paneer Tikka',
            'tags' => 'paneer, starter, veg',
        ]);

        $this->put(route('admin.menu-images.update', $image), [
            'name' => 'Paneer Malai Tikka',
            'tags' => 'paneer, creamy, starter',
        ])->assertRedirect(route('admin.menu-images.index'));

        $this->assertDatabaseHas('preset_food_images', [
            'id' => $image->id,
            'name' => 'Paneer Malai Tikka',
            'tags' => 'paneer, creamy, starter',
        ]);

        $this->delete(route('admin.menu-images.destroy', $image))
            ->assertRedirect(route('admin.menu-images.index'));

        $this->assertDatabaseMissing('preset_food_images', [
            'id' => $image->id,
        ]);
    }
}
