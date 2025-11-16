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
        Schema::table('scheduled_institution_orders', function (Blueprint $table) {
            $table->unique(['institution_id', 'branch_id', 'kitchen_id', 'order_date'], 'unique_order_per_day');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scheduled_institution_orders', function (Blueprint $table) {
            //
        });
    }
};
