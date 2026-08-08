<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CouponService
{
    public function findValidCoupon(int $businessId, ?string $code, float $subtotal, ?User $user = null): ?Coupon
    {
        if (! $code) {
            return null;
        }

        $coupon = Coupon::where('business_id', $businessId)
            ->where('code', strtoupper($code))
            ->first();

        if (! $coupon || ! $coupon->is_active) {
            throw ValidationException::withMessages(['coupon_code' => ['Coupon is invalid.']]);
        }

        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            throw ValidationException::withMessages(['coupon_code' => ['Coupon is not active yet.']]);
        }

        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            throw ValidationException::withMessages(['coupon_code' => ['Coupon has expired.']]);
        }

        if ($subtotal < (float) $coupon->minimum_order) {
            throw ValidationException::withMessages(['coupon_code' => ['Order does not meet the coupon minimum.']]);
        }

        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            throw ValidationException::withMessages(['coupon_code' => ['Coupon usage limit has been reached.']]);
        }

        if ($user && $coupon->per_user_limit !== null) {
            $usedByUser = $coupon->orders()->where('user_id', $user->id)->count();

            if ($usedByUser >= $coupon->per_user_limit) {
                throw ValidationException::withMessages(['coupon_code' => ['Coupon usage limit has been reached for this user.']]);
            }
        }

        return $coupon;
    }

    public function discountFor(Coupon $coupon, float $subtotal): float
    {
        $discount = $coupon->type === 'percentage'
            ? $subtotal * ((float) $coupon->value / 100)
            : (float) $coupon->value;

        if ($coupon->maximum_discount !== null) {
            $discount = min($discount, (float) $coupon->maximum_discount);
        }

        return round(min($discount, $subtotal), 2);
    }
}
