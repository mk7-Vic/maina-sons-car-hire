@extends('layouts.app')

@section('title', 'Manage Payments | Maina & Sons')

@section('content')
    <div class="page-heading">
        <h1>Manage Payments</h1>
        <p>Review customer payments and monitor transaction statuses.</p>
    </div>

    <section class="booking-card">
        @if (session('success'))
            <p>{{ session('success') }}</p>
        @endif

        @if ($errors->any())
            <div role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="GET" action="{{ route('admin.payments.index') }}"
            style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px;">
            <input type="search" name="search" value="{{ request('search') }}"
                placeholder="Search booking or transaction reference..."
                style="flex:1;min-width:220px;padding:12px;">

            <select name="status" style="padding:12px;">
                <option value="">All statuses</option>
                @foreach (['pending', 'completed', 'failed', 'refunded'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>
                        {{ ucfirst($status) }}
                    </option>
                @endforeach
            </select>

            <button class="btn" type="submit">Search</button>
            <a href="{{ route('admin.payments.index') }}" class="btn">Clear</a>
        </form>

        @if ($payments->isEmpty())
            <div class="empty-state">
                <h3>No payments found</h3>
                <p>Recorded payments will appear here.</p>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;min-width:850px;">
                    <thead>
                        <tr style="text-align:left;background:var(--light);">
                            <th style="padding:14px;">Booking</th>
                            <th style="padding:14px;">Customer</th>
                            <th style="padding:14px;">Amount</th>
                            <th style="padding:14px;">Method</th>
                            <th style="padding:14px;">Reference</th>
                            <th style="padding:14px;">Status</th>
                            <th style="padding:14px;">Date</th>
                            <th style="padding:14px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payments as $payment)
                            <tr style="border-bottom:1px solid #e5e7eb;">
                                <td style="padding:14px;">
                                    <a href="{{ route('admin.bookings.show', $payment->booking) }}">
                                        {{ $payment->booking->booking_reference }}
                                    </a>
                                </td>
                                <td style="padding:14px;">
                                    {{ $payment->booking->customer_name }}
                                </td>
                                <td style="padding:14px;">
                                    KSh {{ number_format((float) $payment->amount, 2) }}
                                </td>
                                <td style="padding:14px;">
                                    {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                </td>
                                <td style="padding:14px;">
                                    {{ $payment->transaction_reference ?? '—' }}
                                </td>
                                <td style="padding:14px;">
                                    {{ ucfirst($payment->status) }}
                                </td>
                                <td style="padding:14px;">
                                    {{ $payment->paid_at?->format('d M Y H:i') ?? '—' }}
                                </td>
                                <td style="padding:14px;">
                                    @if ($payment->status === 'pending')
                                        <form method="POST"
                                            action="{{ route('admin.payments.status', $payment) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="completed">
                                            <button type="submit" class="btn">Complete</button>
                                        </form>

                                        <form method="POST"
                                            action="{{ route('admin.payments.status', $payment) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="failed">
                                            <button type="submit" class="btn">Mark Failed</button>
                                        </form>
                                    @elseif ($payment->status === 'completed')
                                        <form method="POST"
                                            action="{{ route('admin.payments.status', $payment) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="refunded">
                                            <button type="submit" class="btn">Refund</button>
                                        </form>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top:25px;">
                {{ $payments->links() }}
            </div>
        @endif

        <a href="{{ route('admin.dashboard') }}" class="btn">Back to Dashboard</a>
    </section>
@endsection
