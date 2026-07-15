@extends('layouts.app')
@section('title', 'New Treatment')
@section('content')
<div class="card"><div class="card-body p-4"><form method="POST" action="{{ route('treatments.store') }}">@include('treatments._form')</form></div></div>
@endsection
