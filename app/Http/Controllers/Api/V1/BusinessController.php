<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\Business\UpdateBusinessOwnerProfileRequest;
use App\Http\Requests\Api\V1\Business\UpdateBusinessRequest;
use App\Http\Requests\Api\V1\Business\UpdateBusinessSettingsRequest;
use App\Http\Resources\Api\V1\BusinessResource;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\CashSetting;
use App\Models\RazorpaySetting;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class BusinessController extends ApiController
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

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

        return $this->success($this->settingsPayload($business), 'Business settings');
    }

    public function updateSettings(UpdateBusinessSettingsRequest $request): JsonResponse
    {
        $business = $this->business($request);

        if (! $business) {
            return $this->error('Business profile not found', 404);
        }

        $validated = $request->validated();
        $settingsData = array_merge(
            Arr::only($validated, $this->settingsFields()),
            $validated['settings'] ?? [],
        );
        $paymentData = array_merge(
            Arr::only($validated, ['cash_enabled', 'razorpay_enabled', 'razorpay_key_id', 'razorpay_key_secret']),
            $validated['payments'] ?? [],
        );

        $existingRazorpaySetting = RazorpaySetting::where('business_id', $business->id)->first();
        if (array_key_exists('razorpay_enabled', $paymentData) || array_key_exists('razorpay_key_id', $paymentData) || array_key_exists('razorpay_key_secret', $paymentData)) {
            $razorpayEnabled = array_key_exists('razorpay_enabled', $paymentData)
                ? (bool) $paymentData['razorpay_enabled']
                : (bool) $existingRazorpaySetting?->enabled;
            $keyId = $paymentData['razorpay_key_id'] ?? $existingRazorpaySetting?->key_id;
            $keySecret = $paymentData['razorpay_key_secret'] ?? $existingRazorpaySetting?->key_secret;

            if ($razorpayEnabled && (! $keyId || ! $keySecret)) {
                return $this->error('Validation failed', 422, [
                    'payments.razorpay_key_secret' => ['The Razorpay key id and key secret are required when Razorpay is enabled.'],
                ]);
            }
        }

        if ($request->hasFile('logo')) {
            if ($business->logo_path) {
                Storage::disk('public')->delete($business->logo_path);
            }

            $settingsData['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        $cashSetting = CashSetting::firstOrCreate(['business_id' => $business->id], ['enabled' => true]);
        if (array_key_exists('cash_enabled', $paymentData)) {
            $cashSetting->update(['enabled' => (bool) $paymentData['cash_enabled']]);
        }

        $razorpaySetting = RazorpaySetting::firstOrCreate(['business_id' => $business->id], ['enabled' => false]);
        if (array_key_exists('razorpay_enabled', $paymentData) || array_key_exists('razorpay_key_id', $paymentData) || array_key_exists('razorpay_key_secret', $paymentData)) {
            $razorpayEnabled = array_key_exists('razorpay_enabled', $paymentData)
                ? (bool) $paymentData['razorpay_enabled']
                : (bool) $razorpaySetting->enabled;
            $keyId = $paymentData['razorpay_key_id'] ?? $razorpaySetting->key_id;

            $razorpaySetting->update([
                'enabled' => $razorpayEnabled,
                'key_id' => $keyId,
                'key_secret' => array_key_exists('razorpay_key_secret', $paymentData)
                    ? $paymentData['razorpay_key_secret']
                    : $razorpaySetting->key_secret,
            ]);
        }

        $businessUpdate = [];
        if (array_key_exists('brand_name', $settingsData)) {
            $businessUpdate['name'] = $settingsData['brand_name'];
        }
        if (array_key_exists('business_email', $settingsData)) {
            $businessUpdate['business_email'] = $settingsData['business_email'];
            $businessUpdate['email'] = $settingsData['business_email'];
        }
        if (array_key_exists('shop_no', $settingsData)) {
            $businessUpdate['shop_no'] = $settingsData['shop_no'];
        }
        if (array_key_exists('address', $settingsData)) {
            $businessUpdate['address'] = $settingsData['address'];
        }
        if (array_key_exists('state', $settingsData)) {
            $businessUpdate['state'] = $settingsData['state'];
        }
        if (array_key_exists('country', $settingsData)) {
            $businessUpdate['country'] = $settingsData['country'];
        }
        if (array_key_exists('district', $settingsData)) {
            $businessUpdate['district'] = $settingsData['district'];
        }
        if (array_key_exists('pincode', $settingsData)) {
            $businessUpdate['pincode'] = $settingsData['pincode'];
        }
        if (array_key_exists('latitude', $settingsData)) {
            $businessUpdate['latitude'] = $settingsData['latitude'];
        }
        if (array_key_exists('longitude', $settingsData)) {
            $businessUpdate['longitude'] = $settingsData['longitude'];
        }
        if (array_key_exists('gst_no', $settingsData)) {
            $businessUpdate['gst_number'] = $settingsData['gst_no'];
        }
        if (array_key_exists('gst_enabled', $settingsData)) {
            $businessUpdate['gst_enabled'] = (bool) $settingsData['gst_enabled'];
        }
        if (array_key_exists('cgst', $settingsData)) {
            $businessUpdate['cgst'] = $settingsData['cgst'];
        }
        if (array_key_exists('sgst', $settingsData)) {
            $businessUpdate['sgst'] = $settingsData['sgst'];
        }
        if (array_key_exists('logo_path', $settingsData)) {
            $businessUpdate['logo_path'] = $settingsData['logo_path'];
        }

        if ($businessUpdate !== []) {
            $business->update($businessUpdate);
        }

        $this->auditLogService->record($request->user(), $business->id, 'business.settings.updated', $business->fresh());

        return $this->success($this->settingsPayload($business->fresh()), 'Business settings updated');
    }

    private function settingsPayload($business): array
    {
        $cashSetting = CashSetting::where('business_id', $business->id)->first();
        $razorpaySetting = RazorpaySetting::where('business_id', $business->id)->first();

        return [
            'business' => new BusinessResource($business),
            'settings' => [
                'brand_name' => $business->name,
                'logo_path' => $business->logo_path,
                'logo_url' => $business->logo_path ? asset('storage/'.$business->logo_path) : null,
                'business_email' => $business->business_email,
                'shop_no' => $business->shop_no,
                'address' => $business->address,
                'country' => $business->country,
                'state' => $business->state,
                'district' => $business->district,
                'pincode' => $business->pincode,
                'latitude' => $business->latitude,
                'longitude' => $business->longitude,
                'gst_no' => $business->gst_number,
                'gst_enabled' => (bool) $business->gst_enabled,
                'cgst' => $business->cgst,
                'sgst' => $business->sgst,
            ],
            'payments' => [
                'cash_enabled' => (bool) $cashSetting?->enabled,
                'razorpay_enabled' => (bool) $razorpaySetting?->enabled,
                'razorpay_key_id' => $razorpaySetting?->key_id,
            ],
        ];
    }

    private function settingsFields(): array
    {
        return [
            'brand_name',
            'business_email',
            'shop_no',
            'address',
            'country',
            'state',
            'district',
            'pincode',
            'latitude',
            'longitude',
            'gst_no',
            'gst_enabled',
            'cgst',
            'sgst',
        ];
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

    public function ownerProfile(Request $request): JsonResponse
    {
        if (! $this->canManageOwnerProfile($request)) {
            return $this->error('Only business owners can access this profile.', 403);
        }

        $business = $this->business($request);

        if (! $business) {
            return $this->error('Business profile not found', 404);
        }

        return $this->success($this->ownerProfilePayload($request, $business), 'Business owner profile');
    }

    public function updateOwnerProfile(UpdateBusinessOwnerProfileRequest $request): JsonResponse
    {
        if (! $this->canManageOwnerProfile($request)) {
            return $this->error('Only business owners can update this profile.', 403);
        }

        $business = $this->business($request);

        if (! $business) {
            return $this->error('Business profile not found', 404);
        }

        $data = $request->validated();
        $user = $request->user();

        $ownerData = [];
        if (array_key_exists('owner_name', $data) || array_key_exists('name', $data)) {
            $ownerData['name'] = $data['owner_name'] ?? $data['name'];
        }
        if (array_key_exists('owner_email', $data) || array_key_exists('email', $data)) {
            $ownerData['email'] = $data['owner_email'] ?? $data['email'];
        }
        if (array_key_exists('owner_phone', $data) || array_key_exists('phone', $data)) {
            $ownerData['phone'] = $data['owner_phone'] ?? $data['phone'];
        }

        if ($request->hasFile('profile_image')) {
            $this->deletePublicFile($user->profile_image_path);
            $ownerData['profile_image_path'] = $request->file('profile_image')->store('profile-images', 'public');
        } elseif ($request->boolean('remove_profile_image')) {
            $this->deletePublicFile($user->profile_image_path);
            $ownerData['profile_image_path'] = null;
        }

        if ($ownerData !== []) {
            $user->update($ownerData);
        }

        $businessData = [];
        if (array_key_exists('business_name', $data) || array_key_exists('brand_name', $data)) {
            $businessData['name'] = $data['business_name'] ?? $data['brand_name'];
        }
        if (array_key_exists('business_type', $data) || array_key_exists('type', $data)) {
            $businessData['type'] = $data['business_type'] ?? $data['type'];
        }
        if (array_key_exists('business_email', $data)) {
            $businessData['email'] = $data['business_email'];
            $businessData['business_email'] = $data['business_email'];
        }
        if (array_key_exists('business_phone', $data)) {
            $businessData['phone'] = $data['business_phone'];
        }
        if (array_key_exists('gst_number', $data) || array_key_exists('gst_no', $data)) {
            $businessData['gst_number'] = $data['gst_number'] ?? $data['gst_no'];
        }

        foreach (['address', 'city', 'state', 'country', 'opening_time', 'closing_time', 'timezone'] as $field) {
            if (array_key_exists($field, $data)) {
                $businessData[$field] = $data[$field];
            }
        }

        if ($request->hasFile('logo')) {
            $this->deletePublicFile($business->logo_path);
            $businessData['logo_path'] = $request->file('logo')->store('logos', 'public');
        } elseif ($request->boolean('remove_logo')) {
            $this->deletePublicFile($business->logo_path);
            $businessData['logo_path'] = null;
        }

        $businessData = array_merge($businessData, $this->businessSettingsDataFromProfile($data));

        if ($businessData !== []) {
            $business->update($businessData);
        }

        $this->auditLogService->record($user->fresh(), $business->id, 'business.owner_profile.updated', $business->fresh());

        return $this->success($this->ownerProfilePayload($request, $business->fresh()), 'Business owner profile updated');
    }

    private function canManageOwnerProfile(Request $request): bool
    {
        return in_array($request->user()?->role, ['owner', 'admin'], true);
    }

    private function ownerProfilePayload(Request $request, $business): array
    {
        return [
            'owner' => new UserResource($request->user()->fresh()),
            ...$this->settingsPayload($business),
        ];
    }

    private function businessSettingsDataFromProfile(array $data): array
    {
        $settingsData = [];

        if (array_key_exists('shop_no', $data)) {
            $settingsData['shop_no'] = $data['shop_no'];
        }

        foreach (['address', 'country', 'state', 'district', 'pincode', 'latitude', 'longitude'] as $field) {
            if (array_key_exists($field, $data)) {
                $settingsData[$field] = $data[$field];
            }
        }

        return $settingsData;
    }

    private function deletePublicFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
