@extends('layouts.app')
@section('title', 'Edit Product')
@section('content')
<div class="card"><div class="card-body p-4">
    <form method="POST" action="{{ route('products.update', $product) }}">@method('PUT')@include('products._form')</form>
</div></div>
@endsection
