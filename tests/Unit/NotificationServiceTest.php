<?php

namespace Tests\Unit;

use App\Jobs\SendExpoPushNotification;
use App\Models\Business;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_dispatches_push_notifications_after_response_by_default(): void
    {
        Bus::fake();

        $user = $this->makeUser();

        $notification = app(NotificationService::class)->notifyUser(
            $user,
            'order_created',
            'New order received',
            'Order ORD-1001 is pending.',
        );

        $this->assertNotNull($notification->id);
        Bus::assertDispatchedAfterResponse(SendExpoPushNotification::class);
    }

    public function test_it_can_dispatch_push_notifications_to_the_queue(): void
    {
        Bus::fake();
        config()->set('services.expo.dispatch_mode', 'queued');

        $user = $this->makeUser();

        app(NotificationService::class)->notifyUser(
            $user,
            'order_created',
            'New order received',
            'Order ORD-1001 is pending.',
        );

        Bus::assertDispatched(SendExpoPushNotification::class);
    }

    public function test_it_can_dispatch_push_notifications_synchronously(): void
    {
        Bus::fake();
        config()->set('services.expo.dispatch_mode', 'sync');

        $user = $this->makeUser();

        app(NotificationService::class)->notifyUser(
            $user,
            'order_created',
            'New order received',
            'Order ORD-1001 is pending.',
        );

        Bus::assertDispatchedSync(SendExpoPushNotification::class);
    }

    private function makeUser(): User
    {
        $user = User::create([
            'name' => 'Owner',
            'email' => 'owner+'.uniqid().'@example.com',
            'password' => 'password123',
            'role' => 'owner',
            'status' => 'active',
        ]);

        $business = Business::create([
            'owner_user_id' => $user->id,
            'name' => 'Test Restaurant',
            'type' => 'restaurant',
            'status' => 'active',
        ]);

        $user->update(['business_id' => $business->id]);

        return $user->fresh();
    }
}
