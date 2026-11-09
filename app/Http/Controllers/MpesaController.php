<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\MpesaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class MpesaController extends Controller
{
    public function initiate(
        Request $request,
        string $reference,
        MpesaService $mpesa
    ): RedirectResponse {
        $booking = Booking::where('booking_reference', $reference)->firstOrFail();

        $validated = $request->validate([
            'phone_number' => ['required', 'string', 'max:30'],
        ]);

        try {
            $phone = $mpesa->normalizePhone($validated['phone_number']);
        } catch (Throwable $exception) {
            return back()->withErrors([
                'phone_number' => $exception->getMessage(),
            ]);
        }

        $payment = DB::transaction(function () use ($booking, $phone) {
            $lockedBooking = Booking::query()
                ->lockForUpdate()
                ->findOrFail($booking->id);

            $paid = $lockedBooking->payments()
                ->where('status', 'completed')
                ->sum('amount');

            $outstanding = round(
                (float) $lockedBooking->total_amount - (float) $paid,
                2
            );

            if ($outstanding < 1) {
                return null;
            }

            $recentPending = $lockedBooking->payments()
                ->where('payment_method', 'mpesa')
                ->where('status', 'pending')
                ->where('created_at', '>=', now()->subMinutes(5))
                ->exists();

            if ($recentPending) {
                return null;
            }

            return $lockedBooking->payments()->create([
                'amount' => $outstanding,
                'payment_method' => 'mpesa',
                'phone_number' => $phone,
                'status' => 'pending',
            ]);
        });

        if (! $payment) {
            return back()->with(
                'error',
                'There is no outstanding balance, or a recent M-Pesa request is still pending.'
            );
        }

        try {
            $gatewayResponse = $mpesa->stkPush(
                $phone,
                (int) round((float) $payment->amount),
                $booking->booking_reference
            );

            $payment->update([
                'merchant_request_id' => $gatewayResponse['MerchantRequestID'] ?? null,
                'checkout_request_id' => $gatewayResponse['CheckoutRequestID'],
                'gateway_response' => $gatewayResponse,
                'result_description' => $gatewayResponse['ResponseDescription'] ?? null,
            ]);
        } catch (Throwable $exception) {
            $payment->update([
                'status' => 'failed',
                'result_description' => $exception->getMessage(),
            ]);

            Log::error('M-Pesa STK Push failed', [
                'payment_id' => $payment->id,
                'error' => $exception->getMessage(),
            ]);

            return back()->with(
                'error',
                'We could not start the M-Pesa request. Please try again later.'
            );
        }

        return back()->with(
            'success',
            'M-Pesa prompt sent. Complete the payment on your phone, then refresh this page.'
        );
    }

    public function callback(Request $request): JsonResponse
    {
        $callback = $request->input('Body.stkCallback');

        if (! is_array($callback) || empty($callback['CheckoutRequestID'])) {
            Log::warning('Received malformed M-Pesa callback.');

            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        }

        try {
            DB::transaction(function () use ($callback) {
                $payment = Payment::query()
                    ->where('checkout_request_id', $callback['CheckoutRequestID'])
                    ->lockForUpdate()
                    ->first();

                if (! $payment || $payment->status !== 'pending') {
                    return;
                }

                $booking = Booking::query()
                    ->lockForUpdate()
                    ->findOrFail($payment->booking_id);

                $resultCode = (int) ($callback['ResultCode'] ?? -1);
                $items = data_get($callback, 'CallbackMetadata.Item', []);
                $metadata = collect($items)->keyBy('Name');

                $payment->result_code = $resultCode;
                $payment->result_description = $callback['ResultDesc'] ?? null;
                $payment->gateway_response = $callback;

                if ($resultCode !== 0) {
                    $payment->status = 'failed';
                    $payment->save();

                    AuditLog::create([
                        'action' => 'payment.mpesa_failed',
                        'auditable_type' => Payment::class,
                        'auditable_id' => $payment->id,
                        'description' => "M-Pesa payment failed for booking {$booking->booking_reference}.",
                    ]);

                    return;
                }

                $receipt = $metadata->get('MpesaReceiptNumber')['Value'] ?? null;
                $amount = $metadata->get('Amount')['Value'] ?? null;

                if (
                    ! $receipt ||
                    ! is_numeric($amount) ||
                    round((float) $amount, 2) !== round((float) $payment->amount, 2) ||
                    Payment::where('transaction_reference', $receipt)->exists()
                ) {
                    Log::warning('M-Pesa callback failed payment validation', [
                        'payment_id' => $payment->id,
                        'checkout_request_id' => $payment->checkout_request_id,
                    ]);

                    $payment->result_description = 'Callback amount or receipt validation failed.';
                    $payment->save();

                    return;
                }

                $otherPaid = $booking->payments()
                    ->where('status', 'completed')
                    ->where('id', '!=', $payment->id)
                    ->sum('amount');

                if ((float) $otherPaid + (float) $payment->amount
                    > (float) $booking->total_amount) {
                    $payment->result_description = 'Payment would exceed the booking balance.';
                    $payment->save();

                    return;
                }

                $payment->update([
                    'status' => 'completed',
                    'transaction_reference' => $receipt,
                    'paid_at' => now(),
                    'phone_number' => $metadata->get('PhoneNumber')['Value'] ?? $payment->phone_number,
                ]);

                AuditLog::create([
                    'action' => 'payment.mpesa_completed',
                    'auditable_type' => Payment::class,
                    'auditable_id' => $payment->id,
                    'description' => "M-Pesa payment {$receipt} completed for booking {$booking->booking_reference}.",
                ]);
            });
        } catch (Throwable $exception) {
            Log::error('M-Pesa callback processing failed', [
                'error' => $exception->getMessage(),
            ]);
        }

        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Accepted',
        ]);
    }
}
