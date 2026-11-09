@extends('layouts.app')

@section('title', 'Add Branch | Maina & Sons')

@section('content')
    <div class="page-heading">
        <h1>Add Branch</h1>
        <p>Enter the new branch details below.</p>
    </div>

    @include('admin.branches.form', [
        'branch' => null,
        'action' => route('admin.branches.store'),
        'method' => 'POST',
        'buttonText' => 'Add Branch',
    ])
@endsection