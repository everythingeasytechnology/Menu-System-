<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    public const ACTIVE_STATUSES = ['preparing', 'ready', 'served'];

    public const STATUSES = ['preparing', 'ready', 'served', 'completed', 'cancelled'];

    protected $fillable = [
        'business_id',
        'table_id',
        'room_id',
        'service_point_id',
        'user_id',
        'coupon_id',
        'order_number',
        'order_type',
        'customer_name',
        'customer_phone',
        'subtotal',
        'tax',
        'discount',
        'total',
        'payment_status',
        'order_status',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'float',
        'tax' => 'float',
        'discount' => 'float',
        'total' => 'float',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function restaurantTable(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function servicePoint(): BelongsTo
    {
        return $this->belongsTo(ServicePoint::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function scopeLive(Builder $query): Builder
    {
        return $query->whereIn('order_status', self::ACTIVE_STATUSES);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function compactNumber(): string
    {
        return self::compactOrderNumber($this->order_number, $this->id);
    }

    public static function compactOrderNumber(?string $orderNumber, ?int $fallbackId = null): string
    {
        if ($orderNumber && preg_match('/(\d{3,8})$/', $orderNumber, $matches)) {
            return '#'.$matches[1];
        }

        return '#'.($fallbackId ?: 'ORDER');
    }
}
