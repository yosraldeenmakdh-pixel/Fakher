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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('meal_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('rating') ;
            $table->text('comment')->nullable();
            $table->boolean('is_visible')->default(true);

            $table->timestamps();

            $table->unique(['user_id', 'meal_id']);

            $table->index(['meal_id', 'is_visible']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
