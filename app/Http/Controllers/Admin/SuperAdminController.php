<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessSetting;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SuperAdminController extends Controller
{
    public const BUSINESS_STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'suspended' => 'Suspended',
    ];

    public const OWNER_STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'suspended' => 'Suspended',
    ];

    public function index(Request $request)
    {
        $businessQuery = Business::query()
            ->with('owner')
            ->withCount(['orders'])
            ->latest();

        if ($request->filled('business_search')) {
            $search = trim((string) $request->input('business_search'));
            $businessQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%')
                    ->orWhereHas('owner', fn ($owner) => $owner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%'));
            });
        }

        if ($request->filled('business_status') && $request->input('business_status') !== 'all') {
            $businessQuery->where('status', $request->input('business_status'));
        }

        return view('admin.dashboard', [
            'stats' => $this->stats(),
            'businesses' => $businessQuery->limit(60)->get(),
            'businessStatuses' => self::BUSINESS_STATUSES,
            'ownerStatuses' => self::OWNER_STATUSES,
            'filters' => $request->only(['business_search', 'business_status']),
        ]);
    }

    public function storeBusiness(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'business_type' => ['required', 'string', 'max:50'],
            'business_status' => ['required', 'string', Rule::in(array_keys(self::BUSINESS_STATUSES))],
            'business_email' => ['nullable', 'email', 'max:255'],
            'business_phone' => ['nullable', 'string', 'max:30'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'owner_phone' => ['nullable', 'string', 'max:30'],
            'owner_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        DB::transaction(function () use ($data) {
            $owner = User::create([
                'name' => $data['owner_name'],
                'email' => $data['owner_email'],
                'phone' => array_key_exists('owner_phone', $data) ? $data['owner_phone'] : $owner->phone,
                'password' => Hash::make($data['owner_password']),
                'role' => 'owner',
                'status' => 'active',
            ]);

            $business = Business::create([
                'owner_user_id' => $owner->id,
                'name' => $data['business_name'],
                'type' => $data['business_type'],
                'email' => $data['business_email'] ?? null,
                'phone' => $data['business_phone'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'country' => $data['country'] ?? 'India',
                'timezone' => 'Asia/Kolkata',
                'status' => $data['business_status'],
            ]);

            $owner->update(['business_id' => $business->id]);

            BusinessSetting::updateOrCreate(
                ['business_id' => $business->id],
                [
                    'brand_name' => $business->name,
                    'business_email' => $business->email,
                    'country' => $business->country,
                    'state' => $business->state,
                    'gst_enabled' => false,
                    'cgst' => 2.5,
                    'sgst' => 2.5,
                ],
            );
        });

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Business and owner account created.');
    }

    public function updateBusiness(Request $request, Business $business): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:50'],
            'status' => ['required', 'string', Rule::in(array_keys(self::BUSINESS_STATUSES))],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'owner_name' => ['nullable', 'string', 'max:255'],
            'owner_email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($business->owner_user_id)],
            'owner_phone' => ['nullable', 'string', 'max:30'],
            'owner_status' => ['nullable', 'string', Rule::in(array_keys(self::OWNER_STATUSES))],
        ]);

        $businessData = collect($data)
            ->only(['name', 'type', 'status', 'email', 'phone', 'city', 'state', 'country'])
            ->all();

        $business->update($businessData);

        $owner = $business->owner()->first();
        if ($owner) {
            $owner->update([
                'name' => ($data['owner_name'] ?? null) ?: $owner->name,
                'email' => ($data['owner_email'] ?? null) ?: $owner->email,
                'phone' => $data['owner_phone'] ?? null,
                'status' => $data['owner_status'] ?? $owner->status,
            ]);

            if ($owner->status !== 'active') {
                $owner->accessTokens()->update(['revoked_at' => now()]);
            }
        }

        BusinessSetting::where('business_id', $business->id)->update([
            'brand_name' => $business->name,
            'business_email' => $business->email,
            'country' => $business->country,
            'state' => $business->state,
        ]);

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Business updated.');
    }

    private function stats(): array
    {
        return [
            'businesses' => Business::count(),
            'active_businesses' => Business::where('status', 'active')->count(),
            'suspended_businesses' => Business::where('status', 'suspended')->count(),
            'owners' => User::where('role', 'owner')->count(),
            'active_owners' => User::where('role', 'owner')->where('status', 'active')->count(),
            'orders' => Order::count(),
            'gross_sales' => (float) Order::where('order_status', '!=', 'cancelled')->sum('total'),
        ];
    }
}
