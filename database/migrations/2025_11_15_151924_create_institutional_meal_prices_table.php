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
        Schema::create('institutional_meal_prices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('meal_id')
                  ->constrained()
                  ->onDelete('cascade');

            $table->decimal('scheduled_price', 10, 2);

            $table->boolean('is_active')->default(true);


            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institutional_meal_prices');
    }
};
