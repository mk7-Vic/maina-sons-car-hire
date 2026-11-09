@extends('layouts.app')

@section('title', 'Contact Us | Maina & Sons')

@section('content')
    <div class="page-heading">
        <h1>Contact Maina &amp; Sons</h1>
        <p>Send us an enquiry and our team will get back to you.</p>
    </div>

    @if (session('success'))
        <div class="booking-card">{{ session('success') }}</div>
    @endif

    <div class="booking-card">
        @if ($errors->any())
            <div class="form-errors">
                <strong>Please correct the following:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('enquiries.store') }}">
            @csrf

            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input id="name" name="name" value="{{ old('name') }}" maxlength="255" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" maxlength="255" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" maxlength="30">
                </div>

                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input id="subject" name="subject" value="{{ old('subject') }}" maxlength="255" required>
                </div>

                <div class="form-group full-width">
                    <label for="message">Your Message</label>
                    <textarea id="message" name="message" rows="6" maxlength="10000" required>{{ old('message') }}</textarea>
                </div>
            </div>

            <button type="submit" class="btn">Submit Enquiry</button>
        </form>
    </div>
@endsection
