<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
    protected $fillable = [
        'version',
    ];

    public static function current(): self
    {
        return self::query()->first() ?? self::query()->create([
            'version' => '1.0.0',
        ]);
    }
}
