<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];
}
