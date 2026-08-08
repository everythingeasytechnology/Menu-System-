<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'business_id',
        'payment_method',
        'payment_gateway',
        'transaction_id',
        'amount',
        'status',
        'paid_at',
        'gateway_response',
    ];

    protected $hidden = [
        'gateway_response',
    ];

    protected $casts = [
        'amount' => 'float',
        'paid_at' => 'datetime',
        'gateway_response' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
