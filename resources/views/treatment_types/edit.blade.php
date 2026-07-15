@extends('layouts.app')
@section('title', 'Edit Treatment Type')
@section('content')
<div class="card"><div class="card-body p-4"><form method="POST" action="{{ route('treatment-types.update', $treatmentType) }}">@method('PUT')@include('treatment_types._form')</form></div></div>
@endsection
