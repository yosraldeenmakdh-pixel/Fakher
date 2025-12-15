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
            $table->index('user_id');
            $table->index('branch_id');
            $table->index('kitchen_id');
            $table->index('status');
            $table->index('order_date');

            // فهارس مركبة للاستعلامات الشائعة
            $table->index(['status', 'created_at']);
            $table->index(['branch_id', 'status']);
            $table->index(['kitchen_id', 'status']);
            $table->index(['user_id', 'created_at']);
            $table->index(['order_date', 'status']);

            // فهرس للحقول المستخدمة في البحث أو الترتيب
            $table->index(['customer_phone', 'created_at']);

            // إذا كنت تستخدم الموقع الجغرافي في الاستعلامات
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_onlines_tables', function (Blueprint $table) {
            //
        });
    }
};
