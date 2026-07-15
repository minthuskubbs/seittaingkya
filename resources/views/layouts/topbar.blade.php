@php $u = auth()->user(); @endphp
<header class="app-topbar">
    <button class="btn btn-light d-lg-none" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>

    <div class="fw-semibold d-none d-sm-block">@yield('title', 'Dashboard')</div>

    <div class="ms-auto d-flex align-items-center gap-2">
        @if($u->clinic)
            <span class="badge badge-soft-brand"><i class="bi bi-hospital"></i> {{ $u->clinic->name }}</span>
        @else
            @php $activeClinic = \App\Models\Clinic::find(session('active_clinic_id')); @endphp
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" title="New records are filed under this clinic">
                    <i class="bi bi-hospital"></i> Working: {{ $activeClinic->name ?? 'Select clinic' }}
                </button>
                <ul class="dropdown-menu">
                    <li><h6 class="dropdown-header">All clinics visible · new records go to:</h6></li>
                    @foreach(\App\Models\Clinic::where('is_active', true)->orderBy('name')->get() as $c)
                        <li><a class="dropdown-item {{ session('active_clinic_id')==$c->id ? 'active' : '' }}" href="{{ route('switch-clinic', $c) }}">{{ $c->name }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="dropdown">
            <button class="btn btn-light position-relative" data-bs-toggle="dropdown" id="notifBtn">
                <i class="bi bi-bell"></i>
                <span class="notif-dot d-none" id="notifDot"></span>
            </button>
            <div class="dropdown-menu dropdown-menu-end p-0" style="width: 320px;">
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                    <strong>Notifications</strong>
                    <a href="#" class="small" onclick="markAllRead(event)">Mark all read</a>
                </div>
                <div id="notifList" style="max-height: 320px; overflow:auto;">
                    <div class="text-muted small p-3">Loading…</div>
                </div>
                <a href="{{ route('notifications.index') }}" class="d-block text-center small py-2 border-top">View all</a>
            </div>
        </div>

        <div class="dropdown">
            <button class="btn btn-light d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle"></i>
                <span class="d-none d-md-inline">{{ $u->name }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><span class="dropdown-item-text small text-muted">{{ $u->getRoleNames()->map(fn($r)=>ucwords(str_replace('_',' ',$r)))->implode(', ') }}</span></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="dropdown-item"><i class="bi bi-box-arrow-right"></i> Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
