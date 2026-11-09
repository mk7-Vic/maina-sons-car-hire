@extends('layouts.app')

@section('title', 'Add Vehicle | Maina & Sons')

@section('content')
    <div class="page-heading">
        <h1>Add Vehicle</h1>
        <p>Enter the vehicle details below.</p>
    </div>

    @include('admin.vehicles.form', [
        'vehicle' => null,
        'action' => route('admin.vehicles.store'),
        'method' => 'POST',
        'buttonText' => 'Add Vehicle',
    ])
@endsection
