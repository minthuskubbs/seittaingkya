@extends('layouts.app')
@section('title', 'Treatment #'.$treatment->id)

@section('content')
<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-0">Treatment #{{ $treatment->id }}</h4>
        <div class="text-muted small">
            <a href="{{ route('patients.show', $treatment->patient_id) }}">{{ $treatment->patient->name ?? '—' }}</a>
            @if($treatment->patient) ({{ $treatment->patient->patient_code }}) @endif
            · {{ $treatment->treatment_date?->format('Y-m-d') }}
            @if($treatment->doctor) · {{ $treatment->doctor->name }} @endif
        </div>
        <div class="mt-1">
            @forelse($treatment->treatmentTypes as $tt)
                <span class="badge badge-soft-brand">{{ $tt->name }}</span>
            @empty <span class="text-muted small">No treatment type</span> @endforelse
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('treatments.invoice', $treatment) }}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-printer"></i> Invoice</a>
        @can('clinical.manage')<a href="{{ route('treatments.edit', $treatment) }}" class="btn btn-sm btn-brand"><i class="bi bi-pencil"></i> Edit Charges</a>@endcan
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card mb-3"><div class="card-header">Charges</div>
            <div class="table-responsive"><table class="table mb-0">
                <tbody>
                @foreach($treatment->treatmentTypes as $tt)
                    <tr><td>{{ $tt->name }}@if($tt->pivot->qty > 1) ({{ $tt->pivot->qty }} × {{ money($tt->pivot->unit_price) }})@endif</td><td class="text-end">{{ money($tt->pivot->line_total) }}</td></tr>
                @endforeach
                @foreach($treatment->fees as $f)
                    <tr><td>{{ $f->name }}@if($f->is_foc) <span class="badge bg-info">FOC</span>@endif</td><td class="text-end">{{ money($f->line_total) }}</td></tr>
                @endforeach
                @if($treatment->extraction_qty > 0)
                    <tr><td>Tooth Extraction @if($treatment->extractionType) — {{ $treatment->extractionType->name }}@endif ({{ $treatment->extraction_qty }} × {{ money($treatment->extraction_price) }})</td><td class="text-end">{{ money($treatment->extractionTotal()) }}</td></tr>
                @endif
                @if($treatment->implant_qty > 0)
                    <tr><td>Tooth Implant @if($treatment->implantType) — {{ $treatment->implantType->name }}@endif ({{ $treatment->implant_qty }} × {{ money($treatment->implant_price) }})</td><td class="text-end">{{ money($treatment->implantTotal()) }}</td></tr>
                @endif
                @if($treatment->surgery_charge > 0)<tr><td>Surgery</td><td class="text-end">{{ money($treatment->surgery_charge) }}</td></tr>@endif
                @if($treatment->denture_charge > 0)<tr><td>Denture{{ $treatment->dentureType ? ' — '.$treatment->dentureType->name : '' }}</td><td class="text-end">{{ money($treatment->denture_charge) }}</td></tr>@endif
                @if($treatment->additional_charge > 0)<tr><td>Additional</td><td class="text-end">{{ money($treatment->additional_charge) }}</td></tr>@endif
                </tbody>
                <tfoot>
                    <tr class="fw-semibold"><td class="text-end">Treatment Charges</td><td class="text-end">{{ money($treatment->chargesTotal()) }}</td></tr>
                    @if($treatment->sales->count())
                        <tr><td class="text-end">Medicine Sales</td><td class="text-end">{{ money($treatment->sales->sum('total')) }}</td></tr>
                    @endif
                    @if($treatment->discountAmount() > 0)
                        <tr class="text-danger"><td class="text-end">Discount @if($treatment->discount_type==='percent')({{ rtrim(rtrim(number_format($treatment->discount_value,2),'0'),'.') }}%)@endif</td><td class="text-end">- {{ money($treatment->discountAmount()) }}</td></tr>
                    @endif
                    <tr class="fw-bold table-light"><td class="text-end">Invoice Total</td><td class="text-end">{{ money($treatment->invoiceTotal()) }}</td></tr>
                </tfoot>
            </table></div>
        </div>

        @if($treatment->sales->count())
        <div class="card mb-3"><div class="card-header"><i class="bi bi-capsule"></i> Linked Medicine Sales</div>
            <ul class="list-group list-group-flush">
                @foreach($treatment->sales as $s)
                    <li class="list-group-item d-flex justify-content-between">
                        <a href="{{ route('sales.show', $s) }}">{{ $s->sale_no }}</a><span>{{ money($s->total) }}</span></li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>

    <div class="col-lg-5">
        <div class="card mb-3"><div class="card-body">
            @php $paid = $treatment->paidAmount(); $balance = $treatment->balance(); @endphp
            <div class="d-flex justify-content-between"><span class="text-muted">Invoice Total</span><strong>{{ money($treatment->invoiceTotal()) }}</strong></div>
            <div class="d-flex justify-content-between"><span class="text-muted">Paid</span><span class="text-success">{{ money($paid) }}</span></div>
            <div class="d-flex justify-content-between"><span class="text-muted">Balance</span><strong class="{{ $balance>0?'text-danger':'text-success' }}">{{ money($balance) }}</strong></div>
        </div></div>

        @can('billing.manage')
        <div class="card mb-3"><div class="card-header"><i class="bi bi-cash-coin"></i> Record Payment</div><div class="card-body">
            <form method="POST" action="{{ route('treatments.payments.store', $treatment) }}">
                @csrf
                <div class="mb-2">
                    <input type="number" step="0.01" min="0.01" max="{{ $balance > 0 ? $balance : '' }}" name="amount" class="form-control @error('amount') is-invalid @enderror" placeholder="Amount" value="{{ old('amount', $balance > 0 ? $balance : '') }}" required>
                    @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-2"><select name="method" class="form-select">
                    @foreach(\App\Models\Payment::METHODS as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                </select></div>
                <div class="mb-2"><input type="date" name="paid_at" class="form-control" value="{{ date('Y-m-d') }}" required></div>
                <button class="btn btn-sm btn-brand w-100">Add Payment</button>
            </form>
        </div></div>
        @endcan

        <div class="card"><div class="card-header">Payment History</div>
            <ul class="list-group list-group-flush">
                @forelse($treatment->payments as $pay)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>{{ $pay->paid_at?->format('Y-m-d') }} · {{ \App\Models\Payment::METHODS[$pay->method] ?? $pay->method }}</span>
                        <span class="d-flex align-items-center gap-2">
                            <strong>{{ money($pay->amount) }}</strong>
                            @can('billing.manage')
                            <form method="POST" action="{{ route('treatments.payments.destroy', [$treatment, $pay]) }}" onsubmit="return confirm('Void this payment of {{ money($pay->amount) }}?')" class="m-0">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm text-danger p-0" title="Void payment"><i class="bi bi-x-circle"></i></button>
                            </form>
                            @endcan
                        </span></li>
                @empty<li class="list-group-item text-muted text-center">No payments.</li>@endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
