@extends('layouts.app')
@section('title', 'Edit Staff')
@section('content')
<div class="card"><div class="card-body p-4"><form method="POST" action="{{ route('staff.update', $staff) }}">@method('PUT')@include('staff._form')</form></div></div>
@endsection
