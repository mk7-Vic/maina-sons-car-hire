<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b132b">
    <title>@yield('title', 'Maina & Sons Car Hire')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --navy: #0b132b;
            --blue: #1c2541;
            --gold: #fca311;
            --light: #f8f9fa;
            --text: #252a34;
            --muted: #707887;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding-top: 76px;
            overflow-x: hidden;
            font-family: 'Poppins', sans-serif;
            color: var(--text);
            background: var(--light);
        }

        a { text-decoration: none; }

        .site-navbar {
            background: rgba(11, 19, 43, .92);
            backdrop-filter: blur(10px);
            box-shadow: 0 3px 18px rgba(0, 0, 0, .12);
            min-height: 76px;
        }

        .site-navbar .navbar-brand {
            color: var(--gold);
            font-size: 1.55rem;
            letter-spacing: .2px;
        }

        .site-navbar .nav-link {
            color: #fff;
            font-size: .92rem;
            font-weight: 500;
            margin-left: 8px;
            transition: color .2s ease;
        }

        .site-navbar .nav-link:hover,
        .site-navbar .nav-link.active {
            color: var(--gold);
        }

        .site-navbar .navbar-toggler {
            border-color: rgba(255,255,255,.35);
        }

        .site-navbar .navbar-toggler:focus {
            box-shadow: 0 0 0 .2rem rgba(252,163,17,.25);
        }

        .btn-warning {
            background: var(--gold);
            border-color: var(--gold);
            color: var(--navy);
            font-weight: 600;
        }

        .btn-warning:hover {
            background: #e89500;
            border-color: #e89500;
            color: var(--navy);
            transform: translateY(-2px);
        }

        .btn-outline-light:hover { color: var(--navy); }

        .page-content {
            max-width: 1200px;
            min-height: 55vh;
            padding-top: 38px;
            padding-bottom: 38px;
        }

        .page-heading { margin-bottom: 30px; }
        .page-heading h1 { color: var(--navy); margin-bottom: 8px; }
        .page-heading p { color: var(--muted); }

        .vehicle-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 25px;
        }

        .vehicle-card {
            overflow: hidden;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 22px rgba(0,0,0,.07);
            transition: transform .25s ease;
        }

        .vehicle-card:hover { transform: translateY(-5px); }

        .vehicle-image {
            width: 100%;
            height: 210px;
            object-fit: cover;
            background: var(--blue);
        }

        .vehicle-placeholder,
        .details-placeholder {
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, var(--navy), var(--blue));
            color: var(--gold);
            font-size: 32px;
            font-weight: 700;
        }

        .vehicle-placeholder { height: 210px; }
        .vehicle-info { padding: 22px; }
        .vehicle-info h2 { color: var(--navy); font-size: 1.2rem; }
        .category { color: var(--muted); font-size: 14px; }
        .vehicle-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin: 18px 0;
            color: var(--muted);
            font-size: 14px;
        }

        .price { color: var(--navy); font-size: 20px; font-weight: 700; }
        .price small { color: var(--muted); font-size: 13px; }

        .details-card,
        .booking-card {
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 22px rgba(0,0,0,.07);
        }

        .details-image {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 8px;
        }

        .details-placeholder { height: 300px; border-radius: 8px; }
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin: 25px 0;
        }

        .detail-item { padding: 15px; background: var(--light); border-radius: 7px; }
        .detail-item strong { display: block; color: var(--muted); font-size: 13px; margin-bottom: 7px; }
        .empty-state { padding: 35px; background: white; border-radius: 8px; text-align: center; }

        .booking-card { margin-top: 30px; }
        .booking-card h3 { color: var(--navy); margin: 25px 0 15px; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; }
        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .form-group label { color: var(--navy); font-size: 14px; font-weight: 600; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #d5d9e0;
            border-radius: 7px;
            font: inherit;
            background: white;
        }

        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: 2px solid var(--gold);
            border-color: var(--gold);
        }

        .full-width { grid-column: 1 / -1; }
        .form-errors {
            padding: 15px;
            margin-bottom: 20px;
            color: #8b2020;
            background: #fff0f0;
            border: 1px solid #e5a3a3;
            border-radius: 6px;
        }

        .site-footer {
            background: var(--navy);
            color: #adb5bd;
            padding: 55px 0 20px;
        }

        .site-footer h5 { color: white; font-weight: 600; margin-bottom: 18px; }
        .site-footer .footer-brand { color: var(--gold); font-size: 1.5rem; font-weight: 700; }
        .site-footer a { color: #adb5bd; transition: color .2s ease; }
        .site-footer a:hover { color: var(--gold); }
        .site-footer li { margin-bottom: 10px; }
        .site-footer .social-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            margin-right: 8px;
            border-radius: 50%;
            background: rgba(255,255,255,.1);
            font-size: 1.1rem;
        }

        .site-footer .social-links a:hover { color: var(--navy); background: var(--gold); }
        .footer-bottom { border-top: 1px solid rgba(255,255,255,.13); margin-top: 35px; padding-top: 20px; }

        @media (max-width: 991px) {
            .site-navbar .navbar-collapse {
                padding: 12px 0 18px;
            }

            .site-navbar .nav-link { margin: 3px 0; }
        }

        @media (max-width: 600px) {
            .page-content { padding-top: 25px; padding-bottom: 25px; }
            .form-grid { grid-template-columns: 1fr; }
            .booking-card, .details-card { padding: 18px; }
        }
    </style>

    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top site-navbar">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('home') }}">
                Maina <span class="text-white">&</span> Sons
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#mainNavbar" aria-controls="mainNavbar"
                aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}"
                            href="{{ route('home') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('vehicles.*') ? 'active' : '' }}"
                            href="{{ route('vehicles.index') }}">Our Fleet</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}"
                            href="{{ route('contact') }}">Contact</a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-warning btn-sm px-3" href="{{ route('vehicles.index') }}">Book Now</a>
                    </li>

                @auth
                    @if (auth()->user()->role === 'customer')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('customer.dashboard') }}">My Bookings</a>
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="{{ route('customer.logout') }}">
                                @csrf
                                <button class="nav-link border-0 bg-transparent" type="submit">Logout</button>
                            </form>
                        </li>
                    @endif
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('customer.login') }}">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('customer.register') }}">Register</a>
                    </li>
                @endauth
                </ul>
            </div>
        </div>
    </nav>

    @hasSection('full-width')
        @yield('content')
    @else
        <main class="container page-content">
            @yield('content')
        </main>
    @endif

    <footer class="site-footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <a href="{{ route('home') }}" class="footer-brand">Maina & Sons</a>
                    <p class="mt-3">
                        Your journey, our priority. Explore reliable and comfortable
                        car hire options for travel across Kenya.
                    </p>
                    <div class="social-links mt-3">
                        <a href="https://www.instagram.com/victor.m.m_" target="_blank"
                            rel="noopener noreferrer" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                        <a href="https://www.facebook.com/maina" target="_blank"
                            rel="noopener noreferrer" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                        <a href="https://www.tiktok.com/__v1ctxr__" target="_blank"
                            rel="noopener noreferrer" aria-label="TikTok"><i class="bi bi-tiktok"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6">
                    <h5>Explore</h5>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('home') }}">Home</a></li>
                        <li><a href="{{ route('vehicles.index') }}">Browse vehicles</a></li>
                        <li><a href="{{ route('contact') }}">Contact us</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h5>Our Services</h5>
                    <ul class="list-unstyled">
                        <li>Self-drive car hire</li>
                        <li>Chauffeur services</li>
                        <li>Airport transfers</li>
                        <li>Corporate travel</li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h5>Get in Touch</h5>
                    <p><i class="bi bi-telephone-fill me-2"></i>+254 717 855 630</p>
                    <p><i class="bi bi-envelope-fill me-2"></i>info@mainasons.co.ke</p>
                </div>
            </div>

            <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between gap-2 small">
                <span>&copy; {{ date('Y') }} Maina & Sons Car Hire. All rights reserved.</span>
                <span>Drive in comfort. Arrive in style.</span>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
