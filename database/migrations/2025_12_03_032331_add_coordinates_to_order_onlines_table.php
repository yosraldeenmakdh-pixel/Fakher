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
        Schema::table('order_onlines', function (Blueprint $table) {
            $table->dropColumn('address');

            $table->decimal('latitude', 10, 8)->nullable()->after('customer_phone');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_onlines', function (Blueprint $table) {
            //
        });
    }
};
