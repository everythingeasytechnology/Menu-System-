<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('business_email')->nullable()->after('email');
            $table->string('shop_no')->nullable()->after('address');
            $table->string('district')->nullable()->after('state');
            $table->string('pincode')->nullable()->after('country');
            $table->string('latitude')->nullable()->after('pincode');
            $table->string('longitude')->nullable()->after('latitude');
            $table->boolean('gst_enabled')->default(false)->after('gst_number');
            $table->decimal('cgst', 5, 2)->default(2.50)->after('gst_enabled');
            $table->decimal('sgst', 5, 2)->default(2.50)->after('cgst');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn([
                'business_email',
                'shop_no',
                'district',
                'pincode',
                'latitude',
                'longitude',
                'gst_enabled',
                'cgst',
                'sgst',
            ]);
        });
    }
};
