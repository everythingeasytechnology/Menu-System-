<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->business_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'profile_image_url' => $this->profile_image_path ? asset('storage/'.$this->profile_image_path) : null,
            'role' => $this->role,
            'status' => $this->status,
        ];
    }
}
