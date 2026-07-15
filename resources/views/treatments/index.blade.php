@extends('layouts.app')
@section('title', 'Treatments')
@section('content')
<div class="d-flex justify-content-end mb-3">@can('clinical.manage')<a href="{{ route('treatments.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> New Treatment</a>@endcan</div>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Patient</th><th>Date</th><th>Procedure</th><th>Doctor</th><th class="text-end">Total</th><th></th></tr></thead>
    <tbody>
    @forelse($treatments as $t)
        <tr>
            <td><a href="{{ route('treatments.show',$t) }}">{{ $t->patient->patient_code ?? '' }} — {{ $t->patient->name ?? '—' }}</a></td>
            <td>{{ $t->treatment_date?->format('Y-m-d') }}</td>
            <td>{{ $t->procedure->name ?? '—' }}</td><td>{{ $t->doctor->name ?? '—' }}</td>
            <td class="text-end">{{ money($t->total_amount) }}</td>
            <td class="text-end">
                <a href="{{ route('treatments.show',$t) }}" class="btn btn-sm btn-light"><i class="bi bi-eye"></i></a>
                @can('clinical.manage')<a href="{{ route('treatments.edit',$t) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>@endcan</td></tr>
    @empty<tr><td colspan="6" class="text-center text-muted py-4">No treatments.</td></tr>@endforelse
    </tbody>
</table></div></div>
<div class="mt-3">{{ $treatments->links() }}</div>
@endsection
