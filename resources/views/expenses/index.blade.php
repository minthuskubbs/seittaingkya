@extends('layouts.app')
@section('title', 'Expenses')
@section('content')
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card"><div class="card-header">Record Expense</div><div class="card-body">
            <form method="POST" action="{{ route('expenses.store') }}" x-data="{ isSupplies: false }">
                @csrf
                @if($clinics->isNotEmpty())
                    <div class="mb-2"><label class="form-label">Clinic</label>
                        <select name="clinic_id" class="form-select" required>
                            @foreach($clinics as $c)<option value="{{ $c->id }}" @selected($clinicFilter==$c->id)>{{ $c->name }}</option>@endforeach
                        </select></div>
                @else
                    <input type="hidden" name="clinic_id" value="{{ auth()->user()->clinic_id }}">
                @endif
                <div class="mb-2"><label class="form-label">Type</label>
                    <select name="expense_type_id" class="form-select"
                            @change="isSupplies = $event.target.selectedOptions[0].dataset.name === 'Supplies'; if(!isSupplies){ $refs.doc.value=''; $refs.sup.value=''; }">
                        <option value="">— None —</option>
                        @foreach($types as $t)<option value="{{ $t->id }}" data-name="{{ $t->name }}">{{ $t->name }}</option>@endforeach
                    </select></div>
                <div class="mb-2"><label class="form-label">Staff (optional)</label>
                    <select name="user_id" class="form-select">
                        <option value="">—</option>
                        @foreach($staff as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                    </select></div>
                {{-- Supplies expenses can be tied to a doctor and the supplier bought from. --}}
                <div class="mb-2" x-show="isSupplies" x-cloak><label class="form-label">Doctor (optional)</label>
                    <select name="doctor_id" class="form-select" x-ref="doc">
                        <option value="">—</option>
                        @foreach($doctors as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
                    </select></div>
                <div class="mb-2" x-show="isSupplies" x-cloak><label class="form-label">Supplier (optional)</label>
                    <select name="supplier_id" class="form-select" x-ref="sup">
                        <option value="">—</option>
                        @foreach($suppliers as $sup)<option value="{{ $sup->id }}">{{ $sup->name }}</option>@endforeach
                    </select></div>
                <div class="mb-2"><label class="form-label">Title</label>
                    <input name="title" class="form-control" placeholder="e.g. WiFi bill – July" required></div>
                <div class="mb-2"><label class="form-label">Amount (MMK)</label>
                    <input type="number" step="0.01" min="0" name="amount" class="form-control" required></div>
                <div class="mb-2"><label class="form-label">Date</label>
                    <input type="date" name="expense_date" class="form-control" value="{{ date('Y-m-d') }}" required></div>
                <div class="mb-3"><label class="form-label">Note</label>
                    <textarea name="note" class="form-control" rows="2"></textarea></div>
                <button class="btn btn-brand w-100">Save Expense</button>
            </form>
            <div class="alert alert-light small mt-3 mb-0">Staff salaries &amp; doctor commission are handled automatically under
                <a href="{{ route('finance.staff_payroll') }}">Staff Payroll</a> &amp; <a href="{{ route('finance.doctor_payroll') }}">Doctor Payroll</a> — record only non-payroll costs here to avoid double counting.</div>
        </div></div>
    </div>

    <div class="col-lg-8">
        <form class="card mb-3"><div class="card-body row g-2 align-items-end">
            @if($clinics->isNotEmpty())
            <div class="col-md-4"><label class="form-label">Clinic</label>
                <select name="clinic_id" class="form-select">
                    <option value="">All clinics</option>
                    @foreach($clinics as $c)<option value="{{ $c->id }}" @selected((string)$clinicFilter===(string)$c->id)>{{ $c->name }}</option>@endforeach
                </select></div>
            @endif
            <div class="col-md-4"><label class="form-label">Month</label>
                <input type="month" name="month" value="{{ $month->format('Y-m') }}" class="form-control"></div>
            <div class="col-md-3"><button class="btn btn-brand"><i class="bi bi-funnel"></i> Filter</button></div>
        </div></form>

        <div class="card"><div class="card-header d-flex justify-content-between">
            <span>Expenses · {{ $month->format('F Y') }}</span>
            <strong class="text-danger">Total: {{ money($total) }}</strong></div>
            <div class="table-responsive"><table class="table align-middle mb-0">
                <thead><tr><th>Date</th><th>Type</th><th>Title</th><th>Staff / Doctor / Supplier</th><th class="text-end">Amount</th><th></th></tr></thead>
                <tbody>
                @forelse($expenses as $e)
                    <tr>
                        <td>{{ $e->expense_date?->format('Y-m-d') }}</td>
                        <td>{{ $e->type->name ?? '—' }}</td>
                        <td>{{ $e->title }}</td>
                        <td>
                            @if($e->staff){{ $e->staff->name }}@endif
                            @if($e->doctor)<div><i class="bi bi-person-badge text-muted"></i> {{ $e->doctor->name }}</div>@endif
                            @if($e->supplier)<div><i class="bi bi-truck text-muted"></i> {{ $e->supplier->name }}</div>@endif
                            @unless($e->staff || $e->doctor || $e->supplier)—@endunless
                        </td>
                        <td class="text-end">{{ money($e->amount) }}</td>
                        <td class="text-end"><form method="POST" action="{{ route('expenses.destroy', $e) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-sm text-danger"><i class="bi bi-trash"></i></button></form></td>
                    </tr>
                @empty<tr><td colspan="6" class="text-center text-muted py-4">No expenses this month.</td></tr>@endforelse
                </tbody>
            </table></div>
        </div>
        <div class="mt-3">{{ $expenses->links() }}</div>
    </div>
</div>
@endsection
