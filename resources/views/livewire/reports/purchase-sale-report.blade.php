<div>
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
        <div>
            <div class="page-title">Purchase & Sale Report</div>
            <div class="page-subtitle">PO vs sales comparison with date filter</div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="table-card mb-3" style="padding:14px 20px;">
        <div class="d-flex gap-3 align-items-end">
            <div>
                <label class="form-label">From Date</label>
                <input type="date" wire:model.live="dateFrom" class="form-control form-control-sm" style="width:150px;">
            </div>
            <div>
                <label class="form-label">To Date</label>
                <input type="date" wire:model.live="dateTo" class="form-control form-control-sm" style="width:150px;">
            </div>
        </div>
    </div>

    {{-- Summary --}}
    <div class="row g-3 mb-3">
        <div class="col-3">
            <div style="background:#fff5f5; border:1.5px solid #fed7d7; border-radius:10px; padding:14px 18px;">
                <div style="font-size:10px; font-weight:700; text-transform:uppercase; color:#c53030; margin-bottom:4px;">Total Purchases</div>
                <div style="font-size:22px; font-weight:800; color:#c53030;">Rs. {{ number_format($totalPurchases, 0) }}</div>
                <div style="font-size:11px; color:var(--text-muted);">Paid: Rs. {{ number_format($totalPOPaid, 0) }}</div>
                <div style="font-size:11px; color:#e53e3e;">Balance: Rs. {{ number_format($totalPOBalance, 0) }}</div>
            </div>
        </div>
        <div class="col-3">
            <div style="background:#f0fff4; border:1.5px solid #9ae6b4; border-radius:10px; padding:14px 18px;">
                <div style="font-size:10px; font-weight:700; text-transform:uppercase; color:#276749; margin-bottom:4px;">Sale Revenue</div>
                <div style="font-size:22px; font-weight:800; color:#276749;">Rs. {{ number_format($totalSalesRevenue, 0) }}</div>
                <div style="font-size:11px; color:var(--text-muted);">Paid: Rs. {{ number_format($totalSalesPaid, 0) }}</div>
            </div>
        </div>
        <div class="col-3">
            <div style="background:#ebf8ff; border:1.5px solid #bee3f8; border-radius:10px; padding:14px 18px;">
                <div style="font-size:10px; font-weight:700; text-transform:uppercase; color:#2c5282; margin-bottom:4px;">Rental Income</div>
                <div style="font-size:22px; font-weight:800; color:#2c5282;">Rs. {{ number_format($rentalRevenue, 0) }}</div>
            </div>
        </div>
        <div class="col-3">
            <div style="background:{{ $netPosition >= 0 ? '#f0fff4' : '#fff5f5' }}; border:1.5px solid {{ $netPosition >= 0 ? '#9ae6b4' : '#fed7d7' }}; border-radius:10px; padding:14px 18px;">
                <div style="font-size:10px; font-weight:700; text-transform:uppercase; color:{{ $netPosition >= 0 ? '#276749' : '#c53030' }}; margin-bottom:4px;">Net Position</div>
                <div style="font-size:22px; font-weight:800; color:{{ $netPosition >= 0 ? '#276749' : '#c53030' }};">
                    Rs. {{ number_format(abs($netPosition), 0) }}
                    {{ $netPosition < 0 ? '(Loss)' : '' }}
                </div>
                <div style="font-size:11px; color:var(--text-muted);">Income - PO Paid</div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Purchase Orders --}}
        <div class="col-6">
            <div class="table-card">
                <div class="table-card-header">
                    <span class="table-card-title">Purchase Orders ({{ $purchaseOrders->count() }})</span>
                </div>
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>PO #</th>
                            <th>Vendor</th>
                            <th>Date</th>
                            <th style="text-align:right;">Total</th>
                            <th style="text-align:right;">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchaseOrders as $po)
                        <tr>
                            <td>
                                <a href="{{ route('purchase-orders.show', $po->id) }}"
                                   style="font-family:monospace; font-size:11px; font-weight:700; color:var(--navy);">
                                    {{ $po->po_number }}
                                </a>
                            </td>
                            <td style="font-size:12px;">{{ $po->vendor->name }}</td>
                            <td style="font-size:12px;">{{ \Carbon\Carbon::parse($po->order_date)->format('d/m/Y') }}</td>
                            <td style="text-align:right; font-size:12px; font-weight:700;">
                                Rs. {{ number_format($po->total_amount, 0) }}
                            </td>
                            <td style="text-align:right; font-size:12px; font-weight:700;
                                       color:{{ $po->balance_due > 0 ? '#e53e3e' : '#38a169' }};">
                                Rs. {{ number_format($po->balance_due, 0) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align:center; padding:20px; color:var(--text-muted);">
                                No POs in this period
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Sales --}}
        <div class="col-6">
            <div class="table-card">
                <div class="table-card-header">
                    <span class="table-card-title">Sales ({{ $sales->count() }})</span>
                </div>
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Sale #</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th style="text-align:right;">Total</th>
                            <th style="text-align:right;">Paid</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                        <tr>
                            <td>
                                <a href="{{ route('sales.show', $sale->id) }}"
                                   style="font-family:monospace; font-size:11px; font-weight:700; color:var(--navy);">
                                    #{{ $sale->id }}
                                </a>
                            </td>
                            <td style="font-size:12px;">{{ $sale->customer_name }}</td>
                            <td style="font-size:12px;">{{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') }}</td>
                            <td style="text-align:right; font-size:12px; font-weight:700;">
                                Rs. {{ number_format($sale->total_amount, 0) }}
                            </td>
                            <td style="text-align:right; font-size:12px; font-weight:700; color:#276749;">
                                Rs. {{ number_format($sale->advance_paid, 0) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align:center; padding:20px; color:var(--text-muted);">
                                No sales in this period
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>