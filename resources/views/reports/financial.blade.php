@extends('layouts.app')
@section('title', 'Financial Report')
@section('content')
@include('reports._range')
<div class="d-flex justify-content-end mb-3">
    <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn btn-outline-secondary"><i class="bi bi-filetype-csv"></i> Export CSV</a>
</div>
<div class="row g-3">
    <div class="col-6 col-lg-3"><div class="card stat-card"><div class="card-body">
        <div class="text-muted small">Revenue Collected</div><div class="h4 text-success">{{ money($revenue) }}</div></div></div></div>
    <div class="col-6 col-lg-3"><div class="card stat-card"><div class="card-body">
        <div class="text-muted small">Total Expenses</div><div class="h4 text-danger">{{ money($expenses) }}</div>
        <div class="small text-muted">Bills {{ money($manualExpenses) }} · Payroll {{ money($payroll) }}</div></div></div></div>
    <div class="col-6 col-lg-3"><div class="card stat-card"><div class="card-body">
        <div class="text-muted small">Net Profit</div><div class="h4 {{ $net < 0 ? 'text-danger' : 'text-brand' }}">{{ money($net) }}</div></div></div></div>
    <div class="col-6 col-lg-3"><div class="card stat-card"><div class="card-body">
        <div class="text-muted small">Medicine Sales</div><div class="h4">{{ money($salesTotal) }}</div></div></div></div>
</div>
<div class="card mt-3"><div class="card-body">
    <div class="d-flex justify-content-between"><span class="text-muted">Treatment Billed (period)</span><span>{{ money($treatmentBilled) }}</span></div>
</div></div>
@endsection
