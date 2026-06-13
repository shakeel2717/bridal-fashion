@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')

    <div class="section-label">آج کا خلاصہ — Today's Overview</div>

    {{-- Stats Row 1 --}}
    <div class="row g-2 mb-2">
        @if (auth()->user()->canAccess('stat_total_customers'))
            <div class="col-3">
                <div class="stat-card stat-blue">
                    <div class="stat-label">Total Customers</div>
                    <div class="stat-value">{{ $stats['total_customers'] }}</div>
                    <div class="stat-sub">Registered in system</div>
                </div>
            </div>
        @endif

        @if (auth()->user()->canAccess('stat_total_products'))
            <div class="col-3">
                <div class="stat-card stat-blue">
                    <div class="stat-label">Total Products</div>
                    <div class="stat-value">{{ $stats['total_products'] }}</div>
                    <div class="stat-sub">In inventory</div>
                </div>
            </div>
        @endif

        @if (auth()->user()->canAccess('stat_active_rentals'))
            <div class="col-3">
                <div class="stat-card stat-green">
                    <div class="stat-label">Active Rentals</div>
                    <div class="stat-value">{{ $stats['active_rentals'] }}</div>
                    <div class="stat-sub">Currently out</div>
                </div>
            </div>
        @endif

        @if (auth()->user()->canAccess('stat_monthly_revenue'))
            <div class="col-3">
                <div class="stat-card stat-gold">
                    <div class="stat-label">Monthly Revenue</div>
                    <div class="stat-value" style="font-size:17px;">
                        Rs. {{ number_format($stats['monthly_revenue'], 0) }}
                    </div>
                    <div class="stat-sub">{{ now()->format('F Y') }}</div>
                </div>
            </div>
        @endif
    </div>

    {{-- Stats Row 2 --}}
    <div class="row g-2 mb-2">
        @if (auth()->user()->canAccess('stat_pickup_today'))
            <div class="col-3">
                <div class="stat-card stat-purple">
                    <div class="stat-label">Pickup Today</div>
                    <div class="stat-value">{{ $stats['pickup_today'] }}</div>
                    <div class="stat-sub">Customers arriving</div>
                </div>
            </div>
        @endif

        @if (auth()->user()->canAccess('stat_pickup_tomorrow'))
            <div class="col-3">
                <div class="stat-card stat-teal">
                    <div class="stat-label">Pickup Tomorrow</div>
                    <div class="stat-value">{{ $stats['pickup_tomorrow'] }}</div>
                    <div class="stat-sub">Arriving tomorrow</div>
                </div>
            </div>
        @endif

        @if (auth()->user()->canAccess('stat_overdue'))
            <div class="col-3">
                <div class="stat-card stat-red">
                    <div class="stat-label">Overdue Returns</div>
                    <div class="stat-value">{{ $stats['overdue'] }}</div>
                    <div class="stat-sub">Action needed</div>
                </div>
            </div>
        @endif

        @if (auth()->user()->canAccess('stat_pending_balance'))
            <div class="col-3">
                <div class="stat-card stat-gold">
                    <div class="stat-label">Pending Balance</div>
                    <div class="stat-value" style="font-size:17px;">
                        Rs. {{ number_format($stats['pending_balance'], 0) }}
                    </div>
                    <div class="stat-sub">From active rentals</div>
                </div>
            </div>
        @endif
    </div>

    {{-- Stats Row 3 (Admin only) --}}
    @if (auth()->user()->isAdmin())
        @if (auth()->user()->isAdmin())
            <div class="row g-2 mb-2">
                @if (auth()->user()->canAccess('stat_total_cash'))
                    <div class="col-3">
                        <div class="stat-card stat-green">
                            <div class="stat-label">Total Cash & Bank</div>
                            <div class="stat-value" style="font-size:17px;">
                                Rs. {{ number_format($stats['total_cash'], 0) }}
                            </div>
                            <div class="stat-sub">All accounts combined</div>
                        </div>
                    </div>
                @endif

                @if (auth()->user()->canAccess('stat_expenses'))
                    <div class="col-3">
                        <div class="stat-card stat-red">
                            <div class="stat-label">Expenses This Month</div>
                            <div class="stat-value" style="font-size:17px;">
                                Rs. {{ number_format($stats['total_expenses'], 0) }}
                            </div>
                            <div class="stat-sub">{{ now()->format('F Y') }}</div>
                        </div>
                    </div>
                @endif

                @if (auth()->user()->canAccess('stat_total_sales'))
                    <div class="col-3">
                        <div class="stat-card stat-blue">
                            <div class="stat-label">Sales This Month</div>
                            <div class="stat-value">{{ $stats['total_sales'] }}</div>
                            <div class="stat-sub">Completed sales</div>
                        </div>
                    </div>
                @endif

                @if (auth()->user()->canAccess('stat_pending_po'))
                    <div class="col-3">
                        <div class="stat-card stat-purple">
                            <div class="stat-label">PO Balance Due</div>
                            <div class="stat-value" style="font-size:17px;">
                                Rs. {{ number_format($stats['pending_po'], 0) }}
                            </div>
                            <div class="stat-sub">Vendor payments pending</div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Account Balances (Admin only) --}}
        @if ($accounts->count() > 0)
            <div class="d-flex gap-2 mb-3 flex-wrap">
                @foreach ($accounts as $account)
                    <a href="{{ route('accounts.index') }}"
                        style="background:#fff; border-radius:8px; padding:8px 16px; font-size:12px; border:1px solid var(--border); text-decoration:none; display:flex; align-items:center; gap:8px;">
                        @if ($account->type === 'cash')
                            <i class="bi bi-cash-stack" style="color:#38a169;"></i>
                        @elseif($account->type === 'bank')
                            <i class="bi bi-bank" style="color:#3182ce;"></i>
                        @else
                            <i class="bi bi-phone" style="color:#805ad5;"></i>
                        @endif
                        <span style="color:var(--text-muted);">{{ $account->name }}</span>
                        <span class="fw-700"
                            style="color:{{ $account->current_balance >= 0 ? 'var(--navy)' : '#e53e3e' }};">
                            Rs. {{ number_format($account->current_balance, 0) }}
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
    @endif

    @if ($duplicateBookings->count() > 0)
        <div
            style="background:#fff5f5; border:1.5px solid #fc8181; border-radius:10px; padding:14px 18px; margin-bottom:16px;">
            <div style="font-size:12px; font-weight:700; color:#c53030; margin-bottom:10px;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                Duplicate Bookings Detected — {{ $duplicateBookings->count() }} item(s) booked multiple times
            </div>
            @foreach ($duplicateBookings as $productId => $group)
                <div style="font-size:11px; color:#c53030; padding:3px 0; border-bottom:1px solid #fed7d7;">
                    <strong>{{ $group->first()->product?->code }}</strong>
                    — {{ $group->first()->product?->name }}
                    — booked {{ $group->count() }} times concurrently
                </div>
            @endforeach
            <div style="margin-top:8px;">
                <a href="{{ route('rentals.index') }}" style="font-size:11px; color:#c53030; font-weight:600;">
                    View Rentals →
                </a>
            </div>
        </div>
    @endif

    {{-- Module Cards --}}
    <div class="section-label">Modules — تمام سیکشن</div>

    <div class="row g-2 mb-3">
        @if (auth()->user()->canAccess('customers'))
            <div class="col-2">
                <a href="{{ route('customers.index') }}" class="module-card">
                    <div class="mod-icon-wrap gold">
                        <i class="bi bi-people-fill" style="font-size:22px; color:#c9963a;"></i>
                    </div>
                    <div class="mod-name">Customers</div>
                </a>
            </div>
        @endif

        @if (auth()->user()->canAccess('rentals'))
            <div class="col-2">
                <a href="{{ route('rentals.index') }}" class="module-card">
                    <div class="mod-icon-wrap blue">
                        <i class="bi bi-box-seam-fill" style="font-size:22px; color:#3182ce;"></i>
                    </div>
                    <div class="mod-name">Rentals</div>
                </a>
            </div>
        @endif

        @if (auth()->user()->canAccess('sales'))
            <div class="col-2">
                <a href="{{ route('sales.index') }}" class="module-card">
                    <div class="mod-icon-wrap green">
                        <i class="bi bi-cart-check-fill" style="font-size:22px; color:#38a169;"></i>
                    </div>
                    <div class="mod-name">Sales</div>
                </a>
            </div>
        @endif

        @if (auth()->user()->canAccess('products'))
            <div class="col-2">
                <a href="{{ route('products.index') }}" class="module-card">
                    <div class="mod-icon-wrap purple">
                        <i class="bi bi-tags-fill" style="font-size:22px; color:#805ad5;"></i>
                    </div>
                    <div class="mod-name">Stock</div>
                </a>
            </div>
        @endif

        @if (auth()->user()->canAccess('categories'))
            <div class="col-2">
                <a href="{{ route('categories.index') }}" class="module-card">
                    <div class="mod-icon-wrap teal">
                        <i class="bi bi-folder-fill" style="font-size:22px; color:#319795;"></i>
                    </div>
                    <div class="mod-name">Categories</div>
                </a>
            </div>
        @endif

        @if (auth()->user()->canAccess('vendors'))
            <div class="col-2">
                <a href="{{ route('vendors.index') }}" class="module-card">
                    <div class="mod-icon-wrap gold">
                        <i class="bi bi-shop-window" style="font-size:22px; color:#c9963a;"></i>
                    </div>
                    <div class="mod-name">Vendors</div>
                </a>
            </div>
        @endif

        @if (auth()->user()->canAccess('employees'))
            <div class="col-2">
                <a href="{{ route('employees.index') }}" class="module-card">
                    <div class="mod-icon-wrap blue">
                        <i class="bi bi-person-badge-fill" style="font-size:22px; color:#3182ce;"></i>
                    </div>
                    <div class="mod-name">Employees</div>
                </a>
            </div>
        @endif

        @if (auth()->user()->canAccess('attendance'))
            <div class="col-2">
                <a href="{{ route('attendance.index') }}" class="module-card">
                    <div class="mod-icon-wrap red">
                        <i class="bi bi-calendar-check-fill" style="font-size:22px; color:#e53e3e;"></i>
                    </div>
                    <div class="mod-name">Attendance</div>
                </a>
            </div>
        @endif

        @if (auth()->user()->canAccess('salary'))
            <div class="col-2">
                <a href="{{ route('salary.index') }}" class="module-card">
                    <div class="mod-icon-wrap green">
                        <i class="bi bi-cash-stack" style="font-size:22px; color:#38a169;"></i>
                    </div>
                    <div class="mod-name">Salary</div>
                </a>
            </div>
        @endif

        @if (auth()->user()->canAccess('advances'))
            <div class="col-2">
                <a href="{{ route('advances.index') }}" class="module-card">
                    <div class="mod-icon-wrap purple">
                        <i class="bi bi-credit-card-fill" style="font-size:22px; color:#805ad5;"></i>
                    </div>
                    <div class="mod-name">Advances</div>
                </a>
            </div>
        @endif

        @if (auth()->user()->canAccess('purchase_orders'))
            <div class="col-2">
                <a href="{{ route('purchase-orders.index') }}" class="module-card">
                    <div class="mod-icon-wrap gold">
                        <i class="bi bi-bag-check-fill" style="font-size:22px; color:#c9963a;"></i>
                    </div>
                    <div class="mod-name">Purchase Orders</div>
                </a>
            </div>
        @endif

        @if (auth()->user()->canAccess('accounts'))
            <div class="col-2">
                <a href="{{ route('accounts.index') }}" class="module-card">
                    <div class="mod-icon-wrap blue">
                        <i class="bi bi-bank2" style="font-size:22px; color:#3182ce;"></i>
                    </div>
                    <div class="mod-name">Accounts</div>
                </a>
            </div>
        @endif

        @if (auth()->user()->canAccess('expenses'))
            <div class="col-2">
                <a href="{{ route('expenses.index') }}" class="module-card">
                    <div class="mod-icon-wrap red">
                        <i class="bi bi-receipt-cutoff" style="font-size:22px; color:#e53e3e;"></i>
                    </div>
                    <div class="mod-name">Expenses</div>
                </a>
            </div>
        @endif

        @if (auth()->user()->canAccess('notifications'))
            <div class="col-2">
                <a href="{{ route('notifications.index') }}" class="module-card">
                    <div class="mod-icon-wrap red">
                        <i class="bi bi-bell-fill" style="font-size:22px; color:#e53e3e;"></i>
                    </div>
                    <div class="mod-name">Alerts</div>
                </a>
            </div>
        @endif

        @if (auth()->user()->canAccess('reports'))
            <div class="col-2">
                <a href="{{ route('reports.index') }}" class="module-card">
                    <div class="mod-icon-wrap teal">
                        <i class="bi bi-bar-chart-fill" style="font-size:22px; color:#319795;"></i>
                    </div>
                    <div class="mod-name">Reports</div>
                </a>
            </div>
        @endif

        @if (auth()->user()->isAdmin())
            <div class="col-2">
                <a href="{{ route('feature-toggles.index') }}" class="module-card">
                    <div class="mod-icon-wrap pink" style="background:#fff5f7;">
                        <i class="bi bi-shield-lock-fill" style="font-size:22px; color:#d53f8c;"></i>
                    </div>
                    <div class="mod-name">Permissions</div>
                </a>
            </div>
        @endif

        @if (auth()->user()->isAdmin())
            <div class="col-2">
                <a href="{{ route('backup.index') }}" class="module-card">
                    <div class="mod-icon-wrap pink" style="background:#fff5f7;">
                        <i class="bi bi-database-check" style="font-size:22px; color:#d53f8c;"></i>
                    </div>
                    <div class="mod-name">Backup</div>
                </a>
            </div>
        @endif
    </div>

    {{-- Alert Row --}}
    <div class="row g-2">
        @if (auth()->user()->canAccess('dash_overdue_card'))
            <div class="col-4">
                <div class="info-card">
                    <div class="info-card-title">
                        <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                        Overdue Returns
                    </div>
                    @forelse($overdue as $rental)
                        <div class="info-item">
                            <div>
                                <div class="item-name">{{ $rental->customer_name }}</div>
                                <div class="item-code">{{ $rental->items->pluck('product_code')->join(', ') }}</div>
                            </div>
                            <span class="badge-status overdue">
                                {{ \Carbon\Carbon::parse($rental->return_date)->diffForHumans() }}
                            </span>
                        </div>
                    @empty
                        <div style="font-size:12px; color:var(--text-muted); text-align:center; padding:10px 0;">
                            <i class="bi bi-check-circle text-success"></i> No overdue items
                        </div>
                    @endforelse
                </div>
            </div>
        @endif

        @if (auth()->user()->canAccess('dash_pickup_card'))
            <div class="col-4">
                <div class="info-card">
                    <div class="info-card-title">
                        <i class="bi bi-truck text-warning"></i>
                        Pickup Today
                    </div>
                    @forelse($pickupToday as $rental)
                        <div class="info-item">
                            <div>
                                <div class="item-name">{{ $rental->customer_name }}</div>
                                <div class="item-code">{{ $rental->customer_phone1 }}</div>
                            </div>
                            <span class="badge-status {{ $rental->status === 'ready' ? 'ready' : 'pending' }}">
                                {{ ucfirst($rental->status) }}
                            </span>
                        </div>
                    @empty
                        <div style="font-size:12px; color:var(--text-muted); text-align:center; padding:10px 0;">
                            No pickups today
                        </div>
                    @endforelse
                </div>
            </div>
        @endif

        @if (auth()->user()->canAccess('dash_return_card'))
            <div class="col-4">
                <div class="info-card">
                    <div class="info-card-title">
                        <i class="bi bi-calendar2-event text-info"></i>
                        Return Tomorrow
                    </div>
                    @forelse($returnTomorrow as $rental)
                        <div class="info-item">
                            <div>
                                <div class="item-name">{{ $rental->customer_name }}</div>
                                <div class="item-code">{{ $rental->customer_phone1 }}</div>
                            </div>
                            <span class="badge-status booked">Expected</span>
                        </div>
                    @empty
                        <div style="font-size:12px; color:var(--text-muted); text-align:center; padding:10px 0;">
                            No returns tomorrow
                        </div>
                    @endforelse
                </div>
            </div>
        @endif
    </div>

@endsection
