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
        Schema::create('institution_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('official_institutions')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->string('order_number')->unique();
            $table->date('delivery_date');
            $table->time('delivery_time');
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', [ 'Pending ','confirmed', 'delivered', 'cancelled']);
            $table->text('special_instructions')->nullable();
            $table->foreignId('kitchen_id')->nullable()->constrained()->onDelete('set null');
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
        Schema::dropIfExists('institution_orders');
    }
};
