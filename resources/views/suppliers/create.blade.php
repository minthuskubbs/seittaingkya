@extends('layouts.app')
@section('title', 'New Supplier')
@section('content')
<div class="card"><div class="card-body p-4"><form method="POST" action="{{ route('suppliers.store') }}">@include('suppliers._form')</form></div></div>
@endsection
