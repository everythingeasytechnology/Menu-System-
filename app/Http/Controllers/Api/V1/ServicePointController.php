<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\ServicePoints\ServicePointRequest;
use App\Http\Resources\Api\V1\ServicePointResource;
use App\Models\RestaurantTable;
use App\Models\Room;
use App\Models\ServicePoint;
use App\Services\AuditLogService;
use App\Services\QrCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ServicePointController extends ApiController
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly QrCodeService $qrCodeService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $points = ServicePoint::where('business_id', $this->businessId($request))
            ->when($request->filled('point_type'), fn ($query) => $query->where('point_type', $request->input('point_type')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->input('category')))
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

        return $this->success(new ServicePointResource($point), 'Service point created', 201);
    }

    public function show(Request $request, ServicePoint $servicePoint): JsonResponse
    {
        if ($servicePoint->business_id !== $this->businessId($request)) {
            return $this->error('Resource not found', 404);
        }

        return $this->success(new ServicePointResource($servicePoint), 'Service point details');
    }

    public function update(ServicePointRequest $request, ServicePoint $servicePoint): JsonResponse
    {
        if ($servicePoint->business_id !== $this->businessId($request)) {
            return $this->error('Resource not found', 404);
        }

        $servicePoint->update($request->validated());
        $this->auditLogService->record($request->user(), $servicePoint->business_id, 'service_point.updated', $servicePoint);

        return $this->success(new ServicePointResource($servicePoint->fresh()), 'Service point updated');
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

        return $this->success(new ServicePointResource($servicePoint->fresh()), 'Service point deactivated');
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
        if ($servicePoint->business_id !== $this->businessId($request)) {
            abort(404);
        }

        $svg = $this->qrCodeService->svg($this->scanUrl($servicePoint));
        $filename = Str::slug($servicePoint->code.'-'.$servicePoint->name).'-scanner.svg';

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
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
        return url('/api/v1/public/menu/'.$servicePoint->qr_identifier);
    }
}
