@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="card stat-card h-100"><div class="card-body d-flex align-items-center gap-3">
            <div class="icon bg-brand"><i class="bi bi-people"></i></div>
            <div><div class="text-muted small">Patients</div><div class="h4 mb-0">{{ $stats['patients'] }}</div></div>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card h-100"><div class="card-body d-flex align-items-center gap-3">
            <div class="icon bg-brand-2"><i class="bi bi-calendar-check"></i></div>
            <div><div class="text-muted small">Appointments Today</div><div class="h4 mb-0">{{ $stats['appointments_today'] }}</div></div>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card h-100"><div class="card-body d-flex align-items-center gap-3">
            <div class="icon bg-brand"><i class="bi bi-cart3"></i></div>
            <div><div class="text-muted small">Sales Today</div><div class="h4 mb-0">{{ $stats['sales_today'] }}</div></div>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card h-100"><div class="card-body d-flex align-items-center gap-3">
            <div class="icon bg-brand"><i class="bi bi-exclamation-triangle"></i></div>
            <div><div class="text-muted small">Low Stock</div><div class="h4 mb-0">{{ $stats['low_stock'] }}</div></div>
        </div></div>
    </div>
</div>

@if(!is_null($revenueToday))
<div class="card mb-3"><div class="card-body d-flex align-items-center justify-content-between">
    <div><div class="text-muted small">Revenue Collected Today</div><div class="h3 mb-0 text-brand">{{ money($revenueToday) }}</div></div>
    <i class="bi bi-graph-up-arrow display-5 text-brand opacity-25"></i>
</div></div>
@endif

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar-check"></i> Today's Appointments</span>
                @can('appointments.manage')<a href="{{ route('appointments.create') }}" class="btn btn-sm btn-brand">New</a>@endcan
            </div>
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead><tr><th>Time</th><th>Patient</th><th>Doctor</th><th>Status</th></tr></thead>
                    <tbody>
                    @forelse($todaysAppointments as $a)
                        <tr>
                            <td>{{ $a->scheduled_at->format('h:i A') }}</td>
                            <td><a href="{{ route('appointments.show', $a) }}">{{ $a->patient->name ?? '—' }}</a></td>
                            <td>{{ $a->doctor->name ?? '—' }}</td>
                            <td><span class="badge bg-secondary text-capitalize">{{ $a->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No appointments today.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-box-seam"></i> Low Stock Alerts</div>
            <ul class="list-group list-group-flush">
                @forelse($lowStockProducts as $p)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        {{ $p->name }}
                        <span class="badge bg-danger">{{ $p->stock_qty }} {{ $p->unit }}</span>
                    </li>
                @empty
                    <li class="list-group-item text-muted text-center py-4">All stock levels healthy.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
