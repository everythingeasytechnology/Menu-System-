<?php

namespace App\Http\Controllers\Api\V1\Customers;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Api\V1\Customers\ValidateCustomerCouponRequest;
use App\Http\Resources\Api\V1\CouponResource;
use App\Models\Coupon;
use App\Services\CouponService;
use App\Services\Customers\ScannerContextResolver;
use Illuminate\Http\JsonResponse;

class CustomerCouponController extends ApiController
{
    public function __construct(
        private readonly ScannerContextResolver $scannerContextResolver,
        private readonly CouponService $couponService,
    ) {
    }

    public function index(string $qr): JsonResponse
    {
        [$business, $context] = $this->scannerContextResolver->resolve($qr);

        $coupons = Coupon::where('business_id', $business->id)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->where(function ($query) {
                $query->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit');
            })
            ->orderBy('minimum_order')
            ->orderBy('code')
            ->get();

        return $this->success([
            'context' => $context,
            'coupons' => CouponResource::collection($coupons),
        ], 'Customer coupons');
    }

    public function validateCoupon(ValidateCustomerCouponRequest $request, string $qr): JsonResponse
    {
        [$business, $context] = $this->scannerContextResolver->resolve($qr);
        $data = $request->validated();
        $coupon = $this->couponService->findValidCoupon(
            $business->id,
            $data['code'],
            (float) $data['subtotal'],
        );

        return $this->success([
            'context' => $context,
            'coupon' => new CouponResource($coupon),
            'discount' => $this->couponService->discountFor($coupon, (float) $data['subtotal']),
        ], 'Coupon is valid');
    }
}
