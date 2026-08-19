<?php

namespace App\Services;

use App\Jobs\SendExpoPushNotification;
use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;

class NotificationService
{
    public function notifyUser(User $user, string $type, string $title, string $message, array $data = []): AppNotification
    {
        $notification = AppNotification::create([
            'user_id' => $user->id,
            'business_id' => $user->business_id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);

        $this->dispatchPushNotification($notification->id);

        return $notification;
    }

    public function notifyBusiness(int $businessId, string $type, string $title, string $message, array $data = []): Collection
    {
        return User::where('business_id', $businessId)
            ->where('status', 'active')
            ->get()
            ->map(fn (User $user) => $this->notifyUser($user, $type, $title, $message, $data));
    }

    private function dispatchPushNotification(int $notificationId): void
    {
        $job = new SendExpoPushNotification($notificationId);

        match (config('services.expo.dispatch_mode', 'after_response')) {
            'sync' => Bus::dispatchSync($job),
            'queued' => Bus::dispatch($job),
            default => Bus::dispatchAfterResponse($job),
        };
    }
}
