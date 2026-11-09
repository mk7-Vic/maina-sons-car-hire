@extends('layouts.app')

@section('title', 'Maina & Sons Car Hire | Drive in Comfort and Style')
@section('full-width', 'yes')

@section('content')
<header class="hero-section d-flex align-items-center text-center text-white">
    <div class="container hero-content">
        <span class="hero-eyebrow">MAINA & SONS CAR HIRE</span>
        <h1 class="display-3 fw-bold mt-3">Drive in Comfort<br>and Style</h1>
        <p class="lead my-4">Reliable car hire services for every journey across Kenya.</p>
        <div class="d-flex flex-wrap justify-content-center gap-3">
            <a href="{{ route('vehicles.index') }}" class="btn btn-warning btn-lg px-4">
                Explore Our Fleet
            </a>
            <a href="{{ route('contact') }}" class="btn btn-outline-light btn-lg px-4">
                Book Now
            </a>
        </div>
    </div>
</header>

<section class="find-section py-5">
    <div class="container">
        <div class="find-card shadow-lg p-4 p-lg-5">
            <div class="row align-items-center g-3">
                <div class="col-lg-8">
                    <span class="text-warning fw-semibold text-uppercase small">Start your journey</span>
                    <h2 class="h3 text-white mb-1">Looking for the right vehicle?</h2>
                    <p class="text-white-50 mb-0">Browse our available vehicles and choose what suits your trip.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="{{ route('vehicles.index') }}" class="btn btn-warning btn-lg px-4">
                        Find Your Vehicle <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-light" id="fleet">
    <div class="container py-3">
        <div class="text-center mb-5">
            <span class="section-kicker">FIND YOUR RIDE</span>
            <h2 class="section-title">Explore Our Fleet</h2>
            <p class="text-muted">Choose from our selection of currently available vehicles.</p>
        </div>

        <div class="row g-4">
            @forelse ($featuredVehicles as $vehicle)
                <div class="col-md-6 col-lg-3">
                    <article class="card fleet-card h-100 border-0 shadow-sm">
                        @if ($vehicle->image_path)
                            <img src="{{ asset('storage/' . $vehicle->image_path) }}"
                                class="card-img-top" alt="{{ $vehicle->make }} {{ $vehicle->model }}">
                        @else
                            <div class="fleet-placeholder">
                                <i class="bi bi-car-front-fill"></i>
                            </div>
                        @endif

                        <div class="card-body d-flex flex-column p-4">
                            <span class="badge rounded-pill bg-light text-dark align-self-start mb-3">
                                {{ $vehicle->category->name ?? 'Available Vehicle' }}
                            </span>
                            <h3 class="h5 fw-bold text-navy">
                                {{ $vehicle->make }} {{ $vehicle->model }}
                            </h3>
                            <p class="text-muted small mb-2">
                                {{ $vehicle->year ?? 'Year not specified' }}
                                @if ($vehicle->transmission)
                                    <span class="mx-1">·</span>{{ $vehicle->transmission }}
                                @endif
                                <span class="mx-1">·</span>{{ $vehicle->seats }} seats
                            </p>
                            <div class="mt-auto">
                                <p class="vehicle-rate mb-3">
                                    KSh {{ number_format((float) $vehicle->daily_rate) }}
                                    <span>/ day</span>
                                </p>
                                <a href="{{ route('vehicles.show', $vehicle) }}"
                                    class="btn btn-warning w-100">View Details</a>
                            </div>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-light text-center py-5">
                        <i class="bi bi-car-front display-5 text-secondary"></i>
                        <h3 class="h5 mt-3">Our fleet is being updated</h3>
                        <p class="text-muted mb-0">Please contact us for current vehicle availability.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="text-center mt-5">
            <a href="{{ route('vehicles.index') }}" class="btn btn-dark btn-lg px-5">
                View All Vehicles
            </a>
        </div>
    </div>
</section>

