@extends('layouts.app')
@section('title', 'Logged-in Users')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="text-muted small">Users active within the last {{ $lifetime }} minutes. Idle sessions drop off automatically.</div>
</div>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>User</th><th>Role</th><th>Clinic</th><th>Device</th><th>IP Address</th><th>Last Active</th><th></th></tr></thead>
    <tbody>
    @forelse($sessions as $s)
        <tr>
            <td>
                <i class="bi bi-person-circle text-brand"></i> {{ $s['user']->name }}
                @if($s['is_current'])<span class="badge bg-brand ms-1">This device</span>@endif
                <div class="text-muted small">{{ $s['user']->email }}</div>
            </td>
            <td>{{ $s['user']->getRoleNames()->map(fn($r)=>ucwords(str_replace('_',' ',$r)))->implode(', ') }}</td>
            <td>{{ $s['user']->clinic->name ?? 'All' }}</td>
            <td><span title="{{ $s['user_agent'] }}"><i class="bi bi-laptop"></i> {{ $s['device'] }}</span></td>
            <td><code>{{ $s['ip'] }}</code></td>
            <td>{{ $s['last_active']->diffForHumans() }}</td>
            <td class="text-end">
                @unless($s['is_current'])
                    <form method="POST" action="{{ route('sessions.destroy', $s['id']) }}" onsubmit="return confirm('Sign out this device?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-box-arrow-right"></i> Revoke</button>
                    </form>
                @endunless
            </td>
        </tr>
    @empty
        <tr><td colspan="7" class="text-center text-muted py-4">No active sessions.</td></tr>
    @endforelse
    </tbody>
</table></div></div>
@endsection
