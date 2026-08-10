<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('orders')
            ->whereIn('order_status', ['pending', 'confirmed'])
            ->update(['order_status' => 'preparing']);

        DB::table('order_items')
            ->whereIn('status', ['pending', 'confirmed'])
            ->update(['status' => 'preparing']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not reversible: original pending/confirmed distinction is lost.
    }
};
