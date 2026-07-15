@extends('layouts.app')
@section('title', 'Edit Fee')
@section('content')
<div class="card"><div class="card-body p-4">
    <form method="POST" action="{{ route('fees.update', $fee) }}">@method('PUT')@include('fees._form')</form>
</div></div>
@endsection
