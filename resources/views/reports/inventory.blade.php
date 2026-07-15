@extends('layouts.app')
@section('title', 'Inventory Report')
@section('content')
<div class="d-flex justify-content-end mb-3">
    <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn btn-outline-secondary"><i class="bi bi-filetype-csv"></i> Export CSV</a>
</div>
@if($clinics->isNotEmpty())
<form class="card mb-3"><div class="card-body row g-2 align-items-end">
    <div class="col-md-4"><label class="form-label">Clinic</label>
        <select name="clinic_id" class="form-select"><option value="">All clinics</option>
            @foreach($clinics as $c)<option value="{{ $c->id }}" @selected((string)$clinicFilter===(string)$c->id)>{{ $c->name }}</option>@endforeach
        </select></div>
    <div class="col-md-3"><button class="btn btn-brand"><i class="bi bi-funnel"></i> Filter</button></div>
</div></form>
@endif
<div class="card mb-3"><div class="card-body d-flex justify-content-between">
    <span class="text-muted">Total Stock Value (at cost)</span><strong class="text-brand">{{ money($stockValue) }}</strong></div></div>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Product</th>@if($clinics->isNotEmpty())<th>Clinic</th>@endif<th>Type</th><th class="text-center">Stock</th><th class="text-end">Cost</th><th class="text-end">Value</th><th>Status</th></tr></thead>
    <tbody>
    @foreach($products as $p)
        <tr class="{{ $p->isLowStock()?'table-warning':'' }}">
            <td>{{ $p->name }}</td>@if($clinics->isNotEmpty())<td><span class="badge badge-soft-brand">{{ $p->clinic->name ?? '—' }}</span></td>@endif<td>{{ $p->type }}</td><td class="text-center">{{ $p->stock_qty }} {{ $p->unit }}</td>
            <td class="text-end">{{ money($p->cost_price) }}</td><td class="text-end">{{ money($p->stock_qty*$p->cost_price) }}</td>
            <td>{!! $p->isLowStock()?'<span class="badge bg-danger">Low</span>':'<span class="badge bg-success">OK</span>' !!}</td></tr>
    @endforeach
    </tbody>
</table></div></div>
@endsection
