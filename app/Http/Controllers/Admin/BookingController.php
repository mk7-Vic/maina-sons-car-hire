<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => [
                'nullable',
                'in:pending,confirmed,completed,cancelled',
            ],
        ]);

        $query = Booking::with([
            'vehicle',
            'pickupBranch',
            'returnBranch',
        ]);

        $query->when($validated['search'] ?? null, function ($query, $search) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('booking_reference', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%");
            });
        });

        $query->when($validated['status'] ?? null, function ($query, $status) {
            $query->where('status', $status);
        });

        $bookings = $query
            ->latest()
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.bookings.index', compact('bookings'));
    }

    public function show(Booking $booking): View
    {
        $booking->load([
            'vehicle',
            'pickupBranch',
            'returnBranch',
            'payments',
            'review',
            'user',
        ]);

        return view('admin.bookings.show', compact('booking'));
    }

    public function updateStatus(
        Request $request,
        Booking $booking
    ): RedirectResponse {
        $validated = $request->validate([
            'status' => [
                'required',
                'in:pending,confirmed,completed,cancelled',
            ],
        ]);

        DB::transaction(function () use ($request, $booking, $validated) {
            $lockedBooking = Booking::query()
                ->lockForUpdate()
                ->findOrFail($booking->id);

            $transitions = [
                'pending' => ['confirmed', 'cancelled'],
                'confirmed' => ['completed', 'cancelled'],
                'completed' => [],
                'cancelled' => [],
            ];

            $newStatus = $validated['status'];
            $oldStatus = $lockedBooking->status;

            if (! in_array($newStatus, $transitions[$oldStatus] ?? [], true)) {
                throw ValidationException::withMessages([
                    'status' => 'This booking status transition is not allowed.',
                ]);
            }

            $lockedBooking->update([
                'status' => $newStatus,
            ]);

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'booking.status_changed',
                'auditable_type' => $lockedBooking->getMorphClass(),
                'auditable_id' => $lockedBooking->id,
                'description' => "Booking {$lockedBooking->booking_reference} status changed from {$oldStatus} to {$newStatus}.",
                'ip_address' => $request->ip(),
            ]);
        });

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('success', 'Booking status updated successfully.');
    }
}
