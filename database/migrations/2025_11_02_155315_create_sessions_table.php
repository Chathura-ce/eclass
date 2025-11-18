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
        Schema::create('sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->enum('status', ['scheduled', 'completed', 'no_show_student', 'no_show_teacher'])->default('scheduled');
            $table->string('video_link')->nullable(); // Link to the 3rd-party video SDK room [cite: 71]
            $table->text('teacher_notes')->nullable(); // Part of parent dashboard summary [cite: 27, 74]
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
