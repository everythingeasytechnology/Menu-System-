<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\Business\UpdateBusinessRequest;
use App\Http\Resources\Api\V1\BusinessResource;
use App\Models\BusinessSetting;
use App\Models\CashSetting;
use App\Models\RazorpaySetting;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BusinessController extends ApiController
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function show(Request $request): JsonResponse
    {
        $business = $this->business($request);

        if (! $business) {
            return $this->error('Business profile not found', 404);
        }

        return $this->success(new BusinessResource($business), 'Business profile');
    }

    public function update(UpdateBusinessRequest $request): JsonResponse
    {
        $business = $this->business($request);

        if (! $business) {
            return $this->error('Business profile not found', 404);
        }

        $business->update($request->validated());
        $this->auditLogService->record($request->user(), $business->id, 'business.updated', $business);

        return $this->success(new BusinessResource($business->fresh()), 'Business profile updated');
    }

    public function settings(Request $request): JsonResponse
    {
        $business = $this->business($request);

        if (! $business) {
            return $this->error('Business profile not found', 404);
        }

        $businessSetting = BusinessSetting::where('business_id', $business->id)->first()
            ?? BusinessSetting::whereNull('business_id')->first();
        $cashSetting = CashSetting::where('business_id', $business->id)->first()
            ?? CashSetting::whereNull('business_id')->first();
        $razorpaySetting = RazorpaySetting::where('business_id', $business->id)->first()
            ?? RazorpaySetting::whereNull('business_id')->first();

        return $this->success([
            'business' => new BusinessResource($business),
            'settings' => [
                'brand_name' => $businessSetting?->brand_name,
                'gst_enabled' => (bool) $businessSetting?->gst_enabled,
                'cgst' => $businessSetting?->cgst,
                'sgst' => $businessSetting?->sgst,
            ],
            'payments' => [
                'cash_enabled' => (bool) $cashSetting?->enabled,
                'razorpay_enabled' => (bool) $razorpaySetting?->enabled,
                'razorpay_key_id' => $razorpaySetting?->key_id,
            ],
        ], 'Business settings');
    }

    public function status(Request $request): JsonResponse
    {
        $business = $this->business($request);

        if (! $business) {
            return $this->error('Business profile not found', 404);
        }

        return $this->success([
            'status' => $business->status,
            'timezone' => $business->timezone,
        ], 'Business status');
    }
}
