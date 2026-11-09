@extends('layouts.app')

@section('title', 'Edit Branch | Maina & Sons')

@section('content')
    <div class="page-heading">
        <h1>Edit Branch</h1>
        <p>Update {{ $branch->name }}.</p>
    </div>

    @include('admin.branches.form', [
        'branch' => $branch,
        'action' => route('admin.branches.update', $branch),
        'method' => 'PUT',
        'buttonText' => 'Save Changes',
    ])
@endsection