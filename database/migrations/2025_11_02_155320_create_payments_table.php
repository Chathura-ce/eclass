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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade'); // Who paid
            $table->decimal('amount', 8, 2);
            // Escrow status: paid by student, released to teacher, refunded [cite: 31, 72]
            $table->enum('status', ['pending', 'paid', 'released', 'refunded'])->default('pending');
            $table->string('gateway')->default('payhere'); // Payment gateway used
            $table->string('gateway_ref')->nullable(); // Transaction ID from PayHere
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('released_at')->nullable(); // When funds are released to teacher
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
