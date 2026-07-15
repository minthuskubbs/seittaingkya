@extends('layouts.app')
@section('title', 'New Product')
@section('content')
<div class="card"><div class="card-body p-4">
    <form method="POST" action="{{ route('products.store') }}">@include('products._form')</form>
</div></div>
@endsection
