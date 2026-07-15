@extends('layouts.app')
@section('title', 'Suppliers')
@section('content')
<div class="d-flex justify-content-end mb-3"><a href="{{ route('suppliers.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> New Supplier</a></div>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Name</th><th>Phone</th><th>Email</th><th>Status</th><th></th></tr></thead>
    <tbody>
    @forelse($suppliers as $s)
        <tr><td>{{ $s->name }}</td><td>{{ $s->phone ?? '—' }}</td><td>{{ $s->email ?? '—' }}</td>
            <td>{!! $s->is_active?'<span class="badge bg-success">Active</span>':'<span class="badge bg-secondary">Inactive</span>' !!}</td>
            <td class="text-end">
                <a href="{{ route('suppliers.edit',$s) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                <form method="POST" action="{{ route('suppliers.destroy',$s) }}" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
            </td></tr>
    @empty<tr><td colspan="5" class="text-center text-muted py-4">No suppliers.</td></tr>@endforelse
    </tbody>
</table></div></div>
<div class="mt-3">{{ $suppliers->links() }}</div>
@endsection
