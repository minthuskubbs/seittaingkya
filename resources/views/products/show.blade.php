@extends('layouts.app')
@section('title', 'Product · '.$product->name)
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4>{{ $product->name }} @if($product->isLowStock())<span class="badge bg-danger">Low Stock</span>@endif</h4>
    @can('inventory.manage')<a href="{{ route('products.edit',$product) }}" class="btn btn-sm btn-brand">Edit</a>@endcan
</div>
<div class="row g-3">
    <div class="col-md-4"><div class="card"><div class="card-body">
        <dl class="row mb-0 small">
            <dt class="col-6">Type</dt><dd class="col-6">{{ $product->type }}</dd>
            <dt class="col-6">Stock</dt><dd class="col-6">{{ $product->stock_qty }} {{ $product->unit }}</dd>
            <dt class="col-6">Cost</dt><dd class="col-6">{{ money($product->cost_price) }}</dd>
            <dt class="col-6">Sale</dt><dd class="col-6">{{ money($product->sale_price) }}</dd>
            <dt class="col-6">Low Alert</dt><dd class="col-6">&le; {{ $product->low_stock_threshold }}</dd>
            <dt class="col-6">Expiry</dt><dd class="col-6">{{ $product->expiry_date ? $product->expiry_date->format('Y-m-d') : '—' }}</dd>
            <dt class="col-6">Supplier</dt><dd class="col-6">{{ $product->supplier->name ?? '—' }}</dd>
        </dl>
    </div></div></div>
    <div class="col-md-8"><div class="card"><div class="card-header">Stock Movements</div>
        <div class="table-responsive"><table class="table mb-0 small">
            <thead><tr><th>Date</th><th>Type</th><th class="text-end">Qty</th><th class="text-end">Balance</th><th>Note</th></tr></thead>
            <tbody>
            @forelse($product->movements as $m)
                <tr><td>{{ $m->created_at->format('Y-m-d H:i') }}</td>
                    <td class="text-capitalize">{{ $m->type }}</td>
                    <td class="text-end {{ $m->quantity<0?'text-danger':'text-success' }}">{{ $m->quantity }}</td>
                    <td class="text-end">{{ $m->balance_after }}</td>
                    <td>{{ $m->reference ?? $m->note }}</td></tr>
            @empty<tr><td colspan="5" class="text-center text-muted">No movements.</td></tr>@endforelse
            </tbody>
        </table></div>
    </div></div>
</div>
@endsection
