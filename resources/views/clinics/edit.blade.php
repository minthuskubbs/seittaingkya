@extends('layouts.app')
@section('title', 'Edit Clinic')
@section('content')
<div class="card"><div class="card-body p-4"><form method="POST" action="{{ route('clinics.update',$clinic) }}">@method('PUT')@include('clinics._form')</form></div></div>
@endsection
