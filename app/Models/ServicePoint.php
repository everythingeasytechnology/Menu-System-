<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicePoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'seats',
        'category',
        'status',
        'order_number',
        'amount',
        'items',
    ];

    protected $casts = [
        'items' => 'array',
        'seats' => 'integer',
        'amount' => 'float',
    ];
}
