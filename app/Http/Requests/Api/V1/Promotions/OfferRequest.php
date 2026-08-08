<?php

namespace App\Http\Requests\Api\V1\Promotions;

use App\Http\Requests\Api\V1\ApiFormRequest;

class OfferRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'name' => [$this->isMethod('post') ? 'required' : 'sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'offer_type' => ['sometimes', 'string', 'in:percentage,fixed'],
            'discount_value' => [$this->isMethod('post') ? 'required' : 'sometimes', 'numeric', 'min:0'],
            'rules' => ['nullable', 'array'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
