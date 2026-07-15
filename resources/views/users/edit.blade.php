@extends('layouts.app')
@section('title', 'Edit User')
@section('content')
<div class="card"><div class="card-body p-4"><form method="POST" action="{{ route('users.update',$user) }}">@method('PUT')@include('users._form')</form></div></div>
@endsection
