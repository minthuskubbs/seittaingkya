@extends('layouts.app')
@section('title', 'New Clinic')
@section('content')
<div class="card"><div class="card-body p-4"><form method="POST" action="{{ route('clinics.store') }}">@include('clinics._form')</form></div></div>
@endsection
