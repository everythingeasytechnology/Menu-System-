<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresetFoodImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tags',
        'image_path',
    ];
}
