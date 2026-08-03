@extends('layouts.app')
@section('title', 'Doctor Payroll')
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

<form method="POST" action="{{ route('finance.doctor_payroll.store') }}" x-data="payroll()">
    @csrf
    <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
    <input type="hidden" name="clinic_id" value="{{ $clinicFilter }}">

    <div class="card"><div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clipboard2-data"></i> {{ $month->format('F Y') }} — enter days worked, totals compute automatically</span>
        <button class="btn btn-sm btn-brand"><i class="bi bi-check-lg"></i> Save Payroll</button>
    </div>
    <div class="table-responsive"><table class="table align-middle mb-0" style="min-width:900px">
        <thead><tr>
            <th>Doctor</th>
            <th class="text-end">Total Income <span class="text-muted fw-normal">(Treatment + Lab)</span></th>
            <th class="text-end">Lab Fees <span class="text-muted fw-normal">(Denture)</span></th>
            <th class="text-center" style="width:90px">1-Day Salary</th>
            <th class="text-center" style="width:90px">Days</th>
            <th class="text-end">Basic Salary</th>
            <th class="text-center">Comm %</th>
            <th class="text-end">Commission</th>
            <th class="text-end">Total</th>
        </tr></thead>
        <tbody>
        @forelse($rows as $i => $r)
            <tr x-data="row({
                    income: {{ $r['total_income'] }},
                    denture: {{ $r['denture_total'] }},
                    oneDay: {{ $r['one_day_salary'] }},
                    pct: {{ $r['commission_percent'] }},
                    days: {{ $r['days_worked'] }}
                })">
                <td>{{ $r['doctor']->name }}
                    @if($r['saved'])<span class="badge bg-success ms-1" title="Saved">saved</span>@endif</td>
                <td class="text-end">{{ money($r['total_income']) }}</td>
                <td class="text-end">{{ money($r['denture_total']) }}</td>
                <td class="text-center">{{ number_format($r['one_day_salary']) }}</td>
                <td class="text-center">
                    <input type="number" min="0" max="31" class="form-control form-control-sm text-center"
                           style="width:70px;margin:auto" name="days[{{ $r['doctor']->id }}]" x-model.number="days" @input="calc()">
                </td>
                <td class="text-end" x-text="fmt(basic)"></td>
                <td class="text-center">{{ rtrim(rtrim(number_format($r['commission_percent'],2),'0'),'.') }}%</td>
                <td class="text-end" x-text="fmt(commission)"></td>
                <td class="text-end fw-semibold" x-text="fmt(total)"></td>
            </tr>
        @empty
            <tr><td colspan="9" class="text-center text-muted py-4">
                No active doctors for this clinic. Add doctors first, then set their one-day salary &amp; commission %.
            </td></tr>
        @endforelse
        </tbody>
    </table></div></div>

    @if(count($rows))
    <div class="alert alert-light small mt-3">
        <strong>How it's calculated:</strong>
        Total Income = Treatment Fees + Lab (Denture) · Basic Salary = 1-Day Salary × Days ·
        Commission = (Total Income − 2 × Basic Salary − Lab Fees) × Commission % ·
        Doctor Total = Basic Salary + Commission.
    </div>
    @endif
</form>

@push('scripts')
<script>
function payroll() { return {}; }
function row(d) {
  return {
    income: d.income, denture: d.denture, oneDay: d.oneDay, pct: d.pct, days: d.days,
    basic: 0, commission: 0, total: 0,
    init() { this.calc(); },
    fmt(n) { return new Intl.NumberFormat().format(Math.round(n||0)) + ' MMK'; },
    calc() {
      this.basic = this.oneDay * (this.days || 0);
      const base = Math.max(0, this.income - (2 * this.basic) - this.denture);
      this.commission = Math.round(base * this.pct / 100);
      this.total = this.basic + this.commission;
    }
  }
}
</script>
@endpush
@endsection
