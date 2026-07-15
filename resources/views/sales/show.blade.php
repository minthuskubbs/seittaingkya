@extends('layouts.app')
@section('title', 'Sale '.$sale->sale_no)
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4>{{ $sale->sale_no }} <span class="badge badge-soft-brand">{{ \App\Models\SaleType::labelFor($sale->sale_type) }}</span></h4>
    <a href="{{ route('sales.receipt',$sale) }}" target="_blank" class="btn btn-sm btn-brand"><i class="bi bi-printer"></i> Print Receipt</a>
</div>
<div class="row g-3">
    <div class="col-lg-8"><div class="card"><div class="table-responsive"><table class="table mb-0">
        <thead><tr><th>Item</th><th class="text-center">Qty</th><th class="text-end">Price</th><th class="text-end">Total</th></tr></thead>
        <tbody>@foreach($sale->items as $it)
            <tr><td>{{ $it->name }}</td><td class="text-center">{{ $it->quantity }}</td>
                <td class="text-end">{{ money($it->price) }}</td><td class="text-end">{{ money($it->line_total) }}</td></tr>
        @endforeach</tbody>
        <tfoot>
            <tr><td colspan="3" class="text-end">Subtotal</td><td class="text-end">{{ money($sale->subtotal) }}</td></tr>
            <tr><td colspan="3" class="text-end">Discount</td><td class="text-end">{{ money($sale->discount) }}</td></tr>
            <tr class="fw-bold"><td colspan="3" class="text-end">Total</td><td class="text-end">{{ money($sale->total) }}</td></tr>
        </tfoot>
    </table></div></div></div>
    <div class="col-lg-4"><div class="card"><div class="card-body small">
        <div><strong>Customer:</strong> {{ $sale->patient->name ?? $sale->customer_name ?? '—' }}</div>
        <div><strong>Date:</strong> {{ $sale->sold_at?->format('Y-m-d H:i') }}</div>
        <div><strong>Payment:</strong> {{ $sale->payments->map(fn($p)=>\App\Models\Payment::METHODS[$p->method]??$p->method)->implode(', ') }}</div>
    </div></div></div>
</div>
@endsection
