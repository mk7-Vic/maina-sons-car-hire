@extends('layouts.app')

@section('title', 'Manage Bookings | Maina & Sons')

@section('content')
    <div class="page-heading">
        <h1>Manage Bookings</h1>
        <p>View and monitor customer reservations.</p>
    </div>

    <section class="booking-card">
        <h2>All Bookings</h2>

        <form method="GET"
            action="{{ route('admin.bookings.index') }}"
            style="display: flex; gap: 12px; flex-wrap: wrap; margin-top: 20px;">

            <input
                type="search"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search reference, customer or email..."
                style="flex: 1; min-width: 220px; padding: 12px; border: 1px solid #ddd; border-radius: 6px;">

            <select
                name="status"
                style="padding: 12px; border: 1px solid #ddd; border-radius: 6px;">
                <option value="">All statuses</option>
                <option value="pending" @selected(request('status') === 'pending')>
                    Pending
                </option>
                <option value="confirmed" @selected(request('status') === 'confirmed')>
                    Confirmed
                </option>
                <option value="completed" @selected(request('status') === 'completed')>
                    Completed
                </option>
                <option value="cancelled" @selected(request('status') === 'cancelled')>
                    Cancelled
                </option>
            </select>

            <button type="submit" class="btn">
                Search
            </button>

            <a href="{{ route('admin.bookings.index') }}" class="btn">
                Clear
            </a>
        </form>

        @if ($bookings->isEmpty())
            <div class="empty-state">
                <h3>No bookings found</h3>
                <p>Customer reservations will appear here.</p>
            </div>
        @else
            <div style="overflow-x: auto; margin-top: 20px;">
                <table style="width: 100%; border-collapse: collapse; min-width: 850px;">
                    <thead>
                        <tr style="text-align: left; background: var(--light);">
                            <th style="padding: 14px;">Reference</th>
                            <th style="padding: 14px;">Customer</th>
                            <th style="padding: 14px;">Vehicle</th>
                            <th style="padding: 14px;">Pickup</th>
                            <th style="padding: 14px;">Return</th>
                            <th style="padding: 14px;">Total</th>
                            <th style="padding: 14px;">Status</th>
                            <th style="padding: 14px;">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($bookings as $booking)
                            <tr style="border-bottom: 1px solid #e5e7eb;">
                                <td style="padding: 14px;">
                                    {{ $booking->booking_reference }}
                                </td>
                                <td style="padding: 14px;">
                                    {{ $booking->customer_name }}
                                    <br>
                                    <small>{{ $booking->customer_email }}</small>
                                </td>
                                <td style="padding: 14px;">
                                    {{ $booking->vehicle->make }}
                                    {{ $booking->vehicle->model }}
                                    <br>
                                    <small>{{ $booking->vehicle->registration_number }}</small>
                                </td>
                                <td style="padding: 14px;">
                                    {{ $booking->pickupBranch->name }}
                                    <br>
                                    <small>{{ $booking->pickup_at->format('d M Y, H:i') }}</small>
                                </td>
                                <td style="padding: 14px;">
                                    {{ $booking->returnBranch->name }}
                                    <br>
                                    <small>{{ $booking->return_at->format('d M Y, H:i') }}</small>
                                </td>
                                <td style="padding: 14px;">
                                    KSh {{ number_format((float) $booking->total_amount, 2) }}
                                </td>
                                <td style="padding: 14px;">
                                    {{ ucfirst($booking->status) }}
                                </td>
                                <td style="padding: 14px;">
                                    <a href="{{ route('admin.bookings.show', $booking) }}">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 25px;">
                {{ $bookings->links() }}
            </div>
        @endif

        <a href="{{ route('admin.dashboard') }}" class="btn">
            Back to Dashboard
        </a>
    </section>
@endsection
