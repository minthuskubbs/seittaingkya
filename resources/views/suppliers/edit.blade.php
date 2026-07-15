@extends('layouts.app')
@section('title', 'Edit Supplier')
@section('content')
<div class="card"><div class="card-body p-4"><form method="POST" action="{{ route('suppliers.update',$supplier) }}">@method('PUT')@include('suppliers._form')</form></div></div>
@endsection
