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
        Schema::create('online_order_confirmations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('order_onlines')->onDelete('cascade');
            $table->foreignId('kitchen_id')->constrained()->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->string('order_number');
            $table->dateTime('delivery_date'); // تاريخ الاستلام
            $table->decimal('total_amount', 12, 2);
            $table->json('order_items')->nullable() ;
            $table->enum('status', ['confirmed', 'delivered', 'cancelled']);
            $table->text('special_instructions')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('online_order_confirmations');
    }
};
