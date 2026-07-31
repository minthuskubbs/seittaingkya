@extends('layouts.app')
@section('title', 'Fees & Charges')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="text-muted small">Fees are set <strong>per clinic</strong>. New prices apply to <strong>new</strong> appointments only — existing appointments keep their locked price.</div>
    <div class="d-flex gap-2">
        @if($clinics->isNotEmpty())
        <form><select name="clinic_id" class="form-select" onchange="this.form.submit()">
            <option value="">All clinics</option>
            @foreach($clinics as $c)<option value="{{ $c->id }}" @selected((string)$clinicFilter===(string)$c->id)>{{ $c->name }}</option>@endforeach
        </select></form>
        @endif
        <a href="{{ route('fees.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> New Fee</a>
    </div>
</div>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Name</th>@if($clinics->isNotEmpty())<th>Clinic</th>@endif<th>Group</th><th>Category</th><th class="text-end">Price</th><th>Status</th><th></th></tr></thead>
    <tbody>
    @forelse($fees as $fee)
        <tr>
            <td>{{ $fee->name }} @if($fee->is_foc)<span class="badge bg-info">FOC</span>@endif</td>
            @if($clinics->isNotEmpty())<td><span class="badge badge-soft-brand">{{ $fee->clinic->name ?? '—' }}</span></td>@endif
            <td><span class="badge {{ $fee->fee_group === 'service' ? 'bg-secondary' : 'bg-brand' }}">{{ \App\Models\Fee::GROUPS[$fee->fee_group] ?? $fee->fee_group }}</span></td>
            <td>{{ \App\Models\Fee::CATEGORIES[$fee->category] ?? $fee->category }}</td>
            <td class="text-end">{{ money($fee->effectivePrice()) }}</td>
            <td>{!! $fee->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td>
            <td class="text-end">
                <a href="{{ route('fees.edit', $fee) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                <form method="POST" action="{{ route('fees.destroy', $fee) }}" class="d-inline" onsubmit="return confirm('Delete fee?')">
                    @csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                </form>
            </td>
        </tr>
    @empty<tr><td colspan="{{ $clinics->isNotEmpty() ? 7 : 6 }}" class="text-center text-muted py-4">No fees yet.</td></tr>@endforelse
    </tbody>
</table></div></div>
<div class="mt-3">{{ $fees->links() }}</div>
@endsection
