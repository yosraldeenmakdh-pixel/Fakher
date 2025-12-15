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
        Schema::create('kitchen_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kitchen_id')->nullable()->constrained('kitchens')->onDelete('set null');
            $table->text('amount');
            $table->string('transaction_reference')->nullable();
            $table->string('verification_file')->nullable();
            $table->enum('status', ['pending', 'verified', 'rejected']);
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kitchen_payments');
    }
};
