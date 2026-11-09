@extends('layouts.app')

@section('title', 'Manage Branches | Maina & Sons')

@section('content')
    <div class="page-heading">
        <h1>Manage Branches</h1>
        <p>View, search and manage company branches.</p>

        <a href="{{ route('admin.dashboard') }}" class="btn">Dashboard</a>
        <a href="{{ route('admin.branches.create') }}" class="btn">Add Branch</a>
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
                <input
                    id="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Branch name, location, phone or email"
                >
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">All branches</option>
                    <option value="active" @selected(request('status') === 'active')>
                        Active
                    </option>
                    <option value="inactive" @selected(request('status') === 'inactive')>
                        Inactive
                    </option>
                </select>
            </div>
        </div>

        <button class="btn" type="submit">Filter Branches</button>
        <a href="{{ route('admin.branches.index') }}" class="btn">Clear</a>
    </form>

    @forelse ($branches as $branch)
        <article class="vehicle-card booking-card">
            <div class="vehicle-info">
                <h2>{{ $branch->name }}</h2>

                <p><strong>Location:</strong> {{ $branch->location }}</p>
                <p><strong>Phone:</strong> {{ $branch->phone ?: 'Not provided' }}</p>
                <p><strong>Email:</strong> {{ $branch->email ?: 'Not provided' }}</p>

                <p>
                    <strong>Status:</strong>
                    {{ $branch->is_active ? 'Active' : 'Inactive' }}
                </p>

                <p>
                    <strong>Pickup bookings:</strong>
                    {{ $branch->pickup_bookings_count }}
                </p>

                <p>
                    <strong>Return bookings:</strong>
                    {{ $branch->return_bookings_count }}
                </p>

                <a href="{{ route('admin.branches.edit', $branch) }}" class="btn">
                    Edit Branch
                </a>
            </div>
        </article>
    @empty
        <div class="empty-state">No branches found.</div>
    @endforelse

    {{ $branches->links() }}
@endsection