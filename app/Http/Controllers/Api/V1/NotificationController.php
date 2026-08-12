<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\Notifications\DeviceTokenRequest;
use App\Http\Resources\Api\V1\NotificationResource;
use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $token = $request->validated('expo_push_token') ?? $request->validated('device_token');

        DB::transaction(function () use ($request, $token) {
            User::where('expo_push_token', $token)
                ->whereKeyNot($request->user()->id)
                ->update(['expo_push_token' => null]);

            $request->user()->update(['expo_push_token' => $token]);
        });

        return $this->success(null, 'Expo push token registered', 201);
    }

    public function deleteDeviceToken(Request $request, ?string $deviceToken = null): JsonResponse
    {
        if ($deviceToken !== null && $request->user()->expo_push_token !== $deviceToken) {
            return $this->error('Resource not found', 404);
        }

        $request->user()->update(['expo_push_token' => null]);

        return $this->success(null, 'Expo push token removed');
    }
}
