@extends('layouts.app')

@section('title', 'Add Vehicle Category | Maina & Sons')

@section('content')
    <div class="page-heading">
        <h1>Add Vehicle Category</h1>
        <p>Create a category for organising vehicles.</p>
    </div>

    @include('admin.categories.form', [
        'action' => route('admin.categories.store'),
        'method' => 'POST',
        'buttonText' => 'Create Category',
    ])
@endsection
