<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:pending,completed,failed,refunded'],
        ]);

        $query = Payment::with('booking');

        $query->when($validated['search'] ?? null, function ($query, $search) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('transaction_reference', 'like', "%{$search}%")
                    ->orWhereHas('booking', function ($bookingQuery) use ($search) {
                        $bookingQuery->where('booking_reference', 'like', "%{$search}%")
                            ->orWhere('customer_name', 'like', "%{$search}%");
                    });
            });
        });

        $query->when($validated['status'] ?? null, function ($query, $status) {
            $query->where('status', $status);
        });

        $payments = $query->latest()->paginate(15)->withQueryString();

        return view('admin.payments.index', compact('payments'));
    }

    public function store(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
            'payment_method' => ['required', 'in:cash,card,mpesa,bank_transfer'],
            'transaction_reference' => ['nullable', 'string', 'max:255', 'unique:payments,transaction_reference'],
        ]);

        DB::transaction(function () use ($request, $booking, $validated) {
            $lockedBooking = Booking::query()
                ->lockForUpdate()
                ->findOrFail($booking->id);

            $paid = $lockedBooking->payments()
                ->where('status', 'completed')
                ->sum('amount');

            $outstanding = (float) $lockedBooking->total_amount - (float) $paid;

            if ((float) $validated['amount'] > $outstanding) {
                throw ValidationException::withMessages([
                    'amount' => 'The payment cannot exceed the outstanding booking balance.',
                ]);
            }

            $payment = $lockedBooking->payments()->create([
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'transaction_reference' => $validated['transaction_reference'] ?? null,
                'status' => 'pending',
            ]);

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'payment.created',
                'auditable_type' => $payment->getMorphClass(),
                'auditable_id' => $payment->id,
                'description' => "Payment of KSh {$payment->amount} recorded for booking {$lockedBooking->booking_reference}.",
                'ip_address' => $request->ip(),
            ]);
        });

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('success', 'Payment recorded successfully.');
    }

    public function updateStatus(Request $request, Payment $payment): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:completed,failed,refunded'],
        ]);

        DB::transaction(function () use ($request, $payment, $validated) {
            $lockedPayment = Payment::query()
                ->lockForUpdate()
                ->findOrFail($payment->id);

            $oldStatus = $lockedPayment->status;
            $newStatus = $validated['status'];

            $transitions = [
                'pending' => ['completed', 'failed'],
                'completed' => ['refunded'],
                'failed' => [],
                'refunded' => [],
            ];

            if (! in_array($newStatus, $transitions[$oldStatus] ?? [], true)) {
                throw ValidationException::withMessages([
                    'status' => 'This payment status transition is not allowed.',
                ]);
            }

            if ($newStatus === 'completed') {
                $lockedBooking = Booking::query()
                    ->lockForUpdate()
                    ->findOrFail($lockedPayment->booking_id);

                $otherPaid = $lockedBooking->payments()
                    ->where('status', 'completed')
                    ->where('id', '!=', $lockedPayment->id)
                    ->sum('amount');

                if ((float) $otherPaid + (float) $lockedPayment->amount
                    > (float) $lockedBooking->total_amount) {
                    throw ValidationException::withMessages([
                        'status' => 'Completing this payment would exceed the booking total.',
                    ]);
                }
            }

            $lockedPayment->update([
                'status' => $newStatus,
                'paid_at' => $newStatus === 'completed'
                    ? now()
                    : $lockedPayment->paid_at,
            ]);

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'payment.status_changed',
                'auditable_type' => $lockedPayment->getMorphClass(),
                'auditable_id' => $lockedPayment->id,
                'description' => "Payment #{$lockedPayment->id} status changed from {$oldStatus} to {$newStatus}.",
                'ip_address' => $request->ip(),
            ]);
        });

        return redirect()
            ->route('admin.payments.index')
            ->with('success', 'Payment status updated successfully.');
    }
}
