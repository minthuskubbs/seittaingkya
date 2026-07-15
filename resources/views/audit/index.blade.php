@extends('layouts.app')
@section('title', 'Audit Logs')
@section('content')
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0 small">
    <thead><tr><th>Time</th><th>User</th><th>Action</th><th>Description</th><th>IP</th></tr></thead>
    <tbody>
    @forelse($logs as $log)
        <tr><td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
            <td>{{ $log->user->name ?? 'System' }}</td>
            <td><span class="badge bg-secondary text-capitalize">{{ $log->action }}</span></td>
            <td>{{ $log->description }}</td><td>{{ $log->ip_address }}</td></tr>
    @empty<tr><td colspan="5" class="text-center text-muted py-4">No audit entries.</td></tr>@endforelse
    </tbody>
</table></div></div>
<div class="mt-3">{{ $logs->links() }}</div>
@endsection
