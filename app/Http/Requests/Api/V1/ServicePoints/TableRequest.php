<?php

namespace App\Http\Requests\Api\V1\ServicePoints;

use App\Http\Requests\Api\V1\ApiFormRequest;

class TableRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'name' => [$this->isMethod('post') ? 'required' : 'sometimes', 'string', 'max:255'],
            'capacity' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'status' => ['sometimes', 'string', 'in:available,occupied,reserved,bill_pending,maintenance'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
