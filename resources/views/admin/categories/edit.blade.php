@extends('layouts.app')

@section('title', 'Edit Vehicle Category | Maina & Sons')

@section('content')
    <div class="page-heading">
        <h1>Edit Vehicle Category</h1>
        <p>Update {{ $category->name }}.</p>
    </div>

    @include('admin.categories.form', [
        'action' => route('admin.categories.update', $category),
        'method' => 'PUT',
        'buttonText' => 'Save Changes',
    ])
@endsection
