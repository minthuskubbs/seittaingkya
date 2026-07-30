@extends('layouts.app')
@section('title', 'Treatment Report')
@section('content')
<form class="card mb-3"><div class="card-body row g-2 align-items-end">
    <div class="col-md-2"><label class="form-label">From</label>
        <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="form-control"></div>
    <div class="col-md-2"><label class="form-label">To</label>
        <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="form-control"></div>
    <div class="col-md-2"><label class="form-label">Doctor</label>
        <select name="doctor_id" class="form-select">
            <option value="">All</option>
            @foreach($doctors as $d)<option value="{{ $d->id }}" @selected(request('doctor_id')==$d->id)>{{ $d->name }}</option>@endforeach
        </select></div>
    <div class="col-md-2"><label class="form-label">Treatment Type</label>
        <select name="treatment_type_id" class="form-select">
            <option value="">All</option>
            @foreach($treatmentTypes as $tt)<option value="{{ $tt->id }}" @selected(request('treatment_type_id')==$tt->id)>{{ $tt->name }}</option>@endforeach
        </select></div>
    <div class="col-md-2"><label class="form-label">Patient Code</label>
        <input name="patient_code" value="{{ request('patient_code') }}" class="form-control" placeholder="e.g. P00003"></div>
    <div class="col-md-2 d-flex gap-2">
        <button class="btn btn-brand"><i class="bi bi-funnel"></i></button>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn btn-outline-secondary"><i class="bi bi-filetype-csv"></i></a>
    </div>
</div></form>

<div class="mb-3">
    <span class="badge bg-brand fs-6">Total: {{ $treatments->total() }} record{{ $treatments->total() == 1 ? '' : 's' }}</span>
    <span class="text-muted small ms-2">(matching the current filters)</span>
</div>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Date</th><th>Patient</th><th>Treatment Type</th><th>Doctor</th><th></th></tr></thead>
    <tbody>
    @forelse($treatments as $t)
        <tr>
            <td>{{ $t->treatment_date?->format('Y-m-d') }}</td>
            <td><a href="{{ route('patients.show', $t->patient_id) }}">{{ $t->patient->patient_code ?? '' }} — {{ $t->patient->name ?? '—' }}</a></td>
            <td>@forelse($t->treatmentTypes as $tt)<span class="badge badge-soft-brand">{{ $tt->name }}</span> @empty<span class="text-muted">—</span>@endforelse</td>
            <td>{{ $t->doctor->name ?? '—' }}</td>
            <td class="text-end"><a href="{{ route('treatments.show', $t) }}" class="btn btn-sm btn-light"><i class="bi bi-eye"></i></a></td>
        </tr>
    @empty<tr><td colspan="5" class="text-center text-muted py-4">No treatments match the filters.</td></tr>@endforelse
    </tbody>
</table></div></div>
<div class="mt-3">{{ $treatments->links() }}</div>
@endsection
