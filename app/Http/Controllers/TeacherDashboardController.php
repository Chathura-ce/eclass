<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use Illuminate\Support\Carbon;

class TeacherDashboardController extends Controller
{
    /**
     * Get statistics and upcoming classes for the teacher dashboard.
     */
    public function index(Request $request)
    {
        $teacher = $request->user();

        // 1. Calculate Total Earnings (Only confirmed bookings count as money earned)
        $totalEarnings = Booking::where('teacher_id', $teacher->id)
            ->where('status', 'confirmed')
            ->sum('price');

        // 2. Count Total Bookings (All time)
        $totalBookings = Booking::where('teacher_id', $teacher->id)->count();

        // 3. Count Pending Requests (To show a badge or alert)
        $pendingRequests = Booking::where('teacher_id', $teacher->id)
            ->where('status', 'pending')
            ->count();

        // 4. Get Upcoming Classes (Next 5)
        $upcomingClasses = Booking::where('teacher_id', $teacher->id)
            ->where('scheduled_at', '>=', Carbon::now()) // Future dates only
            ->whereIn('status', ['confirmed', 'pending'])
            ->with('student:id,name') // Load student name
            ->orderBy('scheduled_at', 'asc') // Sooner first
            ->limit(5)
            ->get();

        return response()->json([
            'earnings' => number_format($totalEarnings, 2),
            'total_bookings' => $totalBookings,
            'pending_requests' => $pendingRequests,
            'upcoming_classes' => $upcomingClasses
        ]);
    }
}