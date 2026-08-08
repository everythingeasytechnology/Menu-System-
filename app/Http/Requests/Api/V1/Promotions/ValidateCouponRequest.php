<?php

namespace App\Http\Requests\Api\V1\Promotions;

use App\Http\Requests\Api\V1\ApiFormRequest;

class ValidateCouponRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:100'],
            'subtotal' => ['required', 'numeric', 'min:0'],
        ];
    }
}
