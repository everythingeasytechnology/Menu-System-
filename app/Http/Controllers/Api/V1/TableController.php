<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\ServicePoints\TableRequest;
use App\Http\Resources\Api\V1\RestaurantTableResource;
use App\Models\RestaurantTable;
use App\Models\Room;
use App\Models\ServicePoint;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TableController extends ApiController
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $tables = RestaurantTable::where('business_id', $this->businessId($request))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->orderBy('name')
            ->paginate((int) $request->input('per_page', 25));

        return $this->success(RestaurantTableResource::collection($tables), 'Tables');
    }

    public function store(TableRequest $request): JsonResponse
    {
        $table = RestaurantTable::create([
            ...$request->validated(),
            'business_id' => $this->businessId($request),
            'qr_identifier' => $this->generateQr('tbl'),
        ]);

        $this->auditLogService->record($request->user(), $table->business_id, 'table.created', $table);

        return $this->success(new RestaurantTableResource($table), 'Table created', 201);
    }

    public function show(Request $request, RestaurantTable $table): JsonResponse
    {
        if ($table->business_id !== $this->businessId($request)) {
            return $this->error('Resource not found', 404);
        }

        return $this->success(new RestaurantTableResource($table), 'Table details');
    }

    public function update(TableRequest $request, RestaurantTable $table): JsonResponse
    {
        if ($table->business_id !== $this->businessId($request)) {
            return $this->error('Resource not found', 404);
        }

        $table->update($request->validated());
        $this->auditLogService->record($request->user(), $table->business_id, 'table.updated', $table);

        return $this->success(new RestaurantTableResource($table->fresh()), 'Table updated');
    }

    public function destroy(Request $request, RestaurantTable $table): JsonResponse
    {
        if ($table->business_id !== $this->businessId($request)) {
            return $this->error('Resource not found', 404);
        }

        $table->update(['is_active' => false, 'status' => 'maintenance']);
        $this->auditLogService->record($request->user(), $table->business_id, 'table.deactivated', $table);

        return $this->success(new RestaurantTableResource($table->fresh()), 'Table deactivated');
    }

    public function qr(Request $request, RestaurantTable $table): JsonResponse
    {
        if ($table->business_id !== $this->businessId($request)) {
            return $this->error('Resource not found', 404);
        }

        return $this->success(['qr_identifier' => $table->qr_identifier], 'Table QR identifier');
    }

    private function generateQr(string $prefix): string
    {
        do {
            $qr = $prefix.'_'.Str::lower(Str::random(32));
        } while (
            RestaurantTable::where('qr_identifier', $qr)->exists()
            || Room::where('qr_identifier', $qr)->exists()
            || ServicePoint::where('qr_identifier', $qr)->exists()
        );

        return $qr;
    }
}
