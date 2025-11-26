<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\StudentAuthController;
use App\Http\Controllers\Auth\TeacherAuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\TeacherDashboardController;
use App\Http\Controllers\TeacherProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| 1. Public / Guest Routes (No Auth Required)
|--------------------------------------------------------------------------
*/

// --- General Auth ---
Route::post('/login', [AuthController::class, 'login']);

// --- Registration ---
Route::prefix('teachers')->controller(TeacherAuthController::class)->group(function () {
    Route::post('/register', 'register');
});

Route::controller(StudentAuthController::class)->group(function () {
    Route::post('/students/register', 'register')->name('students.register');
    Route::post('/parents/register', 'register')->name('parents.register');
});

// --- Public Teacher Discovery ---
Route::controller(TeacherProfileController::class)->group(function () {
    Route::get('/teachers', 'index');      // List verified teachers
    Route::get('/teachers/{id}', 'show');  // Single public profile
});

// --- Payment Webhooks (External) ---
// No auth middleware because this comes from the PayHere server
Route::post('/payment/notify', [PaymentController::class, 'notify']);


/*
|--------------------------------------------------------------------------
| 2. Protected Routes (Requires 'auth:sanctum')
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->group(function () {

    // --- General User & Logout ---
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    // --- Shared Logic (Student & Teacher) ---
    // Both roles can view their own bookings
    Route::get('/my-bookings', [BookingController::class, 'index']);


    // --- Role: Teacher Only ---
    Route::middleware(['role:teacher'])->prefix('teacher')->controller(TeacherProfileController::class)->group(function () {
        // Routes become: /teacher/profile
        Route::get('/profile', 'edit');   // Get logged-in profile
        Route::put('/profile', 'update'); // Update logged-in profile
        // Route::get('/bookings', [TeacherBookingController::class, 'index']);
         Route::get('/dashboard', [TeacherDashboardController::class, 'index']);
    });


    // --- Role: Student Only ---
    Route::middleware(['role:student'])->group(function () {

        // Booking Actions
        Route::post('/bookings', [BookingController::class, 'store']);

        // Payment Actions
        Route::get('/payment/initiate/{bookingId}', [PaymentController::class, 'initiate']);
    });

});