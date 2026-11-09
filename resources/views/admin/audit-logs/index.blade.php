@extends('layouts.app')

@section('title', 'Audit Logs | Maina & Sons')

@section('content')
    <div class="page-heading">
        <h1>Audit Logs</h1>
        <p>Review administrative activity and changes made in the system.</p>
    </div>

    <div class="booking-card">
        <form method="GET" action="{{ route('admin.audit-logs.index') }}">
            <div class="form-grid">
                <div class="form-group">
                    <label for="search">Search</label>
                    <input id="search" name="search" value="{{ request('search') }}"
                        placeholder="Description, action or IP address">
                </div>

                <div class="form-group">
                    <label for="action">Action</label>
                    <select id="action" name="action">
                        <option value="">All actions</option>
                        @foreach ($actions as $action)
                            <option value="{{ $action }}" @selected(request('action') === $action)>
                                {{ $action }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="user_id">Administrator / User</label>
                    <select id="user_id" name="user_id">
                        <option value="">All users</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="from">From</label>
                    <input type="date" id="from" name="from" value="{{ request('from') }}">
                </div>

                <div class="form-group">
                    <label for="to">To</label>
                    <input type="date" id="to" name="to" value="{{ request('to') }}">
                </div>
            </div>

            <button type="submit" class="btn">Apply Filters</button>
            <a href="{{ route('admin.audit-logs.index') }}" class="btn">Clear</a>
        </form>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('d M Y, H:i:s') }}</td>
                            <td>{{ $log->user?->name ?? 'System / Deleted User' }}</td>
                            <td>{{ $log->action }}</td>
                            <td>{{ $log->description ?: '—' }}</td>
                            <td>{{ $log->ip_address ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5">No audit records match these filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $logs->links() }}
    </div>
@endsection
