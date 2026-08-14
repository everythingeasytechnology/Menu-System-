<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('business_settings');
    }

    public function down(): void
    {
        Schema::create('business_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->nullable()->constrained('businesses')->nullOnDelete();
            $table->string('brand_name')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('business_email')->nullable();
            $table->string('shop_no')->nullable();
            $table->text('address')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('district')->nullable();
            $table->string('pincode')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('gst_no')->nullable();
            $table->boolean('gst_enabled')->default(false);
            $table->decimal('cgst', 5, 2)->default(2.50);
            $table->decimal('sgst', 5, 2)->default(2.50);
            $table->timestamps();
        });
    }
};
