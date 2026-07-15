@extends('layouts.app')
@section('title', 'New Staff')
@section('content')
<div class="card"><div class="card-body p-4"><form method="POST" action="{{ route('staff.store') }}">@include('staff._form')</form></div></div>
@endsection
