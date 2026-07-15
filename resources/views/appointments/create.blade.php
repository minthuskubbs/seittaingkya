@extends('layouts.app')
@section('title', 'New Appointment')
@section('content')
<div class="card"><div class="card-body p-4">
    <form method="POST" action="{{ route('appointments.store') }}">
        @include('appointments._form')
    </form>
</div></div>
@endsection
