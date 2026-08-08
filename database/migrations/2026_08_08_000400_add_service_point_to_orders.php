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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('service_point_id')
                ->nullable()
                ->after('room_id')
                ->constrained('service_points')
                ->nullOnDelete();

            $table->index(['service_point_id', 'order_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['service_point_id', 'order_status']);
            $table->dropConstrainedForeignId('service_point_id');
        });
    }
};
