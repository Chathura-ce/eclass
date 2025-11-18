<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\TeacherProfile;
use App\Models\Booking;
use App\Models\Session;
use App\Models\Review;
use App\Models\Payment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create a default Admin user
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@eclass.lk',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 2. Create a default Student user
        User::factory()->create([
            'name' => 'Student User',
            'email' => 'student@eclass.lk',
            'password' => Hash::make('password'),
            'role' => 'student',
        ]);

        // 3. Create 10 Teachers, each with a profile
        TeacherProfile::factory(10)->create();

        // 4. Create 20 more random Students
        User::factory(20)->create(['role' => 'student']);

        // 5. Create 50 Bookings, Sessions, Payments, and Reviews
        Session::factory(50)
            ->afterCreating(function ($session) {

                // Get the booking associated with this session
                $booking = $session->booking;

                // Make sure the booking is confirmed
                $booking->update(['status' => 'confirmed']);

                // Create a payment for this booking
                Payment::factory()->create([
                    'booking_id' => $booking->id,
                    'student_id' => $booking->student_id,
                    'amount' => $booking->price,
                    'status' => 'released', // Assume these past sessions are paid and released
                ]);

                // *** THIS IS THE FIX ***
                // Now, create a review using the correct IDs from the booking
                Review::factory()->create([
                    'session_id' => $session->id,
                    'student_id' => $booking->student_id,
                    'teacher_id' => $booking->teacher_id,
                ]);
            })
            ->create(); // This creates the 50 sessions
    }
}