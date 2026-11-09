@extends('layouts.app')

@section('title', 'My Bookings | Maina & Sons')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h2 text-navy mb-1">My Bookings</h1>
        <p class="text-muted mb-0">Welcome, {{ auth()->user()->name }}. Here is your booking history.</p>
    </div>
    <a href="{{ route('vehicles.index') }}" class="btn btn-warning">
        <i class="bi bi-car-front me-1"></i> Browse Vehicles
    </a>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if ($bookings->isEmpty())
    <div class="card border-0 shadow-sm text-center p-5">
        <i class="bi bi-calendar2-check display-4 text-muted mb-3"></i>
        <h2 class="h4">No bookings yet</h2>
        <p class="text-muted">Your bookings will appear here once you make a reservation.</p>
        <a href="{{ route('vehicles.index') }}" class="btn btn-warning align-self-center">
            Explore Our Fleet
        </a>
    </div>
@else
    <div class="table-responsive bg-white rounded shadow-sm">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Reference</th>
                    <th>Vehicle</th>
                    <th>Pickup</th>
                    <th>Return</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bookings as $booking)
                    <tr>
                        <td class="fw-semibold">{{ $booking->booking_reference }}</td>
                        <td>{{ $booking->vehicle->make }} {{ $booking->vehicle->model }}</td>
                        <td>
                            {{ $booking->pickup_at->format('d M Y') }}
                            <div class="small text-muted">{{ $booking->pickupBranch->name }}</div>
                        </td>
                        <td>
                            {{ $booking->return_at->format('d M Y') }}
                            <div class="small text-muted">{{ $booking->returnBranch->name }}</div>
                        </td>
                        <td>KSh {{ number_format((float) $booking->total_amount, 2) }}</td>
                        <td>
                            <span class="badge text-bg-{{ match($booking->status) {
                                'confirmed', 'completed' => 'success',
                                'cancelled' => 'danger',
                                default => 'warning',
                            } }}">{{ ucfirst($booking->status) }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $bookings->links() }}</div>
@endif
@endsection
