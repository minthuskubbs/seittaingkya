@extends('layouts.app')
@section('title', 'Edit Doctor')
@section('content')
<div class="card"><div class="card-body p-4"><form method="POST" action="{{ route('doctors.update', $doctor) }}">@method('PUT')@include('doctors._form')</form></div></div>
@endsection
