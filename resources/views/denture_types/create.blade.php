@extends('layouts.app')
@section('title', 'New Denture Type')
@section('content')
<div class="card"><div class="card-body p-4"><form method="POST" action="{{ route('denture-types.store') }}">@include('denture_types._form')</form></div></div>
@endsection
