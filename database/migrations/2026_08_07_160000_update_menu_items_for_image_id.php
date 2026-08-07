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
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn('image_path');
            $table->foreignId('preset_food_image_id')
                  ->nullable()
                  ->after('cooking_time')
                  ->constrained('preset_food_images')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('preset_food_image_id');
            $table->string('image_path')->nullable()->after('cooking_time');
        });
    }
};
