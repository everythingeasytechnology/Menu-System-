<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $business = $this->currentBusiness();
        $baseQuery = Coupon::where('business_id', $business->id);

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                })
                ->where(function ($query) {
                    $query->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                })
                ->count(),
            'redemptions' => (clone $baseQuery)->sum('used_count'),
            'expired' => (clone $baseQuery)->whereNotNull('expires_at')->where('expires_at', '<', now())->count(),
        ];

        $coupons = (clone $baseQuery)
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('code', 'like', '%' . $request->input('search') . '%');
            })
            ->when($request->filled('type') && $request->input('type') !== 'all', function ($query) use ($request) {
                $query->where('type', $request->input('type'));
            })
            ->when($request->filled('status') && $request->input('status') !== 'all', function ($query) use ($request) {
                match ($request->input('status')) {
                    'active' => $query
                        ->where('is_active', true)
                        ->where(function ($query) {
                            $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                        })
                        ->where(function ($query) {
                            $query->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                        }),
                    'scheduled' => $query->where('is_active', true)->whereNotNull('starts_at')->where('starts_at', '>', now()),
                    'expired' => $query->whereNotNull('expires_at')->where('expires_at', '<', now()),
                    'inactive' => $query->where('is_active', false),
                    default => $query,
                };
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $editingCoupon = null;
        if ($request->filled('edit')) {
            $editingCoupon = Coupon::where('business_id', $business->id)->find($request->integer('edit'));
        }

        return view('coupons', compact('business', 'coupons', 'editingCoupon', 'stats'));
    }

    public function store(Request $request)
    {
        $business = $this->currentBusiness();
        $data = $this->validatedData($request, $business);

        Coupon::create([
            ...$data,
            'business_id' => $business->id,
        ]);

        return redirect()->route('dashboard.coupons.index')->with('success', 'Coupon created successfully.');
    }

    public function update(Request $request, Coupon $coupon)
    {
        $business = $this->currentBusiness();
        $this->authorizeCoupon($coupon, $business);

        $coupon->update($this->validatedData($request, $business, $coupon));

        return redirect()->route('dashboard.coupons.index')->with('success', 'Coupon updated successfully.');
    }

    public function toggle(Coupon $coupon)
    {
        $business = $this->currentBusiness();
        $this->authorizeCoupon($coupon, $business);

        $coupon->update(['is_active' => ! $coupon->is_active]);

        return redirect()->route('dashboard.coupons.index')->with('success', 'Coupon status updated successfully.');
    }

    public function destroy(Coupon $coupon)
    {
        $business = $this->currentBusiness();
        $this->authorizeCoupon($coupon, $business);

        $coupon->update(['is_active' => false]);

        return redirect()->route('dashboard.coupons.index')->with('success', 'Coupon deactivated successfully.');
    }

    private function validatedData(Request $request, Business $business, ?Coupon $coupon = null): array
    {
        $request->merge([
            'code' => strtoupper(trim((string) $request->input('code', ''))),
        ]);

        $uniqueCode = Rule::unique('coupons', 'code')
            ->where(fn ($query) => $query->where('business_id', $business->id));

        if ($coupon) {
            $uniqueCode->ignore($coupon->id);
        }

        $data = $request->validate([
            'code' => ['required', 'string', 'max:100', $uniqueCode],
            'type' => ['required', 'string', Rule::in(['percentage', 'fixed'])],
            'value' => ['required', 'numeric', 'min:0'],
            'minimum_order' => ['nullable', 'numeric', 'min:0'],
            'maximum_discount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'per_user_limit' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['required', 'boolean'],
        ]);

        $data['minimum_order'] ??= 0;
        $data['is_active'] = (bool) $data['is_active'];

        return $data;
    }

    private function currentBusiness(): Business
    {
        $user = auth()->user();

        if ($user->business) {
            return $user->business;
        }

        $ownedBusiness = $user->ownedBusinesses()->latest()->first();
        if ($ownedBusiness) {
            $user->update(['business_id' => $ownedBusiness->id]);

            return $ownedBusiness;
        }

        $business = Business::create([
            'owner_user_id' => $user->id,
            'name' => 'EverythingEasy',
            'type' => 'restaurant',
            'email' => $user->email,
            'country' => 'India',
            'timezone' => 'Asia/Kolkata',
            'status' => 'active',
        ]);

        $user->update(['business_id' => $business->id]);

        return $business;
    }

    private function authorizeCoupon(Coupon $coupon, Business $business): void
    {
        abort_unless($coupon->business_id === $business->id, 404);
    }
}
