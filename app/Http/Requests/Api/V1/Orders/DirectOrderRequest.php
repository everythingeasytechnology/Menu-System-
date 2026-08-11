<?php

namespace App\Http\Requests\Api\V1\Orders;

use App\Http\Requests\Api\V1\ApiFormRequest;

class DirectOrderRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'order_id' => ['nullable', 'integer', 'min:1'],
            'table_id' => ['nullable', 'integer', 'exists:restaurant_tables,id'],
            'room_id' => ['nullable', 'integer', 'exists:rooms,id'],
            'service_point_id' => ['nullable', 'integer', 'exists:service_points,id'],
            'order_type' => ['nullable', 'string', 'in:dine_in,room_service,takeaway'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'coupon_code' => ['nullable', 'string', 'max:100'],
            'payment_method' => ['nullable', 'string', 'in:cash,online,razorpay'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_item_id' => ['required', 'integer', 'exists:menu_items,id'],
            'items.*.variant_id' => ['nullable', 'integer', 'exists:menu_item_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'items.*.special_instructions' => ['nullable', 'string', 'max:500'],
            'items.*.client_price' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
