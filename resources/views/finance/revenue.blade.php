@extends('layouts.app')
@section('title', 'Revenue Report')
@section('content')
<form class="card mb-3"><div class="card-body row g-2 align-items-end">
    <div class="col-md-3"><label class="form-label">From</label><input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="form-control"></div>
    <div class="col-md-3"><label class="form-label">To</label><input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="form-control"></div>
    <div class="col-md-3"><label class="form-label">Clinic</label>
        <select name="clinic_id" class="form-select"><option value="">All clinics</option>
            @foreach($clinics as $c)<option value="{{ $c->id }}" @selected($clinicId==$c->id)>{{ $c->name }}</option>@endforeach</select></div>
    <div class="col-md-3"><button class="btn btn-brand w-100"><i class="bi bi-funnel"></i> Filter</button></div>
</div></form>

<div class="row g-3 mb-3">
    <div class="col-6 col-lg"><div class="card stat-card h-100"><div class="card-body">
        <div class="text-muted small">Total Income</div><div class="h4 text-success">{{ money($income) }}</div>
        <div class="small text-muted">Payments {{ money($totalPayments) }} · Other {{ money($manualIncome) }}</div></div></div></div>
    <div class="col-6 col-lg"><div class="card stat-card h-100"><div class="card-body">
        <div class="text-muted small">Payroll</div><div class="h4 text-danger">{{ money($payrollTotal) }}</div>
        <a href="{{ route('finance.staff_payroll') }}" class="small">Staff</a> · <a href="{{ route('finance.doctor_payroll') }}" class="small">Doctor</a></div></div></div>
    <div class="col-6 col-lg"><div class="card stat-card h-100"><div class="card-body">
        <div class="text-muted small">Expense</div><div class="h4 text-danger">{{ money($manualExpenses) }}</div>
        <a href="{{ route('expenses.index') }}" class="small">View</a></div></div></div>
    <div class="col-6 col-lg"><div class="card stat-card h-100"><div class="card-body">
        <div class="text-muted small">Total Expense</div><div class="h4 text-danger">{{ money($expenses) }}</div>
        <div class="small text-muted">Payroll + Expense</div></div></div></div>
    <div class="col-6 col-lg"><div class="card stat-card h-100"><div class="card-body">
        <div class="text-muted small">Net Profit</div><div class="h4 {{ $net < 0 ? 'text-danger' : 'text-brand' }}">{{ money($net) }}</div>
        <div class="small text-muted">Income − Total Expense</div></div></div></div>
</div>

<div class="card"><div class="card-header">Income by Payment Method</div>
    <table class="table mb-0"><tbody>
        @forelse($byMethod as $method => $total)
            <tr><td>{{ \App\Models\Payment::METHODS[$method] ?? $method }}</td><td class="text-end">{{ money($total) }}</td></tr>
        @empty<tr><td colspan="2" class="text-center text-muted">No payments in range.</td></tr>@endforelse
    </tbody></table>
</div>
<p class="text-muted small mt-2">Expenses = manually recorded expenses + payroll (base salary + doctor commission) for the period.</p>
@endsection
