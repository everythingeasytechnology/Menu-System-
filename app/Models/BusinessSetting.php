<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_name',
        'logo_path',
        'business_email',
        'shop_no',
        'address',
        'country',
        'state',
        'district',
        'pincode',
        'gst_no',
    ];
}
