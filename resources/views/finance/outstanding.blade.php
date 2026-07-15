@extends('layouts.app')
@section('title', 'Outstanding Payments')
@section('content')
<form class="card mb-3"><div class="card-body row g-2 align-items-end">
    <div class="col-md-4"><label class="form-label">Clinic</label>
        <select name="clinic_id" class="form-select"><option value="">All clinics</option>
            @foreach($clinics as $c)<option value="{{ $c->id }}" @selected($clinicId==$c->id)>{{ $c->name }}</option>@endforeach</select></div>
    <div class="col-md-3"><button class="btn btn-brand w-100">Filter</button></div>
    <div class="col-md-5 text-md-end"><span class="text-muted">Total Outstanding:</span> <strong class="text-danger">{{ money($totalOutstanding) }}</strong></div>
</div></form>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Treatment</th><th>Patient</th><th class="text-end">Total</th><th class="text-end">Paid</th><th class="text-end">Balance</th><th></th></tr></thead>
    <tbody>
    @forelse($treatments as $t)
        <tr><td>TX-{{ $t->id }}</td><td>{{ $t->patient->name ?? '—' }}</td>
            <td class="text-end">{{ money($t->invoiceTotal()) }}</td><td class="text-end">{{ money($t->paidAmount()) }}</td>
            <td class="text-end text-danger">{{ money($t->balance()) }}</td>
            <td class="text-end"><a href="{{ route('treatments.show',$t) }}" class="btn btn-sm btn-light"><i class="bi bi-eye"></i></a></td></tr>
    @empty<tr><td colspan="6" class="text-center text-muted py-4">No outstanding balances.</td></tr>@endforelse
    </tbody>
</table></div></div>
@endsection
