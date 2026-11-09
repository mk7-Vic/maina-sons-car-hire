
@extends('layouts.app')

@section('title', 'Booking Details | Maina & Sons')

@section('content')
    <div class="page-heading">
        <h1>Booking Details</h1>
        <p>Reference: {{ $booking->booking_reference }}</p>
    </div>

    <section class="booking-card">
        @if (session('success'))
            <p>{{ session('success') }}</p>
        @endif

        @if ($errors->any())
            <div role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <h2>Customer Information</h2>
        <p><strong>Name:</strong> {{ $booking->customer_name }}</p>
        <p><strong>Email:</strong> {{ $booking->customer_email }}</p>
        <p><strong>Phone:</strong> {{ $booking->customer_phone }}</p>
        <p>
            <strong>Account:</strong>
            {{ $booking->user ? $booking->user->name : 'Guest booking' }}
        </p>

        <hr>

        <h2>Vehicle Information</h2>
        <p>
            <strong>Vehicle:</strong>
            {{ $booking->vehicle->make }} {{ $booking->vehicle->model }}
        </p>
        <p>
            <strong>Registration:</strong>
            {{ $booking->vehicle->registration_number }}
        </p>

        <hr>

        <h2>Rental Information</h2>
        <p><strong>Pickup branch:</strong> {{ $booking->pickupBranch->name }}</p>
        <p><strong>Return branch:</strong> {{ $booking->returnBranch->name }}</p>
        <p><strong>Pickup:</strong> {{ $booking->pickup_at->format('d M Y, H:i') }}</p>
        <p><strong>Return:</strong> {{ $booking->return_at->format('d M Y, H:i') }}</p>
        <p><strong>Rental days:</strong> {{ $booking->rental_days }}</p>

        <hr>

        <h2>Payment Summary</h2>
        <p>
            <strong>Daily rate:</strong>
            KSh {{ number_format((float) $booking->daily_rate, 2) }}
        </p>
        <p>
            <strong>Total amount:</strong>
            KSh {{ number_format((float) $booking->total_amount, 2) }}
        </p>
        <p><strong>Booking status:</strong> {{ ucfirst($booking->status) }}</p>

        @if ($booking->notes)
            <p><strong>Customer notes:</strong> {{ $booking->notes }}</p>
        @endif

        @if ($booking->payments->isNotEmpty())
            <h3>Payment Records</h3>

            @foreach ($booking->payments as $payment)
                <p>
                    KSh {{ number_format((float) $payment->amount, 2) }}
                    — {{ ucfirst($payment->status) }}
                </p>
            @endforeach
        @else
            <p>No payment records have been added.</p>
        @endif

        <hr>

        <h2>Manage Booking Status</h2>

        @if ($booking->status === 'pending')
            <form method="POST" action="{{ route('admin.bookings.status', $booking) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="confirmed">
                <button type="submit" class="btn">Confirm Booking</button>
            </form>

            <form method="POST" action="{{ route('admin.bookings.status', $booking) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="cancelled">
                <button type="submit" class="btn">Cancel Booking</button>
            </form>

        @elseif ($booking->status === 'confirmed')
            <form method="POST" action="{{ route('admin.bookings.status', $booking) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="completed">
                <button type="submit" class="btn">Mark Completed</button>
            </form>

            <form method="POST" action="{{ route('admin.bookings.status', $booking) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="cancelled">
                <button type="submit" class="btn">Cancel Booking</button>
            </form>

        @else
            <p>This booking is {{ $booking->status }}. No further status changes are allowed.</p>
        @endif

        <a href="{{ route('admin.bookings.index') }}" class="btn">
            Back to Bookings
        </a>
    </section>
@endsection
