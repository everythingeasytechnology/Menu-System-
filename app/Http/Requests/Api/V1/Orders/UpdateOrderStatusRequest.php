<?php

namespace App\Http\Requests\Api\V1\Orders;

use App\Http\Requests\Api\V1\ApiFormRequest;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateOrderStatusRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', Rule::in(Order::STATUSES)],
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.id' => ['required_with:items', 'integer'],
            'items.*.status' => ['required_with:items', 'string', Rule::in(OrderItem::STATUSES)],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->filled('status') && ! $this->filled('items')) {
                $validator->errors()->add('status', 'Provide an order status, item statuses, or both.');
            }
        });
    }
}
