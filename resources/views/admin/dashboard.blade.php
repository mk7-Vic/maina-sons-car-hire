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

        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="btn">Log Out</button>
        </form>
    </section>
@endsection
