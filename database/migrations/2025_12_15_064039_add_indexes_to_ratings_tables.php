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
        Schema::table('ratings', function (Blueprint $table) {
            $table->index(['meal_id', 'rating']); // لحساب متوسط التقييم
            $table->index(['created_at', 'is_visible']); // لاسترجاع التقييمات الحديثة والمرئية
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ratings_tables', function (Blueprint $table) {
            //
        });
    }
};
