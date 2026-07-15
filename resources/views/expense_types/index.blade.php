@extends('layouts.app')
@section('title', 'Expense Types')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="text-muted small">Categories available when recording an expense.</div>
    <a href="{{ route('expense-types.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> New Expense Type</a>
</div>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Name</th><th class="text-center">Order</th><th>Status</th><th></th></tr></thead>
    <tbody>
    @forelse($expenseTypes as $t)
        <tr>
            <td>{{ $t->name }}</td>
            <td class="text-center">{{ $t->sort_order }}</td>
            <td>{!! $t->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td>
            <td class="text-end">
                <a href="{{ route('expense-types.edit', $t) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                <form method="POST" action="{{ route('expense-types.destroy', $t) }}" class="d-inline" onsubmit="return confirm('Delete?')">
                    @csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                </form>
            </td>
        </tr>
    @empty<tr><td colspan="4" class="text-center text-muted py-4">No expense types.</td></tr>@endforelse
    </tbody>
</table></div></div>
<div class="mt-3">{{ $expenseTypes->links() }}</div>
@endsection
