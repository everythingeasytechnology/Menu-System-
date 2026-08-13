<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\Staff\StaffRequest;
use App\Http\Requests\Api\V1\Staff\StaffStatusRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StaffController extends ApiController
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function index(Request $request): JsonResponse
    {
        $staff = User::where('business_id', $this->businessId($request))
            ->where('role', '!=', 'owner')
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->input('role')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate((int) $request->input('per_page', 25));

        return $this->success(UserResource::collection($staff), 'Staff members');
    }

    public function store(StaffRequest $request): JsonResponse
    {
        $data = $request->validated();
        $profileImagePath = $request->hasFile('profile_image')
            ? $this->storeProfileImage($request)
            : null;

        $staff = User::create([
            'business_id' => $this->businessId($request),
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'profile_image_path' => $profileImagePath,
            'role' => $data['role'],
            'status' => $data['status'] ?? 'active',
            'password' => $data['password'],
        ]);

        $this->auditLogService->record($request->user(), $staff->business_id, 'staff.created', $staff, [
            'role' => $staff->role,
            'status' => $staff->status,
        ]);

        return $this->success(new UserResource($staff), 'Staff member created', 201);
    }

    public function show(Request $request, User $staff): JsonResponse
    {
        if (! $this->canAccessStaff($request, $staff)) {
            return $this->error('Resource not found', 404);
        }

        return $this->success(new UserResource($staff), 'Staff member details');
    }

    public function update(StaffRequest $request, User $staff): JsonResponse
    {
        if (! $this->canAccessStaff($request, $staff)) {
            return $this->error('Resource not found', 404);
        }

        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        }

        unset($data['profile_image'], $data['remove_profile_image']);

        if ($request->hasFile('profile_image')) {
            $this->deleteProfileImage($staff);
            $data['profile_image_path'] = $this->storeProfileImage($request);
        } elseif ($request->boolean('remove_profile_image')) {
            $this->deleteProfileImage($staff);
            $data['profile_image_path'] = null;
        }

        $staff->update($data);

        $this->auditLogService->record($request->user(), $staff->business_id, 'staff.updated', $staff, [
            'role' => $staff->role,
            'status' => $staff->status,
        ]);

        return $this->success(new UserResource($staff->fresh()), 'Staff member updated');
    }

    public function destroy(Request $request, User $staff): JsonResponse
    {
        if (! $this->canAccessStaff($request, $staff)) {
            return $this->error('Resource not found', 404);
        }

        $staff->update(['status' => 'inactive']);
        $staff->accessTokens()->update(['revoked_at' => now()]);

        $this->auditLogService->record($request->user(), $staff->business_id, 'staff.deactivated', $staff);

        return $this->success(new UserResource($staff->fresh()), 'Staff member deactivated');
    }

    public function updateStatus(StaffStatusRequest $request, User $staff): JsonResponse
    {
        if (! $this->canAccessStaff($request, $staff)) {
            return $this->error('Resource not found', 404);
        }

        $staff->update(['status' => $request->validated('status')]);

        if ($staff->status !== 'active') {
            $staff->accessTokens()->update(['revoked_at' => now()]);
        }

        $this->auditLogService->record($request->user(), $staff->business_id, 'staff.status_updated', $staff, [
            'status' => $staff->status,
        ]);

        return $this->success(new UserResource($staff->fresh()), 'Staff status updated');
    }

    public function roles(): JsonResponse
    {
        return $this->success([
            ['value' => 'admin', 'label' => 'Admin'],
            ['value' => 'manager', 'label' => 'Manager'],
            ['value' => 'waiter', 'label' => 'Waiter'],
            ['value' => 'kitchen_staff', 'label' => 'Kitchen Staff'],
            ['value' => 'cashier', 'label' => 'Cashier'],
        ], 'Staff roles');
    }

    private function canAccessStaff(Request $request, User $staff): bool
    {
        return $staff->business_id === $this->businessId($request)
            && $staff->role !== 'owner';
    }

    private function storeProfileImage(Request $request): string
    {
        return $request->file('profile_image')->store('profile-images', 'public');
    }

    private function deleteProfileImage(User $staff): void
    {
        if ($staff->profile_image_path) {
            Storage::disk('public')->delete($staff->profile_image_path);
        }
    }
}
