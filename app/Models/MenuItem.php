<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'menu_category_id',
        'name',
        'description',
        'category',
        'type',
        'price',
        'tax_rate',
        'preparation_time_minutes',
        'cooking_time',
        'preset_food_image_id',
        'stock',
        'availability',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'stock' => 'boolean',
        'availability' => 'boolean',
        'price' => 'float',
        'tax_rate' => 'float',
        'preparation_time_minutes' => 'integer',
        'sort_order' => 'integer',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function menuCategory(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class);
    }

    /**
     * Get the preset image associated with this menu item.
     */
    public function presetImage()
    {
        return $this->belongsTo(PresetFoodImage::class, 'preset_food_image_id');
    }

    /**
     * Get the variants/prices for the menu item.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(MenuItemVariant::class);
    }
}
