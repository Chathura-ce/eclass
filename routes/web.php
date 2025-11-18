<?php

use Illuminate\Support\Facades\Route;

// Send ALL web requests to the 'welcome' view
// Vue Router will handle the actual navigation inside the browser
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');