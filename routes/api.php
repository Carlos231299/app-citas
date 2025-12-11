<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppointmentController;

// Public Slots
Route::get('/slots', [AppointmentController::class, 'getAvailableSlots']);
Route::get('/calendar/events', [AppointmentController::class, 'events']);

// Admin Actions (could be protected via Sanctum, but using web session auth for simplicity in shared domain if needed, or simple protection)
// For this simple monolithic setup, we'll keep these here to be called by AXIOS from dashboard.
Route::patch('/appointments/{appointment}/complete', [AppointmentController::class, 'complete']);
Route::patch('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel']);
