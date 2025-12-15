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

            $table->foreignId('payment_id')->nullable()->constrained('kitchen_payments')->onDelete('set null');

            $table->enum('transaction_type', [
                'online_order',
                'payment',
            ]);

            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('order_type')->nullable();

            // وصف الحركة
            $table->string('description')->nullable();

            // المبلغ (موجب للإضافة إلى الرصيد، سالب للخصم)
            $table->text('amount');

            // الرصيد قبل وبعد الحركة
            $table->text('balance_before');
            $table->text('balance_after');

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
