<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    public const STATUSES = ['pending', 'confirmed', 'preparing', 'ready', 'served', 'cancelled'];

    protected $fillable = [
        'order_id',
        'menu_item_id',
        'menu_item_variant_id',
        'item_name',
        'variant_label',
        'price',
        'quantity',
        'status',
        'tax',
        'discount',
        'total',
        'special_instructions',
    ];

    protected $casts = [
        'price' => 'float',
        'quantity' => 'integer',
        'tax' => 'float',
        'discount' => 'float',
        'total' => 'float',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }
}
