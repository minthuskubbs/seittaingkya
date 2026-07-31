@extends('layouts.app')
@section('title', 'Denture Types')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="text-muted small">Predefined denture types with prices and supplier.</div>
    <a href="{{ route('denture-types.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> New Denture Type</a>
</div>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Name</th><th class="text-end">Price</th><th>Supplier</th><th class="text-center">Order</th><th>Status</th><th></th></tr></thead>
    <tbody>
    @forelse($dentureTypes as $t)
        <tr>
            <td>{{ $t->name }}</td>
            <td class="text-end">{{ money($t->price) }}</td>
            <td>{{ $t->supplier->name ?? '—' }}</td>
            <td class="text-center">{{ $t->sort_order }}</td>
            <td>{!! $t->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td>
            <td class="text-end">
                <a href="{{ route('denture-types.edit', $t) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                <form method="POST" action="{{ route('denture-types.destroy', $t) }}" class="d-inline" onsubmit="return confirm('Delete?')">
                    @csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                </form>
            </td>
        </tr>
    @empty<tr><td colspan="6" class="text-center text-muted py-4">No denture types yet.</td></tr>@endforelse
    </tbody>
</table></div></div>
<div class="mt-3">{{ $dentureTypes->links() }}</div>
@endsection
