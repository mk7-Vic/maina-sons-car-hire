@extends('layouts.app')

@section('title', 'Customer Login | Maina & Sons')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7 col-lg-5">
        <div class="card border-0 shadow-sm p-4">
            <h1 class="h3 text-center mb-2">Customer Login</h1>
            <p class="text-muted text-center mb-4">Sign in to view and manage your bookings.</p>

            <form method="POST" action="{{ route('customer.login.store') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                        class="form-control @error('email') is-invalid @enderror" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input id="password" type="password" name="password"
                        class="form-control" required>
                </div>

                <button class="btn btn-warning w-100" type="submit">Sign In</button>
            </form>

            <p class="text-center mt-4 mb-0">
                New customer?
                <a href="{{ route('customer.register') }}">Create an account</a>
            </p>
        </div>
    </div>
</div>
@endsection
