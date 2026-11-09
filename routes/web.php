<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/bookings', [BookingController::class, 'store'])
    ->name('bookings.store');

Route::get('/bookings/{reference}/confirmation', [
    BookingController::class,
    'confirmation',
])->name('bookings.confirmation');

Route::get('/vehicles', [VehicleController::class, 'index'])
    ->name('vehicles.index');

Route::get('/vehicles/{vehicle}', [VehicleController::class, 'show'])
    ->name('vehicles.show');
