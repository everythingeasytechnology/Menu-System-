<?php

namespace Tests\Feature\Api\V1;

class BusinessSettingsApiTest extends ApiTestCase
{
    public function test_owner_can_update_business_settings_from_app_api(): void
    {
        [$business, $user] = $this->createBusinessUser('settings-api@example.com');

        $response = $this
            ->withHeaders($this->authHeaders($user))
            ->putJson('/api/v1/business/settings', [
                'settings' => [
                    'brand_name' => 'Mobile Cafe',
                    'business_email' => 'mobile@example.com',
                    'shop_no' => 'A-12',
                    'address' => 'MG Road',
                    'country' => 'India',
                    'state' => 'Delhi',
                    'district' => 'New Delhi',
                    'pincode' => '110001',
                    'latitude' => '28.6304',
                    'longitude' => '77.2177',
                    'gst_no' => '07ABCDE1234F1Z5',
                    'gst_enabled' => true,
                    'cgst' => 2.5,
                    'sgst' => 2.5,
                ],
                'payments' => [
                    'cash_enabled' => false,
                    'razorpay_enabled' => true,
                    'razorpay_key_id' => 'rzp_test_mobile',
                    'razorpay_key_secret' => 'secret_mobile',
                ],
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Business settings updated')
            ->assertJsonPath('data.settings.brand_name', 'Mobile Cafe')
            ->assertJsonPath('data.settings.business_email', 'mobile@example.com')
            ->assertJsonPath('data.settings.gst_enabled', true)
            ->assertJsonPath('data.settings.cgst', 2.5)
            ->assertJsonPath('data.payments.cash_enabled', false)
            ->assertJsonPath('data.payments.razorpay_enabled', true)
            ->assertJsonPath('data.payments.razorpay_key_id', 'rzp_test_mobile')
            ->assertJsonMissing(['razorpay_key_secret' => 'secret_mobile']);

        $this->assertDatabaseHas('business_settings', [
            'business_id' => $business->id,
            'brand_name' => 'Mobile Cafe',
            'business_email' => 'mobile@example.com',
            'gst_no' => '07ABCDE1234F1Z5',
            'gst_enabled' => true,
            'cgst' => 2.5,
            'sgst' => 2.5,
        ]);
        $this->assertDatabaseHas('cash_settings', [
            'business_id' => $business->id,
            'enabled' => false,
        ]);
        $this->assertDatabaseHas('razorpay_settings', [
            'business_id' => $business->id,
            'enabled' => true,
            'key_id' => 'rzp_test_mobile',
            'key_secret' => 'secret_mobile',
        ]);
        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'name' => 'Mobile Cafe',
            'email' => 'mobile@example.com',
            'gst_number' => '07ABCDE1234F1Z5',
        ]);
    }

    public function test_settings_update_accepts_flat_mobile_payload(): void
    {
        [$business, $user] = $this->createBusinessUser('settings-flat-api@example.com');

        $response = $this
            ->withHeaders($this->authHeaders($user))
            ->patchJson('/api/v1/business/settings', [
                'brand_name' => 'Flat Payload Cafe',
                'business_email' => 'flat@example.com',
                'cash_enabled' => true,
                'razorpay_enabled' => false,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.settings.brand_name', 'Flat Payload Cafe')
            ->assertJsonPath('data.payments.cash_enabled', true)
            ->assertJsonPath('data.payments.razorpay_enabled', false);

        $this->assertDatabaseHas('business_settings', [
            'business_id' => $business->id,
            'brand_name' => 'Flat Payload Cafe',
            'business_email' => 'flat@example.com',
        ]);
    }

    public function test_settings_update_validates_app_payload(): void
    {
        [, $user] = $this->createBusinessUser('settings-validation-api@example.com');

        $response = $this
            ->withHeaders($this->authHeaders($user))
            ->putJson('/api/v1/business/settings', [
                'settings' => [
                    'business_email' => 'not-an-email',
                    'cgst' => 101,
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation failed')
            ->assertJsonValidationErrors(['settings.business_email', 'settings.cgst']);
    }

    public function test_razorpay_enable_requires_keys_when_missing(): void
    {
        [, $user] = $this->createBusinessUser('settings-razorpay-api@example.com');

        $response = $this
            ->withHeaders($this->authHeaders($user))
            ->putJson('/api/v1/business/settings', [
                'payments' => [
                    'razorpay_enabled' => true,
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation failed');

        $this->assertDatabaseMissing('razorpay_settings', [
            'enabled' => true,
        ]);
    }
}
