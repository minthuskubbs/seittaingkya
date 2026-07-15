@extends('layouts.app')
@section('title', 'Edit Expense Type')
@section('content')
<div class="card"><div class="card-body p-4"><form method="POST" action="{{ route('expense-types.update', $expenseType) }}">@method('PUT')@include('expense_types._form')</form></div></div>
@endsection
