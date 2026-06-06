<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Dulhan House') }} — @yield('title', 'Dashboard')</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    @livewireStyles
</head>
<body>

<div class="app-wrapper">

    {{-- Top Bar --}}
    <div class="app-topbar">
        <div class="topbar-brand">
            <div class="brand-logo">
                {{-- Replace with your Flaticon SVG: public/icons/wedding-rings.svg --}}
                <i class="bi bi-gem" style="font-size:18px; color:#fff;"></i>
            </div>
            <div class="brand-text">
                <div class="brand-name">{{ config('app.name', 'Dulhan House') }}</div>
                <div class="brand-sub">Bridal &amp; Sherwani</div>
            </div>
        </div>

        <div class="topbar-center">
            <div class="user-greeting">خوش آمدید</div>
            <div class="user-name">
                {{ auth()->user()->name }}
                <span style="font-size:11px; color:var(--gold); font-weight:500;">
                    ({{ ucfirst(auth()->user()->role) }})
                </span>
            </div>
        </div>

        <div class="topbar-right">
            {{-- Notifications --}}
            <a href="{{ route('notifications.index') }}" class="top-icon-btn" title="Notifications">
                <i class="bi bi-bell"></i>
                {{-- Show dot if alerts exist --}}
                @if(isset($alertCount) && $alertCount > 0)
                    <span class="notif-badge"></span>
                @endif
            </a>

            {{-- Settings (Admin only) --}}
            @if(auth()->user()->isAdmin())
            <a href="{{ route('settings.index') }}" class="top-icon-btn" title="Settings">
                <i class="bi bi-gear"></i>
            </a>
            @endif

            {{-- Logout --}}
            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit" class="top-logout-btn">
                    <i class="bi bi-box-arrow-right"></i>
                    Logout
                </button>
            </form>
        </div>
    </div>

    {{-- Body --}}
    <div class="app-body">

        {{-- Sidebar --}}
        <div class="app-sidebar">
            <a href="{{ route('dashboard') }}"
               class="sb-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"
               data-label="Dashboard">
                <i class="bi bi-grid-1x2"></i>
            </a>

            @if(auth()->user()->canAccess('customers'))
            <a href="{{ route('customers.index') }}"
               class="sb-item {{ request()->routeIs('customers.*') ? 'active' : '' }}"
               data-label="Customers">
                <i class="bi bi-people"></i>
            </a>
            @endif

            @if(auth()->user()->canAccess('rentals'))
            <a href="{{ route('rentals.index') }}"
               class="sb-item {{ request()->routeIs('rentals.*') ? 'active' : '' }}"
               data-label="Rentals">
                <i class="bi bi-box-seam"></i>
            </a>
            @endif

            @if(auth()->user()->canAccess('sales'))
            <a href="{{ route('sales.index') }}"
               class="sb-item {{ request()->routeIs('sales.*') ? 'active' : '' }}"
               data-label="Sales">
                <i class="bi bi-cart3"></i>
            </a>
            @endif

            @if(auth()->user()->canAccess('products'))
            <a href="{{ route('products.index') }}"
               class="sb-item {{ request()->routeIs('products.*') ? 'active' : '' }}"
               data-label="Products">
                <i class="bi bi-tags"></i>
            </a>
            @endif

            @if(auth()->user()->canAccess('categories'))
            <a href="{{ route('categories.index') }}"
               class="sb-item {{ request()->routeIs('categories.*') ? 'active' : '' }}"
               data-label="Categories">
                <i class="bi bi-folder2"></i>
            </a>
            @endif

            @if(auth()->user()->canAccess('vendors'))
            <a href="{{ route('vendors.index') }}"
               class="sb-item {{ request()->routeIs('vendors.*') ? 'active' : '' }}"
               data-label="Vendors">
                <i class="bi bi-shop"></i>
            </a>
            @endif

            <div class="sb-divider"></div>

            @if(auth()->user()->canAccess('employees'))
            <a href="{{ route('employees.index') }}"
               class="sb-item {{ request()->routeIs('employees.*') ? 'active' : '' }}"
               data-label="Employees">
                <i class="bi bi-person-badge"></i>
            </a>
            @endif

            @if(auth()->user()->canAccess('attendance'))
            <a href="{{ route('attendance.index') }}"
               class="sb-item {{ request()->routeIs('attendance.*') ? 'active' : '' }}"
               data-label="Attendance">
                <i class="bi bi-calendar-check"></i>
            </a>
            @endif

            @if(auth()->user()->canAccess('salary'))
            <a href="{{ route('salary.index') }}"
               class="sb-item {{ request()->routeIs('salary.*') ? 'active' : '' }}"
               data-label="Salary">
                <i class="bi bi-cash-stack"></i>
            </a>
            @endif

            @if(auth()->user()->canAccess('advances'))
            <a href="{{ route('advances.index') }}"
               class="sb-item {{ request()->routeIs('advances.*') ? 'active' : '' }}"
               data-label="Advances">
                <i class="bi bi-credit-card"></i>
            </a>
            @endif

            <div class="sb-divider"></div>

            @if(auth()->user()->canAccess('notifications'))
            <a href="{{ route('notifications.index') }}"
               class="sb-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}"
               data-label="Alerts">
                <i class="bi bi-bell"></i>
            </a>
            @endif

            @if(auth()->user()->canAccess('reports'))
            <a href="{{ route('reports.index') }}"
               class="sb-item {{ request()->routeIs('reports.*') ? 'active' : '' }}"
               data-label="Reports">
                <i class="bi bi-bar-chart-line"></i>
            </a>
            @endif

            @if(auth()->user()->isAdmin())
            <div class="sb-divider"></div>
            <a href="{{ route('feature-toggles.index') }}"
               class="sb-item {{ request()->routeIs('feature-toggles.*') ? 'active' : '' }}"
               data-label="Permissions">
                <i class="bi bi-shield-lock"></i>
            </a>
            @endif
        </div>

        {{-- Page Content --}}
        <div class="app-main">
            @yield('content')
        </div>

    </div>
</div>

@livewireScripts
@stack('scripts')
</body>
</html>