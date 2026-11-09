@extends('layouts.app')

@section('title', 'Admin Dashboard | Maina & Sons')

@section('content')
    <div class="page-heading">
        <h1>Admin Dashboard</h1>
        <p>Welcome, {{ auth()->user()->name }}. Manage your car hire operations.</p>
    </div>

    <section class="vehicle-grid">
        <article class="vehicle-card">
            <div class="vehicle-info">
                <div class="category">Total Bookings</div>
                <h2>{{ $totalBookings }}</h2>
            </div>
        </article>

        <article class="vehicle-card">
            <div class="vehicle-info">
                <div class="category">Pending Bookings</div>
                <h2>{{ $pendingBookings }}</h2>
            </div>
        </article>

        <article class="vehicle-card">
            <div class="vehicle-info">
                <div class="category">Confirmed Bookings</div>
                <h2>{{ $confirmedBookings }}</h2>
            </div>
        </article>

        <article class="vehicle-card">
            <div class="vehicle-info">
                <div class="category">Total Vehicles</div>
                <h2>{{ $totalVehicles }}</h2>
            </div>
        </article>

        <article class="vehicle-card">
            <div class="vehicle-info">
                <div class="category">Available Vehicles</div>
                <h2>{{ $availableVehicles }}</h2>
            </div>
        </article>
    </section>

    <section class="booking-card">
        <h2>Administration</h2>
        <p>You are logged in with administrator privileges.</p>

        <a href="{{ route('admin.bookings.index') }}" class="btn">
            Manage Bookings
        </a>

        <a href="{{ route('admin.vehicles.index') }}" class="btn">
            Manage Vehicles
        </a>

        <a href="{{ route('admin.categories.index') }}" class="btn">
            Manage Vehicle Categories
        </a>

        <a href="{{ route('admin.audit-logs.index') }}" class="btn">
            View Audit Logs
        </a>

        <a href="{{ route('admin.branches.index') }}" class="btn">
            Manage Branches
        </a>

        <a href="{{ route('admin.enquiries.index') }}" class="btn">
            Manage Enquiries
        </a>

        <a href="{{ route('admin.payments.index') }}" class="btn">
            Manage Payments
        </a>

        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="btn">Log Out</button>
        </form>
    </section>
@endsection
