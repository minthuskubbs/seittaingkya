@extends('layouts.app')
@section('title', 'New Tooth Charge Type')
@section('content')
<div class="card"><div class="card-body p-4"><form method="POST" action="{{ route('tooth-charge-types.store') }}">@include('tooth_charge_types._form')</form></div></div>
@endsection
