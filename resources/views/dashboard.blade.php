@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')

    {{-- Reports Topbar --}}
    <div class="table-card mb-3" style="padding:10px 14px;">
        <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap">
            @foreach ([['label' => 'Sales', 'icon' => 'bi-cart-check-fill', 'route' => 'reports.sales', 'color' => '#38a169'], ['label' => 'Purchase', 'icon' => 'bi-bag-check-fill', 'route' => 'reports.purchase-sale', 'color' => '#c9963a'], ['label' => 'Rentals', 'icon' => 'bi-box-seam-fill', 'route' => 'reports.rentals', 'color' => '#3182ce'], ['label' => 'Stock', 'icon' => 'bi-tags-fill', 'route' => 'reports.all-items', 'color' => '#805ad5'], ['label' => 'Customers & Vendors', 'icon' => 'bi-people-fill', 'route' => 'reports.customer-vendor', 'color' => '#319795'], ['label' => 'Employees', 'icon' => 'bi-person-badge-fill', 'route' => 'reports.employee', 'color' => '#d53f8c']] as $rep)
                <a href="{{ route($rep['route']) }}"
                    style="display:inline-flex; align-items:center; gap:5px; padding:5px 12px;
                    background:#f8fafc; border:1.5px solid var(--border);
                    border-radius:7px; text-decoration:none; font-size:12px; font-weight:600;
                    color:{{ $rep['color'] }};">
                    <i class="bi {{ $rep['icon'] }}" style="font-size:13px;"></i>
                    {{ $rep['label'] }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Item availability quick access (all staff) --}}
    <a href="{{ route('rentals.availability') }}" class="table-card mb-3"
       style="display:flex; align-items:center; gap:12px; padding:12px 16px; text-decoration:none;">
        <div style="width:42px; height:42px; border-radius:10px; background:#ebf8ff; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
            <i class="bi bi-upc-scan" style="font-size:20px; color:#3182ce;"></i>
        </div>
        <div>
            <div style="font-weight:700; color:var(--navy);">Check Item Availability</div>
            <div style="font-size:12px; color:var(--text-muted);">
                Enter a product code to see its upcoming pickup &amp; return dates
            </div>
        </div>
        <i class="bi bi-arrow-right ms-auto" style="color:#3182ce; font-size:18px;"></i>
    </a>

    <div class="section-label">آج کا خلاصہ — Today's Overview</div>

    {{-- Stats Toggle --}}
    <div x-data="{ open: false }" class="mb-2">

        <div class="text-center mb-2">
            <button @click="open = !open"
                style="background:none; border:none; cursor:pointer; padding:4px 16px; display:inline-flex; flex-direction:column; align-items:center; gap:2px;">
                <i class="bi" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"
                    style="font-size:22px; font-weight:900; color:var(--navy);"></i>
            </button>
        </div>

        <div x-show="open" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" x-cloak>

            {{-- Single Stats Row: Operational first, financial after --}}
            <div class="row g-2 mb-2">
                @if (auth()->user()->canAccess('stat_active_rentals'))
                    <div class="col-2">
                        <div class="stat-card stat-green">
                            <div class="stat-label">Active Rentals</div>
                            <div class="stat-value">{{ $stats['active_rentals'] }}</div>
                            <div class="stat-sub">Currently out</div>
                        </div>
                    </div>
                @endif
                @if (auth()->user()->canAccess('stat_pickup_today'))
                    <div class="col-2">
                        <div class="stat-card stat-purple">
                            <div class="stat-label">Pickup Today</div>
                            <div class="stat-value">{{ $stats['pickup_today'] }}</div>
                            <div class="stat-sub">Customers arriving</div>
                        </div>
                    </div>
                @endif
                @if (auth()->user()->canAccess('stat_pickup_tomorrow'))
                    <div class="col-2">
                        <div class="stat-card stat-teal">
                            <div class="stat-label">Pickup Tomorrow</div>
                            <div class="stat-value">{{ $stats['pickup_tomorrow'] }}</div>
                            <div class="stat-sub">Arriving tomorrow</div>
                        </div>
                    </div>
                @endif
                @if (auth()->user()->canAccess('stat_overdue'))
                    <div class="col-2">
                        <div class="stat-card stat-red">
                            <div class="stat-label">Overdue Returns</div>
                            <div class="stat-value">{{ $stats['overdue'] }}</div>
                            <div class="stat-sub">Action needed</div>
                        </div>
                    </div>
                @endif
                @if (auth()->user()->canAccess('stat_pending_balance'))
                    <div class="col-2">
                        <div class="stat-card stat-gold">
                            <div class="stat-label">Pending Balance</div>
                            <div class="stat-value" style="font-size:15px;">Rs.
                                {{ number_format($stats['pending_balance'], 0) }}
                            </div>
                            <div class="stat-sub">From active rentals</div>
                        </div>
                    </div>
                @endif
                @if (auth()->user()->canAccess('stat_monthly_revenue'))
                    <div class="col-2">
                        <div class="stat-card stat-gold">
                            <div class="stat-label">Monthly Revenue</div>
                            <div class="stat-value" style="font-size:15px;">Rs.
                                {{ number_format($stats['monthly_revenue'], 0) }}
                            </div>
                            <div class="stat-sub">{{ now()->format('F Y') }}</div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Admin Stats Row --}}
            @if (auth()->user()->isAdmin())
                <div class="row g-2 mb-2">
                    @if (auth()->user()->canAccess('stat_total_customers'))
                        <div class="col-2">
                            <div class="stat-card stat-blue">
                                <div class="stat-label">Customers</div>
                                <div class="stat-value">{{ $stats['total_customers'] }}</div>
                                <div class="stat-sub">Registered</div>
                            </div>
                        </div>
                    @endif
                    @if (auth()->user()->canAccess('stat_total_products'))
                        <div class="col-2">
                            <div class="stat-card stat-blue">
                                <div class="stat-label">Products</div>
                                <div class="stat-value">{{ $stats['total_products'] }}</div>
                                <div class="stat-sub">In inventory</div>
                            </div>
                        </div>
                    @endif
                    @if (auth()->user()->canAccess('stat_total_sales'))
                        <div class="col-2">
                            <div class="stat-card stat-blue">
                                <div class="stat-label">Sales This Month</div>
                                <div class="stat-value">{{ $stats['total_sales'] }}</div>
                                <div class="stat-sub">Completed</div>
                            </div>
                        </div>
                    @endif
                    @if (auth()->user()->canAccess('stat_total_cash'))
                        <div class="col-2">
                            <div class="stat-card stat-green">
                                <div class="stat-label">Cash & Bank</div>
                                <div class="stat-value" style="font-size:15px;">Rs.
                                    {{ number_format($stats['total_cash'], 0) }}
                                </div>
                                <div class="stat-sub">All accounts</div>
                            </div>
                        </div>
                    @endif
                    @if (auth()->user()->canAccess('stat_expenses'))
                        <div class="col-2">
                            <div class="stat-card stat-red">
                                <div class="stat-label">Expenses</div>
                                <div class="stat-value" style="font-size:15px;">Rs.
                                    {{ number_format($stats['total_expenses'], 0) }}</div>
                                <div class="stat-sub">{{ now()->format('F Y') }}</div>
                            </div>
                        </div>
                    @endif
                    @if (auth()->user()->canAccess('stat_pending_po'))
                        <div class="col-2">
                            <div class="stat-card stat-purple">
                                <div class="stat-label">PO Balance Due</div>
                                <div class="stat-value" style="font-size:15px;">Rs.
                                    {{ number_format($stats['pending_po'], 0) }}
                                </div>
                                <div class="stat-sub">Vendor payments</div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

        </div>
    </div>

    {{-- Main Content: Modules + Activity --}}
    <div class="row g-3">

        {{-- LEFT: Module Grid --}}
        <div class="col-8">

            <hr>
            {{-- Core Modules --}}
            <div class="row g-2 mb-3">
                @if (auth()->user()->canAccess('customers'))
                    <div class="col-2">
                        <a href="{{ route('customers.index') }}" class="module-card">
                            <div class="mod-icon-wrap gold"><i class="bi bi-people-fill"
                                    style="font-size:22px; color:#c9963a;"></i></div>
                            <div class="mod-name">Customers</div>
                        </a>
                    </div>
                @endif
                @if (auth()->user()->canAccess('rentals'))
                    <div class="col-2">
                        <a href="{{ route('rentals.index') }}" class="module-card">
                            <div class="mod-icon-wrap blue"><i class="bi bi-box-seam-fill"
                                    style="font-size:22px; color:#3182ce;"></i></div>
                            <div class="mod-name">Rentals</div>
                        </a>
                    </div>
                @endif
                <div class="col-2">
                    <a href="{{ route('rentals.calendar') }}" class="module-card">
                        <div class="mod-icon-wrap blue"><i class="bi bi-calendar3"
                                style="font-size:22px; color:#3182ce;"></i></div>
                        <div class="mod-name">Calendar</div>
                    </a>
                </div>
                @if (auth()->user()->canAccess('sales'))
                    <div class="col-2">
                        <a href="{{ route('sales.index') }}" class="module-card">
                            <div class="mod-icon-wrap green"><i class="bi bi-cart-check-fill"
                                    style="font-size:22px; color:#38a169;"></i></div>
                            <div class="mod-name">Sales</div>
                        </a>
                    </div>
                @endif
                @if (auth()->user()->canAccess('products'))
                    <div class="col-2">
                        <a href="{{ route('products.index') }}" class="module-card">
                            <div class="mod-icon-wrap purple"><i class="bi bi-tags-fill"
                                    style="font-size:22px; color:#805ad5;"></i></div>
                            <div class="mod-name">Stock</div>
                        </a>
                    </div>
                @endif
                <div class="col-2">
                    <a href="{{ route('products.code-registry') }}" class="module-card">
                        <div class="mod-icon-wrap teal"><i class="bi bi-upc-scan"
                                style="font-size:22px; color:#319795;"></i></div>
                        <div class="mod-name">Code Registry</div>
                    </a>
                </div>
                <div class="col-2">
                    <a href="{{ route('bill-books.index') }}" class="module-card">
                        <div class="mod-icon-wrap teal"><i class="bi bi-journal-bookmark-fill"
                                style="font-size:22px; color:#319795;"></i></div>
                        <div class="mod-name">Bill Books</div>
                    </a>
                </div>
                @if(auth()->user()->isAdmin())
                <div class="col-2">
                    <a href="{{ route('phonebook.index') }}" class="module-card">
                        <div class="mod-icon-wrap teal"><i class="bi bi-telephone-fill"
                                style="font-size:22px; color:#319795;"></i></div>
                        <div class="mod-name">Phone Book</div>
                    </a>
                </div>
                @endif
                @if (auth()->user()->canAccess('notifications'))
                    <div class="col-2">
                        <a href="{{ route('notifications.index') }}" class="module-card">
                            <div class="mod-icon-wrap red"><i class="bi bi-bell-fill"
                                    style="font-size:22px; color:#e53e3e;"></i></div>
                            <div class="mod-name">Alerts</div>
                        </a>
                    </div>
                @endif
                @if (auth()->user()->canAccess('reports'))
                    <div class="col-2">
                        <a href="{{ route('reports.index') }}" class="module-card">
                            <div class="mod-icon-wrap teal"><i class="bi bi-bar-chart-fill"
                                    style="font-size:22px; color:#319795;"></i></div>
                            <div class="mod-name">Reports</div>
                        </a>
                    </div>
                @endif
            </div>

            <div class="row g-2 mb-3">
                @if (auth()->user()->canAccess('categories'))
                    <div class="col-2">
                        <a href="{{ route('categories.index') }}" class="module-card">
                            <div class="mod-icon-wrap teal"><i class="bi bi-folder-fill"
                                    style="font-size:22px; color:#319795;"></i></div>
                            <div class="mod-name">Categories</div>
                        </a>
                    </div>
                @endif
                @if (auth()->user()->canAccess('vendors'))
                    <div class="col-2">
                        <a href="{{ route('vendors.index') }}" class="module-card">
                            <div class="mod-icon-wrap gold"><i class="bi bi-shop-window"
                                    style="font-size:22px; color:#c9963a;"></i></div>
                            <div class="mod-name">Vendors</div>
                        </a>
                    </div>
                @endif
                @if (auth()->user()->canAccess('purchase_orders'))
                    <div class="col-2">
                        <a href="{{ route('purchase-orders.index') }}" class="module-card">
                            <div class="mod-icon-wrap gold"><i class="bi bi-bag-check-fill"
                                    style="font-size:22px; color:#c9963a;"></i></div>
                            <div class="mod-name">Purchase Orders</div>
                        </a>
                    </div>
                @endif
                @if (auth()->user()->canAccess('accounts'))
                    <div class="col-2">
                        <a href="{{ route('accounts.index') }}" class="module-card">
                            <div class="mod-icon-wrap blue"><i class="bi bi-bank2"
                                    style="font-size:22px; color:#3182ce;"></i></div>
                            <div class="mod-name">Accounts</div>
                        </a>
                    </div>
                @endif
                @if (auth()->user()->canAccess('expenses'))
                    <div class="col-2">
                        <a href="{{ route('expenses.index') }}" class="module-card">
                            <div class="mod-icon-wrap red"><i class="bi bi-receipt-cutoff"
                                    style="font-size:22px; color:#e53e3e;"></i></div>
                            <div class="mod-name">Expenses</div>
                        </a>
                    </div>
                @endif
                @if (auth()->user()->canAccess('employees'))
                    <div class="col-2">
                        <a href="{{ route('employees.index') }}" class="module-card">
                            <div class="mod-icon-wrap blue"><i class="bi bi-person-badge-fill"
                                    style="font-size:22px; color:#3182ce;"></i></div>
                            <div class="mod-name">Employees</div>
                        </a>
                    </div>
                @endif
            </div>

            {{-- HR & Admin --}}
            @if (auth()->user()->isAdmin() || auth()->user()->canAccess('employees'))
                <div class="row g-2 mb-3">
                    @if (auth()->user()->canAccess('attendance'))
                        <div class="col-2">
                            <a href="{{ route('attendance.index') }}" class="module-card">
                                <div class="mod-icon-wrap red"><i class="bi bi-calendar-check-fill"
                                        style="font-size:22px; color:#e53e3e;"></i></div>
                                <div class="mod-name">Attendance</div>
                            </a>
                        </div>
                    @endif
                    @if (auth()->user()->canAccess('salary'))
                        <div class="col-2">
                            <a href="{{ route('salary.index') }}" class="module-card">
                                <div class="mod-icon-wrap green"><i class="bi bi-cash-stack"
                                        style="font-size:22px; color:#38a169;"></i></div>
                                <div class="mod-name">Salary</div>
                            </a>
                        </div>
                    @endif
                    @if (auth()->user()->canAccess('advances'))
                        <div class="col-2">
                            <a href="{{ route('advances.index') }}" class="module-card">
                                <div class="mod-icon-wrap purple"><i class="bi bi-credit-card-fill"
                                        style="font-size:22px; color:#805ad5;"></i></div>
                                <div class="mod-name">Advances</div>
                            </a>
                        </div>
                    @endif
                    @if (auth()->user()->isAdmin())
                        <div class="col-2">
                            <a href="{{ route('loans.index') }}" class="module-card">
                                <div class="mod-icon-wrap pink" style="background:#fff5f7;"><i class="bi bi-person"
                                        style="font-size:22px; color:#d53f8c;"></i></div>
                                <div class="mod-name">Dasti Khata</div>
                            </a>
                        </div>
                        <div class="col-2">
                            <a href="{{ route('feature-toggles.index') }}" class="module-card">
                                <div class="mod-icon-wrap pink" style="background:#fff5f7;"><i
                                        class="bi bi-shield-lock-fill" style="font-size:22px; color:#d53f8c;"></i></div>
                                <div class="mod-name">Permissions</div>
                            </a>
                        </div>
                        <div class="col-2">
                            <a href="{{ route('backup.index') }}" class="module-card">
                                <div class="mod-icon-wrap pink" style="background:#fff5f7;"><i
                                        class="bi bi-database-check" style="font-size:22px; color:#d53f8c;"></i></div>
                                <div class="mod-name">Backup</div>
                            </a>
                        </div>
                    @endif
                </div>
            @endif

        </div>

        {{-- RIGHT: Activity Cards --}}
        <div class="col-4">

            {{-- Overdue Returns --}}
            @if (auth()->user()->canAccess('dash_overdue_card'))
                <div class="info-card mb-3">
                    <div class="info-card-title" style="display:flex; align-items:center; gap:8px;">
                        <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                        Overdue Returns
                        @if ($overdue->count() > 0)
                            <span
                                style="margin-left:auto; font-size:10px; background:#fff5f5; color:#c53030; padding:2px 8px; border-radius:10px; font-weight:700;">
                                {{ $overdue->count() }}
                            </span>
                        @endif
                    </div>
                    @forelse($overdue as $rental)
                        <a href="{{ route('rentals.show', $rental->id) }}" class="info-item"
                            style="text-decoration:none;">
                            <div>
                                <div class="item-name">{{ $rental->customer_name }}</div>
                                <div class="item-code">{{ $rental->items->pluck('product_code')->join(', ') }}</div>
                            </div>
                            <span class="badge-status overdue">
                                {{ \Carbon\Carbon::parse($rental->return_date)->diffForHumans() }}
                            </span>
                        </a>
                    @empty
                        <div style="font-size:12px; color:var(--text-muted); text-align:center; padding:12px 0;">
                            <i class="bi bi-check-circle text-success"></i> No overdue items
                        </div>
                    @endforelse
                </div>
            @endif

            {{-- Pickup Today --}}
            @if (auth()->user()->canAccess('dash_pickup_card'))
                <div class="info-card mb-3">
                    <div class="info-card-title" style="display:flex; align-items:center; gap:8px;">
                        <i class="bi bi-truck text-warning"></i>
                        Pickup Today
                        @if ($pickupToday->count() > 0)
                            <span
                                style="margin-left:auto; font-size:10px; background:#fffbeb; color:#b7791f; padding:2px 8px; border-radius:10px; font-weight:700;">
                                {{ $pickupToday->count() }}
                            </span>
                        @endif
                    </div>
                    @forelse($pickupToday as $rental)
                        <a href="{{ route('rentals.show', $rental->id) }}" class="info-item"
                            style="text-decoration:none;">
                            <div>
                                <div class="item-name">{{ $rental->customer_name }}</div>
                                <div class="item-code">{{ $rental->customer_phone1 }}</div>
                            </div>
                            <span class="badge-status {{ $rental->status === 'ready' ? 'ready' : 'pending' }}">
                                {{ ucfirst($rental->status) }}
                            </span>
                        </a>
                    @empty
                        <div style="font-size:12px; color:var(--text-muted); text-align:center; padding:12px 0;">
                            No pickups today
                        </div>
                    @endforelse
                </div>
            @endif

            {{-- Returns Tomorrow --}}
            @if (auth()->user()->canAccess('dash_return_card'))
                <div class="info-card">
                    <div class="info-card-title" style="display:flex; align-items:center; gap:8px;">
                        <i class="bi bi-calendar2-event text-info"></i>
                        Returns Tomorrow
                        @if ($returnTomorrow->count() > 0)
                            <span
                                style="margin-left:auto; font-size:10px; background:#ebf8ff; color:#2c5282; padding:2px 8px; border-radius:10px; font-weight:700;">
                                {{ $returnTomorrow->count() }}
                            </span>
                        @endif
                    </div>
                    @forelse($returnTomorrow as $rental)
                        <a href="{{ route('rentals.show', $rental->id) }}" class="info-item"
                            style="text-decoration:none;">
                            <div>
                                <div class="item-name">{{ $rental->customer_name }}</div>
                                <div class="item-code">{{ $rental->customer_phone1 }}</div>
                            </div>
                            <span class="badge-status booked">Expected</span>
                        </a>
                    @empty
                        <div style="font-size:12px; color:var(--text-muted); text-align:center; padding:12px 0;">
                            No returns tomorrow
                        </div>
                    @endforelse
                </div>
            @endif

        </div>
    </div>

@endsection
