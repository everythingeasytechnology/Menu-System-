<?php

namespace App\Http\Requests\Api\V1\Business;

use App\Http\Requests\Api\V1\ApiFormRequest;

class UpdateBusinessSettingsRequest extends ApiFormRequest
{
    public function rules(): array
    {
        $settingsRules = [
            'brand_name' => ['sometimes', 'required', 'string', 'max:255'],
            'business_email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'shop_no' => ['sometimes', 'nullable', 'string', 'max:255'],
            'address' => ['sometimes', 'nullable', 'string'],
            'country' => ['sometimes', 'nullable', 'string', 'max:255'],
            'state' => ['sometimes', 'nullable', 'string', 'max:255'],
            'district' => ['sometimes', 'nullable', 'string', 'max:255'],
            'pincode' => ['sometimes', 'nullable', 'string', 'max:20'],
            'latitude' => ['sometimes', 'nullable', 'string', 'max:100'],
            'longitude' => ['sometimes', 'nullable', 'string', 'max:100'],
            'gst_no' => ['sometimes', 'nullable', 'string', 'max:50'],
            'gst_enabled' => ['sometimes', 'boolean'],
            'cgst' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'sgst' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
        ];

        $rules = [
            'settings' => ['sometimes', 'array'],
            'payments' => ['sometimes', 'array'],
            'logo' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:2048'],
            'payments.cash_enabled' => ['sometimes', 'boolean'],
            'payments.razorpay_enabled' => ['sometimes', 'boolean'],
            'payments.razorpay_key_id' => ['sometimes', 'nullable', 'string', 'max:255'],
            'payments.razorpay_key_secret' => ['sometimes', 'nullable', 'string', 'max:255'],
            'cash_enabled' => ['sometimes', 'boolean'],
            'razorpay_enabled' => ['sometimes', 'boolean'],
            'razorpay_key_id' => ['sometimes', 'nullable', 'string', 'max:255'],
            'razorpay_key_secret' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];

        foreach ($settingsRules as $field => $fieldRules) {
            $rules[$field] = $fieldRules;
            $rules['settings.'.$field] = $fieldRules;
        }

        return $rules;
    }
}
