<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'menu_item_id' => $this->menu_item_id,
            'menu_item_variant_id' => $this->menu_item_variant_id,
            'item_name' => $this->item_name,
            'variant_label' => $this->variant_label,
            'price' => (float) $this->price,
            'quantity' => $this->quantity,
            'tax' => (float) $this->tax,
            'discount' => (float) $this->discount,
            'total' => (float) $this->total,
            'special_instructions' => $this->special_instructions,
        ];
    }
}
