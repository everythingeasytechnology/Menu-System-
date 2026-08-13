<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class BusinessOwnerProfileApiTest extends ApiTestCase
{
    public function test_owner_can_get_business_owner_profile(): void
    {
        [$business, $owner] = $this->createBusinessUser('owner-profile@example.com');

        $this->withHeaders($this->authHeaders($owner))
            ->getJson('/api/v1/business-owner/profile')
            ->assertOk()
            ->assertJsonPath('message', 'Business owner profile')
            ->assertJsonPath('data.owner.id', $owner->id)
            ->assertJsonPath('data.owner.email', 'owner-profile@example.com')
            ->assertJsonPath('data.business.id', $business->id)
            ->assertJsonPath('data.business.name', 'Test Restaurant');
    }

    public function test_owner_can_update_profile_and_business_logo_with_form_data(): void
    {
        Storage::fake('public');

        [$business, $owner] = $this->createBusinessUser('owner-form@example.com');

        $response = $this->withHeaders($this->authHeaders($owner))
            ->post('/api/v1/business-owner/profile', [
                'owner_name' => 'Updated Owner',
                'owner_email' => 'updated-owner@example.com',
                'owner_phone' => '+919999999991',
                'business_name' => 'Updated Cafe',
                'business_email' => 'business-updated@example.com',
                'business_phone' => '+919999999992',
                'gst_number' => '07ABCDE1234F1Z5',
                'shop_no' => 'A-12',
                'address' => 'MG Road',
                'city' => 'Delhi',
                'state' => 'Delhi',
                'district' => 'New Delhi',
                'country' => 'India',
                'pincode' => '110001',
                'profile_image' => UploadedFile::fake()->image('owner.jpg', 300, 300),
                'logo' => UploadedFile::fake()->image('logo.png', 300, 300),
            ]);

        $owner->refresh();
        $business->refresh();

        $response->assertOk()
            ->assertJsonPath('message', 'Business owner profile updated')
            ->assertJsonPath('data.owner.name', 'Updated Owner')
            ->assertJsonPath('data.owner.email', 'updated-owner@example.com')
            ->assertJsonPath('data.owner.profile_image_url', asset('storage/'.$owner->profile_image_path))
            ->assertJsonPath('data.business.name', 'Updated Cafe')
            ->assertJsonPath('data.business.logo_url', asset('storage/'.$business->logo_path))
            ->assertJsonPath('data.settings.brand_name', 'Updated Cafe')
            ->assertJsonPath('data.settings.logo_url', asset('storage/'.$business->logo_path));

        Storage::disk('public')->assertExists($owner->profile_image_path);
        Storage::disk('public')->assertExists($business->logo_path);

        $this->assertDatabaseHas('business_settings', [
            'business_id' => $business->id,
            'brand_name' => 'Updated Cafe',
            'business_email' => 'business-updated@example.com',
            'shop_no' => 'A-12',
            'gst_no' => '07ABCDE1234F1Z5',
        ]);
    }

    public function test_staff_cannot_manage_business_owner_profile(): void
    {
        [$business, $owner] = $this->createBusinessUser('owner-denied@example.com');
        $staff = User::create([
            'business_id' => $business->id,
            'name' => 'Waiter',
            'email' => 'owner-profile-staff@example.com',
            'password' => 'password123',
            'role' => 'waiter',
            'status' => 'active',
        ]);

        $this->withHeaders($this->authHeaders($staff))
            ->getJson('/api/v1/business-owner/profile')
            ->assertForbidden();

        $this->withHeaders($this->authHeaders($staff))
            ->post('/api/v1/business-owner/profile', [
                'owner_name' => 'Not Allowed',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('users', [
            'id' => $owner->id,
            'name' => 'Owner',
        ]);
    }
}
