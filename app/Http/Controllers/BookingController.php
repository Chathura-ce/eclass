<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\TeacherProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    /**
     * Create a new booking (Student only).
     */
    public function store(Request $request)
    {
        $student = $request->user(); // The logged-in student

        // 1. Validate the Request
        $validator = Validator::make($request->all(), [
            'teacher_id' => ['required', 'exists:users,id'], // Must be a valid user
            'scheduled_at' => ['required', 'date', 'after:now'], // Must be in the future
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 2. Get the Teacher's Profile to find their rate
        // We assume the 'teacher_id' passed is the User ID of the teacher
        $teacherProfile = TeacherProfile::where('user_id', $request->teacher_id)->first();

        if (!$teacherProfile) {
            return response()->json(['message' => 'This user is not a teacher.'], 404);
        }

        // 3. Create the Booking
        $booking = Booking::create([
            'student_id' => $student->id,
            'teacher_id' => $request->teacher_id,
            'scheduled_at' => $request->scheduled_at,
            'status' => 'pending', // Default status
            'price' => $teacherProfile->hourly_rate, // Snapshot the current price
        ]);

        return response()->json([
            'message' => 'Booking request sent successfully!',
            'booking' => $booking
        ], 201);
    }

    /**
     * List bookings for the current user (works for Student OR Teacher).
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'student') {
            // If student, get bookings where I am the student
            $bookings = $user->bookingsAsStudent()->with('teacher:id,name')->get();
        } elseif ($user->role === 'teacher') {
            // If teacher, get bookings where I am the teacher
            $bookings = $user->bookingsAsTeacher()->with('student:id,name')->get();
        } else {
            return response()->json(['data' => []]);
        }

        return response()->json(['data' => $bookings]);
    }
}