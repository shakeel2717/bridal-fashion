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

    {{-- Status Counts --}}
    <div class="d-flex gap-2 mb-3 flex-wrap">
        @foreach ([
        'booked' => ['label' => 'Booked', 'color' => '#2c5282', 'bg' => '#ebf4ff'],
        'ready' => ['label' => 'Ready', 'color' => '#b7791f', 'bg' => '#fffaf0'],
        'picked_up' => ['label' => 'Picked Up', 'color' => '#553c9a', 'bg' => '#f5f0ff'],
        'partially_picked_up' => ['label' => 'Partial', 'color' => '#c05621', 'bg' => '#fffaf0'],
        'returned' => ['label' => 'Returned', 'color' => '#276749', 'bg' => '#f0fff4'],
        'overdue' => ['label' => 'Overdue', 'color' => '#c53030', 'bg' => '#fff5f5'],
        'overpaid' => ['label' => 'Overpaid', 'color' => '#e53e3e', 'bg' => '#fff5f5'],
        'late_pickup' => ['label' => 'Late Pickup', 'color' => '#b7791f', 'bg' => '#fffaf0'],
        'late_return' => ['label' => 'Late Return', 'color' => '#c53030', 'bg' => '#fff5f5'],
    ] as $key => $info)
            @php $isActive = $activeFilter === $key; @endphp
            <div style="background:{{ $isActive ? $info['bg'] : '#fff' }}; border-radius:7px; padding:7px 14px; font-size:12px; border:1px solid {{ $isActive ? $info['color'] : 'var(--border)' }}; cursor:pointer; {{ $isActive ? 'box-shadow:0 0 0 1px ' . $info['color'] . ';' : '' }}"
                wire:click="setActiveFilter('{{ $key }}')">
                <span style="color:{{ $isActive ? $info['color'] : 'var(--text-muted)' }};">{{ $info['label'] }}</span>
                <span class="ms-2 fw-700" style="color:{{ $info['color'] }};">{{ $counts[$key] }}</span>
            </div>
        @endforeach

        @if ($activeFilter)
            <div style="background:#fff; border-radius:7px; padding:7px 14px; font-size:12px; border:1px solid var(--border); cursor:pointer; color:var(--text-muted);"
                wire:click="clearFilter">
                <i class="bi bi-x-circle me-1"></i> Clear
            </div>
        @endif
    </div>

    <div class="table-card">
        <div class="table-card-header" style="flex-wrap:wrap; gap:10px;">
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <div class="tab-pills" style="margin-bottom:0;">
                    <button class="tab-pill {{ $activeFilter === '' ? 'active' : '' }}"
                        wire:click="clearFilter">All</button>
                    <button class="tab-pill {{ $activeFilter === 'booked' ? 'active' : '' }}"
                        wire:click="setActiveFilter('booked')">Booked</button>
                    <button class="tab-pill {{ $activeFilter === 'ready' ? 'active' : '' }}"
                        wire:click="setActiveFilter('ready')">Ready</button>
                    <button class="tab-pill {{ $activeFilter === 'picked_up' ? 'active' : '' }}"
                        wire:click="setActiveFilter('picked_up')">Picked Up</button>
                    <button class="tab-pill {{ $activeFilter === 'returned' ? 'active' : '' }}"
                        wire:click="setActiveFilter('returned')">Returned</button>
                    <button class="tab-pill {{ $activeFilter === 'cancelled' ? 'active' : '' }}"
                        wire:click="setActiveFilter('cancelled')">Cancelled</button>
                </div>
            </div>
            <div style="width:250px;">
                <input type="text" wire:model.live.debounce.400ms="search" class="form-control form-control-sm"
                    placeholder="Search name, phone, CNIC, bill ref...">
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
                                    <span class="badge badge-primary bg-primary" style="font-size:15px;">
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
                                @endif
                            @else
                                <span style="color:var(--text-muted);">—</span>
                            @endif
                        </td>
                        <td style="font-size:12px;">
                            @if ($rental->return_date)
                                {{ \Carbon\Carbon::parse($rental->return_date)->format('d/m/Y') }}
                                @if (
                                    \Carbon\Carbon::parse($rental->return_date)->isPast() &&
                                        !in_array($rental->status, ['returned', 'cancelled', 'abandoned']))
                                    <span class="badge-status overdue ms-1">Late</span>
                                @endif
                            @else
                                <span style="color:var(--text-muted);">—</span>
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
