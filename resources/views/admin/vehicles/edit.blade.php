@extends('layouts.app')

@section('title', 'Edit Vehicle | Maina & Sons')

@section('content')
    <div class="page-heading">
        <h1>Edit Vehicle</h1>
        <p>Update {{ $vehicle->registration_number }}.</p>
    </div>

    @include('admin.vehicles.form', [
        'vehicle' => $vehicle,
        'action' => route('admin.vehicles.update', $vehicle),
        'method' => 'PUT',
        'buttonText' => 'Save Changes',
    ])
@endsection
