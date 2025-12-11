<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\BarberController;
use App\Http\Controllers\ReportController;

// Public Public
Route::get('/', [AppointmentController::class, 'publicIndex'])->name('home');
Route::post('/book', [AppointmentController::class, 'store'])->name('book');

// Guest Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Admin Protected
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    Route::get('/dashboard', [AppointmentController::class, 'index'])->name('dashboard');
    Route::get('/calendar', [AppointmentController::class, 'calendar'])->name('calendar');
    Route::resource('services', ServiceController::class);
    Route::resource('barbers', BarberController::class);
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/pdf', [ReportController::class, 'pdf'])->name('reports.pdf');
});
