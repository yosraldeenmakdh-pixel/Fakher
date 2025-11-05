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
        Schema::create('official_institutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('institution_type', ['scheduled', 'normal'])->default('normal');
            $table->string('name');
            $table->string('contract_number')->unique();
            $table->date('contract_start_date');
            $table->date('contract_end_date');
            $table->enum('contract_status', ['active', 'expired', 'suspended', 'renewed']);
            $table->decimal('Financial_debts', 12, 2)->default(0);
            // $table->string('contact_person');
            $table->string('contact_phone');
            $table->string('contact_email');
            $table->text('special_instructions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('official_institutions');
    }
};
