<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'image_path' => $this->image_path ? asset($this->image_path) : null,
            'sort_order' => $this->sort_order,
            'active' => $this->active,
            'status' => $this->status,
            'items' => MenuItemResource::collection($this->whenLoaded('menuItems')),
        ];
    }
}
