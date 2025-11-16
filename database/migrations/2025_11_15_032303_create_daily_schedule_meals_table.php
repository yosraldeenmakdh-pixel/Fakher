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
        Schema::create('daily_schedule_meals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_kitchen_schedule_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('meal_id')->constrained()->onDelete('cascade');
            $table->enum('meal_type', ['breakfast', 'lunch', 'dinner']);
            $table->timestamps();

            $table->unique(
                ['daily_kitchen_schedule_id', 'meal_id', 'meal_type'],
                'dsm_unique_schedule_meal_type'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_schedule_meals');
    }
};
