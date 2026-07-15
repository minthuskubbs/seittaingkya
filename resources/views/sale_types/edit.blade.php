@extends('layouts.app')
@section('title', 'Edit Sale Type')
@section('content')
<div class="card"><div class="card-body p-4"><form method="POST" action="{{ route('sale-types.update', $saleType) }}">@method('PUT')@include('sale_types._form')</form></div></div>
@endsection
