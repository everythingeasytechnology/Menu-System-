<?php

namespace App\Http\Requests\Api\V1\Customers;

use App\Http\Requests\Api\V1\ApiFormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCustomerOrderRequest extends ApiFormRequest
{
    protected function prepareForValidation(): void
    {
        $items = collect($this->input('items', []))
            ->map(function ($item) {
                if (! is_array($item)) {
                    return $item;
                }

                if (! array_key_exists('menu_item_id', $item)) {
                    $item['menu_item_id'] = $item['menu_item_id']
                        ?? $item['id']
                        ?? $item['item_id']
                        ?? $item['menu_id']
                        ?? null;
                }

                return $item;
            })
            ->all();

        $this->merge([
            'items' => $items,
        ]);
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'coupon_code' => ['nullable', 'string', 'max:100'],
            'payment_method' => ['nullable', 'string', 'in:cash,online,razorpay'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_item_id' => ['required', 'integer', 'exists:menu_items,id'],
            'items.*.variant_id' => ['nullable', 'integer', 'exists:menu_item_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'items.*.special_instructions' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $validator->errors()->first() ?: 'Validation failed',
            'errors' => $validator->errors(),
        ], 422));
    }
}
