@extends('layouts.app')
@section('title', 'Edit Denture Type')
@section('content')
<div class="card"><div class="card-body p-4"><form method="POST" action="{{ route('denture-types.update', $type) }}">@method('PUT')@include('denture_types._form')</form></div></div>
@endsection
