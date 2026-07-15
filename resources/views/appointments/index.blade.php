@extends('layouts.app')
@section('title', 'Appointments')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <form class="d-flex gap-2">
        <input type="date" name="date" value="{{ request('date') }}" class="form-control">
        <select name="status" class="form-select">
            <option value="">All statuses</option>
            @foreach(['booked','completed','cancelled','no_show'] as $s)
                <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
        <button class="btn btn-outline-secondary"><i class="bi bi-funnel"></i></button>
    </form>
    @can('appointments.manage')
        <a href="{{ route('appointments.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> New Appointment</a>
    @endcan
</div>

<div class="card"><div class="table-responsive">
    <table class="table align-middle mb-0">
        <thead><tr><th>No</th><th>Patient</th><th>Date/Time</th><th>Doctor</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse($appointments as $a)
            <tr>
                <td>{{ $a->appointment_no }}</td>
                <td><a href="{{ route('appointments.show', $a) }}">{{ $a->patient->name ?? '—' }}</a></td>
                <td>{{ $a->scheduled_at->format('Y-m-d H:i') }}</td>
                <td>{{ $a->doctor->name ?? '—' }}</td>
                <td><span class="badge bg-secondary text-capitalize">{{ str_replace('_',' ',$a->status) }}</span></td>
                <td class="text-end">
                    <a href="{{ route('appointments.show', $a) }}" class="btn btn-sm btn-brand"><i class="bi bi-clipboard2-pulse"></i> Treatment</a>
                    @can('appointments.manage')<a href="{{ route('appointments.edit', $a) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>@endcan
                </td>
            </tr>
        @empty<tr><td colspan="6" class="text-center text-muted py-4">No appointments.</td></tr>@endforelse
        </tbody>
    </table>
</div></div>
<div class="mt-3">{{ $appointments->links() }}</div>
@endsection
