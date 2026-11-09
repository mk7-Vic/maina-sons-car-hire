@extends('layouts.app')

@section('title', 'Manage Enquiries | Maina & Sons')

@section('content')
    <div class="page-heading">
        <h1>Manage Enquiries</h1>
        <p>Review customer enquiries and track their progress.</p>
        <a href="{{ route('admin.dashboard') }}" class="btn">Dashboard</a>
    </div>

    @if (session('success'))
        <div class="booking-card">{{ session('success') }}</div>
    @endif

    <form method="GET" class="booking-card">
        <div class="form-grid">
            <div class="form-group">
                <label for="search">Search</label>
                <input id="search" name="search" value="{{ request('search') }}"
                    placeholder="Name, email or subject">
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>
                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <button type="submit" class="btn">Filter Enquiries</button>
        <a href="{{ route('admin.enquiries.index') }}" class="btn">Clear</a>
    </form>

    @forelse ($enquiries as $enquiry)
        <article class="vehicle-card booking-card">
            <div class="vehicle-info">
                <h2>{{ $enquiry->subject }}</h2>
                <p><strong>From:</strong> {{ $enquiry->name }} ({{ $enquiry->email }})</p>
                <p><strong>Received:</strong> {{ $enquiry->created_at->format('d M Y, H:i') }}</p>
                <p><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $enquiry->status)) }}</p>
                <a href="{{ route('admin.enquiries.show', $enquiry) }}" class="btn">View Enquiry</a>
            </div>
        </article>
    @empty
        <div class="empty-state">No enquiries found.</div>
    @endforelse

    {{ $enquiries->links() }}
@endsection
