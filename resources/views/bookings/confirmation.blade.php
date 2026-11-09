
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation | Maina & Sons</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 40px 20px;
            background: #f4f6f9;
            color: #1c2541;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 680px;
            margin: 0 auto;
            padding: 32px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        }

        .success {
            color: #198754;
            font-weight: bold;
        }

        h1 {
            margin-bottom: 8px;
        }

        .reference {
            margin: 24px 0;
            padding: 16px;
            background: #f4f6f9;
            border-radius: 8px;
        }

        .details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 24px;
        }

        .detail {
            padding-bottom: 12px;
            border-bottom: 1px solid #eee;
        }

        .detail strong {
            display: block;
            margin-bottom: 6px;
        }

        .button {
            display: inline-block;
            margin-top: 28px;
            padding: 12px 20px;
            background: #1c2541;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }

        @media (max-width: 520px) {
            .details {
                grid-template-columns: 1fr;
            }

            .container {
                padding: 22px;
            }
        }
    </style>
</head>
<body>
    <main class="container">
        <p class="success">Booking request received</p>

        <h1>Thank you for choosing Maina & Sons</h1>

        <p>
            Your booking request has been submitted successfully.
            Please keep your booking reference for future enquiries.
        </p>

        <div class="reference">
            <strong>Booking reference</strong>
            <h2>{{ $booking->booking_reference }}</h2>
            <span>Status: {{ ucfirst($booking->status) }}</span>
        </div>

        <h2>Booking details</h2>

        <div class="details">
            <div class="detail">
                <strong>Vehicle</strong>
                {{ $booking->vehicle->make }}
                {{ $booking->vehicle->model }}
            </div>

            <div class="detail">
                <strong>Registration</strong>
                {{ $booking->vehicle->registration_number }}
            </div>

            <div class="detail">
                <strong>Pickup branch</strong>
                {{ $booking->pickupBranch->name }}
            </div>

            <div class="detail">
                <strong>Return branch</strong>
                {{ $booking->returnBranch->name }}
            </div>

            <div class="detail">
                <strong>Pickup date</strong>
                {{ $booking->pickup_at->format('d M Y, H:i') }}
            </div>

            <div class="detail">
                <strong>Return date</strong>
                {{ $booking->return_at->format('d M Y, H:i') }}
            </div>

            <div class="detail">
                <strong>Rental duration</strong>
                {{ $booking->rental_days }}
                {{ $booking->rental_days === 1 ? 'day' : 'days' }}
            </div>

            <div class="detail">
                <strong>Total amount</strong>
                KSh {{ number_format((float) $booking->total_amount, 2) }}
            </div>
        </div>

        @php
            $paidAmount = $booking->payments()->where('status', 'completed')->sum('amount');
            $outstanding = max(0, (float) $booking->total_amount - (float) $paidAmount);
        @endphp

        <div class="reference">
            <strong>Payment status</strong>
            <p>Paid: KSh {{ number_format((float) $paidAmount, 2) }}</p>
            <p>Outstanding: KSh {{ number_format($outstanding, 2) }}</p>

            @foreach ($booking->payments()->latest()->get() as $payment)
                <p>
                    {{ strtoupper($payment->payment_method) }}:
                    {{ ucfirst($payment->status) }}
                    @if ($payment->transaction_reference)
                        — {{ $payment->transaction_reference }}
                    @endif
                </p>
            @endforeach
        </div>

        @if ($outstanding >= 1)
            <h2>Pay with M-Pesa</h2>
            <p>You'll receive a payment prompt on your phone.</p>

            <form method="POST" action="{{ route('bookings.mpesa', $booking->booking_reference) }}">
                @csrf
                <label for="phone_number">M-Pesa phone number</label>
                <input
                    id="phone_number"
                    name="phone_number"
                    type="tel"
                    value="{{ old('phone_number', $booking->customer_phone) }}"
                    required
                    maxlength="30"
                    style="display:block;width:100%;padding:12px;margin:10px 0;"
                >
                @error('phone_number')
                    <p style="color:#b02a37;">{{ $message }}</p>
                @enderror
                <button class="button" type="submit">Pay KSh {{ number_format($outstanding, 2) }} with M-Pesa</button>
            </form>
        @else
            <p class="success">Your booking has been fully paid.</p>
        @endif

        @if (session('success'))
            <p class="success">{{ session('success') }}</p>
        @endif

        @if (session('error'))
            <p style="color:#b02a37;">{{ session('error') }}</p>
        @endif

        <a href="{{ url('/') }}" class="button">Return to homepage</a>
    </main>
</body>
</html>
