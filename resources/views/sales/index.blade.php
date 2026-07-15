@extends('layouts.app')
@section('title', 'Medicine Sales')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <form class="d-flex gap-2">
        <input type="date" name="date" value="{{ request('date') }}" class="form-control">
        <select name="type" class="form-select"><option value="">All</option>
            @foreach(\App\Models\SaleType::active()->get() as $t)<option value="{{ $t->slug }}" @selected(request('type')===$t->slug)>{{ $t->name }}</option>@endforeach
        </select>
        <button class="btn btn-outline-secondary"><i class="bi bi-funnel"></i></button>
    </form>
    <a href="{{ route('sales.create') }}" class="btn btn-brand"><i class="bi bi-cart-plus"></i> New Sale</a>
</div>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>No</th><th>Type</th><th>Customer</th><th>Date</th><th class="text-end">Total</th><th></th></tr></thead>
    <tbody>
    @forelse($sales as $s)
        <tr><td>{{ $s->sale_no }}</td><td>{{ \App\Models\SaleType::labelFor($s->sale_type) }}</td>
            <td>{{ $s->patient->name ?? $s->customer_name ?? '—' }}</td>
            <td>{{ $s->sold_at?->format('Y-m-d H:i') }}</td>
            <td class="text-end">{{ money($s->total) }}</td>
            <td class="text-end">
                <a href="{{ route('sales.show',$s) }}" class="btn btn-sm btn-light"><i class="bi bi-eye"></i></a>
                <a href="{{ route('sales.receipt',$s) }}" target="_blank" class="btn btn-sm btn-light"><i class="bi bi-printer"></i></a>
            </td></tr>
    @empty<tr><td colspan="6" class="text-center text-muted py-4">No sales.</td></tr>@endforelse
    </tbody>
</table></div></div>
<div class="mt-3">{{ $sales->links() }}</div>
@endsection
