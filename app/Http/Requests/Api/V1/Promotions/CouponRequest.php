<?php

namespace App\Http\Requests\Api\V1\Promotions;

use App\Http\Requests\Api\V1\ApiFormRequest;

class CouponRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'code' => [$this->isMethod('post') ? 'required' : 'sometimes', 'string', 'max:100'],
            'type' => ['sometimes', 'string', 'in:percentage,fixed'],
            'value' => [$this->isMethod('post') ? 'required' : 'sometimes', 'numeric', 'min:0'],
            'minimum_order' => ['sometimes', 'numeric', 'min:0'],
            'maximum_discount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'per_user_limit' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
