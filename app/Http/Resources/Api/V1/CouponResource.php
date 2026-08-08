<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'type' => $this->type,
            'value' => (float) $this->value,
            'minimum_order' => (float) $this->minimum_order,
            'maximum_discount' => $this->maximum_discount === null ? null : (float) $this->maximum_discount,
            'usage_limit' => $this->usage_limit,
            'used_count' => $this->used_count,
            'per_user_limit' => $this->per_user_limit,
            'starts_at' => $this->starts_at?->toISOString(),
            'expires_at' => $this->expires_at?->toISOString(),
            'is_active' => $this->is_active,
        ];
    }
}
