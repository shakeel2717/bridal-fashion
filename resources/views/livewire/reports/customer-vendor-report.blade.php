<div>
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
        <div>
            <div class="page-title">Customer & Vendor Report</div>
            <div class="page-subtitle">Activity per customer and vendor</div>
        </div>
    </div>

    {{-- Tabs + Filters --}}
    <div class="table-card mb-3" style="padding:14px 20px;">
        <div class="d-flex gap-3 align-items-end flex-wrap">
            <div>
                <label class="form-label">View</label>
                <div class="walkin-toggle" style="max-width:220px;">
                    <button type="button"
                            class="toggle-btn {{ $activeTab === 'customers' ? 'active' : '' }}"
                            wire:click="$set('activeTab','customers')">
                        <i class="bi bi-people me-1"></i> Customers
                    </button>
                    <button type="button"
                            class="toggle-btn {{ $activeTab === 'vendors' ? 'active' : '' }}"
                            wire:click="$set('activeTab','vendors')">
                        <i class="bi bi-shop me-1"></i> Vendors
                    </button>
                </div>
            </div>
            <div>
                <label class="form-label">From Date</label>
                <input type="date" wire:model.live="dateFrom" class="form-control form-control-sm" style="width:150px;">
            </div>
            <div>
                <label class="form-label">To Date</label>
                <input type="date" wire:model.live="dateTo" class="form-control form-control-sm" style="width:150px;">
            </div>
            <div>
                <label class="form-label">Search</label>
                <input type="text" wire:model.live.debounce.400ms="search"
                       class="form-control form-control-sm" style="width:180px;"
                       placeholder="Name or phone...">
            </div>
        </div>
    </div>

    {{-- Customers --}}
    @if($activeTab === 'customers')
    <div class="table-card">
        <div class="table-card-header">
            <span class="table-card-title">
                Customer Activity ({{ $customerData->count() }})
            </span>
        </div>
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th style="text-align:center;">Rentals</th>
                    <th style="text-align:center;">Sales</th>
                    <th style="text-align:right;">Total Value</th>
                    <th style="text-align:right;">Total Paid</th>
                    <th style="text-align:right;">Balance</th>
                    <th>Last Visit</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customerData as $c)
                <tr>
                    <td style="font-size:13px; font-weight:600;">{{ $c['name'] }}</td>
                    <td style="font-size:12px; color:var(--text-muted);">{{ $c['phone'] }}</td>
                    <td style="text-align:center; font-size:14px; font-weight:700; color:#2c5282;">
                        {{ $c['rental_count'] }}
                    </td>
                    <td style="text-align:center; font-size:14px; font-weight:700; color:#276749;">
                        {{ $c['sale_count'] }}
                    </td>
                    <td style="text-align:right; font-size:12px; font-weight:600;">
                        Rs. {{ number_format($c['total_rental'], 0) }}
                    </td>
                    <td style="text-align:right; font-size:12px; font-weight:700; color:#276749;">
                        Rs. {{ number_format($c['total_paid'], 0) }}
                    </td>
                    <td style="text-align:right; font-size:12px; font-weight:700;
                               color:{{ $c['total_balance'] > 0 ? '#e53e3e' : '#38a169' }};">
                        Rs. {{ number_format($c['total_balance'], 0) }}
                    </td>
                    <td style="font-size:11px; color:var(--text-muted);">
                        {{ $c['last_visit'] ? \Carbon\Carbon::parse($c['last_visit'])->format('d/m/Y') : '—' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center; padding:30px; color:var(--text-muted);">
                        No customer activity in selected period
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif

    {{-- Vendors --}}
    @if($activeTab === 'vendors')
    <div class="table-card">
        <div class="table-card-header">
            <span class="table-card-title">
                Vendor Activity ({{ $vendorData->count() }})
            </span>
        </div>
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Vendor</th>
                    <th>Phone</th>
                    <th style="text-align:center;">PO Count</th>
                    <th style="text-align:right;">Total PO</th>
                    <th style="text-align:right;">Total Paid</th>
                    <th style="text-align:right;">Balance Due</th>
                    <th>Last Order</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vendorData as $v)
                <tr>
                    <td style="font-size:13px; font-weight:600;">{{ $v['name'] }}</td>
                    <td style="font-size:12px; color:var(--text-muted);">{{ $v['phone'] ?? '—' }}</td>
                    <td style="text-align:center; font-size:14px; font-weight:700; color:#2c5282;">
                        {{ $v['po_count'] }}
                    </td>
                    <td style="text-align:right; font-size:12px; font-weight:600;">
                        Rs. {{ number_format($v['total_po'], 0) }}
                    </td>
                    <td style="text-align:right; font-size:12px; font-weight:700; color:#276749;">
                        Rs. {{ number_format($v['total_paid'], 0) }}
                    </td>
                    <td style="text-align:right; font-size:12px; font-weight:700;
                               color:{{ $v['total_balance'] > 0 ? '#e53e3e' : '#38a169' }};">
                        Rs. {{ number_format($v['total_balance'], 0) }}
                    </td>
                    <td style="font-size:11px; color:var(--text-muted);">
                        {{ $v['last_order'] ? \Carbon\Carbon::parse($v['last_order'])->format('d/m/Y') : '—' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center; padding:30px; color:var(--text-muted);">
                        No vendor activity in selected period
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif
</div>