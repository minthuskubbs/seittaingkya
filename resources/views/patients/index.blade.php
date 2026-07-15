@extends('layouts.app')
@section('title', 'Patients')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <form class="d-flex" style="max-width:340px">
        <input name="q" value="{{ request('q') }}" class="form-control" placeholder="Search name, phone, code…">
        <button class="btn btn-outline-secondary ms-2"><i class="bi bi-search"></i></button>
    </form>
    @can('patients.manage')
        <a href="{{ route('patients.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> New Patient</a>
    @endcan
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Code</th><th>Name</th><th>Age</th><th>Phone</th><th>Doctor</th><th></th></tr></thead>
            <tbody>
            @forelse($patients as $p)
                <tr>
                    <td><span class="badge badge-soft-brand">{{ $p->patient_code }}</span></td>
                    <td><a href="{{ route('patients.show', $p) }}" class="fw-semibold">{{ $p->name }}</a>
                        @if($p->diabetes)<span class="badge bg-warning text-dark ms-1">Diabetes</span>@endif
                        @if($p->hypertension)<span class="badge bg-warning text-dark ms-1">Hypertension</span>@endif</td>
                    <td>{{ $p->age ?? '—' }}</td>
                    <td>{{ $p->phone ?? '—' }}</td>
                    <td>{{ $p->assignedDoctor->name ?? '—' }}</td>
                    <td class="text-end">
                        <a href="{{ route('patients.show', $p) }}" class="btn btn-sm btn-light"><i class="bi bi-eye"></i></a>
                        @can('patients.manage')
                        <a href="{{ route('patients.edit', $p) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No patients found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $patients->links() }}</div>
@endsection
