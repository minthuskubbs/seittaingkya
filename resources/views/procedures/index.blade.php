@extends('layouts.app')
@section('title', 'Procedures')
@section('content')
<div class="d-flex justify-content-end mb-3"><a href="{{ route('procedures.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> New Procedure</a></div>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Code</th><th>Name</th><th class="text-end">Default Price</th><th>Status</th><th></th></tr></thead>
    <tbody>
    @forelse($procedures as $pr)
        <tr><td>{{ $pr->code }}</td><td>{{ $pr->name }}</td><td class="text-end">{{ money($pr->default_price) }}</td>
            <td>{!! $pr->is_active?'<span class="badge bg-success">Active</span>':'<span class="badge bg-secondary">Inactive</span>' !!}</td>
            <td class="text-end">
                <a href="{{ route('procedures.edit',$pr) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                <form method="POST" action="{{ route('procedures.destroy',$pr) }}" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
            </td></tr>
    @empty<tr><td colspan="5" class="text-center text-muted py-4">No procedures.</td></tr>@endforelse
    </tbody>
</table></div></div>
<div class="mt-3">{{ $procedures->links() }}</div>
@endsection
