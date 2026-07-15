@extends('layouts.app')
@section('title', 'Staff')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <form class="d-flex" style="max-width:340px">
        <input name="q" value="{{ request('q') }}" class="form-control" placeholder="Search staff…">
        <button class="btn btn-outline-secondary ms-2"><i class="bi bi-search"></i></button>
    </form>
    <a href="{{ route('staff.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> New Staff</a>
</div>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Name</th><th>Clinic</th><th>Position</th><th>Phone</th><th>Status</th><th></th></tr></thead>
    <tbody>
    @forelse($staff as $s)
        <tr>
            <td class="fw-semibold">{{ $s->name }}</td>
            <td><span class="badge badge-soft-brand">{{ $s->clinic->name ?? '—' }}</span></td>
            <td>{{ $s->position ?? '—' }}</td>
            <td>{{ $s->phone ?? '—' }}</td>
            <td>{!! $s->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td>
            <td class="text-end">
                <a href="{{ route('staff.edit', $s) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                <form method="POST" action="{{ route('staff.destroy', $s) }}" class="d-inline" onsubmit="return confirm('Delete staff?')">
                    @csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                </form>
            </td>
        </tr>
    @empty<tr><td colspan="6" class="text-center text-muted py-4">No staff yet.</td></tr>@endforelse
    </tbody>
</table></div></div>
<div class="mt-3">{{ $staff->links() }}</div>
@endsection
