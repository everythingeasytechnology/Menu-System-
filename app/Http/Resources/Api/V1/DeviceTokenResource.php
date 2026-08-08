<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeviceTokenResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'platform' => $this->platform,
            'app_version' => $this->app_version,
            'device_id' => $this->device_id,
            'is_active' => $this->is_active,
            'last_used_at' => $this->last_used_at?->toISOString(),
        ];
    }
}
