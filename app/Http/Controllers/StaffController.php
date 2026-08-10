<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditLogService;
use App\Services\OwnerDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StaffController extends Controller
{
    public const ROLES = [
        'admin' => 'Admin',
        'manager' => 'Manager',
        'waiter' => 'Waiter',
        'kitchen_staff' => 'Kitchen Staff',
        'cashier' => 'Cashier',
    ];

    public const STATUSES = ['active', 'inactive', 'suspended'];

    public function __construct(
        private readonly OwnerDashboardService $dashboardService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function index(Request $request)
    {
        $business = $this->dashboardService->businessFor($request->user());

        $staff = User::where('business_id', $business->id)
            ->where('role', '!=', 'owner')
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => $this->formatStaff($user));

        return view('staff', [
            'business' => $business,
            'staffPayload' => $staff->values(),
            'roles' => self::ROLES,
            'statuses' => self::STATUSES,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $business = $this->dashboardService->businessFor($request->user());

        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
                'phone' => ['nullable', 'string', 'max:30'],
                'role' => ['required', 'string', Rule::in(array_keys(self::ROLES))],
                'status' => ['sometimes', 'string', Rule::in(self::STATUSES)],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);
        } catch (ValidationException $exception) {
            return $this->validationError($exception);
        }

        $staff = User::create([
            'business_id' => $business->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'],
            'status' => $data['status'] ?? 'active',
            'password' => Hash::make($data['password']),
        ]);

        $this->auditLogService->record($request->user(), $business->id, 'staff.created', $staff, [
            'role' => $staff->role,
            'status' => $staff->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Staff member created.',
            'staff' => $this->formatStaff($staff),
        ], 201);
    }

    public function update(Request $request, User $staff): JsonResponse
    {
        $business = $this->dashboardService->businessFor($request->user());
        abort_unless($this->canAccess($staff, $business->id), 404);

        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($staff->id)],
                'phone' => ['nullable', 'string', 'max:30'],
                'role' => ['required', 'string', Rule::in(array_keys(self::ROLES))],
                'status' => ['required', 'string', Rule::in(self::STATUSES)],
                'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            ]);
        } catch (ValidationException $exception) {
            return $this->validationError($exception);
        }

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $staff->update($data);

        if ($staff->status !== 'active') {
            $staff->accessTokens()->update(['revoked_at' => now()]);
        }

        $this->auditLogService->record($request->user(), $business->id, 'staff.updated', $staff, [
            'role' => $staff->role,
            'status' => $staff->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Staff member updated.',
            'staff' => $this->formatStaff($staff->fresh()),
        ]);
    }

    public function destroy(Request $request, User $staff): JsonResponse
    {
        $business = $this->dashboardService->businessFor($request->user());
        abort_unless($this->canAccess($staff, $business->id), 404);

        $staff->update(['status' => 'inactive']);
        $staff->accessTokens()->update(['revoked_at' => now()]);

        $this->auditLogService->record($request->user(), $business->id, 'staff.deactivated', $staff);

        return response()->json([
            'success' => true,
            'message' => 'Staff member deactivated.',
            'staff' => $this->formatStaff($staff->fresh()),
        ]);
    }

    private function canAccess(User $staff, int $businessId): bool
    {
        return $staff->business_id === $businessId && $staff->role !== 'owner';
    }

    private function formatStaff(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'roleLabel' => self::ROLES[$user->role] ?? ucfirst(str_replace('_', ' ', $user->role)),
            'status' => $user->status,
            'statusLabel' => ucfirst($user->status),
            'initials' => $this->initials($user->name),
        ];
    }

    private function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $initials = collect($parts)->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('');

        return mb_strtoupper($initials ?: 'S');
    }

    private function validationError(ValidationException $exception): JsonResponse
    {
        $errors = $exception->errors();

        return response()->json([
            'success' => false,
            'message' => collect($errors)->flatten()->first() ?: 'Validation failed',
            'errors' => $errors,
        ], 422);
    }
}
