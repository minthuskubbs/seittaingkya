@extends('layouts.app')
@section('title', 'New Expense Type')
@section('content')
<div class="card"><div class="card-body p-4"><form method="POST" action="{{ route('expense-types.store') }}">@include('expense_types._form')</form></div></div>
@endsection
