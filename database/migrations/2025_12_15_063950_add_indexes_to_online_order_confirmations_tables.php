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
        Schema::table('online_order_confirmations', function (Blueprint $table) {
            $table->index('order_id');
            $table->index('kitchen_id');
            $table->index('order_number');
            $table->index('status');
            $table->index('delivery_date');

            // فهارس مركبة
            $table->index(['kitchen_id', 'delivery_date']);
            $table->index(['status', 'delivery_date']);
            $table->index(['order_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('online_order_confirmations_tables', function (Blueprint $table) {
            //
        });
    }
};
