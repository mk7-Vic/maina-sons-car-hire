@extends('layouts.app')

@section('title', $vehicle->make . ' ' . $vehicle->model . ' | Maina & Sons')

@section('content')
    <p>
        <a href="{{ route('vehicles.index') }}">&larr; Back to Vehicles</a>
    </p>

    <div class="page-heading">
        <h1>{{ $vehicle->make }} {{ $vehicle->model }}</h1>
        <p>{{ $vehicle->category->name }} vehicle</p>
    </div>

    <section class="details-card">
        @if ($vehicle->image_path)
            <img
                class="details-image"
                src="{{ asset('storage/' . $vehicle->image_path) }}"
                alt="{{ $vehicle->make }} {{ $vehicle->model }}"
            >
        @else
            <div class="details-placeholder">
                {{ $vehicle->make }} {{ $vehicle->model }}
            </div>
        @endif

        <div class="details-grid">
            <div class="detail-item">
                <strong>Year</strong>
                {{ $vehicle->year ?? 'Not specified' }}
            </div>

            <div class="detail-item">
                <strong>Registration</strong>
                {{ $vehicle->registration_number }}
            </div>

            <div class="detail-item">
                <strong>Transmission</strong>
                {{ $vehicle->transmission ?? 'Not specified' }}
            </div>

            <div class="detail-item">
                <strong>Fuel Type</strong>
                {{ $vehicle->fuel_type ?? 'Not specified' }}
            </div>

            <div class="detail-item">
                <strong>Seats</strong>
                {{ $vehicle->seats }}
            </div>

            <div class="detail-item">
                <strong>Colour</strong>
                {{ $vehicle->color ?? 'Not specified' }}
            </div>
        </div>

        <h2>About This Vehicle</h2>
        <p>
            {{ $vehicle->description ?? 'Contact us for more information about this vehicle.' }}
        </p>

        <p class="price">
            KSh {{ number_format($vehicle->daily_rate, 2) }}
            <small>/ day</small>
        </p>
    </section>

    <section class="booking-card" id="booking">
        <div class="page-heading">
            <h2>Book This Vehicle</h2>
            <p>Complete the form below to request your booking.</p>
        </div>

        @if ($errors->any())
            <div class="form-errors">
                <strong>Please correct the following:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($branches->isEmpty())
            <p>Bookings are temporarily unavailable because no active branches are configured.</p>
        @else
            <form action="{{ route('bookings.store') }}" method="POST">
                @csrf

                <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">

                <h3>Customer Details</h3>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="customer_name">Full Name</label>
                        <input
                            type="text"
                            id="customer_name"
                            name="customer_name"
                            value="{{ old('customer_name') }}"
                            required
                            maxlength="255"
                        >
                    </div>

                    <div class="form-group">
                        <label for="customer_email">Email Address</label>
                        <input
                            type="email"
                            id="customer_email"
                            name="customer_email"
                            value="{{ old('customer_email') }}"
                            required
                            maxlength="255"
                        >
                    </div>

                    <div class="form-group">
                        <label for="customer_phone">Phone Number</label>
                        <input
                            type="tel"
                            id="customer_phone"
                            name="customer_phone"
                            value="{{ old('customer_phone') }}"
                            required
                            maxlength="30"
                        >
                    </div>
                </div>

                <h3>Rental Details</h3>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="pickup_branch_id">Pickup Branch</label>
                        <select id="pickup_branch_id" name="pickup_branch_id" required>
                            <option value="">Select pickup branch</option>
                            @foreach ($branches as $branch)
                                <option
                                    value="{{ $branch->id }}"
                                    @selected(old('pickup_branch_id') == $branch->id)
                                >
                                    {{ $branch->name }} — {{ $branch->location }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="return_branch_id">Return Branch</label>
                        <select id="return_branch_id" name="return_branch_id" required>
                            <option value="">Select return branch</option>
                            @foreach ($branches as $branch)
                                <option
                                    value="{{ $branch->id }}"
                                    @selected(old('return_branch_id') == $branch->id)
                                >
                                    {{ $branch->name }} — {{ $branch->location }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="pickup_at">Pickup Date and Time</label>
                        <input
                            type="datetime-local"
                            id="pickup_at"
                            name="pickup_at"
                            value="{{ old('pickup_at') }}"
                            min="{{ now()->format('Y-m-d\TH:i') }}"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="return_at">Return Date and Time</label>
                        <input
                            type="datetime-local"
                            id="return_at"
                            name="return_at"
                            value="{{ old('return_at') }}"
                            required
                        >
                    </div>

                    <div class="form-group full-width">
                        <label for="notes">Additional Notes (Optional)</label>
                        <textarea
                            id="notes"
                            name="notes"
                            rows="4"
                            maxlength="1000"
                        >{{ old('notes') }}</textarea>
                    </div>
                </div>

                <button type="submit" class="btn">Submit Booking Request</button>
            </form>
        @endif
    </section>
@endsection
