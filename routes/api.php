<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppointmentController;

// Public Slots
Route::get('/slots', [AppointmentController::class, 'getAvailableSlots']);
// Route::get('/calendar/events', [AppointmentController::class, 'events']);

// Bot Routes
Route::post('/bot/cancel', [AppointmentController::class, 'cancelFromBot']);
Route::get('/notifications/pending', [AppointmentController::class, 'getPendingNotifications']);
Route::post('/notifications/mark-sent', [AppointmentController::class, 'markNotificationSent']);
