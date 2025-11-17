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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kitchen_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->decimal('total', 10, 2);
            $table->text('special_instructions')->nullable();

            $table->enum('status', ['confirmed','delivered'])
                  ->default('confirmed');

            $table->timestamp('delivered_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
