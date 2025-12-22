<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\BarberController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;

// ...

// Public Route (Redirect to Login or Landing)
Route::get('/', [AppointmentController::class, 'publicIndex'])->name('welcome');
Route::post('/book', [AppointmentController::class, 'store'])->name('book');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    // Password Recovery (Link based)
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
    
    // 2FA Verification
    Route::get('2fa', [App\Http\Controllers\TwoFactorController::class, 'index'])->name('2fa.index');
    Route::post('2fa', [App\Http\Controllers\TwoFactorController::class, 'verify'])->name('2fa.verify');
    Route::post('2fa/resend', [App\Http\Controllers\TwoFactorController::class, 'resend'])->name('2fa.resend');
});

// Admin Protected
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/update-status', [ProfileController::class, 'updateStatus'])->name('profile.updateStatus');
    Route::post('/notifications/mark-read', [App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.markRead');
    Route::post('/notifications/{id}/mark-read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');

    Route::get('/dashboard', [AppointmentController::class, 'index'])->name('dashboard');
    Route::get('/calendar', [AppointmentController::class, 'calendar'])->name('calendar');
    Route::get('/calendar/events', [AppointmentController::class, 'events'])->name('calendar.events');
    Route::get('/appointments/{appointment}', [AppointmentController::class, 'show'])->name('appointments.show');
    Route::put('/appointments/{appointment}', [AppointmentController::class, 'update'])->name('appointments.update');
    Route::patch('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::patch('/appointments/{appointment}/confirm', [AppointmentController::class, 'confirm'])->name('appointments.confirm');
    Route::patch('/appointments/{appointment}/complete', [AppointmentController::class, 'complete'])->name('appointments.complete');
    Route::patch('/appointments/{appointment}/complete', [AppointmentController::class, 'complete'])->name('appointments.complete');
    Route::patch('/appointments/{appointment}/reopen', [AppointmentController::class, 'reopen'])->name('appointments.reopen');
Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');
    Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');
    Route::resource('services', ServiceController::class);
    Route::resource('barbers', BarberController::class);
    Route::resource('products', App\Http\Controllers\ProductController::class);
    Route::resource('categories', App\Http\Controllers\CategoryController::class);
    
    // POS
    Route::get('/pos', [App\Http\Controllers\PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/store', [App\Http\Controllers\PosController::class, 'store'])->name('pos.store');
    Route::get('/pos/history', [App\Http\Controllers\PosController::class, 'history'])->name('pos.history');
    Route::get('/pos/history/export-pdf', [App\Http\Controllers\PosController::class, 'exportPdf'])->name('pos.history.pdf');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/pdf', [ReportController::class, 'pdf'])->name('reports.pdf');
});
