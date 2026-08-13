<?php

namespace App\Http\Requests\Api\V1\Menu;

use App\Http\Requests\Api\V1\ApiFormRequest;

class MenuItemRequest extends ApiFormRequest
{
    public function rules(): array
    {
        $isCreating = $this->isMethod('post') && ! $this->route('menuItem');

        return [
            'category_id' => [$isCreating ? 'required' : 'sometimes', 'integer', 'exists:menu_categories,id'],
            'name' => [$isCreating ? 'required' : 'sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['sometimes', 'string', 'in:veg,non-veg'],
            'price' => [$isCreating ? 'required' : 'sometimes', 'numeric', 'min:0'],
            'tax_rate' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'preparation_time_minutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'cooking_time' => ['nullable', 'string', 'max:100'],
            'preset_food_image_id' => ['nullable', 'integer', 'exists:preset_food_images,id'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'preset_image_id' => ['nullable', 'integer', 'exists:preset_food_images,id'],
            'remove_image' => ['nullable', 'string'],
            'availability' => ['sometimes', 'boolean'],
            'stock' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', 'string', 'in:active,inactive'],
            'variants' => ['sometimes', 'array'],
            'variants.*.id' => ['nullable', 'integer', 'exists:menu_item_variants,id'],
            'variants.*.label' => ['required_with:variants', 'string', 'max:255'],
            'variants.*.price' => ['required_with:variants', 'numeric', 'min:0'],
        ];
    }
}
