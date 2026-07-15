@extends('layouts.app')
@section('title', 'Staff Payroll')
@section('content')
<form class="card mb-3" method="GET"><div class="card-body row g-2 align-items-end">
    @if($clinics->isNotEmpty())
    <div class="col-md-4"><label class="form-label">Clinic</label>
        <select name="clinic_id" class="form-select" onchange="this.form.submit()">
            <option value="">Select clinic…</option>
            @foreach($clinics as $c)<option value="{{ $c->id }}" @selected((string)$clinicFilter===(string)$c->id)>{{ $c->name }}</option>@endforeach
        </select></div>
    @endif
    <div class="col-md-4"><label class="form-label">Month</label>
        <input type="month" name="month" value="{{ $month->format('Y-m') }}" class="form-control" onchange="this.form.submit()"></div>
</div></form>

<form method="POST" action="{{ route('finance.staff_payroll.store') }}">
    @csrf
    <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
    <input type="hidden" name="clinic_id" value="{{ $clinicFilter }}">

    <div class="card"><div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-wallet2"></i> Staff Payroll — {{ $month->format('F Y') }}</span>
        <button class="btn btn-sm btn-brand"><i class="bi bi-check-lg"></i> Save Payroll</button>
    </div>
    <div class="table-responsive"><table class="table align-middle mb-0" style="min-width:900px">
        <thead><tr>
            <th>Staff</th>
            <th class="text-end" style="width:150px">Basic Salary</th>
            <th class="text-end" style="width:130px">Bonus</th>
            <th class="text-end" style="width:150px">Attendance Allow.</th>
            <th class="text-end" style="width:160px">Transportation Allow.</th>
            <th class="text-end" style="width:150px">Total</th>
        </tr></thead>
        <tbody>
        @forelse($rows as $r)
            @php $pr = $r['payroll']; $st = $r['staff']; @endphp
            {{-- Saved month wins; otherwise pre-fill from the staff's default pay. --}}
            <tr x-data="staffRow({
                    basic: {{ $pr->basic_salary ?? $st->basic_salary ?? 0 }},
                    bonus: {{ $pr->bonus ?? 0 }},
                    attendance: {{ $pr->attendance_allowance ?? $st->attendance_allowance ?? 0 }},
                    transport: {{ $pr->transportation_allowance ?? $st->transportation_allowance ?? 0 }}
                })">
                <td>{{ $r['staff']->name }}
                    <div class="text-muted small">{{ $r['staff']->position }}</div>
                    @if($pr)<span class="badge bg-success" title="Saved">saved</span>@endif</td>
                <td><input type="number" min="0" step="0.01" class="form-control form-control-sm text-end"
                        name="rows[{{ $r['staff']->id }}][basic_salary]" x-model.number="basic" @input="calc()"></td>
                <td><input type="number" min="0" step="0.01" class="form-control form-control-sm text-end"
                        name="rows[{{ $r['staff']->id }}][bonus]" x-model.number="bonus" @input="calc()"></td>
                <td><input type="number" min="0" step="0.01" class="form-control form-control-sm text-end"
                        name="rows[{{ $r['staff']->id }}][attendance_allowance]" x-model.number="attendance" @input="calc()"></td>
                <td><input type="number" min="0" step="0.01" class="form-control form-control-sm text-end"
                        name="rows[{{ $r['staff']->id }}][transportation_allowance]" x-model.number="transport" @input="calc()"></td>
                <td class="text-end fw-semibold" x-text="fmt(total)"></td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4">No active staff for this clinic. Add staff first.</td></tr>
        @endforelse
        </tbody>
    </table></div></div>

    @if(count($rows))
    <div class="alert alert-light small mt-3">Total = Basic Salary + Bonus + Attendance Allowance + Transportation Allowance.</div>
    @endif
</form>

@push('scripts')
<script>
function staffRow(d) {
  return {
    basic: d.basic, bonus: d.bonus, attendance: d.attendance, transport: d.transport, total: 0,
    init() { this.calc(); },
    fmt(n) { return new Intl.NumberFormat().format(Math.round(n||0)) + ' MMK'; },
    calc() { this.total = (this.basic||0) + (this.bonus||0) + (this.attendance||0) + (this.transport||0); }
  }
}
</script>
@endpush
@endsection
