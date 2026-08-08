<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'brand_name',
        'logo_path',
        'business_email',
        'shop_no',
        'address',
        'country',
        'state',
        'district',
        'pincode',
        'latitude',
        'longitude',
        'gst_no',
        'gst_enabled',
        'cgst',
        'sgst',
    ];

    protected $casts = [
        'gst_enabled' => 'boolean',
        'cgst' => 'float',
        'sgst' => 'float',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
