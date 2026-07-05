<div>
    @if (session('success'))
        <div class="alert alert-success py-2 mb-3" style="font-size:13px;">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif

    <div class="d-flex align-items-center justify-content-between mb-2">
        <div>
            <div class="page-title">Sales</div>
            <div class="page-subtitle">All product sales</div>
        </div>
        <a href="{{ route('sales.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
            <i class="bi bi-plus-lg"></i> New Sale
        </a>
    </div>

    {{-- Filter Toggle --}}
    <div x-data="{ open: false }" class="mb-3">
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
            <div class="d-flex gap-2 flex-wrap justify-content-center">
                @foreach ([
        'completed' => ['label' => 'Completed', 'icon' => 'bi-check-circle', 'color' => '#276749'],
        'pending' => ['label' => 'Pending', 'icon' => 'bi-hourglass-split', 'color' => '#b7791f'],
        'cancelled' => ['label' => 'Cancelled', 'icon' => 'bi-x-circle', 'color' => '#718096'],
        'refunded' => ['label' => 'Refunded', 'icon' => 'bi-arrow-return-left', 'color' => '#553c9a'],
        'pickup_today' => ['label' => 'Pickup Today', 'icon' => 'bi-calendar-check', 'color' => '#b7791f'],
        'pickup_future' => ['label' => 'Future Pickup', 'icon' => 'bi-calendar-check', 'color' => '#3182ce'],
        'pickup_overdue' => ['label' => 'Overdue Pickup', 'icon' => 'bi-calendar-x', 'color' => '#c53030'],
        'pickup_pending' => ['label' => 'Not Yet Taken', 'icon' => 'bi-bag-x', 'color' => '#c05621'],
        'balance_due' => ['label' => 'Balance Due', 'icon' => 'bi-cash-coin', 'color' => '#c53030'],
    ] as $key => $info)
                    @php $isActive = $activeFilter === $key; @endphp
                    <div wire:click="setActiveFilter('{{ $key }}')"
                        style="background:{{ $isActive ? 'var(--navy)' : '#fff' }};
                               border-radius:9px; padding:8px 14px; font-size:12px;
                               border:1.5px solid {{ $isActive ? 'var(--navy)' : 'var(--border)' }};
                               cursor:pointer; text-align:center; min-width:80px;
                               {{ $isActive ? 'box-shadow:0 2px 8px rgba(0,0,0,0.15);' : '' }}">
                        <div style="margin-bottom:4px;">
                            <i class="bi {{ $info['icon'] }}"
                                style="font-size:16px; color:{{ $isActive ? '#fff' : '#a0aec0' }};"></i>
                        </div>
                        <div
                            style="color:{{ $isActive ? '#fff' : 'var(--text-muted)' }}; font-weight:500; line-height:1.2;">
                            {{ $info['label'] }}
                        </div>
                        <div
                            style="font-weight:800; color:{{ $isActive ? '#fff' : $info['color'] }}; font-size:13px; margin-top:2px;">
                            {{ $counts[$key] }}
                        </div>
                    </div>
                @endforeach

                @if ($activeFilter)
                    <div wire:click="clearFilter"
                        style="background:#fff; border-radius:9px; padding:8px 14px; font-size:12px;
                               border:1.5px solid var(--border); cursor:pointer; text-align:center;
                               min-width:80px; color:var(--text-muted); display:flex; flex-direction:column;
                               align-items:center; justify-content:center; gap:4px;">
                        <i class="bi bi-x-circle" style="font-size:16px;"></i>
                        <div>Clear</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-card-header" style="flex-wrap:wrap; gap:10px;">
            <div style="width:250px;">
                <input type="text" wire:model.live.debounce.400ms="search" class="form-control form-control-sm"
                    placeholder="Search name, phone, CNIC, bill ref...">
            </div>
            <div class="d-flex gap-2 align-items-center">
                <input type="date" wire:model.live="dateFrom" class="form-control form-control-sm"
                    style="width:140px;">
                <span style="font-size:12px; color:var(--text-muted);">to</span>
                <input type="date" wire:model.live="dateTo" class="form-control form-control-sm"
                    style="width:140px;">
            </div>
        </div>

        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Bill Ref</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Sale Date</th>
                    <th>Total</th>
                    <th>Received</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th style="width:80px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                    @php
                        $pendingItems = $sale->items->where('pickup_status', 'pending');
                        $today = now()->toDateString();
                        $hasOverduePickup =
                            $pendingItems->filter(fn($i) => $i->pickup_date && $i->pickup_date < $today)->count() > 0;
                        $hasTodayPickup =
                            $pendingItems->filter(fn($i) => $i->pickup_date && $i->pickup_date === $today)->count() > 0;
                    @endphp
                    <tr>
                        <td>
                            <span style="font-family:monospace; font-size:12px; font-weight:700;">
                                {{ $sale->bill_ref ?? '#' . $sale->id }}
                            </span>
                        </td>
                        <td>
                            <div style="font-weight:600; font-size:13px;">{{ $sale->customer_name }}</div>
                            <div style="font-size:11px; color:var(--text-muted);">{{ $sale->customer_phone1 }}</div>
                        </td>
                        <td>
                            @foreach ($sale->items->take(2) as $item)
                                <span
                                    style="font-family:monospace; font-size:10px; background:var(--gold-light); color:var(--gold-hover); padding:1px 6px; border-radius:3px; margin-right:2px;">
                                    {{ $item->product_code }}
                                    @if ($item->qty > 1)
                                        ×{{ $item->qty }}
                                    @endif
                                </span>
                            @endforeach
                            @if ($sale->items->count() > 2)
                                <span
                                    style="font-size:10px; color:var(--text-muted);">+{{ $sale->items->count() - 2 }}</span>
                            @endif

                            {{-- Pickup date indicators --}}
                            @if ($hasOverduePickup)
                                <div style="font-size:10px; color:#c53030; font-weight:700; margin-top:2px;">
                                    <i class="bi bi-calendar-x" style="font-size:10px;"></i> Overdue pickup
                                </div>
                            @elseif($hasTodayPickup)
                                <div style="font-size:10px; color:#b7791f; font-weight:700; margin-top:2px;">
                                    <i class="bi bi-calendar-check" style="font-size:10px;"></i> Pickup today
                                </div>
                            @endif
                        </td>
                        <td style="font-size:12px;">
                            {{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') }}
                        </td>
                        <td style="font-size:13px; font-weight:600;">
                            Rs. {{ number_format($sale->total_amount, 0) }}
                        </td>
                        <td style="font-size:13px; color:#276749; font-weight:600;">
                            Rs. {{ number_format($sale->advance_paid, 0) }}
                        </td>
                        <td
                            style="font-size:13px; font-weight:700;
                                   color:{{ $sale->remaining_balance > 0 ? '#e53e3e' : '#38a169' }};">
                            Rs. {{ number_format($sale->remaining_balance, 0) }}
                            @if ($sale->remaining_balance <= 0)
                                <i class="bi bi-check-circle-fill" style="font-size:11px;"></i>
                            @endif
                        </td>
                        <td>
                            <span class="sale-status-badge {{ $sale->status }}">
                                {{ ucfirst($sale->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('sales.show', $sale->id) }}"
                                class="btn btn-sm btn-outline-secondary action-btn" title="View">
                                <i class="bi bi-eye" style="font-size:12px;"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9"
                            style="text-align:center; padding:30px; color:var(--text-muted); font-size:13px;">
                            <i class="bi bi-cart" style="font-size:32px; display:block; margin-bottom:8px;"></i>
                            No sales found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($sales->hasPages())
            <div style="padding:12px 16px; border-top:1px solid var(--border);">
                {{ $sales->links('vendor.pagination.simple-bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
