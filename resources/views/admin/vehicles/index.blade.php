@extends('layouts.app')

@section('title', 'Manage Vehicles | Maina & Sons')

@push('styles')
<style>
    .admin-vehicle-list { display: grid; gap: 18px; }

    .admin-vehicle-card {
        display: grid;
        grid-template-columns: 190px minmax(0, 1fr);
        gap: 22px;
        align-items: center;
        padding: 18px;
        margin: 0;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 5px 22px rgba(0,0,0,.07);
    }

    .admin-vehicle-photo, .admin-vehicle-no-photo {
        width: 100%;
        height: 135px;
        border-radius: 9px;
        overflow: hidden;
    }

    .admin-vehicle-photo {
        display: block;
        object-fit: cover;
        background: #eef1f5;
    }

    .admin-vehicle-no-photo {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px;
        color: #707887;
        background: #f1f3f6;
        border: 1px dashed #b8c0cc;
        text-align: center;
        font-size: .88rem;
    }

    .admin-vehicle-no-photo i { font-size: 1.8rem; }
    .admin-vehicle-details h2 {
        margin-bottom: 10px;
        color: var(--navy);
        font-size: 1.15rem;
    }
    .admin-vehicle-details p {
        margin-bottom: 5px;
        color: var(--muted);
        font-size: .92rem;
    }
    .admin-vehicle-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 9px;
        margin-top: 14px;
    }
    .admin-vehicle-actions form { margin: 0; }
    .admin-vehicle-actions .btn {
        padding: 7px 13px;
        font-size: .88rem;
    }
    .admin-vehicle-pagination {
        display: flex;
        justify-content: center;
        margin-top: 24px;
    }
    .admin-vehicle-pagination .pagination {
        flex-wrap: wrap;
        gap: 4px;
        margin-bottom: 0;
    }

    @media (max-width: 600px) {
        .admin-vehicle-card { grid-template-columns: 1fr; gap: 14px; }
        .admin-vehicle-photo, .admin-vehicle-no-photo { height: 190px; }
    }
</style>
@endpush

@section('content')
    <div class="page-heading">
        <h1>Manage Vehicles</h1>
        <p>View, search and manage the company fleet. Vehicle photos can be added or replaced from each vehicle's edit page.</p>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Dashboard</a>
            <a href="{{ route('admin.vehicles.create') }}" class="btn">Add Vehicle</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
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
        <div class="d-flex flex-wrap gap-2 mt-3">
            <button class="btn" type="submit">Filter Vehicles</button>
            <a href="{{ route('admin.vehicles.index') }}" class="btn btn-outline-secondary">Clear</a>
        </div>
    </form>

    <div class="admin-vehicle-list mt-4">
        @forelse ($vehicles as $vehicle)
            <article class="admin-vehicle-card">
                <div>
                    @if ($vehicle->image_path)
                        <img
                            class="admin-vehicle-photo"
                            src="{{ asset('storage/'.$vehicle->image_path) }}"
                            alt="{{ $vehicle->make }} {{ $vehicle->model }}"
                            loading="lazy"
                        >
                    @else
                        <div class="admin-vehicle-no-photo">
                            <i class="bi bi-camera"></i>
                            <span>No photo uploaded</span>
                        </div>
                    @endif
                </div>

                <div class="admin-vehicle-details">
                    <h2>{{ $vehicle->make }} {{ $vehicle->model }}</h2>
                    <p><strong>Registration:</strong> {{ $vehicle->registration_number }}</p>
                    <p><strong>Category:</strong> {{ $vehicle->category?->name ?? 'Uncategorised' }}</p>
                    <p><strong>Daily rate:</strong> KSh {{ number_format((float) $vehicle->daily_rate, 2) }}</p>
                    <p><strong>Status:</strong> {{ ucfirst($vehicle->status) }}</p>

                    <div class="admin-vehicle-actions">
                        <a href="{{ route('admin.vehicles.edit', $vehicle) }}" class="btn">Edit / Update Photo</a>
                        <form method="POST" action="{{ route('admin.vehicles.destroy', $vehicle) }}"
                            onsubmit="return confirm('Delete this vehicle? This cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-outline-danger" type="submit">Delete</button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="empty-state">No vehicles found.</div>
        @endforelse
    </div>

    <div class="admin-vehicle-pagination">
        {{ $vehicles->links() }}
    </div>
@endsection
