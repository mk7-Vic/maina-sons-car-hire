<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login | Maina & Sons</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: #f5f6fa;
            font-family: Arial, sans-serif;
            color: #252a34;
        }

        .login-card {
            width: min(400px, 88%);
            background: white;
            padding: 35px;
            border-radius: 10px;
            box-shadow: 0 3px 20px #00000012;
        }

        h1 { color: #0b132b; }
        label { display: block; margin: 18px 0 7px; }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #d5d9e0;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            margin-top: 25px;
            padding: 13px;
            border: 0;
            border-radius: 5px;
            background: #fca311;
            color: #0b132b;
            font-weight: bold;
            cursor: pointer;
        }

        .error { color: #a12626; margin-top: 15px; }
    </style>
</head>
<body>
    <main class="login-card">
        <h1>Admin Login</h1>
        <p>Maina & Sons Car Hire</p>

        @if ($errors->any())
            <div class="error">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.store') }}">
            @csrf

            <label for="email">Email Address</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
            >

            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                required
            >

            <button type="submit">Login</button>
        </form>
    </main>
</body>
</html>
