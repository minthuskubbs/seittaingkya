@extends('layouts.app')
@section('title', 'New Fee')
@section('content')
<div class="card"><div class="card-body p-4">
    <form method="POST" action="{{ route('fees.store') }}">@include('fees._form')</form>
</div></div>
@endsection
