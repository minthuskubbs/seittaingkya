@extends('layouts.app')
@section('title', 'New Doctor')
@section('content')
<div class="card"><div class="card-body p-4"><form method="POST" action="{{ route('doctors.store') }}">@include('doctors._form')</form></div></div>
@endsection
