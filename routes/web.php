<?php

use App\Http\Controllers\BookingController;
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
