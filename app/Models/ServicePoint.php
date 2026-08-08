<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicePoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'code',
        'qr_identifier',
        'name',
        'seats',
        'category',
        'point_type',
        'status',
        'is_active',
        'order_number',
        'amount',
        'items',
    ];

    protected $casts = [
        'items' => 'array',
        'seats' => 'integer',
        'amount' => 'float',
        'is_active' => 'boolean',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
