<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RestaurantTableResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'qr_identifier' => $this->qr_identifier,
            'capacity' => $this->capacity,
            'status' => $this->status,
            'is_active' => $this->is_active,
        ];
    }
}
