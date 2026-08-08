<?php

namespace App\Http\Requests\Api\V1\Payments;

use App\Http\Requests\Api\V1\ApiFormRequest;

class StorePaymentRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'payment_method' => ['required', 'string', 'in:cash,online,razorpay'],
            'payment_gateway' => ['nullable', 'string', 'max:100'],
            'transaction_id' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'in:pending,paid,failed,refunded'],
            'paid_at' => ['nullable', 'date'],
        ];
    }
}
