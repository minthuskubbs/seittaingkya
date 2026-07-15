<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#b30b0b">
    <title>Login · {{ config('app.name') }}</title>
    <link rel="manifest" href="{{ url('/manifest.webmanifest') }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="auth-wrap">
    <div class="card auth-card">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <img src="{{ asset('vendor/pwa/logo.png') }}" alt="{{ config('app.name') }}" style="max-width:300px;width:80%;height:auto" class="mb-3">
                <p class="text-muted small mb-0">Sign in to your account</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control form-control-lg" value="{{ old('email') }}" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control form-control-lg" required>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <button type="submit" class="btn btn-brand btn-lg w-100">Sign In</button>
            </form>

            <div class="mt-4 small text-muted">
                <div class="fw-semibold mb-1">Demo accounts (password: <code>password</code>)</div>
                superadmin@dental.local · admin1@dental.local · assist1@dental.local
            </div>
        </div>
    </div>
</div>
</body>
</html>
