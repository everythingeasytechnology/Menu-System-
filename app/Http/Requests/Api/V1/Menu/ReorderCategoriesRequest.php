<?php

namespace App\Http\Requests\Api\V1\Menu;

use App\Http\Requests\Api\V1\ApiFormRequest;

class ReorderCategoriesRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'categories' => ['required', 'array', 'min:1'],
            'categories.*.id' => ['required', 'integer', 'exists:menu_categories,id'],
            'categories.*.sort_order' => ['required', 'integer', 'min:0'],
        ];
    }
}
