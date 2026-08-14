<?php

namespace App\Jobs;

use App\Models\AppNotification;
use App\Services\ExpoPushService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendExpoPushNotification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [10, 30, 60];

    public function __construct(private readonly int $notificationId)
    {
    }

    public function handle(ExpoPushService $expoPushService): void
    {
        $notification = AppNotification::with('user')->find($this->notificationId);

        if (! $notification || ! $notification->user || ! $notification->user->expo_push_token) {
            return;
        }

        $user = $notification->user;

        $result = $expoPushService->send(
            $user->expo_push_token,
            $notification->title,
            $notification->message,
            ['notification_id' => $notification->id, 'type' => $notification->type] + (is_array($notification->data) ? $notification->data : []),
        );

        if ($result === 'device_not_registered') {
            $user->update(['expo_push_token' => null]);

            return;
        }

        if ($result === 'error') {
            Log::warning('Expo push notification delivery failed.', [
                'notification_id' => $notification->id,
                'user_id' => $user->id,
                'attempt' => $this->attempts(),
            ]);

            throw new \RuntimeException("Expo push delivery failed for notification {$notification->id}.");
        }
    }
}
