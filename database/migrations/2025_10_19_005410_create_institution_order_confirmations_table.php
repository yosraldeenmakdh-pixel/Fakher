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
        Schema::create('institution_order_confirmations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained('institution_orders')->onDelete('set null');
            $table->foreignId('kitchen_id')->nullable()->constrained()->onDelete('set null');
            $table->text('notes')->nullable();
            $table->string('order_number');
            $table->date('delivery_date');
            $table->time('delivery_time');
            $table->decimal('total_amount', 12, 2);
            $table->json('order_items')->nullable();
            $table->enum('status', [ 'pending','confirmed', 'delivered', 'cancelled']);
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
        Schema::dropIfExists('institution_order_confirmations');
    }
};
