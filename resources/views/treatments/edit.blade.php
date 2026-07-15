@extends('layouts.app')
@section('title', 'Edit Treatment')
@section('content')
<div class="card"><div class="card-body p-4"><form method="POST" action="{{ route('treatments.update',$treatment) }}">@method('PUT')@include('treatments._form')</form></div></div>
@endsection
