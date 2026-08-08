<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->menu_category_id,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'type' => $this->type,
            'price' => (float) $this->price,
            'tax_rate' => (float) $this->tax_rate,
            'preparation_time_minutes' => $this->preparation_time_minutes,
            'cooking_time' => $this->cooking_time,
            'image' => $this->presetImage?->image_path,
            'available' => (bool) ($this->stock && $this->availability && $this->status === 'active'),
            'sort_order' => $this->sort_order,
            'status' => $this->status,
            'variants' => MenuItemVariantResource::collection($this->whenLoaded('variants')),
        ];
    }
}
