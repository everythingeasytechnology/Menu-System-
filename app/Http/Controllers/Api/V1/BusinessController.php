<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\Business\UpdateBusinessRequest;
use App\Http\Requests\Api\V1\Business\UpdateBusinessSettingsRequest;
use App\Http\Resources\Api\V1\BusinessResource;
use App\Models\BusinessSetting;
use App\Models\CashSetting;
use App\Models\RazorpaySetting;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

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

        $businessSetting = BusinessSetting::firstOrCreate(['business_id' => $business->id]);

        if ($request->hasFile('logo')) {
            if ($businessSetting->logo_path) {
                Storage::disk('public')->delete($businessSetting->logo_path);
            }

            $settingsData['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        if ($settingsData !== []) {
            $businessSetting->update(Arr::only($settingsData, array_merge($this->settingsFields(), ['logo_path'])));
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
            $businessUpdate['email'] = $settingsData['business_email'];
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
        if (array_key_exists('gst_no', $settingsData)) {
            $businessUpdate['gst_number'] = $settingsData['gst_no'];
        }
        if (array_key_exists('logo_path', $settingsData)) {
            $businessUpdate['logo_path'] = $settingsData['logo_path'];
        }

        if ($businessUpdate !== []) {
            $business->update($businessUpdate);
        }

        $this->auditLogService->record($request->user(), $business->id, 'business.settings.updated', $businessSetting->fresh());

        return $this->success($this->settingsPayload($business->fresh()), 'Business settings updated');
    }

    private function settingsPayload($business): array
    {
        $businessSetting = BusinessSetting::where('business_id', $business->id)->first()
            ?? BusinessSetting::whereNull('business_id')->first();
        $cashSetting = CashSetting::where('business_id', $business->id)->first()
            ?? CashSetting::whereNull('business_id')->first();
        $razorpaySetting = RazorpaySetting::where('business_id', $business->id)->first()
            ?? RazorpaySetting::whereNull('business_id')->first();

        return [
            'business' => new BusinessResource($business),
            'settings' => [
                'brand_name' => $businessSetting?->brand_name,
                'logo_path' => $businessSetting?->logo_path,
                'logo_url' => $businessSetting?->logo_path ? asset('storage/'.$businessSetting->logo_path) : null,
                'business_email' => $businessSetting?->business_email,
                'shop_no' => $businessSetting?->shop_no,
                'address' => $businessSetting?->address,
                'country' => $businessSetting?->country,
                'state' => $businessSetting?->state,
                'district' => $businessSetting?->district,
                'pincode' => $businessSetting?->pincode,
                'latitude' => $businessSetting?->latitude,
                'longitude' => $businessSetting?->longitude,
                'gst_no' => $businessSetting?->gst_no,
                'gst_enabled' => (bool) $businessSetting?->gst_enabled,
                'cgst' => $businessSetting?->cgst,
                'sgst' => $businessSetting?->sgst,
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
}
