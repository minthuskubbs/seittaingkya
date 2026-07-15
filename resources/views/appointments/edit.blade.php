@extends('layouts.app')
@section('title', 'Edit Appointment')
@section('content')
<div class="card"><div class="card-body p-4">
    <form method="POST" action="{{ route('appointments.update', $appointment) }}">
        @method('PUT')
        @include('appointments._form')
    </form>
</div></div>
@endsection
