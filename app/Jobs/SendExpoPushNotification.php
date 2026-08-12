<?php

namespace App\Jobs;

use App\Models\AppNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendExpoPushNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly int $notificationId)
    {
    }

    public function handle(): void
    {
        $notification = AppNotification::with('user')->find($this->notificationId);

        if (! $notification || ! $notification->user || ! $notification->user->expo_push_token) {
            return;
        }

        Log::info('Expo push notification queued for provider delivery.', [
            'notification_id' => $notification->id,
            'user_id' => $notification->user->id,
            'has_expo_push_token' => true,
        ]);
    }
}
