<?php

namespace App\Http\Requests\Api\V1\ServicePoints;

use App\Http\Requests\Api\V1\ApiFormRequest;

class ServicePointRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'name' => [$this->isMethod('post') ? 'required' : 'sometimes', 'string', 'max:255'],
            'seats' => [$this->isMethod('post') ? 'required' : 'sometimes', 'integer', 'min:1', 'max:500'],
            'category' => [$this->isMethod('post') ? 'required' : 'sometimes', 'string', 'max:255'],
            'point_type' => ['sometimes', 'string', 'in:table,room,counter,takeaway,lounge,villa,pool,other'],
            'status' => ['sometimes', 'string', 'in:available,occupied,reserved,bill_pending,bill-pending,maintenance'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
