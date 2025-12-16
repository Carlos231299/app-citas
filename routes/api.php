<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppointmentController;

// Public Slots
Route::get('/slots', [AppointmentController::class, 'getAvailableSlots']);
// Route::get('/calendar/events', [AppointmentController::class, 'events']);

// Routes moved to web.php for auth consistency
