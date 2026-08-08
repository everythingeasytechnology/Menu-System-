<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\Notifications\DeviceTokenRequest;
use App\Http\Resources\Api\V1\DeviceTokenResource;
use App\Http\Resources\Api\V1\NotificationResource;
use App\Models\AppNotification;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $notifications = AppNotification::where('user_id', $request->user()->id)
            ->latest()
            ->paginate((int) $request->input('per_page', 25));

        return $this->success(NotificationResource::collection($notifications), 'Notifications');
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $count = AppNotification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->count();

        return $this->success(['unread_count' => $count], 'Unread notification count');
    }

    public function markRead(Request $request, AppNotification $notification): JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            return $this->error('Resource not found', 404);
        }

        $notification->update(['read_at' => now()]);

        return $this->success(new NotificationResource($notification->fresh()), 'Notification marked read');
    }

    public function readAll(Request $request): JsonResponse
    {
        AppNotification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return $this->success(null, 'Notifications marked read');
    }

    public function storeDeviceToken(DeviceTokenRequest $request): JsonResponse
    {
        $deviceToken = DeviceToken::updateOrCreate(
            ['device_token' => $request->validated('device_token')],
            [
                'user_id' => $request->user()->id,
                'business_id' => $request->user()->business_id,
                'platform' => $request->validated('platform'),
                'app_version' => $request->validated('app_version'),
                'device_id' => $request->validated('device_id'),
                'is_active' => true,
                'last_used_at' => now(),
            ],
        );

        return $this->success(new DeviceTokenResource($deviceToken), 'Device token registered', 201);
    }

    public function deleteDeviceToken(Request $request, DeviceToken $deviceToken): JsonResponse
    {
        if ($deviceToken->user_id !== $request->user()->id) {
            return $this->error('Resource not found', 404);
        }

        $deviceToken->update(['is_active' => false]);

        return $this->success(null, 'Device token deactivated');
    }
}
