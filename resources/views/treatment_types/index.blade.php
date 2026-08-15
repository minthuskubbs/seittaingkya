@extends('layouts.app')
@section('title', 'Treatment Types')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="text-muted small">Per-clinic pricing. Selectable (multi-check) on the treatment form.</div>
    <div class="d-flex gap-2">
        <form method="GET" class="d-flex">
            <select name="clinic_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All clinics</option>
                @foreach($clinics as $c)<option value="{{ $c->id }}" @selected((string)$clinicFilter===(string)$c->id)>{{ $c->name }}</option>@endforeach
            </select>
        </form>
        <a href="{{ route('treatment-types.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> New Treatment Type</a>
    </div>
</div>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Clinic</th><th>Name</th><th class="text-end">Price</th><th class="text-center">Qty?</th><th class="text-center">Order</th><th>Status</th><th></th></tr></thead>
    <tbody>
    @forelse($treatmentTypes as $t)
        <tr>
            <td><span class="badge badge-soft-brand">{{ $t->clinic->name ?? '—' }}</span></td>
            <td>{{ $t->name }}</td>
            <td class="text-end">{{ money($t->price) }}</td>
            <td class="text-center">{!! $t->require_qty ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' !!}</td>
            <td class="text-center">{{ $t->sort_order }}</td>
            <td>{!! $t->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td>
            <td class="text-end">
                <a href="{{ route('treatment-types.edit', $t) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                <form method="POST" action="{{ route('treatment-types.destroy', $t) }}" class="d-inline" onsubmit="return confirm('Delete?')">
                    @csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                </form>
            </td>
        </tr>
    @empty<tr><td colspan="7" class="text-center text-muted py-4">No treatment types.</td></tr>@endforelse
    </tbody>
</table></div></div>
<div class="mt-3">{{ $treatmentTypes->links() }}</div>
@endsection
