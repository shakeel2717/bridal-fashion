<div>
    @if(session('success'))
        <div class="alert alert-success py-2 mb-3" style="font-size:13px;">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">Purchase Orders</div>
            <div class="page-subtitle">Vendor purchases & stock management</div>
        </div>
        <a href="{{ route('purchase-orders.create') }}"
           class="btn btn-primary btn-sm d-flex align-items-center gap-2">
            <i class="bi bi-plus-lg"></i> New Purchase Order
        </a>
    </div>

    {{-- Stats --}}
    <div class="d-flex gap-2 mb-3 flex-wrap">
        <div style="background:#fff; border-radius:7px; padding:7px 14px; font-size:12px; border:1px solid var(--border);">
            <span style="color:var(--text-muted);">Draft</span>
            <span class="ms-2 fw-700" style="color:#718096;">{{ $counts['draft'] }}</span>
        </div>
        <div style="background:#fff; border-radius:7px; padding:7px 14px; font-size:12px; border:1px solid var(--border);">
            <span style="color:var(--text-muted);">Ordered</span>
            <span class="ms-2 fw-700" style="color:#2c5282;">{{ $counts['ordered'] }}</span>
        </div>
        <div style="background:#fff; border-radius:7px; padding:7px 14px; font-size:12px; border:1px solid var(--border);">
            <span style="color:var(--text-muted);">Partial</span>
            <span class="ms-2 fw-700" style="color:#b7791f;">{{ $counts['partial'] }}</span>
        </div>
        <div style="background:#fff; border-radius:7px; padding:7px 14px; font-size:12px; border:1px solid var(--border);">
            <span style="color:var(--text-muted);">Received</span>
            <span class="ms-2 fw-700" style="color:#276749;">{{ $counts['received'] }}</span>
        </div>
        @if($totalBalance > 0)
        <div style="background:#fff5f5; border-radius:7px; padding:7px 14px; font-size:12px; border:1px solid #fed7d7;">
            <span style="color:#c53030;">Total Due</span>
            <span class="ms-2 fw-700" style="color:#c53030;">
                Rs. {{ number_format($totalBalance, 0) }}
            </span>
        </div>
        @endif
    </div>

    <div class="table-card">
        <div class="table-card-header" style="flex-wrap:wrap; gap:10px;">
            <div class="d-flex gap-2 align-items-center">
                <div class="tab-pills" style="margin-bottom:0;">
                    <button class="tab-pill {{ $filterStatus === '' ? 'active' : '' }}"
                            wire:click="$set('filterStatus','')">All</button>
                    <button class="tab-pill {{ $filterStatus === 'draft' ? 'active' : '' }}"
                            wire:click="$set('filterStatus','draft')">Draft</button>
                    <button class="tab-pill {{ $filterStatus === 'ordered' ? 'active' : '' }}"
                            wire:click="$set('filterStatus','ordered')">Ordered</button>
                    <button class="tab-pill {{ $filterStatus === 'partial' ? 'active' : '' }}"
                            wire:click="$set('filterStatus','partial')">Partial</button>
                    <button class="tab-pill {{ $filterStatus === 'received' ? 'active' : '' }}"
                            wire:click="$set('filterStatus','received')">Received</button>
                </div>
                <select wire:model.live="filterVendor"
                        class="form-select form-select-sm" style="width:160px;">
                    <option value="">All Vendors</option>
                    @foreach($vendors as $vendor)
                        <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="width:240px;">
                <input type="text"
                       wire:model.live.debounce.400ms="search"
                       class="form-control form-control-sm"
                       placeholder="Search PO#, bill#, vendor...">
            </div>
        </div>

        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>PO Number</th>
                    <th>Vendor</th>
                    <th>Bill #</th>
                    <th>Date</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Paid</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th style="width:70px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td>
                        <span style="font-family:monospace; font-size:12px; font-weight:700; color:var(--navy);">
                            {{ $order->po_number }}
                        </span>
                    </td>
                    <td>
                        <div style="font-weight:600; font-size:13px;">{{ $order->vendor->name }}</div>
                    </td>
                    <td style="font-size:12px; font-family:monospace;">
                        {{ $order->vendor_bill_number ?? '—' }}
                    </td>
                    <td style="font-size:12px;">
                        {{ \Carbon\Carbon::parse($order->order_date)->format('d/m/Y') }}
                    </td>
                    <td style="font-size:13px; text-align:center; font-weight:600;">
                        {{ $order->items->count() }}
                    </td>
                    <td style="font-size:13px; font-weight:600;">
                        Rs. {{ number_format($order->total_amount, 0) }}
                    </td>
                    <td style="font-size:13px; color:#276749; font-weight:600;">
                        Rs. {{ number_format($order->amount_paid, 0) }}
                    </td>
                    <td style="font-size:13px; font-weight:700;
                               color:{{ $order->balance_due > 0 ? '#e53e3e' : '#38a169' }};">
                        Rs. {{ number_format($order->balance_due, 0) }}
                    </td>
                    <td>
                        <span class="po-status-badge {{ $order->status }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('purchase-orders.show', $order->id) }}"
                           class="btn btn-sm btn-outline-secondary action-btn">
                            <i class="bi bi-eye" style="font-size:12px;"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" style="text-align:center; padding:30px; color:var(--text-muted); font-size:13px;">
                        <i class="bi bi-bag" style="font-size:32px; display:block; margin-bottom:8px;"></i>
                        No purchase orders found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($orders->hasPages())
        <div style="padding:12px 16px; border-top:1px solid var(--border);">
            {{ $orders->links('vendor.pagination.simple-bootstrap-5') }}
        </div>
        @endif
    </div>
</div>