@extends('layouts.app')
@section('title', 'Appointment Calendar')

@php
    use Illuminate\Support\Carbon;
    $today = Carbon::today();
    $statusColors = [
        'booked' => '#b30b0b',
        'completed' => '#198754',
        'cancelled' => '#6c757d',
        'no_show' => '#fd7e14',
    ];
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('appointments.calendar', array_filter(['month' => $month->copy()->subMonth()->format('Y-m'), 'clinic_id' => $clinicFilter])) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-chevron-left"></i></a>
        <h4 class="mb-0">{{ $month->format('F Y') }}</h4>
        <a href="{{ route('appointments.calendar', array_filter(['month' => $month->copy()->addMonth()->format('Y-m'), 'clinic_id' => $clinicFilter])) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-chevron-right"></i></a>
        <a href="{{ route('appointments.calendar', array_filter(['clinic_id' => $clinicFilter])) }}" class="btn btn-light btn-sm">Today</a>
    </div>
    <div class="d-flex gap-2">
        @if($clinics->isNotEmpty())
            <form method="GET">
                <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
                <select name="clinic_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All clinics</option>
                    @foreach($clinics as $c)
                        <option value="{{ $c->id }}" @selected((string)$clinicFilter === (string)$c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </form>
        @endif
        @can('appointments.manage')
            <a href="{{ route('appointments.create') }}" class="btn btn-brand btn-sm"><i class="bi bi-plus-lg"></i> New</a>
        @endcan
    </div>
</div>

<div class="card"><div class="card-body p-2 p-md-3">
    <div class="table-responsive">
        <table class="table table-bordered mb-0 calendar-table" style="table-layout:fixed; min-width:760px;">
            <thead>
                <tr class="text-center small">
                    @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d)
                        <th class="bg-light">{{ $d }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php $day = $gridStart->copy(); @endphp
                @while($day <= $gridEnd)
                    <tr>
                        @for($i = 0; $i < 7; $i++)
                            @php
                                $key = $day->toDateString();
                                $items = $appointments->get($key, collect());
                                $inMonth = $day->month === $month->month;
                                $isToday = $day->isSameDay($today);
                            @endphp
                            <td class="align-top p-1 {{ $inMonth ? '' : 'bg-light text-muted' }}" style="height:120px; vertical-align:top;">
                                <div class="d-flex justify-content-between align-items-center px-1">
                                    <span class="small fw-semibold {{ $isToday ? 'badge bg-brand' : '' }}">{{ $day->day }}</span>
                                    @if($items->count())<span class="badge badge-soft-brand">{{ $items->count() }}</span>@endif
                                </div>
                                <div style="max-height:90px; overflow:auto;">
                                    @foreach($items as $a)
                                        <a href="{{ route('appointments.show', $a) }}"
                                           class="d-block text-white text-decoration-none rounded px-1 mb-1 small text-truncate"
                                           style="background: {{ $statusColors[$a->status] ?? '#b30b0b' }};"
                                           title="{{ $a->scheduled_at->format('g:i A') }} · {{ $a->patient->name ?? '' }}{{ $a->doctor ? ' · Dr. '.$a->doctor->name : '' }} ({{ $a->status }})">
                                            {{ $a->scheduled_at->format('g:i A') }} {{ $a->patient->name ?? '—' }}
                                        </a>
                                    @endforeach
                                </div>
                            </td>
                            @php $day->addDay(); @endphp
                        @endfor
                    </tr>
                @endwhile
            </tbody>
        </table>
    </div>
    <div class="d-flex flex-wrap gap-3 mt-2 small">
        @foreach($statusColors as $status => $color)
            <span><span class="d-inline-block rounded" style="width:12px;height:12px;background:{{ $color }}"></span>
                {{ ucfirst(str_replace('_',' ',$status)) }}</span>
        @endforeach
    </div>
</div></div>
@endsection
