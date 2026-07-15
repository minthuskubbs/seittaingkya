@extends('layouts.app')
@section('title', 'Prescription')
@section('content')
<div class="card"><div class="card-body">
    <div class="d-flex justify-content-between mb-3">
        <div><h5 class="mb-0">{{ $prescription->patient->name }}</h5>
            <div class="text-muted small">{{ $prescription->prescribed_date?->format('Y-m-d') }} · {{ $prescription->doctor->name ?? '—' }}</div></div>
        <button class="btn btn-sm btn-outline-secondary no-print" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
    </div>
    <table class="table">
        <thead><tr><th>Medicine</th><th>Dosage</th><th>Frequency</th><th>Duration</th><th>Instructions</th></tr></thead>
        <tbody>@foreach($prescription->items as $it)
            <tr><td>{{ $it->medicine_name }}</td><td>{{ $it->dosage }}</td><td>{{ $it->frequency }}</td>
                <td>{{ $it->duration }}</td><td>{{ $it->instructions }}</td></tr>
        @endforeach</tbody>
    </table>
    @if($prescription->notes)<div class="alert alert-light">{{ $prescription->notes }}</div>@endif
</div></div>
@endsection