<section class="why-us py-5">
    <div class="container py-3">
        <div class="text-center mb-5">
            <span class="section-kicker">THE MAINA & SONS DIFFERENCE</span>
            <h2 class="section-title">Why Choose Us?</h2>
            <p class="text-muted">We make getting around simpler, more comfortable and convenient.</p>
        </div>

        <div class="row g-4">
            <div class="col-sm-6 col-lg-3">
                <div class="feature-box text-center h-100">
                    <i class="bi bi-car-front-fill"></i>
                    <h3 class="h5">Vehicle Selection</h3>
                    <p>Explore a range of vehicles for different journeys and travel needs.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="feature-box text-center h-100">
                    <i class="bi bi-wallet2"></i>
                    <h3 class="h5">Clear Daily Rates</h3>
                    <p>View vehicle rates upfront before you submit your booking request.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="feature-box text-center h-100">
                    <i class="bi bi-headset"></i>
                    <h3 class="h5">Customer Support</h3>
                    <p>Get in touch with our team whenever you need help with your enquiry.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="feature-box text-center h-100">
                    <i class="bi bi-shield-check"></i>
                    <h3 class="h5">Peace of Mind</h3>
                    <p>Our team helps coordinate your booking and vehicle arrangements.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="services-section py-5">
    <div class="container py-3">
        <div class="text-center mb-5">
            <span class="section-kicker text-warning">TRAVEL YOUR WAY</span>
            <h2 class="section-title text-white">Our Services</h2>
            <p class="text-white-50">Flexible transport options for personal and business journeys.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="service-card h-100">
                    <i class="bi bi-key-fill"></i>
                    <h3 class="h5">Self Drive</h3>
                    <p>Enjoy the freedom to travel at your own pace with a vehicle of your choice.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="service-card h-100">
                    <i class="bi bi-person-badge-fill"></i>
                    <h3 class="h5">Chauffeur Service</h3>
                    <p>Arrange convenient transport for business and personal occasions.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="service-card h-100">
                    <i class="bi bi-airplane-fill"></i>
                    <h3 class="h5">Airport Transfers</h3>
                    <p>Plan your airport pickup or drop-off with a pre-arranged vehicle.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="service-card h-100">
                    <i class="bi bi-briefcase-fill"></i>
                    <h3 class="h5">Corporate Travel</h3>
                    <p>Transport arrangements for work trips, meetings and business needs.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="service-card h-100">
                    <i class="bi bi-heart-fill"></i>
                    <h3 class="h5">Special Occasions</h3>
                    <p>Make your celebrations and important events easier to get to.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="service-card h-100">
                    <i class="bi bi-calendar-check-fill"></i>
                    <h3 class="h5">Long-Term Hire</h3>
                    <p>Contact us to discuss vehicle hire arrangements for longer periods.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="how-section py-5 bg-white" id="how-it-works">
    <div class="container py-3">
        <div class="text-center mb-5">
            <span class="section-kicker">SIMPLE AND CONVENIENT</span>
            <h2 class="section-title">How It Works</h2>
            <p class="text-muted">Getting on the road starts with four simple steps.</p>
        </div>

        <div class="row g-4 text-center">
            <div class="col-6 col-lg-3">
                <div class="step-card h-100">
                    <span class="step-number">1</span>
                    <i class="bi bi-car-front-fill"></i>
                    <h3 class="h5">Choose a Vehicle</h3>
                    <p>Browse the fleet and select a vehicle for your journey.</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="step-card h-100">
                    <span class="step-number">2</span>
                    <i class="bi bi-calendar-check-fill"></i>
                    <h3 class="h5">Submit a Booking</h3>
                    <p>Provide your travel dates and booking information.</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="step-card h-100">
                    <span class="step-number">3</span>
                    <i class="bi bi-check-circle-fill"></i>
                    <h3 class="h5">Get Confirmation</h3>
                    <p>Our team reviews your request and follows up with you.</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="step-card h-100">
                    <span class="step-number">4</span>
                    <i class="bi bi-geo-alt-fill"></i>
                    <h3 class="h5">Pick Up and Go</h3>
                    <p>Arrange collection details and enjoy your journey.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="cta-section py-5 text-center text-white">
    <div class="container py-5">
        <span class="section-kicker text-warning">YOUR NEXT JOURNEY STARTS HERE</span>
        <h2 class="display-5 fw-bold mt-3">Ready for the Road?</h2>
        <p class="lead mx-auto my-4">
            Find a vehicle that suits your plans and make your booking enquiry today.
        </p>
        <a href="{{ route('vehicles.index') }}" class="btn btn-warning btn-lg px-5">
            Browse Our Fleet
        </a>
    </div>
