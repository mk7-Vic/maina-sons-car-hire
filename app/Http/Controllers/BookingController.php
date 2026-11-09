<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function store(
        StoreBookingRequest $request,
        BookingService $bookingService
    ): RedirectResponse {
        $booking = $bookingService->create(
            $request->validated(),
            $request->user()
        );

        return redirect()->route('bookings.confirmation', [
            'reference' => $booking->booking_reference,
        ]);
    }

    public function confirmation(string $reference): View
    {
        $booking = Booking::where(
            'booking_reference',
            $reference
        )->firstOrFail();

        $booking->load(['vehicle', 'pickupBranch', 'returnBranch']);

        return view('bookings.confirmation', compact('booking'));
    }
}
