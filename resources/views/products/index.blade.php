@extends('layouts.app')
@section('title', 'Products')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <form class="d-flex gap-2 flex-wrap">
        @if($clinics->isNotEmpty())
            <select name="clinic_id" class="form-select">
                <option value="">All clinics</option>
                @foreach($clinics as $c)
                    <option value="{{ $c->id }}" @selected((string)$clinicFilter === (string)$c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        @endif
        <input name="q" value="{{ request('q') }}" class="form-control" placeholder="Search…">
        <select name="type" class="form-select"><option value="">All types</option>
            <option value="medicine" @selected(request('type')==='medicine')>Medicine</option>
            <option value="dental_supply" @selected(request('type')==='dental_supply')>Dental Supply</option>
        </select>
        <div class="form-check d-flex align-items-center ms-1"><input type="checkbox" class="form-check-input me-1" name="low" value="1" @checked(request('low'))> Low</div>
        <button class="btn btn-outline-secondary"><i class="bi bi-funnel"></i></button>
    </form>
    @can('inventory.manage')<a href="{{ route('products.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> New Product</a>@endcan
</div>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Name</th>@if($clinics->isNotEmpty())<th>Clinic</th>@endif<th>Type</th><th class="text-end">Cost</th><th class="text-end">Price</th><th class="text-center">Stock</th><th>Supplier</th><th></th></tr></thead>
    <tbody>
    @forelse($products as $p)
        <tr class="{{ $p->isLowStock() ? 'table-warning' : '' }}">
            <td><a href="{{ route('products.show', $p) }}">{{ $p->name }}</a> <span class="text-muted small">{{ $p->sku }}</span></td>
            @if($clinics->isNotEmpty())<td><span class="badge badge-soft-brand">{{ $p->clinic->name ?? '—' }}</span></td>@endif
            <td>{{ $p->type==='medicine'?'Medicine':'Dental Supply' }}</td>
            <td class="text-end">{{ money($p->cost_price) }}</td>
            <td class="text-end">{{ money($p->sale_price) }}</td>
            <td class="text-center">{{ $p->stock_qty }} {{ $p->unit }}
                @if($p->isLowStock())<i class="bi bi-exclamation-triangle-fill text-danger" title="Low"></i>@endif</td>
            <td>{{ $p->supplier->name ?? '—' }}</td>
            <td class="text-end">
                @can('inventory.manage')<a href="{{ route('products.edit', $p) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>@endcan
            </td>
        </tr>
    @empty<tr><td colspan="{{ $clinics->isNotEmpty() ? 8 : 7 }}" class="text-center text-muted py-4">No products.</td></tr>@endforelse
    </tbody>
</table></div></div>
<div class="mt-3">{{ $products->links() }}</div>
@endsection
