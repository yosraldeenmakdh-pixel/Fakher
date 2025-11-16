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
        Schema::create('scheduled_institution_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->nullable()->constrained('official_institutions')->onDelete('set null');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('kitchen_id')->nullable()->constrained()->onDelete('set null');
            // التاريخ والوقت
            $table->date('order_date');
            // $table->time('delivery_time');

            // عدد الأشخاص فقط (لا تفاصيل الوجبات)
            $table->integer('breakfast_persons');
            $table->integer('lunch_persons');
            $table->integer('dinner_persons');

            // الحالة والمبلغ
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', ['pending','confirmed','delivered','cancelled']);

            // تعليمات خاصة
            $table->text('special_instructions')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_institution_orders');
    }
};
