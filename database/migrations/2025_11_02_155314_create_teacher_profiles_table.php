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
        Schema::create('teacher_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Links to a User with 'teacher' role
            $table->text('bio')->nullable();
            $table->boolean('verified')->default(false); // Verification status [cite: 23]
            $table->timestamp('free_promo_ends_at')->nullable(); // For the free starter promotion [cite: 68]
            $table->json('availability')->nullable(); // Simple JSON for calendar availability
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_profiles');
    }
};
