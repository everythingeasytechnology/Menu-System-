<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\ServicePoints\ServicePointRequest;
use App\Http\Resources\Api\V1\ServicePointResource;
use App\Models\RestaurantTable;
use App\Models\Room;
use App\Models\ServicePoint;
use App\Services\AuditLogService;
use App\Services\QrCodeService;
use App\Services\ScanUrlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ServicePointController extends ApiController
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly QrCodeService $qrCodeService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = ServicePoint::with(['activeOrders' => fn ($query) => $query->with('items')->latest()])
            ->withCount('activeOrders')
            ->where('business_id', $this->businessId($request))
            ->when($request->filled('point_type'), fn ($query) => $query->where('point_type', $request->input('point_type')))
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->input('category')));

        if ($request->filled('status')) {
            $this->applyStatusFilter($query, $request->input('status'));
        }

        $points = $query
            ->orderBy('category')
            ->orderBy('name')
            ->paginate((int) $request->input('per_page', 25));

        return $this->success(ServicePointResource::collection($points), 'Service points');
    }

    public function store(ServicePointRequest $request): JsonResponse
    {
        $data = $request->validated();

        $point = ServicePoint::create([
            'business_id' => $this->businessId($request),
            'code' => $this->generateCode(),
            'qr_identifier' => $this->generateQr(),
            'name' => $data['name'],
            'seats' => $data['seats'],
            'category' => $data['category'],
            'point_type' => $data['point_type'] ?? 'table',
            'status' => $data['status'] ?? 'available',
            'is_active' => $data['is_active'] ?? true,
            'amount' => 0,
            'items' => [],
        ]);

        $this->auditLogService->record($request->user(), $point->business_id, 'service_point.created', $point);

        return $this->success(new ServicePointResource($this->withOccupancy($point)), 'Service point created', 201);
    }

    public function show(Request $request, ServicePoint $servicePoint): JsonResponse
    {
        if ($servicePoint->business_id !== $this->businessId($request)) {
            return $this->error('Resource not found', 404);
        }

        return $this->success(new ServicePointResource($this->withOccupancy($servicePoint)), 'Service point details');
    }

    public function update(ServicePointRequest $request, ServicePoint $servicePoint): JsonResponse
    {
        if ($servicePoint->business_id !== $this->businessId($request)) {
            return $this->error('Resource not found', 404);
        }

        $servicePoint->update($request->validated());
        $this->auditLogService->record($request->user(), $servicePoint->business_id, 'service_point.updated', $servicePoint);

        return $this->success(new ServicePointResource($this->withOccupancy($servicePoint->fresh())), 'Service point updated');
    }

    public function destroy(Request $request, ServicePoint $servicePoint): JsonResponse
    {
        if ($servicePoint->business_id !== $this->businessId($request)) {
            return $this->error('Resource not found', 404);
        }

        $servicePoint->update([
            'is_active' => false,
            'status' => 'maintenance',
        ]);

        $this->auditLogService->record($request->user(), $servicePoint->business_id, 'service_point.deactivated', $servicePoint);

        return $this->success(new ServicePointResource($this->withOccupancy($servicePoint->fresh())), 'Service point deactivated');
    }

    public function qr(Request $request, ServicePoint $servicePoint): JsonResponse
    {
        if ($servicePoint->business_id !== $this->businessId($request)) {
            return $this->error('Resource not found', 404);
        }

        return $this->success([
            'qr_identifier' => $servicePoint->qr_identifier,
            'scan_url' => $this->scanUrl($servicePoint),
            'scanner_download_url' => url('/api/v1/service-points/'.$servicePoint->id.'/scanner'),
        ], 'Service point QR identifier');
    }

    public function downloadScanner(Request $request, ServicePoint $servicePoint): Response
    {
        $servicePoint->loadMissing('business');

        $png = $this->qrCodeService->scannerPng(
            $this->scanUrl($servicePoint),
            $servicePoint->business?->name ?? 'EverythingEasy',
        );
        $filename = Str::slug($servicePoint->code.'-'.$servicePoint->name).'-scanner.png';

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    private function generateCode(): string
    {
        do {
            $code = 'SP-'.Str::upper(Str::random(8));
        } while (ServicePoint::where('code', $code)->exists());

        return $code;
    }

    private function generateQr(): string
    {
        do {
            $qr = 'sp_'.Str::lower(Str::random(32));
        } while (
            ServicePoint::where('qr_identifier', $qr)->exists()
            || RestaurantTable::where('qr_identifier', $qr)->exists()
            || Room::where('qr_identifier', $qr)->exists()
        );

        return $qr;
    }

    private function scanUrl(ServicePoint $servicePoint): string
    {
        return ScanUrlService::forQr($servicePoint->qr_identifier);
    }

    private function withOccupancy(ServicePoint $servicePoint): ServicePoint
    {
        return $servicePoint
            ->load(['activeOrders' => fn ($query) => $query->with('items')->latest()])
            ->loadCount('activeOrders');
    }

    private function applyStatusFilter($query, string $status): void
    {
        $status = str_replace('_', '-', $status);

        if ($status === 'occupied') {
            $query->where(function ($query) {
                $query->where('status', 'occupied')
                    ->orWhereHas('activeOrders');
            });

            return;
        }

        if ($status === 'available') {
            $query->where('status', 'available')
                ->whereDoesntHave('activeOrders');

            return;
        }

        $query->where('status', $status);
    }
}
