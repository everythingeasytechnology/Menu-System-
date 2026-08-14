<?php

namespace Tests\Unit;

use App\Jobs\SendExpoPushNotification;
use App\Models\AppNotification;
use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExpoPushNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sends_a_push_notification_to_expo(): void
    {
        Http::fake([
            'exp.host/*' => Http::response([
                'data' => ['status' => 'ok', 'id' => 'ticket-1'],
            ]),
        ]);

        $notification = $this->makeNotification('ExponentPushToken[valid-token]');

        (new SendExpoPushNotification($notification->id))->handle(app(\App\Services\ExpoPushService::class));

        Http::assertSent(function ($request) {
            return $request->url() === config('services.expo.push_url')
                && $request['to'] === 'ExponentPushToken[valid-token]'
                && $request['title'] === 'New order received';
        });

        $this->assertNotNull($notification->user->fresh()->expo_push_token);
    }

    public function test_it_clears_the_token_when_expo_reports_device_not_registered(): void
    {
        Http::fake([
            'exp.host/*' => Http::response([
                'data' => [
                    'status' => 'error',
                    'message' => 'The recipient device is not registered.',
                    'details' => ['error' => 'DeviceNotRegistered'],
                ],
            ]),
        ]);

        $notification = $this->makeNotification('ExponentPushToken[stale-token]');

        (new SendExpoPushNotification($notification->id))->handle(app(\App\Services\ExpoPushService::class));

        $this->assertNull($notification->user->fresh()->expo_push_token);
    }

    public function test_it_retries_on_transient_failure(): void
    {
        Http::fake([
            'exp.host/*' => Http::response(null, 500),
        ]);

        $notification = $this->makeNotification('ExponentPushToken[valid-token]');

        $this->expectException(\RuntimeException::class);

        (new SendExpoPushNotification($notification->id))->handle(app(\App\Services\ExpoPushService::class));
    }

    private function makeNotification(string $expoPushToken): AppNotification
    {
        $user = User::create([
            'name' => 'Owner',
            'email' => 'owner+'.uniqid().'@example.com',
            'password' => 'password123',
            'role' => 'owner',
            'status' => 'active',
            'expo_push_token' => $expoPushToken,
        ]);

        $business = Business::create([
            'owner_user_id' => $user->id,
            'name' => 'Test Restaurant',
            'type' => 'restaurant',
            'status' => 'active',
        ]);

        $user->update(['business_id' => $business->id]);

        return AppNotification::create([
            'user_id' => $user->id,
            'business_id' => $business->id,
            'type' => 'order_created',
            'title' => 'New order received',
            'message' => 'Order ORD-1001 is pending.',
        ]);
    }
}
