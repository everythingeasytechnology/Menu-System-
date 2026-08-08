<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\ServicePoints\RoomRequest;
use App\Http\Resources\Api\V1\RoomResource;
use App\Models\RestaurantTable;
use App\Models\Room;
use App\Models\ServicePoint;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoomController extends ApiController
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $rooms = Room::where('business_id', $this->businessId($request))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->orderBy('name')
            ->paginate((int) $request->input('per_page', 25));

        return $this->success(RoomResource::collection($rooms), 'Rooms');
    }

    public function store(RoomRequest $request): JsonResponse
    {
        $room = Room::create([
            ...$request->validated(),
            'business_id' => $this->businessId($request),
            'qr_identifier' => $this->generateQr(),
        ]);

        $this->auditLogService->record($request->user(), $room->business_id, 'room.created', $room);

        return $this->success(new RoomResource($room), 'Room created', 201);
    }

    public function show(Request $request, Room $room): JsonResponse
    {
        if ($room->business_id !== $this->businessId($request)) {
            return $this->error('Resource not found', 404);
        }

        return $this->success(new RoomResource($room), 'Room details');
    }

    public function update(RoomRequest $request, Room $room): JsonResponse
    {
        if ($room->business_id !== $this->businessId($request)) {
            return $this->error('Resource not found', 404);
        }

        $room->update($request->validated());
        $this->auditLogService->record($request->user(), $room->business_id, 'room.updated', $room);

        return $this->success(new RoomResource($room->fresh()), 'Room updated');
    }

    public function destroy(Request $request, Room $room): JsonResponse
    {
        if ($room->business_id !== $this->businessId($request)) {
            return $this->error('Resource not found', 404);
        }

        $room->update(['is_active' => false, 'status' => 'maintenance']);
        $this->auditLogService->record($request->user(), $room->business_id, 'room.deactivated', $room);

        return $this->success(new RoomResource($room->fresh()), 'Room deactivated');
    }

    public function qr(Request $request, Room $room): JsonResponse
    {
        if ($room->business_id !== $this->businessId($request)) {
            return $this->error('Resource not found', 404);
        }

        return $this->success(['qr_identifier' => $room->qr_identifier], 'Room QR identifier');
    }

    private function generateQr(): string
    {
        do {
            $qr = 'room_'.Str::lower(Str::random(32));
        } while (
            Room::where('qr_identifier', $qr)->exists()
            || RestaurantTable::where('qr_identifier', $qr)->exists()
            || ServicePoint::where('qr_identifier', $qr)->exists()
        );

        return $qr;
    }
}
