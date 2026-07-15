@extends('layouts.app')
@section('title', 'Treatment List')
@section('content')
@include('reports._range')
<div class="d-flex justify-content-end mb-3">
    <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn btn-outline-secondary"><i class="bi bi-filetype-csv"></i> Export CSV</a>
</div>
<div class="card"><div class="card-header d-flex justify-content-between">
    <span>By Treatment ({{ $from->format('Y-m-d') }} → {{ $to->format('Y-m-d') }})</span>
    <strong>Total Income: {{ money($grandTotal) }}</strong></div>
    <div class="table-responsive"><table class="table align-middle mb-0">
        <thead><tr><th>Tx-Name</th><th class="text-center">Patients</th><th class="text-center">Treatments</th><th class="text-end">Total Income</th></tr></thead>
        <tbody>
        @forelse($rows as $r)
            <tr><td>{{ $r['name'] }}</td>
                <td class="text-center">{{ $r['patient_count'] }}</td>
                <td class="text-center">{{ $r['treatment_count'] }}</td>
                <td class="text-end">{{ money($r['total_income']) }}</td></tr>
        @empty<tr><td colspan="4" class="text-center text-muted py-4">No treatments in range.</td></tr>@endforelse
        </tbody>
    </table></div>
</div>
<p class="text-muted small mt-2">Patients = distinct patients per treatment type. Total Income = sum of treatment charges.</p>
@endsection
