@extends('layouts.app')

@section('title', 'Vehicle Categories | Maina & Sons')

@section('content')
    <div class="page-heading">
        <h1>Vehicle Categories</h1>
        <p>Manage the categories used to organise your fleet.</p>
    </div>

    <div class="booking-card">
        <a href="{{ route('admin.categories.create') }}" class="btn">Add Category</a>
        <a href="{{ route('admin.vehicles.index') }}" class="btn">Manage Vehicles</a>

        @if (session('success'))
            <p class="success-message">{{ session('success') }}</p>
        @endif
        @if (session('error'))
            <p class="error-message">{{ session('error') }}</p>
        @endif

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Description</th>
                        <th>Vehicles</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td>{{ $category->name }}</td>
                            <td>{{ $category->slug }}</td>
                            <td>{{ $category->description ?: '—' }}</td>
                            <td>{{ $category->vehicles_count }}</td>
                            <td>
                                <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm">Edit</a>
                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" style="display:inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-warning btn-sm"
                                        onclick="return confirm('Delete this category?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5">No categories have been created.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $categories->links() }}
    </div>
@endsection
