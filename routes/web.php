<?php

use Illuminate\Support\Facades\Route;

// Define the "login" route name so Laravel knows where to redirect
Route::get('/login', function () {
    return view('welcome');
})->name('login');

// Existing catch-all route (Keep this at the bottom)
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');