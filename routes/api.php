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
Route::get('/client-notifications/pending', [AppointmentController::class, 'getPendingClientNotifications']);
Route::post('/client-notifications/mark-sent', [AppointmentController::class, 'markClientNotificationSent']);

Route::get('/reminders/pending', [AppointmentController::class, 'getPendingReminders']);
Route::post('/reminders/mark-sent', [AppointmentController::class, 'markReminderSent']);
Route::post('/bot/confirm', [AppointmentController::class, 'confirmFromBot']);

// Ratings
use App\Http\Controllers\BotController;
Route::post('/bot/rate', [BotController::class, 'rate']);
