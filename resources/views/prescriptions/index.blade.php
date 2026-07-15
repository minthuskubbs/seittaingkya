@extends('layouts.app')
@section('title', 'Prescriptions')
@section('content')
<div class="d-flex justify-content-end mb-3">@can('clinical.manage')<a href="{{ route('prescriptions.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> New Prescription</a>@endcan</div>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Date</th><th>Patient</th><th>Doctor</th><th>Items</th><th></th></tr></thead>
    <tbody>
    @forelse($prescriptions as $pr)
        <tr><td>{{ $pr->prescribed_date?->format('Y-m-d') }}</td>
            <td>{{ $pr->patient->name ?? '—' }}</td><td>{{ $pr->doctor->name ?? '—' }}</td>
            <td>{{ $pr->items->count() }}</td>
            <td class="text-end"><a href="{{ route('prescriptions.show',$pr) }}" class="btn btn-sm btn-light"><i class="bi bi-eye"></i></a></td></tr>
    @empty<tr><td colspan="5" class="text-center text-muted py-4">No prescriptions.</td></tr>@endforelse
    </tbody>
</table></div></div>
<div class="mt-3">{{ $prescriptions->links() }}</div>
@endsection
