@extends('layouts.app')
@section('title', 'Patient Report')
@section('content')
<div class="d-flex justify-content-end mb-3">
    <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn btn-outline-secondary"><i class="bi bi-filetype-csv"></i> Export CSV</a>
</div>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Code</th><th>Name</th><th>Age</th><th>Phone</th><th class="text-center">Appointments</th><th class="text-center">Treatments</th></tr></thead>
    <tbody>
    @foreach($patients as $p)
        <tr><td>{{ $p->patient_code }}</td><td>{{ $p->name }}</td><td>{{ $p->age ?? '—' }}</td><td>{{ $p->phone ?? '—' }}</td>
            <td class="text-center">{{ $p->appointments_count }}</td><td class="text-center">{{ $p->treatments_count }}</td></tr>
    @endforeach
    </tbody>
</table></div></div>
<div class="mt-3">{{ $patients->links() }}</div>
@endsection
