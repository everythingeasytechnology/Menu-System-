<?php

namespace App\Http\Requests\Api\V1\Notifications;

use App\Http\Requests\Api\V1\ApiFormRequest;

class DeviceTokenRequest extends ApiFormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->filled('device_token') && ! $this->filled('expo_push_token')) {
            $this->merge([
                'expo_push_token' => $this->input('device_token'),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'expo_push_token' => ['required_without:device_token', 'string', 'max:512'],
            'device_token' => ['required_without:expo_push_token', 'string', 'max:512'],
            'platform' => ['nullable', 'string', 'in:expo,ios,android'],
            'app_version' => ['nullable', 'string', 'max:50'],
            'device_id' => ['nullable', 'string', 'max:255'],
        ];
    }
}
