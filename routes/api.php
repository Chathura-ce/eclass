<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\StudentAuthController;
use App\Http\Controllers\Auth\TeacherAuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\TeacherProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// --- Public Teacher Routes ---

// 1. Register a new Teacher
// This will create a new User with the 'teacher' role AND a new TeacherProfile
Route::post('/teachers/register', [TeacherAuthController::class, 'register']);

// 2. Get a list of all verified teachers (for student search/discovery)
// This supports the "Smart Teacher Discovery" feature
Route::get('/teachers', [TeacherProfileController::class, 'index']);

// 3. Get a single teacher's public profile
// This is for the teacher's public-facing profile page
Route::get('/teachers/{id}', [TeacherProfileController::class, 'show']);

// --- Protected Teacher Routes (Requires Authentication) ---
// These routes are for the logged-in teacher to manage their *own* profile.

Route::middleware(['auth:sanctum', 'role:teacher'])->group(function () {

    // 4. Get the *currently logged-in* teacher's profile
    Route::get('/teacher/profile', [TeacherProfileController::class, 'edit']);

    // 5. Update the *currently logged-in* teacher's profile (bio, availability, etc.)
    Route::put('/teacher/profile', [TeacherProfileController::class, 'update']);

    // 6. Get the *currently logged-in* teacher's bookings
//    Route::get('/teacher/bookings', [TeacherBookingController::class, 'index']);
});

// --- Public Student/Parent Routes ---

// 1. Register a new Student
Route::post('/students/register', [StudentAuthController::class, 'register'])
    ->name('students.register'); // <-- ADD THIS

// 2. Register a new Parent
Route::post('/parents/register', [StudentAuthController::class, 'register'])
    ->name('parents.register'); // <-- ADD THIS

// --- Universal Login & Logout Routes ---
Route::post('/login', [AuthController::class, 'login']);

//It's part of the auth flow
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

// 1. Create a Booking (Student Only)
Route::middleware(['auth:sanctum', 'role:student'])
    ->post('/bookings', [BookingController::class, 'store']);

// 2. List My Bookings (Student OR Teacher)
// We check for 'auth:sanctum' but we don't restrict the role here,
// because the controller handles both roles logic.
Route::middleware(['auth:sanctum'])
    ->get('/my-bookings', [BookingController::class, 'index']);