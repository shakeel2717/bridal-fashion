@extends('layouts.auth')
@section('title', 'Login')

@section('content')
<div class="auth-card">
    <div class="auth-logo">
        <div class="auth-logo-icon">
            {{-- Replace with Flaticon SVG: wedding rings or diamond icon --}}
            <i class="bi bi-gem" style="font-size:26px; color:#fff;"></i>
        </div>
        <div class="auth-logo-name">{{ config('app.name', 'Dulhan House') }}</div>
        <div class="auth-logo-sub">Bridal &amp; Sherwani Management</div>
    </div>

    <div class="auth-title">Sign In to Continue</div>

    @if($errors->any())
    <div class="alert alert-danger py-2" style="font-size:13px;">
        {{ $errors->first() }}
    </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email"
                   name="email"
                   class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email') }}"
                   placeholder="admin@example.com"
                   autofocus
                   required>
        </div>

        <div class="mb-4">
            <label class="form-label">Password</label>
            <input type="password"
                   name="password"
                   class="form-control @error('password') is-invalid @enderror"
                   placeholder="••••••••"
                   required>
        </div>

        <div class="mb-3 d-flex align-items-center justify-content-between">
            <div class="form-check" style="margin:0;">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label" for="remember" style="font-size:12px;">
                    Remember me
                </label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100" style="height:40px; font-size:14px;">
            <i class="bi bi-box-arrow-in-right me-1"></i> Login
        </button>
    </form>

    <div style="text-align:center; margin-top:20px; font-size:11px; color:var(--text-muted);">
        Contact Admin to reset your password
    </div>
</div>
@endsection