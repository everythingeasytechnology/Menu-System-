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
        $notification = AppNotification::with(['user.deviceTokens' => function ($query) {
            $query->where('is_active', true);
        }])->find($this->notificationId);

        if (! $notification || ! $notification->user) {
            return;
        }

        foreach ($notification->user->deviceTokens as $deviceToken) {
            Log::info('Expo push notification queued for provider delivery.', [
                'notification_id' => $notification->id,
                'device_token_id' => $deviceToken->id,
                'platform' => $deviceToken->platform,
            ]);
        }
    }
}
