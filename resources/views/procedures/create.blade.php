@extends('layouts.app')
@section('title', 'New Procedure')
@section('content')
<div class="card"><div class="card-body p-4"><form method="POST" action="{{ route('procedures.store') }}">@include('procedures._form')</form></div></div>
@endsection
