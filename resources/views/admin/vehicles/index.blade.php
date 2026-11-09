@extends('layouts.app')

@section('title', 'Manage Vehicles | Maina & Sons')

@section('content')
    <div class="page-heading">
        <h1>Manage Vehicles</h1>
        <p>View, search and manage the company fleet.</p>
        <a href="{{ route('admin.dashboard') }}" class="btn">Dashboard</a>
        <a href="{{ route('admin.vehicles.create') }}" class="btn">Add Vehicle</a>
    </div>

    @if (session('success'))
        <div class="booking-card">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="form-errors">{{ session('error') }}</div>
    @endif

    <form method="GET" class="booking-card">
        <div class="form-grid">
            <div class="form-group">
                <label for="search">Search</label>
                <input id="search" name="search" value="{{ request('search') }}" placeholder="Registration, make or model">
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">All statuses</option>
                    @foreach (['available', 'maintenance', 'unavailable'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <button class="btn" type="submit">Filter Vehicles</button>
        <a href="{{ route('admin.vehicles.index') }}" class="btn">Clear</a>
    </form>

    @forelse ($vehicles as $vehicle)
        <article class="vehicle-card booking-card">
            <div class="vehicle-info">
                <h2>{{ $vehicle->make }} {{ $vehicle->model }}</h2>
                <p><strong>Registration:</strong> {{ $vehicle->registration_number }}</p>
                <p><strong>Category:</strong> {{ $vehicle->category->name }}</p>
                <p><strong>Daily rate:</strong> KSh {{ number_format((float) $vehicle->daily_rate, 2) }}</p>
                <p><strong>Status:</strong> {{ ucfirst($vehicle->status) }}</p>

                <a href="{{ route('admin.vehicles.edit', $vehicle) }}" class="btn">Edit</a>

                <form method="POST" action="{{ route('admin.vehicles.destroy', $vehicle) }}"
                    onsubmit="return confirm('Delete this vehicle? This cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button class="btn" type="submit">Delete</button>
                </form>
            </div>
        </article>
    @empty
        <div class="empty-state">No vehicles found.</div>
    @endforelse

    {{ $vehicles->links() }}
@endsection