</section>
@endsection

@push('styles')
<style>
    .hero-section {
        min-height: min(760px, calc(100vh - 76px));
        padding: 100px 0;
        background:
            linear-gradient(110deg, rgba(11,19,43,.88), rgba(11,19,43,.48)),
            url('/images/background-image2.avif'),
            linear-gradient(135deg, #0b132b, #1c2541);
        background-size: cover;
        background-position: center;
    }

    .hero-content { max-width: 900px; }
    .hero-eyebrow, .section-kicker {
        color: #d18a0b;
        font-size: .8rem;
        font-weight: 700;
        letter-spacing: 2px;
    }

    .hero-section h1 {
        font-size: clamp(2.8rem, 7vw, 5.2rem);
        line-height: 1.12;
        text-shadow: 2px 3px 12px rgba(0,0,0,.35);
    }

    .hero-section p { color: rgba(255,255,255,.9); }
    .find-section { background: var(--navy); }
    .find-card { background: var(--blue); border-radius: 18px; }
    .section-title { color: var(--navy); font-weight: 700; margin: 10px 0 14px; }
    .fleet-card { border-radius: 15px; overflow: hidden; transition: transform .3s ease, box-shadow .3s ease; }
    .fleet-card:hover { transform: translateY(-8px); box-shadow: 0 15px 30px rgba(0,0,0,.12) !important; }
    .fleet-card .card-img-top, .fleet-placeholder { height: 205px; object-fit: contain; padding: 12px; background: #f1f3f7; }
    .fleet-placeholder { display: grid; place-items: center; color: var(--gold); font-size: 4rem; }
    .text-navy { color: var(--navy); }
    .vehicle-rate { color: var(--navy); font-size: 1.25rem; font-weight: 700; }
    .vehicle-rate span { color: var(--muted); font-size: .85rem; font-weight: 400; }
    .feature-box, .service-card, .step-card { border-radius: 18px; padding: 30px 22px; transition: transform .3s ease; }
    .feature-box { background: var(--blue); color: white; }
    .feature-box:hover, .service-card:hover, .step-card:hover { transform: translateY(-7px); }
    .feature-box > i, .service-card > i, .step-card > i { display: block; color: var(--gold); font-size: 2.7rem; margin-bottom: 20px; }
    .feature-box h3, .service-card h3, .step-card h3 { font-weight: 600; }
    .feature-box p, .service-card p, .step-card p { margin-bottom: 0; line-height: 1.7; }
    .services-section { background: var(--navy); color: white; }
    .services-section .section-title { color: white; }
    .service-card { background: var(--blue); height: 100%; }
    .step-card { background: var(--blue); color: white; }
    .step-number { display: grid; place-items: center; width: 46px; height: 46px; margin: 0 auto 18px; border-radius: 50%; background: var(--navy); color: white; font-size: 1.2rem; font-weight: 700; }
    .cta-section { background: linear-gradient(110deg, rgba(11,19,43,.94), rgba(28,37,65,.86)), url('/images/background-image.jpeg'), var(--navy); background-size: cover; background-position: center; }
    .cta-section p { max-width: 680px; color: rgba(255,255,255,.85); }
    @media (max-width: 575px) {
        .hero-section { min-height: 590px; padding: 75px 0; }
        .hero-section .btn { width: 100%; }
        .find-card { text-align: center; }
        .step-card { padding: 22px 12px; }
        .step-card p { font-size: .85rem; }
    }
</style>
@endpush
