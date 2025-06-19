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
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('meter_reading_id')->constrained();
            $table->string('bill_ref')->unique();
            $table->date('billing_date');
            $table->integer('consumption');
            $table->decimal('amount_due', 10, 2);
            $table->decimal('penalty', 10, 2)->default(0);
            $table->boolean('is_paid')->default(false);
            $table->date('due_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
