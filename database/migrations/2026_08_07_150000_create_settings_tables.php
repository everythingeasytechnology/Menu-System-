<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('business_settings', function (Blueprint $table) {
            $table->id();
            $table->string('brand_name')->nullable();
            $table->string('business_email')->nullable();
            $table->string('shop_no')->nullable();
            $table->text('address')->nullable();
            $table->string('district')->nullable();
            $table->string('pincode')->nullable();
            $table->string('gst_no')->nullable();
            $table->timestamps();
        });

        Schema::create('razorpay_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->default(false);
            $table->string('key_id')->nullable();
            $table->string('key_secret')->nullable();
            $table->timestamps();
        });

        Schema::create('cash_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_settings');
        Schema::dropIfExists('razorpay_settings');
        Schema::dropIfExists('cash_settings');
    }
};
