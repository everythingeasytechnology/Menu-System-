<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\Promotions\CouponRequest;
use App\Http\Requests\Api\V1\Promotions\ValidateCouponRequest;
use App\Http\Resources\Api\V1\CouponResource;
use App\Models\Coupon;
use App\Services\AuditLogService;
use App\Services\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CouponController extends ApiController
{
    public function __construct(
        private readonly CouponService $couponService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $coupons = Coupon::where('business_id', $this->businessId($request))
            ->latest()
            ->paginate((int) $request->input('per_page', 25));

        return $this->success(CouponResource::collection($coupons), 'Coupons');
    }

    public function store(CouponRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['code'] = strtoupper($data['code']);
        $this->assertUniqueCode($this->businessId($request), $data['code']);

        $coupon = Coupon::create([
            ...$data,
            'business_id' => $this->businessId($request),
        ]);

        $this->auditLogService->record($request->user(), $coupon->business_id, 'coupon.created', $coupon);

        return $this->success(new CouponResource($coupon), 'Coupon created', 201);
    }

    public function show(Request $request, Coupon $coupon): JsonResponse
    {
        if ($coupon->business_id !== $this->businessId($request)) {
            return $this->error('Resource not found', 404);
        }

        return $this->success(new CouponResource($coupon), 'Coupon details');
    }

    public function update(CouponRequest $request, Coupon $coupon): JsonResponse
    {
        if ($coupon->business_id !== $this->businessId($request)) {
            return $this->error('Resource not found', 404);
        }

        $data = $request->validated();

        if (isset($data['code'])) {
            $data['code'] = strtoupper($data['code']);
            $this->assertUniqueCode($coupon->business_id, $data['code'], $coupon->id);
        }

        $coupon->update($data);
        $this->auditLogService->record($request->user(), $coupon->business_id, 'coupon.updated', $coupon);

        return $this->success(new CouponResource($coupon->fresh()), 'Coupon updated');
    }

    public function destroy(Request $request, Coupon $coupon): JsonResponse
    {
        if ($coupon->business_id !== $this->businessId($request)) {
            return $this->error('Resource not found', 404);
        }

        $coupon->update(['is_active' => false]);

        return $this->success(new CouponResource($coupon->fresh()), 'Coupon deactivated');
    }

    public function validateCoupon(ValidateCouponRequest $request): JsonResponse
    {
        $data = $request->validated();
        $coupon = $this->couponService->findValidCoupon(
            $this->businessId($request),
            $data['code'],
            (float) $data['subtotal'],
            $request->user(),
        );

        return $this->success([
            'coupon' => new CouponResource($coupon),
            'discount' => $this->couponService->discountFor($coupon, (float) $data['subtotal']),
        ], 'Coupon is valid');
    }

    private function assertUniqueCode(int $businessId, string $code, ?int $ignoreId = null): void
    {
        $exists = Coupon::where('business_id', $businessId)
            ->where('code', $code)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages(['code' => ['The coupon code has already been taken.']]);
        }
    }
}
