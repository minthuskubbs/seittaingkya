@extends('layouts.app')
@section('title', $canManage ? 'Stock In / Out' : 'Stock Out')
@section('content')
<div class="row g-3">
    <div class="col-lg-5">
        <div class="card"><div class="card-header">{{ $canManage ? 'Record Stock Movement' : 'Record Stock Out' }}</div><div class="card-body">
            <form method="POST" action="{{ route('stock.store') }}">
                @csrf
                <div class="mb-2"><label class="form-label">Product</label>
                    <select name="product_id" class="form-select" required>
                        <option value="">— Select —</option>
                        @foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}@if($clinics->isNotEmpty()) · {{ $p->clinic->name ?? '' }}@endif (stock {{ $p->stock_qty }})</option>@endforeach
                    </select></div>
                @if($canManage)
                    <div class="mb-2"><label class="form-label">Direction</label>
                        <select name="direction" class="form-select">
                            <option value="in">Stock In</option>
                            <option value="out">Stock Out</option>
                            <option value="adjustment">Adjustment</option>
                        </select></div>
                @else
                    <input type="hidden" name="direction" value="out">
                    <div class="alert alert-light py-2 small mb-2"><i class="bi bi-box-arrow-up"></i> You can record <strong>stock out</strong> only.</div>
                @endif
                <div class="mb-2"><label class="form-label">Quantity</label>
                    <input type="number" name="quantity" class="form-control" min="1" required></div>
                <div class="mb-2"><label class="form-label">Reference</label>
                    <input name="reference" class="form-control" placeholder="e.g. reason / ref no"></div>
                <div class="mb-3"><label class="form-label">Note</label>
                    <textarea name="note" class="form-control" rows="2"></textarea></div>
                <button class="btn btn-brand w-100">Record</button>
            </form>
        </div></div>

        <div class="card mt-3"><div class="card-header">Current Stock</div>
            <div class="table-responsive"><table class="table mb-0 align-middle">
                <thead><tr><th>Product</th>@if($clinics->isNotEmpty())<th>Clinic</th>@endif<th class="text-center">Stock</th></tr></thead>
                <tbody>
                @foreach($products as $p)
                    <tr class="{{ $p->isLowStock()?'table-warning':'' }}">
                        <td>{{ $p->name }}</td>
                        @if($clinics->isNotEmpty())<td><span class="badge badge-soft-brand">{{ $p->clinic->name ?? '—' }}</span></td>@endif
                        <td class="text-center">{{ $p->stock_qty }} {{ $p->unit }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card"><div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-clock-history"></i> Recent Stock Movements</span>
            @if($clinics->isNotEmpty())
            <form><select name="clinic_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All clinics</option>
                @foreach($clinics as $c)<option value="{{ $c->id }}" @selected((string)$clinicFilter===(string)$c->id)>{{ $c->name }}</option>@endforeach
            </select></form>
            @endif
        </div>
            <div class="table-responsive"><table class="table align-middle mb-0 small">
                <thead><tr><th>Date / Time</th><th>Product</th><th>Type</th><th class="text-end">Qty</th><th class="text-end">Balance</th><th>By</th></tr></thead>
                <tbody>
                @forelse($movements as $m)
                    <tr>
                        <td>{{ $m->created_at->format('Y-m-d H:i') }}</td>
                        <td>{{ $m->product->name ?? '—' }}</td>
                        <td><span class="badge {{ $m->type==='out'?'bg-danger':($m->type==='in'?'bg-success':'bg-secondary') }} text-capitalize">{{ $m->type }}</span></td>
                        <td class="text-end {{ $m->quantity<0?'text-danger':'text-success' }}">{{ $m->quantity }}</td>
                        <td class="text-end">{{ $m->balance_after }}</td>
                        <td>{{ $m->creator->name ?? 'System' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No movements yet.</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </div>
    </div>
</div>
@endsection
