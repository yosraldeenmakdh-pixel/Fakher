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
        Schema::create('kitchen_financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kitchen_id')->constrained('kitchens')->onDelete('cascade');

            $table->foreignId('payment_id')->nullable()->constrained('institution_payments')->onDelete('set null');

            // نوع الحركة
            $table->enum('transaction_type', [
                'scheduled_order',    // طلب مجدول
                'special_order',      // طلب خاص
                'emergency_order',    // طلب استنفار
                'online_order',    // طلب من الموقع الالكتروني
                'order',    // طلب من داخل المطبخ نفسه
                'payment',              // استرداد
            ]);

            // العلاقة مع الطلب
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('order_type')->nullable();

            // وصف الحركة
            $table->string('description');

            // المبلغ (موجب للإضافة إلى الرصيد، سالب للخصم)
            $table->decimal('amount', 12, 2);

            // الرصيد قبل وبعد الحركة
            $table->decimal('balance_before', 12, 2);
            $table->decimal('balance_after', 12, 2);

            // حالة الحركة
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('completed');

            // تاريخ الحركة الفعلي
            $table->timestamp('transaction_date');

            $table->timestamps();

            // فهارس للأداء
            $table->index(['kitchen_id', 'transaction_date'], 'kitchen_trans_date_idx');
            $table->index(['kitchen_id', 'transaction_type'], 'kitchen_trans_type_idx');
            $table->index(['order_id', 'order_type']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kitchen_financial_transactions');
    }
};
