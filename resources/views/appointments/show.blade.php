@extends('layouts.app')
@section('title', 'Appointment '.$appointment->appointment_no)

@section('content')
<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-0">{{ $appointment->appointment_no }}
            <span class="badge bg-secondary text-capitalize">{{ str_replace('_',' ',$appointment->status) }}</span></h4>
        <div class="text-muted small">
            @if($appointment->patient)
                <a href="{{ route('patients.show', $appointment->patient_id) }}">{{ $appointment->patient->name }}</a>
            @else
                <span class="text-danger" title="Patient belongs to another clinic">Patient unavailable</span>
            @endif
            · {{ $appointment->scheduled_at->format('Y-m-d H:i') }}
            @if($appointment->doctor) · {{ $appointment->doctor->name }} @endif
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('appointments.invoice', $appointment) }}" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="bi bi-printer"></i> Invoice</a>
        @can('appointments.manage')
            <a href="{{ route('appointments.create') }}?parent={{ $appointment->id }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-repeat"></i> Follow-up</a>
            <a href="{{ route('appointments.edit', $appointment) }}" class="btn btn-sm btn-brand"><i class="bi bi-pencil"></i> Edit</a>
        @endcan
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card mb-3"><div class="card-header">Charges</div>
            <div class="table-responsive"><table class="table mb-0">
                <thead><tr><th>Fee</th><th class="text-capitalize">Category</th><th class="text-end">Price</th></tr></thead>
                <tbody>
                @forelse($appointment->fees as $f)
                    <tr><td>{{ $f->name }} @if($f->is_foc)<span class="badge bg-info">FOC</span>@endif</td>
                        <td class="text-capitalize">{{ $f->category }}</td>
                        <td class="text-end">{{ money($f->line_total) }}</td></tr>
                @empty<tr><td colspan="3" class="text-center text-muted">No charges.</td></tr>@endforelse
                </tbody>
                <tfoot><tr class="fw-bold"><td colspan="2" class="text-end">Total</td><td class="text-end">{{ money($appointment->total_amount) }}</td></tr></tfoot>
            </table></div>
        </div>

        @can('doctor_notes.manage')
        <div class="card mb-3"><div class="card-header"><i class="bi bi-journal-medical"></i> Doctor Note</div><div class="card-body">
            <form method="POST" action="{{ route('appointments.doctor_note', $appointment) }}">
                @csrf @method('PUT')
                <textarea name="doctor_note" class="form-control mb-2" rows="4">{{ $appointment->doctor_note }}</textarea>
                <button class="btn btn-sm btn-brand">Save Note</button>
            </form>
        </div></div>
        @else
        <div class="card mb-3"><div class="card-header"><i class="bi bi-journal-medical"></i> Doctor Note</div>
            <div class="card-body">{{ $appointment->doctor_note ?: 'No note.' }}</div></div>
        @endcan
    </div>

    <div class="col-lg-5">
        <div class="card mb-3"><div class="card-body">
            @php $paid = $appointment->paidAmount(); $balance = $appointment->balance(); @endphp
            <div class="d-flex justify-content-between"><span class="text-muted">Total</span><strong>{{ money($appointment->total_amount) }}</strong></div>
            @if($appointment->denture_charge > 0)
            <div class="d-flex justify-content-between"><span class="text-muted">Denture Charge</span><span>{{ money($appointment->denture_charge) }}</span></div>
            @endif
            <div class="d-flex justify-content-between"><span class="text-muted">Paid</span><span class="text-success">{{ money($paid) }}</span></div>
            <div class="d-flex justify-content-between"><span class="text-muted">Balance</span><strong class="{{ $balance>0?'text-danger':'text-success' }}">{{ money($balance) }}</strong></div>
        </div></div>

        @can('billing.manage')
        <div class="card mb-3"><div class="card-header"><i class="bi bi-cash-coin"></i> Record Payment</div><div class="card-body">
            <form method="POST" action="{{ route('appointments.payments.store', $appointment) }}">
                @csrf
                <div class="mb-2"><input type="number" step="0.01" name="amount" class="form-control" placeholder="Amount" value="{{ $balance > 0 ? $balance : '' }}" required></div>
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
                @forelse($appointment->payments as $pay)
                    <li class="list-group-item d-flex justify-content-between">
                        <span>{{ $pay->paid_at?->format('Y-m-d') }} · {{ \App\Models\Payment::METHODS[$pay->method] ?? $pay->method }}</span>
                        <strong>{{ money($pay->amount) }}</strong>
                    </li>
                @empty<li class="list-group-item text-muted text-center">No payments.</li>@endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
