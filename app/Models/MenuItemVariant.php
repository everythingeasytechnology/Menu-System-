<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItemVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_item_id',
        'label',
        'price',
    ];

    protected $casts = [
        'price' => 'float',
    ];

    /**
     * Get the menu item that owns the variant.
     */
    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }
}
