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
        Schema::table('business_settings', function (Blueprint $table) {
            $table->string('latitude')->nullable()->after('pincode');
            $table->string('longitude')->nullable()->after('latitude');
            $table->boolean('gst_enabled')->default(false)->after('gst_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_settings', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'gst_enabled']);
        });
    }
};
