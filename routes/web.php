<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Public booking routes
Route::post('/bookings', [BookingController::class, 'store'])
    ->name('bookings.store');

Route::get('/bookings/{reference}/confirmation', [
    BookingController::class,
    'confirmation',
])->name('bookings.confirmation');

// Public vehicle routes
Route::get('/vehicles', [VehicleController::class, 'index'])
    ->name('vehicles.index');

Route::get('/vehicles/{vehicle}', [VehicleController::class, 'show'])
    ->name('vehicles.show');

// Admin routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'store'])
        ->name('login.store');

    Route::middleware('admin')->group(function () {
        Route::post('/logout', [AuthController::class, 'destroy'])
            ->name('logout');

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/bookings', [AdminBookingController::class, 'index'])
            ->name('bookings.index');

        Route::get('/bookings/{booking}', [AdminBookingController::class, 'show'])
            ->name('bookings.show');

        Route::patch('/bookings/{booking}/status', [
            AdminBookingController::class,
            'updateStatus',
        ])->name('bookings.status');
    });
});