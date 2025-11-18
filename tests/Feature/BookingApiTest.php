<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\TeacherProfile;
use App\Models\Booking;

class BookingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_create_a_booking(): void
    {
        // Arrange: Create a Teacher with a specific rate
        $teacher = User::factory()->teacher()->create();
        TeacherProfile::factory()->create([
            'user_id' => $teacher->id,
            'hourly_rate' => 1500.00
        ]);

        // Arrange: Create a Student and login
        $student = User::factory()->create(['role' => 'student']);

        // Act: Student tries to book the teacher
        $response = $this->actingAs($student)->postJson('/api/bookings', [
            'teacher_id' => $teacher->id,
            'scheduled_at' => now()->addDays(2)->toDateTimeString(),
        ]);

        // Assert: Check Response
        $response->assertStatus(201)
            ->assertJsonFragment(['message' => 'Booking request sent successfully!']);

        // Assert: Check Database
        $this->assertDatabaseHas('bookings', [
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'price' => 1500.00, // Ensure it grabbed the correct price
            'status' => 'pending'
        ]);
    }

    public function test_teacher_can_view_their_bookings(): void
    {
        // Arrange: Create a Booking
        $teacher = User::factory()->teacher()->create();
        TeacherProfile::factory()->create(['user_id' => $teacher->id]);
        $student = User::factory()->create(['role' => 'student']);

        $booking = Booking::factory()->create([
            'teacher_id' => $teacher->id,
            'student_id' => $student->id
        ]);

        // Act: Teacher checks their bookings
        $response = $this->actingAs($teacher)->getJson('/api/my-bookings');

        // Assert
        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $booking->id]) // Should see the booking
            ->assertJsonFragment(['name' => $student->name]); // Should see student name
    }
}