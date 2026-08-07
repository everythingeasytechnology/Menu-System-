<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\BusinessSetting;
use App\Models\RazorpaySetting;
use App\Models\CashSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test settings index page is accessible.
     */
    public function test_settings_page_loads_successfully(): void
    {
        $response = $this->get('/settings');

        $response->assertStatus(200);
        $response->assertViewHas('business');
        $response->assertViewHas('razorpay');
        $response->assertViewHas('cash');
    }

    /**
     * Test updating business settings with logo.
     */
    public function test_can_update_business_settings(): void
    {
        Storage::fake('public');

        $logo = UploadedFile::fake()->image('logo.png');

        $data = [
            'brand_name' => 'New Brand Name',
            'logo' => $logo,
            'business_email' => 'newbrand@example.com',
            'shop_no' => 'Suite 101',
            'address' => '123 Main Street',
            'country' => 'India',
            'state' => 'Delhi',
            'district' => 'Central City',
            'pincode' => '999999',
            'latitude' => '28.6304',
            'longitude' => '77.2177',
            'gst_no' => '22AAAAA1111A1Z1',
        ];

        $response = $this->post('/settings/business', $data);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Business details updated successfully!');

        $this->assertDatabaseHas('business_settings', [
            'brand_name' => 'New Brand Name',
            'business_email' => 'newbrand@example.com',
            'latitude' => '28.6304',
            'longitude' => '77.2177',
        ]);

        $business = BusinessSetting::first();
        $this->assertNotNull($business->logo_path);
        Storage::disk('public')->assertExists($business->logo_path);
    }

    /**
     * Test updating Razorpay configurations.
     */
    public function test_can_update_razorpay_settings(): void
    {
        $data = [
            'enabled' => '1',
            'key_id' => 'rzp_test_key_123',
            'key_secret' => 'secret_xyz_789',
        ];

        $response = $this->post('/settings/razorpay', $data);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Razorpay settings updated successfully!');
        
        $this->assertDatabaseHas('razorpay_settings', [
            'enabled' => true,
            'key_id' => 'rzp_test_key_123',
            'key_secret' => 'secret_xyz_789',
        ]);
    }

    /**
     * Test updating Cash configuration.
     */
    public function test_can_update_cash_settings(): void
    {
        $data = [
            'enabled' => '1',
        ];

        $response = $this->post('/settings/cash', $data);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Cash payment status updated successfully!');
        
        $this->assertDatabaseHas('cash_settings', [
            'enabled' => true,
        ]);
    }

    /**
     * Test updating password.
     */
    public function test_can_change_user_password(): void
    {
        // Create user
        $user = User::create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'password' => Hash::make('old_password'),
        ]);

        // Try changing password
        $response = $this->actingAs($user)->post('/settings/password', [
            'current_password' => 'old_password',
            'new_password' => 'new_secure_password_123',
            'new_password_confirmation' => 'new_secure_password_123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Password updated successfully!');

        $user->refresh();
        $this->assertTrue(Hash::check('new_secure_password_123', $user->password));
    }

    /**
     * Test updating GST billing configurations.
     */
    public function test_can_update_gst_settings(): void
    {
        $data = [
            'gst_no' => '07BBBBB2222B2Z2',
            'gst_enabled' => '1',
            'cgst' => '1.50',
            'sgst' => '3.50',
        ];

        $response = $this->post('/settings/gst', $data);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'GST settings updated successfully!');

        $this->assertDatabaseHas('business_settings', [
            'gst_no' => '07BBBBB2222B2Z2',
            'gst_enabled' => true,
            'cgst' => 1.50,
            'sgst' => 3.50,
        ]);
    }
}
