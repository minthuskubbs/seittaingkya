@extends('layouts.app')
@section('title', 'Edit Procedure')
@section('content')
<div class="card"><div class="card-body p-4"><form method="POST" action="{{ route('procedures.update',$procedure) }}">@method('PUT')@include('procedures._form')</form></div></div>
@endsection
