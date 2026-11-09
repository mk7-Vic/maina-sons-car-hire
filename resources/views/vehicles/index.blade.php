@extends('layouts.app')

@section('title', 'Our Vehicles | Maina & Sons')

@section('content')
    <div class="page-heading">
        <h1>Explore Our Vehicles</h1>
        <p>Find the right vehicle for your next journey.</p>
    </div>

    @if ($vehicles->isEmpty())
        <div class="empty-state">
            <h2>No vehicles currently available</h2>
            <p>Please check again later.</p>
        </div>
    @else
        <div class="vehicle-grid">
            @foreach ($vehicles as $vehicle)
                <article class="vehicle-card">
                    @if ($vehicle->image_path)
                        <img
                            class="vehicle-image"
                            src="{{ asset('storage/' . $vehicle->image_path) }}"
                            alt="{{ $vehicle->make }} {{ $vehicle->model }}"
                        >
                    @else
                        <div class="vehicle-placeholder">
                            {{ $vehicle->make }}
                        </div>
                    @endif

                    <div class="vehicle-info">
                        <h2>{{ $vehicle->make }} {{ $vehicle->model }}</h2>

                        <div class="category">
                            {{ $vehicle->category->name }}
                        </div>

                        <div class="vehicle-meta">
                            <span>{{ $vehicle->year ?? 'Year N/A' }}</span>
                            <span>{{ $vehicle->transmission ?? 'Transmission N/A' }}</span>
                            <span>{{ $vehicle->seats }} Seats</span>
                        </div>

                        <div class="price">
                            KSh {{ number_format($vehicle->daily_rate, 2) }}
                            <small>/ day</small>
                        </div>

                        <a
                            href="{{ route('vehicles.show', $vehicle) }}"
                            class="btn"
                        >
                            View Details
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
@endsection
