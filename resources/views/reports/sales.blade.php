@extends('layouts.app')
@section('title', 'Sales Report')
@section('content')
@include('reports._range')
<div class="d-flex justify-content-end gap-2 mb-3">
    <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn btn-outline-secondary"><i class="bi bi-filetype-csv"></i> Daily CSV</a>
    <a href="{{ request()->fullUrlWithQuery(['export' => 'items']) }}" class="btn btn-outline-secondary"><i class="bi bi-filetype-csv"></i> Items CSV</a>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card h-100"><div class="card-header d-flex justify-content-between">
            <span>Daily Sales</span><strong>Total: {{ money($grandTotal) }}</strong></div>
            <div class="table-responsive"><table class="table align-middle mb-0">
                <thead><tr><th>Date</th><th class="text-center">Transactions</th><th class="text-end">Total</th></tr></thead>
                <tbody>
                @forelse($daily as $date => $row)
                    <tr><td>{{ $date }}</td><td class="text-center">{{ $row['count'] }}</td><td class="text-end">{{ money($row['total']) }}</td></tr>
                @empty<tr><td colspan="3" class="text-center text-muted py-4">No sales in range.</td></tr>@endforelse
                </tbody>
            </table></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100"><div class="card-header"><i class="bi bi-box-seam"></i> Items Sold</div>
            <div class="table-responsive"><table class="table align-middle mb-0">
                <thead><tr><th>Product</th><th class="text-center">Qty Sold</th><th class="text-end">Total</th></tr></thead>
                <tbody>
                @forelse($items as $it)
                    <tr><td>{{ $it['name'] }}</td><td class="text-center">{{ $it['quantity'] }}</td><td class="text-end">{{ money($it['total']) }}</td></tr>
                @empty<tr><td colspan="3" class="text-center text-muted py-4">No items sold in range.</td></tr>@endforelse
                </tbody>
                @if($items->count())
                <tfoot><tr class="fw-bold"><td>Total</td><td class="text-center">{{ $items->sum('quantity') }}</td><td class="text-end">{{ money($items->sum('total')) }}</td></tr></tfoot>
                @endif
            </table></div>
        </div>
    </div>
</div>
@endsection
