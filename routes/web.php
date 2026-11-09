<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\BranchController as AdminBranchController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EnquiryController as AdminEnquiryController;
use App\Http\Controllers\Admin\VehicleController as AdminVehicleController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/contact', fn () => view('contact'))->name('contact');
Route::post('/enquiries', [EnquiryController::class, 'store'])->name('enquiries.store');

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

        Route::get('/enquiries', [AdminEnquiryController::class, 'index'])
            ->name('enquiries.index');
        Route::get('/enquiries/{enquiry}', [AdminEnquiryController::class, 'show'])
            ->name('enquiries.show');
        Route::patch('/enquiries/{enquiry}', [AdminEnquiryController::class, 'update'])
            ->name('enquiries.update');

        Route::get('/bookings', [AdminBookingController::class, 'index'])
            ->name('bookings.index');

        Route::get('/bookings/{booking}', [AdminBookingController::class, 'show'])
            ->name('bookings.show');

        Route::patch('/bookings/{booking}/status', [
            AdminBookingController::class,
            'updateStatus',
        ])->name('bookings.status');

        Route::get('/vehicles', [AdminVehicleController::class, 'index'])->name('vehicles.index');
        Route::get('/vehicles/create', [AdminVehicleController::class, 'create'])->name('vehicles.create');
        Route::post('/vehicles', [AdminVehicleController::class, 'store'])->name('vehicles.store');
        Route::get('/vehicles/{vehicle}/edit', [AdminVehicleController::class, 'edit'])->name('vehicles.edit');
        Route::put('/vehicles/{vehicle}', [AdminVehicleController::class, 'update'])->name('vehicles.update');
        Route::delete('/vehicles/{vehicle}', [AdminVehicleController::class, 'destroy'])->name('vehicles.destroy');

        // Branch management
        Route::get('/branches', [AdminBranchController::class, 'index'])
            ->name('branches.index');

        Route::get('/branches/create', [AdminBranchController::class, 'create'])
            ->name('branches.create');

        Route::post('/branches', [AdminBranchController::class, 'store'])
            ->name('branches.store');

        Route::get('/branches/{branch}/edit', [AdminBranchController::class, 'edit'])
            ->name('branches.edit');

        Route::put('/branches/{branch}', [AdminBranchController::class, 'update'])
            ->name('branches.update');
    });
});
