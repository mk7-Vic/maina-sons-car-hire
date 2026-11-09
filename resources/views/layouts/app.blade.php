<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Maina & Sons Car Hire')</title>
    <style>
        :root {
            --navy: #0b132b;
            --blue: #1c2541;
            --gold: #fca311;
            --light: #f5f6fa;
            --text: #252a34;
            --muted: #707887;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            color: var(--text);
            background: var(--light);
        }

        a { text-decoration: none; color: inherit; }

        .navbar {
            background: var(--navy);
            color: white;
            padding: 20px 7%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .brand { font-size: 22px; font-weight: bold; }
        .brand span { color: var(--gold); }

        .nav-links { display: flex; gap: 25px; }
        .nav-links a:hover { color: var(--gold); }

        .container {
            width: 86%;
            max-width: 1200px;
            margin: 45px auto;
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
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 15px #0000000d;
            transition: transform .2s;
        }

        .vehicle-card:hover { transform: translateY(-4px); }

        .vehicle-image {
            height: 190px;
            width: 100%;
            object-fit: cover;
            background: var(--blue);
        }

        .vehicle-placeholder {
            height: 190px;
            background: linear-gradient(135deg, var(--navy), var(--blue));
            color: var(--gold);
            display: grid;
            place-items: center;
            font-size: 30px;
            font-weight: bold;
        }

        .vehicle-info { padding: 20px; }
        .vehicle-info h2 { margin: 0 0 8px; color: var(--navy); }
        .category { color: var(--muted); font-size: 14px; }
        .vehicle-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin: 18px 0;
            color: var(--muted);
            font-size: 14px;
        }

        .price {
            font-size: 20px;
            font-weight: bold;
            color: var(--navy);
        }

        .price small { font-size: 13px; color: var(--muted); }

        .btn {
            display: inline-block;
            background: var(--gold);
            color: var(--navy);
            padding: 12px 20px;
            border-radius: 5px;
            font-weight: bold;
            margin-top: 15px;
        }

        .btn:hover { background: #e89500; }

        .details-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 3px 15px #0000000d;
        }

        .details-image {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 8px;
            background: var(--blue);
        }

        .details-placeholder {
            height: 300px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, var(--navy), var(--blue));
            color: var(--gold);
            font-size: 40px;
            font-weight: bold;
            border-radius: 8px;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin: 25px 0;
        }

        .detail-item {
            padding: 15px;
            background: var(--light);
            border-radius: 6px;
        }

        .detail-item strong {
            display: block;
            color: var(--muted);
            font-size: 13px;
            margin-bottom: 7px;
        }

        .empty-state {
            background: white;
            padding: 35px;
            border-radius: 8px;
            text-align: center;
        }

        footer {
            background: var(--navy);
            color: white;
            text-align: center;
            padding: 22px;
            margin-top: 60px;
        }


        .booking-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin-top: 30px;
            box-shadow: 0 3px 15px #0000000d;
        }

        .booking-card h3 {
            color: var(--navy);
            margin: 25px 0 15px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-weight: bold;
            font-size: 14px;
            color: var(--navy);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #d5d9e0;
            border-radius: 5px;
            font: inherit;
            background: white;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: 2px solid var(--gold);
            border-color: var(--gold);
        }

        .full-width {
            grid-column: 1 / -1;
        }

        .form-errors {
            background: #fff0f0;
            border: 1px solid #e5a3a3;
            color: #8b2020;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .form-errors ul {
            margin: 8px 0 0;
            padding-left: 20px;
        }

        @media (max-width: 600px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .booking-card {
                padding: 18px;
            }
        }

        @media (max-width: 600px) {
            .navbar { padding: 18px 5%; }
            .nav-links { gap: 12px; font-size: 14px; }
            .brand { font-size: 18px; }
            .container { width: 90%; margin: 30px auto; }
            .details-card { padding: 18px; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="{{ route('vehicles.index') }}" class="brand">
            MAINA <span>&</span> SONS
        </a>

        <div class="nav-links">
            <a href="{{ route('vehicles.index') }}">Our Vehicles</a>
        </div>
    </nav>

    <main class="container">
        @yield('content')
    </main>

    <footer>
        &copy; {{ date('Y') }} Maina & Sons Car Hire. All rights reserved.
    </footer>
</body>
</html>
