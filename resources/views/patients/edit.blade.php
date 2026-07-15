@extends('layouts.app')
@section('title', 'Edit Patient')

@section('content')
<div class="card"><div class="card-body p-4">
    <form method="POST" action="{{ route('patients.update', $patient) }}">
        @method('PUT')
        @include('patients._form')
    </form>
</div></div>
@endsection
