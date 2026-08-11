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

    public const USER_STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'suspended' => 'Suspended',
    ];

    public const USER_ROLES = [
        'superadmin' => 'Super Admin',
        'owner' => 'Owner',
        'admin' => 'Business Admin',
        'manager' => 'Manager',
        'waiter' => 'Waiter',
        'kitchen_staff' => 'Kitchen Staff',
        'cashier' => 'Cashier',
    ];

    public function index(Request $request)
    {
        $businessQuery = Business::query()
            ->with('owner')
            ->withCount(['users', 'orders'])
            ->latest();

        if ($request->filled('business_search')) {
            $search = trim((string) $request->input('business_search'));
            $businessQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%')
                    ->orWhereHas('owner', fn ($owner) => $owner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%'));
            });
        }

        if ($request->filled('business_status') && $request->input('business_status') !== 'all') {
            $businessQuery->where('status', $request->input('business_status'));
        }

        $userQuery = User::query()
            ->with('business')
            ->latest();

        if ($request->filled('user_search')) {
            $search = trim((string) $request->input('user_search'));
            $userQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('user_role') && $request->input('user_role') !== 'all') {
            $userQuery->where('role', $request->input('user_role'));
        }

        return view('admin.dashboard', [
            'stats' => $this->stats(),
            'businesses' => $businessQuery->limit(60)->get(),
            'users' => $userQuery->limit(100)->get(),
            'businessOptions' => Business::orderBy('name')->get(['id', 'name']),
            'businessStatuses' => self::BUSINESS_STATUSES,
            'userStatuses' => self::USER_STATUSES,
            'userRoles' => self::USER_ROLES,
            'filters' => $request->only(['business_search', 'business_status', 'user_search', 'user_role']),
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
                'phone' => $data['owner_phone'] ?? null,
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
        ]);

        $business->update($data);

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

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', 'string', Rule::in(array_keys(self::USER_ROLES))],
            'status' => ['required', 'string', Rule::in(array_keys(self::USER_STATUSES))],
            'business_id' => ['nullable', 'integer', 'exists:businesses,id'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if ($user->is($request->user()) && ($data['role'] !== 'superadmin' || $data['status'] !== 'active')) {
            return back()->withErrors(['user' => 'You cannot remove your own active super admin access.']);
        }

        if ($data['role'] !== 'superadmin' && empty($data['business_id'])) {
            return back()->withErrors(['business_id' => 'Business is required for non-superadmin users.']);
        }

        if ($data['role'] === 'superadmin') {
            $data['business_id'] = null;
        }

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        if ($user->status !== 'active') {
            $user->accessTokens()->update(['revoked_at' => now()]);
        }

        if ($user->role === 'owner' && $user->business_id) {
            Business::whereKey($user->business_id)->update(['owner_user_id' => $user->id]);
        }

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'User updated.');
    }

    private function stats(): array
    {
        return [
            'businesses' => Business::count(),
            'active_businesses' => Business::where('status', 'active')->count(),
            'suspended_businesses' => Business::where('status', 'suspended')->count(),
            'users' => User::count(),
            'owners' => User::where('role', 'owner')->count(),
            'orders' => Order::count(),
            'gross_sales' => (float) Order::where('order_status', '!=', 'cancelled')->sum('total'),
        ];
    }
}
