@extends('layouts.app')
@section('title', 'Doctors')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <form class="d-flex" style="max-width:340px">
        <input name="q" value="{{ request('q') }}" class="form-control" placeholder="Search doctor…">
        <button class="btn btn-outline-secondary ms-2"><i class="bi bi-search"></i></button>
    </form>
    <a href="{{ route('doctors.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> New Doctor</a>
</div>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Name</th><th>Clinic</th><th>Specialization</th><th class="text-end">1-Day Salary</th><th class="text-center">Commission %</th><th>Status</th><th></th></tr></thead>
    <tbody>
    @forelse($doctors as $d)
        <tr>
            <td class="fw-semibold">{{ $d->name }} <span class="text-muted small">{{ $d->phone }}</span></td>
            <td><span class="badge badge-soft-brand">{{ $d->clinic->name ?? '—' }}</span></td>
            <td>{{ $d->specialization ?? '—' }}</td>
            <td class="text-end">{{ money($d->one_day_salary) }}</td>
            <td class="text-center">{{ rtrim(rtrim(number_format($d->commission_percent,2),'0'),'.') }}%</td>
            <td>{!! $d->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td>
            <td class="text-end">
                <a href="{{ route('doctors.edit', $d) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                <form method="POST" action="{{ route('doctors.destroy', $d) }}" class="d-inline" onsubmit="return confirm('Delete doctor?')">
                    @csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                </form>
            </td>
        </tr>
    @empty<tr><td colspan="7" class="text-center text-muted py-4">No doctors yet.</td></tr>@endforelse
    </tbody>
</table></div></div>
<div class="mt-3">{{ $doctors->links() }}</div>
@endsection
