@extends('layouts.app')
@section('title', 'Income Management')
@section('content')
<div class="row g-3">
    <div class="col-lg-4"><div class="card"><div class="card-header">Add Income</div><div class="card-body">
        <form method="POST" action="{{ route('incomes.store') }}">
            @csrf
            <div class="mb-2"><label class="form-label">Clinic</label>
                <select name="clinic_id" class="form-select" required>
                    @foreach(\App\Models\Clinic::orderBy('name')->get() as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                </select></div>
            <div class="mb-2"><label class="form-label">Title</label><input name="title" class="form-control" required></div>
            <div class="mb-2"><label class="form-label">Category</label><input name="category" class="form-control" value="other"></div>
            <div class="mb-2"><label class="form-label">Amount</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
            <div class="mb-2"><label class="form-label">Date</label><input type="date" name="income_date" class="form-control" value="{{ date('Y-m-d') }}" required></div>
            <div class="mb-3"><label class="form-label">Note</label><textarea name="note" class="form-control" rows="2"></textarea></div>
            <button class="btn btn-brand w-100">Save</button>
        </form>
    </div></div></div>
    <div class="col-lg-8"><div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
        <thead><tr><th>Date</th><th>Title</th><th>Category</th><th class="text-end">Amount</th><th></th></tr></thead>
        <tbody>
        @forelse($incomes as $inc)
            <tr><td>{{ $inc->income_date?->format('Y-m-d') }}</td><td>{{ $inc->title }}</td><td>{{ $inc->category }}</td>
                <td class="text-end">{{ money($inc->amount) }}</td>
                <td class="text-end"><form method="POST" action="{{ route('incomes.destroy',$inc) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-sm text-danger"><i class="bi bi-trash"></i></button></form></td></tr>
        @empty<tr><td colspan="5" class="text-center text-muted py-4">No income records.</td></tr>@endforelse
        </tbody>
    </table></div></div>
    <div class="mt-3">{{ $incomes->links() }}</div></div>
</div>
@endsection
