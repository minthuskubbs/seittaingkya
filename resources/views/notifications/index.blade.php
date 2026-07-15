@extends('layouts.app')
@section('title', 'Notifications')
@section('content')
<div class="card"><ul class="list-group list-group-flush">
    @forelse($notifications as $n)
        <li class="list-group-item {{ $n->read_at ? '' : 'bg-light' }}">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="fw-semibold">{{ $n->data['title'] ?? 'Notification' }}</div>
                    <div class="text-muted small">{{ $n->data['message'] ?? '' }}</div>
                </div>
                <span class="text-muted small">{{ $n->created_at->diffForHumans() }}</span>
            </div>
        </li>
    @empty
        <li class="list-group-item text-center text-muted py-4">No notifications.</li>
    @endforelse
</ul></div>
<div class="mt-3">{{ $notifications->links() }}</div>
@endsection
