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
        Schema::table('menu_categories', function (Blueprint $table) {
            $table->foreignId('business_id')->nullable()->after('id')->constrained('businesses')->nullOnDelete();
            $table->text('description')->nullable()->after('code');
            $table->string('image_path')->nullable()->after('description');
            $table->unsignedInteger('sort_order')->default(0)->after('image_path');
            $table->string('status')->default('active')->after('active');

            $table->index(['business_id', 'active', 'sort_order']);
            $table->index(['business_id', 'status']);
        });

        Schema::table('menu_categories', function (Blueprint $table) {
            $table->dropUnique('menu_categories_name_unique');
            $table->dropUnique('menu_categories_code_unique');
            $table->unique(['business_id', 'name'], 'menu_categories_business_name_unique');
            $table->unique(['business_id', 'code'], 'menu_categories_business_code_unique');
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->foreignId('business_id')->nullable()->after('id')->constrained('businesses')->nullOnDelete();
            $table->foreignId('menu_category_id')->nullable()->after('business_id')->constrained('menu_categories')->nullOnDelete();
            $table->text('description')->nullable()->after('name');
            $table->decimal('price', 10, 2)->default(0)->after('type');
            $table->decimal('tax_rate', 5, 2)->default(0)->after('price');
            $table->unsignedSmallInteger('preparation_time_minutes')->nullable()->after('tax_rate');
            $table->boolean('availability')->default(true)->after('stock');
            $table->unsignedInteger('sort_order')->default(0)->after('availability');
            $table->string('status')->default('active')->after('sort_order');

            $table->index(['business_id', 'menu_category_id']);
            $table->index(['business_id', 'status', 'availability', 'stock']);
            $table->index(['business_id', 'category']);
        });

        Schema::table('menu_item_variants', function (Blueprint $table) {
            $table->index(['menu_item_id', 'price']);
        });

        Schema::table('service_points', function (Blueprint $table) {
            $table->foreignId('business_id')->nullable()->after('id')->constrained('businesses')->nullOnDelete();
            $table->string('qr_identifier')->nullable()->unique()->after('code');
            $table->string('point_type')->default('table')->after('category');
            $table->boolean('is_active')->default(true)->after('status');

            $table->index(['business_id', 'point_type', 'status']);
            $table->index(['business_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_points', function (Blueprint $table) {
            $table->dropConstrainedForeignId('business_id');
            $table->dropUnique(['qr_identifier']);
            $table->dropColumn(['qr_identifier', 'point_type', 'is_active']);
        });

        Schema::table('menu_item_variants', function (Blueprint $table) {
            $table->dropIndex(['menu_item_id', 'price']);
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('business_id');
            $table->dropConstrainedForeignId('menu_category_id');
            $table->dropColumn([
                'description',
                'price',
                'tax_rate',
                'preparation_time_minutes',
                'availability',
                'sort_order',
                'status',
            ]);
        });

        Schema::table('menu_categories', function (Blueprint $table) {
            $table->dropUnique('menu_categories_business_name_unique');
            $table->dropUnique('menu_categories_business_code_unique');
            $table->unique('name');
            $table->unique('code');
        });

        Schema::table('menu_categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('business_id');
            $table->dropColumn(['description', 'image_path', 'sort_order', 'status']);
        });
    }
};
