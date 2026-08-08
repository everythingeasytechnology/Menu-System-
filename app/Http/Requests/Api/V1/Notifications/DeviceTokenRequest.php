<?php

namespace App\Http\Requests\Api\V1\Notifications;

use App\Http\Requests\Api\V1\ApiFormRequest;

class DeviceTokenRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'device_token' => ['required', 'string', 'max:512'],
            'platform' => ['required', 'string', 'in:expo,ios,android'],
            'app_version' => ['nullable', 'string', 'max:50'],
            'device_id' => ['nullable', 'string', 'max:255'],
        ];
    }
}
