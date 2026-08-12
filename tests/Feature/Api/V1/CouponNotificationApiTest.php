<?php

namespace Tests\Feature\Api\V1;

use App\Models\AppNotification;
use App\Models\Coupon;

class CouponNotificationApiTest extends ApiTestCase
{
    public function test_coupon_validation_and_expiration(): void
    {
        [$business, $user] = $this->createBusinessUser();

        Coupon::create([
            'business_id' => $business->id,
            'code' => 'SAVE10',
            'type' => 'percentage',
            'value' => 10,
            'minimum_order' => 100,
            'maximum_discount' => 50,
            'is_active' => true,
        ]);

        Coupon::create([
            'business_id' => $business->id,
            'code' => 'OLD',
            'type' => 'fixed',
            'value' => 20,
            'expires_at' => now()->subDay(),
            'is_active' => true,
        ]);

        $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/v1/coupons/validate', ['code' => 'SAVE10', 'subtotal' => 200])
            ->assertOk()
            ->assertJsonPath('data.discount', 20);

        $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/v1/coupons/validate', ['code' => 'OLD', 'subtotal' => 200])
            ->assertUnprocessable();
    }

    public function test_expo_push_token_and_notification_read_flow(): void
    {
        [$business, $user] = $this->createBusinessUser();

        $tokenResponse = $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/v1/device-tokens', [
                'device_token' => 'ExponentPushToken[test]',
                'platform' => 'expo',
                'app_version' => '1.0.0',
                'device_id' => 'device-1',
            ]);

        $tokenResponse->assertCreated()
            ->assertJsonPath('message', 'Expo push token registered');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'expo_push_token' => 'ExponentPushToken[test]',
        ]);
        $this->assertDatabaseCount('device_tokens', 0);

        $notification = AppNotification::create([
            'user_id' => $user->id,
            'business_id' => $business->id,
            'type' => 'system',
            'title' => 'Hello',
            'message' => 'Welcome',
        ]);

        $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/v1/notifications/unread-count')
            ->assertOk()
            ->assertJsonPath('data.unread_count', 1);

        $this->withHeaders($this->authHeaders($user))
            ->postJson("/api/v1/notifications/{$notification->id}/read")
            ->assertOk()
            ->assertJsonPath('data.read_at', fn ($value) => $value !== null);

        $this->withHeaders($this->authHeaders($user))
            ->deleteJson('/api/v1/device-tokens')
            ->assertOk()
            ->assertJsonPath('message', 'Expo push token removed');

        $this->assertNull($user->fresh()->expo_push_token);
    }
}
