@extends('layouts.app')
@section('title', 'New User')
@section('content')
<div class="card"><div class="card-body p-4"><form method="POST" action="{{ route('users.store') }}">@include('users._form')</form></div></div>
@endsection
