<div>
    @if (session('success'))
        <div class="alert alert-success py-2 mb-3" style="font-size:13px;">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">Rentals</div>
            <div class="page-subtitle">All rental bookings</div>
        </div>
        <a href="{{ route('rentals.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
            <i class="bi bi-plus-lg"></i> New Rental
        </a>
    </div>

    {{-- Duplicate Bookings Warning --}}
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
        </div>
    @endif

    {{-- Status + Special Filters (single combined row) --}}
    <div class="d-flex gap-2 mb-3 flex-wrap">
        @foreach ([
        'booked' => ['label' => 'Booked', 'icon' => 'bi-journal-bookmark', 'color' => '#2c5282', 'bg' => '#ebf4ff'],
        'ready' => ['label' => 'Ready', 'icon' => 'bi-check2-circle', 'color' => '#b7791f', 'bg' => '#fffaf0'],
        'picked_up' => ['label' => 'Picked Up', 'icon' => 'bi-box-arrow-up', 'color' => '#553c9a', 'bg' => '#f5f0ff'],
        'partially_picked_up' => ['label' => 'Partial', 'icon' => 'bi-box-arrow-in-up', 'color' => '#c05621', 'bg' => '#fffaf0'],
        'returned' => ['label' => 'Returned', 'icon' => 'bi-box-arrow-in-down', 'color' => '#276749', 'bg' => '#f0fff4'],
        'cancelled' => ['label' => 'Cancelled', 'icon' => 'bi-x-circle', 'color' => '#718096', 'bg' => '#f7fafc'],
        'due' => ['label' => 'Due', 'icon' => 'bi-cash-coin', 'color' => '#c53030', 'bg' => '#fff5f5'],
        'overpaid' => ['label' => 'Overpaid', 'icon' => 'bi-arrow-up-circle', 'color' => '#e53e3e', 'bg' => '#fff5f5'],
        'late_pickup' => ['label' => 'Late Pickup', 'icon' => 'bi-clock-history', 'color' => '#b7791f', 'bg' => '#fffaf0'],
        'late_return' => ['label' => 'Late Return', 'icon' => 'bi-alarm', 'color' => '#c53030', 'bg' => '#fff5f5'],
        'no_dates' => ['label' => 'No Dates', 'icon' => 'bi-calendar-x', 'color' => '#718096', 'bg' => '#f7fafc'],
    ] as $key => $info)
            @php $isActive = $activeFilter === $key; @endphp
            <div wire:click="setActiveFilter('{{ $key }}')"
                style="background:{{ $isActive ? $info['bg'] : '#fff' }};
                       border-radius:9px;
                       padding:8px 14px;
                       font-size:12px;
                       border:1.5px solid {{ $isActive ? $info['color'] : 'var(--border)' }};
                       cursor:pointer;
                       text-align:center;
                       min-width:80px;
                       {{ $isActive ? 'box-shadow:0 0 0 1px ' . $info['color'] . ';' : '' }}">
                <div style="margin-bottom:4px;">
                    <i class="bi {{ $info['icon'] }}"
                        style="font-size:16px; color:{{ $isActive ? $info['color'] : '#a0aec0' }};"></i>
                </div>
                <div
                    style="color:{{ $isActive ? $info['color'] : 'var(--text-muted)' }}; font-weight:500; line-height:1.2;">
                    {{ $info['label'] }}
                </div>
                <div style="font-weight:800; color:{{ $info['color'] }}; font-size:13px; margin-top:2px;">
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
                    <th style="width:80px;">Bill Ref</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Pickup</th>
                    <th>Return</th>
                    <th>Amount</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th style="width:130px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rentals as $rental)
                    <tr>
                        <td>
                            <span style="font-family:monospace; font-size:12px; font-weight:700;">
                                {{ $rental->bill_ref ?? '#' . $rental->id }}
                            </span>
                            <div style="font-size:10px; color:var(--text-muted);">
                                {{ \Carbon\Carbon::parse($rental->booking_date)->format('d/m/Y') }}
                            </div>
                        </td>
                        <td>
                            <div style="font-weight:600; font-size:13px;">{{ $rental->customer_name }}</div>
                            <div style="font-size:11px; color:var(--text-muted);">{{ $rental->customer_phone1 }}</div>
                            @if ($rental->customer_cnic)
                                <div style="font-size:10px; color:var(--text-muted); font-family:monospace;">
                                    {{ $rental->customer_cnic }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <div style="font-size:12px;">
                                @foreach ($rental->items->take(2) as $item)
                                    <span class="badge badge-primary bg-primary" style="font-size:11px;">
                                        {{ $item->product_code }}
                                    </span>
                                @endforeach
                                @if ($rental->items->count() > 2)
                                    <span style="font-size:10px; color:var(--text-muted);">
                                        +{{ $rental->items->count() - 2 }} more
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td style="font-size:12px;">
                            @if ($rental->pickup_date)
                                {{ \Carbon\Carbon::parse($rental->pickup_date)->format('d/m/Y') }}
                                @if (\Carbon\Carbon::parse($rental->pickup_date)->isToday())
                                    <span class="badge-status ready ms-1">Today</span>
                                @elseif (in_array($rental->status, ['booked', 'ready']) && \Carbon\Carbon::parse($rental->pickup_date)->isPast())
                                    @php $daysLate = (int) \Carbon\Carbon::parse($rental->pickup_date)->diffInDays(now()); @endphp
                                    <div style="font-size:10px; color:#b7791f; font-weight:700; margin-top:2px;">
                                        <i class="bi bi-clock-history" style="font-size:10px;"></i>
                                        {{ $daysLate }}d late
                                    </div>
                                @endif
                            @else
                                <span style="color:#e53e3e; font-size:11px; font-weight:600;">Not Set</span>
                            @endif
                        </td>
                        <td style="font-size:12px;">
                            @if ($rental->return_date)
                                {{ \Carbon\Carbon::parse($rental->return_date)->format('d/m/Y') }}
                                @if (
                                    !in_array($rental->status, ['returned', 'cancelled', 'abandoned']) &&
                                        \Carbon\Carbon::parse($rental->return_date)->isPast())
                                    @php $daysLate = (int) \Carbon\Carbon::parse($rental->return_date)->diffInDays(now()); @endphp
                                    <div style="font-size:10px; color:#c53030; font-weight:700; margin-top:2px;">
                                        <i class="bi bi-alarm" style="font-size:10px;"></i>
                                        {{ $daysLate }}d late
                                    </div>
                                @endif
                            @else
                                <span style="color:#e53e3e; font-size:11px; font-weight:600;">Not Set</span>
                            @endif
                        </td>
                        <td style="font-size:13px; font-weight:600;">
                            Rs. {{ number_format($rental->total_amount, 0) }}
                            @if ($rental->advance_paid > 0)
                                <div style="font-size:10px; color:var(--text-muted);">
                                    Adv: Rs. {{ number_format($rental->advance_paid, 0) }}
                                </div>
                            @endif
                        </td>
                        @php
                            $paid = (float) ($rental->payments_sum_amount ?? 0);
                            $balance = $rental->total_amount - $paid;
                        @endphp
                        <td
                            style="font-size:13px; font-weight:700; color:{{ $balance > 0 ? '#e53e3e' : ($balance < 0 ? '#38a169' : '#38a169') }};">
                            @if ($balance < 0)
                                − Rs. {{ number_format(abs($balance), 0) }}
                                <div style="font-size:9px; color:#38a169; font-weight:600;">Overpaid</div>
                            @elseif ($balance == 0)
                                <span style="color:#38a169;">Paid</span>
                            @else
                                Rs. {{ number_format($balance, 0) }}
                            @endif
                        </td>
                        <td>
                            <span class="rental-status-badge {{ $rental->status }}">
                                {{ ucfirst(str_replace('_', ' ', $rental->status)) }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                <a href="{{ route('rentals.show', $rental->id) }}"
                                    class="btn btn-sm btn-outline-secondary action-btn" title="View Details">
                                    <i class="bi bi-eye" style="font-size:12px;"></i>
                                </a>

                                @if ($rental->status === 'booked')
                                    <button class="btn btn-sm btn-outline-warning action-btn"
                                        wire:click="quickStatus({{ $rental->id }}, 'ready')" title="Mark Ready">
                                        <i class="bi bi-check" style="font-size:12px;"></i>
                                    </button>
                                @endif

                                @if (in_array($rental->status, ['ready', 'booked']))
                                    <button class="btn btn-sm btn-outline-success action-btn"
                                        wire:click="quickStatus({{ $rental->id }}, 'picked_up')"
                                        title="Mark Picked Up">
                                        <i class="bi bi-box-arrow-up" style="font-size:12px;"></i>
                                    </button>
                                @endif

                                @if (in_array($rental->status, ['picked_up', 'partially_picked_up']))
                                    <button class="btn btn-sm btn-outline-primary action-btn"
                                        wire:click="quickStatus({{ $rental->id }}, 'returned')"
                                        title="Mark Returned">
                                        <i class="bi bi-box-arrow-in-down" style="font-size:12px;"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9"
                            style="text-align:center; padding:30px; color:var(--text-muted); font-size:13px;">
                            <i class="bi bi-box-seam" style="font-size:32px; display:block; margin-bottom:8px;"></i>
                            No rentals found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($rentals->hasPages())
            <div style="padding:12px 16px; border-top:1px solid var(--border);">
                {{ $rentals->links('vendor.pagination.simple-bootstrap-5') }}
            </div>
        @endif
    </div>

    {{-- Quick Status Confirm --}}
    @if ($quickStatusId)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered" style="max-width:360px;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title">Confirm Status Change</h6>
                    </div>
                    <div class="modal-body" style="font-size:13px;">
                        Mark this rental as
                        <strong>{{ ucfirst(str_replace('_', ' ', $newStatus)) }}</strong>?
                        @if ($newStatus === 'returned')
                            <div class="mt-2" style="font-size:12px; color:var(--text-muted);">
                                All items will be marked as returned.
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer gap-2">
                        <button class="btn btn-sm btn-outline-secondary"
                            wire:click="$set('quickStatusId', null)">Cancel</button>
                        <button class="btn btn-sm btn-primary" wire:click="applyStatus">Confirm</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
