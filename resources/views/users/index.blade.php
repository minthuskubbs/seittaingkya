@extends('layouts.app')
@section('title', 'Users')
@section('content')
<div class="d-flex justify-content-end mb-3"><a href="{{ route('users.create') }}" class="btn btn-brand"><i class="bi bi-person-plus"></i> New User</a></div>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Clinic</th><th>Status</th><th></th></tr></thead>
    <tbody>
    @foreach($users as $u)
        <tr><td>{{ $u->name }}</td><td>{{ $u->email }}</td>
            <td>{{ $u->getRoleNames()->map(fn($r)=>ucwords(str_replace('_',' ',$r)))->implode(', ') }}</td>
            <td>{{ $u->clinic->name ?? 'All' }}</td>
            <td>{!! $u->is_active?'<span class="badge bg-success">Active</span>':'<span class="badge bg-secondary">Inactive</span>' !!}</td>
            <td class="text-end">
                <a href="{{ route('users.edit',$u) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                @if($u->id !== auth()->id())
                <form method="POST" action="{{ route('users.destroy',$u) }}" class="d-inline" onsubmit="return confirm('Delete user?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
                @endif
            </td></tr>
    @endforeach
    </tbody>
</table></div></div>
<div class="mt-3">{{ $users->links() }}</div>
@endsection
