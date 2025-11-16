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
        Schema::create('scheduled_institution_order_meals', function (Blueprint $table) {
            $table->id();

            // العلاقة مع الطلب الرئيسي
            $table->foreignId('order_id')
                  ->constrained('scheduled_institution_orders')
                  ->onDelete('cascade');

            // العلاقة مع الوجبة المجدولة
            $table->foreignId('daily_schedule_meal_id')
                  ->constrained('daily_schedule_meals')
                  ->onDelete('cascade');

            // عدد هذه الوجبة المطلوبة
            $table->integer('quantity')->default(0);

            // سعر الوجبة الخاص في وقت الطلب
            $table->decimal('unit_price', 10, 2)->default(0);

            // المبلغ الإجمالي لهذه الوجبة
            $table->decimal('total_price', 12, 2)->default(0);

            $table->timestamps();

            // فريد لمنع التكرار
            $table->unique(['order_id', 'daily_schedule_meal_id'],'o_d_schedule_meal_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_institution_order_meals');
    }
};
