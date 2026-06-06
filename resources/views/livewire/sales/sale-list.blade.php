<div>
    @if(session('success'))
        <div class="alert alert-success py-2 mb-3" style="font-size:13px;">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">Sales</div>
            <div class="page-subtitle">All product sales</div>
        </div>
        <a href="{{ route('sales.create') }}"
           class="btn btn-primary btn-sm d-flex align-items-center gap-2">
            <i class="bi bi-plus-lg"></i> New Sale
        </a>
    </div>

    {{-- Counts --}}
    <div class="d-flex gap-2 mb-3 flex-wrap">
        <div style="background:#fff; border-radius:7px; padding:7px 14px; font-size:12px; border:1px solid var(--border);">
            <span style="color:var(--text-muted);">Completed</span>
            <span class="ms-2 fw-700" style="color:#276749;">{{ $counts['completed'] }}</span>
        </div>
        <div style="background:#fff; border-radius:7px; padding:7px 14px; font-size:12px; border:1px solid var(--border);">
            <span style="color:var(--text-muted);">Pending</span>
            <span class="ms-2 fw-700" style="color:#b7791f;">{{ $counts['pending'] }}</span>
        </div>
        <div style="background:#fff; border-radius:7px; padding:7px 14px; font-size:12px; border:1px solid var(--border);">
            <span style="color:var(--text-muted);">Cancelled</span>
            <span class="ms-2 fw-700" style="color:#c53030;">{{ $counts['cancelled'] }}</span>
        </div>
        <div style="background:#fff; border-radius:7px; padding:7px 14px; font-size:12px; border:1px solid var(--border);">
            <span style="color:var(--text-muted);">Total</span>
            <span class="ms-2 fw-700">{{ $counts['total'] }}</span>
        </div>
    </div>

    <div class="table-card">
        <div class="table-card-header" style="flex-wrap:wrap; gap:10px;">
            <div class="d-flex gap-2">
                <div class="tab-pills" style="margin-bottom:0;">
                    <button class="tab-pill {{ $filterStatus === '' ? 'active' : '' }}"
                            wire:click="$set('filterStatus','')">All</button>
                    <button class="tab-pill {{ $filterStatus === 'completed' ? 'active' : '' }}"
                            wire:click="$set('filterStatus','completed')">Completed</button>
                    <button class="tab-pill {{ $filterStatus === 'pending' ? 'active' : '' }}"
                            wire:click="$set('filterStatus','pending')">Pending</button>
                    <button class="tab-pill {{ $filterStatus === 'cancelled' ? 'active' : '' }}"
                            wire:click="$set('filterStatus','cancelled')">Cancelled</button>
                    <button class="tab-pill {{ $filterStatus === 'refunded' ? 'active' : '' }}"
                            wire:click="$set('filterStatus','refunded')">Refunded</button>
                </div>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <input type="date" wire:model.live="dateFrom"
                       class="form-control form-control-sm" style="width:140px;">
                <span style="font-size:12px; color:var(--text-muted);">to</span>
                <input type="date" wire:model.live="dateTo"
                       class="form-control form-control-sm" style="width:140px;">
                <input type="text"
                       wire:model.live.debounce.400ms="search"
                       class="form-control form-control-sm"
                       style="width:220px;"
                       placeholder="Search name, phone, CNIC...">
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
                        @foreach($sale->items->take(2) as $item)
                            <span style="font-family:monospace; font-size:10px; background:var(--gold-light); color:var(--gold-hover); padding:1px 6px; border-radius:3px; margin-right:2px;">
                                {{ $item->product_code }}
                                @if($item->qty > 1) ×{{ $item->qty }} @endif
                            </span>
                        @endforeach
                        @if($sale->items->count() > 2)
                            <span style="font-size:10px; color:var(--text-muted);">+{{ $sale->items->count() - 2 }}</span>
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
                    <td style="font-size:13px; font-weight:700; color:{{ $sale->remaining_balance > 0 ? '#e53e3e' : '#38a169' }};">
                        Rs. {{ number_format($sale->remaining_balance, 0) }}
                        @if($sale->remaining_balance <= 0)
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
                    <td colspan="9" style="text-align:center; padding:30px; color:var(--text-muted); font-size:13px;">
                        <i class="bi bi-cart" style="font-size:32px; display:block; margin-bottom:8px;"></i>
                        No sales found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($sales->hasPages())
        <div style="padding:12px 16px; border-top:1px solid var(--border);">
            {{ $sales->links('vendor.pagination.simple-bootstrap-5') }}
        </div>
        @endif
    </div>
</div>