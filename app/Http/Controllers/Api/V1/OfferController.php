<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\Promotions\OfferRequest;
use App\Http\Resources\Api\V1\OfferResource;
use App\Models\Offer;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfferController extends ApiController
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $offers = Offer::where('business_id', $this->businessId($request))
            ->latest()
            ->paginate((int) $request->input('per_page', 25));

        return $this->success(OfferResource::collection($offers), 'Offers');
    }

    public function store(OfferRequest $request): JsonResponse
    {
        $offer = Offer::create([
            ...$request->validated(),
            'business_id' => $this->businessId($request),
        ]);

        $this->auditLogService->record($request->user(), $offer->business_id, 'offer.created', $offer);

        return $this->success(new OfferResource($offer), 'Offer created', 201);
    }

    public function show(Request $request, Offer $offer): JsonResponse
    {
        if ($offer->business_id !== $this->businessId($request)) {
            return $this->error('Resource not found', 404);
        }

        return $this->success(new OfferResource($offer), 'Offer details');
    }

    public function update(OfferRequest $request, Offer $offer): JsonResponse
    {
        if ($offer->business_id !== $this->businessId($request)) {
            return $this->error('Resource not found', 404);
        }

        $offer->update($request->validated());
        $this->auditLogService->record($request->user(), $offer->business_id, 'offer.updated', $offer);

        return $this->success(new OfferResource($offer->fresh()), 'Offer updated');
    }

    public function destroy(Request $request, Offer $offer): JsonResponse
    {
        if ($offer->business_id !== $this->businessId($request)) {
            return $this->error('Resource not found', 404);
        }

        $offer->update(['is_active' => false]);

        return $this->success(new OfferResource($offer->fresh()), 'Offer deactivated');
    }
}
