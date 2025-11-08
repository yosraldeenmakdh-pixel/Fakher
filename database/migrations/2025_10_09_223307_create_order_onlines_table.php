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
        Schema::create('order_onlines', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('kitchen_id')->nullable()->constrained()->onDelete('cascade'); // للمطبخ المسؤول


            $table->decimal('total', 10, 2)->nullable();
            $table->enum('status', ['collecting','pending','delivered', 'cancelled'])->default('collecting');

            $table->dateTime('order_date')->nullable(); // تاريخ الطلب
            $table->dateTime('confirmed_at')->nullable(); // وقت التأكيد من المطبخ
            $table->dateTime('delivered_at')->nullable();

            $table->text('special_instructions')->nullable();

            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->text('address')->nullable();


            $table->foreignId('confirmed_by')->nullable()->constrained('users')->onDelete('set null');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_onlines');
    }
};
