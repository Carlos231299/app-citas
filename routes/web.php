<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\BarberController;
use App\Http\Controllers\ProfileController;

// ...

// Public Route (Redirect to Login or Landing)
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    
    // Password Recovery
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetCode'])->name('password.email');
    
    Route::get('/verify-code', [AuthController::class, 'showVerifyCode'])->name('password.verify.show');
    Route::post('/verify-code', [AuthController::class, 'verifyCode'])->name('password.verify');
    
    // Reset Password Action (View rendered by verifyCode)
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// Admin Protected
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/dashboard', [AppointmentController::class, 'index'])->name('dashboard');
    Route::get('/calendar', [AppointmentController::class, 'calendar'])->name('calendar');
    Route::resource('services', ServiceController::class);
    Route::resource('barbers', BarberController::class);
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/pdf', [ReportController::class, 'pdf'])->name('reports.pdf');
});
