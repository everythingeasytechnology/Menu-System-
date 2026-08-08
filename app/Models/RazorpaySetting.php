<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RazorpaySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'enabled',
        'key_id',
        'key_secret',
    ];

    protected $hidden = [
        'key_secret',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
