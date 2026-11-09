@extends('layouts.app')

@section('title', 'Enquiry Details | Maina & Sons')

@section('content')
    <div class="page-heading">
        <h1>Enquiry Details</h1>
        <p>Review the customer's message and update its status.</p>
        <a href="{{ route('admin.enquiries.index') }}" class="btn">Back to Enquiries</a>
    </div>

    @if (session('success'))
        <div class="booking-card">{{ session('success') }}</div>
    @endif

    <section class="details-card">
        <h2>{{ $enquiry->subject }}</h2>

        <div class="details-grid">
            <div class="detail-item">
                <strong>Customer</strong>
                {{ $enquiry->name }}
            </div>
            <div class="detail-item">
                <strong>Email</strong>
                {{ $enquiry->email }}
            </div>
            <div class="detail-item">
                <strong>Phone</strong>
                {{ $enquiry->phone ?: 'Not provided' }}
            </div>
            <div class="detail-item">
                <strong>Date received</strong>
                {{ $enquiry->created_at->format('d M Y, H:i') }}
            </div>
        </div>

        <h3>Customer Message</h3>
        <p style="white-space: pre-wrap;">{{ $enquiry->message }}</p>
    </section>

    <section class="booking-card">
        <h2>Update Enquiry</h2>

        @if ($errors->any())
            <div class="form-errors">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.enquiries.update', $enquiry) }}">
            @csrf
            @method('PATCH')

            <div class="form-grid">
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        @foreach (['new', 'in_progress', 'resolved', 'closed'] as $status)
                            <option value="{{ $status }}"
                                @selected(old('status', $enquiry->status) === $status)>
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group full-width">
                    <label for="admin_notes">Internal Admin Notes</label>
                    <textarea id="admin_notes" name="admin_notes" rows="5">{{ old('admin_notes', $enquiry->admin_notes) }}</textarea>
                </div>
            </div>

            <button type="submit" class="btn">Save Changes</button>
        </form>
    </section>
@endsection
