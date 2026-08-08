<?php

namespace App\Http\Requests\Api\V1\ServicePoints;

use App\Http\Requests\Api\V1\ApiFormRequest;

class RoomRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'name' => [$this->isMethod('post') ? 'required' : 'sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'in:available,occupied,reserved,maintenance'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
