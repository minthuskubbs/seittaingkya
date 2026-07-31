<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#b30b0b">
    <meta name="vapid-key" content="{{ config('webpush.vapid.public_key') }}">
    <title>@yield('title', 'Dashboard') · {{ config('app.name') }}</title>

    <link rel="manifest" href="{{ url('/manifest.webmanifest') }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('vendor/pwa/icon-192.png') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>[x-cloak]{display:none!important}</style>
    <script defer src="{{ asset('vendor/alpine/alpine.min.js') }}"></script>
</head>
<body>
<div class="offline-banner"><i class="bi bi-wifi-off"></i> You are offline — changes will sync automatically when reconnected.</div>

@include('layouts.sidebar')
<div class="sidebar-backdrop" onclick="toggleSidebar()"></div>

<div class="app-main">
    @include('layouts.topbar')

    <div class="app-content">
        @include('layouts.flash')
        @yield('content')
    </div>
</div>

<script>
  window.APP = {
    csrf: document.querySelector('meta[name=csrf-token]').content,
    vapid: document.querySelector('meta[name=vapid-key]').content,
    urls: {
      unread: "{{ route('notifications.unread') }}",
      read: "{{ route('notifications.read') }}",
      pushStore: "{{ route('push.store') }}",
      sync: "{{ route('sync.push') }}",
    }
  };
  function toggleSidebar() {
    document.querySelector('.app-sidebar').classList.toggle('open');
    document.querySelector('.sidebar-backdrop').classList.toggle('show');
  }
</script>
<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
