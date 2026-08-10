<?php

namespace App\Http\Requests\Api\V1\Orders;

use App\Http\Requests\Api\V1\ApiFormRequest;
use App\Models\OrderItem;
use Illuminate\Validation\Rule;

class UpdateOrderItemStatusRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(OrderItem::STATUSES)],
        ];
    }
}
