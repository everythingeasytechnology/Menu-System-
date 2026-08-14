<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_user_id',
        'name',
        'type',
        'gst_number',
        'gst_enabled',
        'cgst',
        'sgst',
        'phone',
        'email',
        'business_email',
        'address',
        'shop_no',
        'city',
        'state',
        'district',
        'country',
        'pincode',
        'latitude',
        'longitude',
        'logo_path',
        'opening_time',
        'closing_time',
        'timezone',
        'status',
    ];

    protected $casts = [
        'opening_time' => 'datetime:H:i',
        'closing_time' => 'datetime:H:i',
        'gst_enabled' => 'boolean',
        'cgst' => 'float',
        'sgst' => 'float',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(MenuCategory::class);
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    public function restaurantTables(): HasMany
    {
        return $this->hasMany(RestaurantTable::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function servicePoints(): HasMany
    {
        return $this->hasMany(ServicePoint::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
