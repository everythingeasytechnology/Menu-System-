<?php

namespace App\Http\Requests\Api\V1\Customers;

use App\Http\Requests\Api\V1\ApiFormRequest;

class StoreCustomerOrderRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'coupon_code' => ['nullable', 'string', 'max:100'],
            'payment_method' => ['nullable', 'string', 'in:cash,online,razorpay'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_item_id' => ['required', 'integer', 'exists:menu_items,id'],
            'items.*.variant_id' => ['nullable', 'integer', 'exists:menu_item_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'items.*.special_instructions' => ['nullable', 'string', 'max:500'],
        ];
    }
}
