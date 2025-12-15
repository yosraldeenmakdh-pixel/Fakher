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
        Schema::table('order_online_items', function (Blueprint $table) {
            $table->index('order_online_id');
            $table->index('meal_id');

            // فهرس مركب لاستعلامات الطلبات والوجبات
            $table->index(['order_online_id', 'meal_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_online_items_tables', function (Blueprint $table) {
            //
        });
    }
};
