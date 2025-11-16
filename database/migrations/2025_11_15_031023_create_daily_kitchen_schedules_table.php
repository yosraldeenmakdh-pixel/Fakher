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
        Schema::create('daily_kitchen_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kitchen_id')->nullable()->constrained()->onDelete('set null');

            $table->date('schedule_date');

            $table->timestamps();

            $table->unique(['kitchen_id', 'schedule_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_kitchen_schedules');
    }
};
