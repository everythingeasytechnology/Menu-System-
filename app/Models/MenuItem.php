<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'type',
        'cooking_time',
        'preset_food_image_id',
        'stock',
    ];

    protected $casts = [
        'stock' => 'boolean',
    ];

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
