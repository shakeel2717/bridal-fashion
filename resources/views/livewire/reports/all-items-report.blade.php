<div>
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
        <div>
            <div class="page-title">All Items Summary</div>
            <div class="page-subtitle">Category-wise booking summary with date filter</div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="table-card mb-3" style="padding:14px 20px;">
        <div class="d-flex gap-3 align-items-end flex-wrap">
            <div>
                <label class="form-label">From Date</label>
                <input type="date" wire:model.live="dateFrom" class="form-control form-control-sm" style="width:150px;">
            </div>
            <div>
                <label class="form-label">To Date</label>
                <input type="date" wire:model.live="dateTo" class="form-control form-control-sm" style="width:150px;">
            </div>
            <div>
                <label class="form-label">Category</label>
                <select wire:model.live="filterCategory" class="form-select form-select-sm" style="width:160px;">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Summary Stats --}}
    <div class="row g-3 mb-3">
        <div class="col-4">
            <div style="background:#ebf8ff; border:1.5px solid #bee3f8; border-radius:10px; padding:14px 18px;">
                <div style="font-size:10px; font-weight:700; text-transform:uppercase; color:#2c5282; margin-bottom:4px;">Total Bookings</div>
                <div style="font-size:28px; font-weight:800; color:#2c5282;">{{ $totalBookings }}</div>
            </div>
        </div>
        <div class="col-4">
            <div style="background:#f0fff4; border:1.5px solid #9ae6b4; border-radius:10px; padding:14px 18px;">
                <div style="font-size:10px; font-weight:700; text-transform:uppercase; color:#276749; margin-bottom:4px;">Total Revenue</div>
                <div style="font-size:24px; font-weight:800; color:#276749;">Rs. {{ number_format($totalRevenue, 0) }}</div>
            </div>
        </div>
        <div class="col-4">
            <div style="background:#faf5ff; border:1.5px solid #d6bcfa; border-radius:10px; padding:14px 18px;">
                <div style="font-size:10px; font-weight:700; text-transform:uppercase; color:#553c9a; margin-bottom:4px;">Unique Items Booked</div>
                <div style="font-size:28px; font-weight:800; color:#553c9a;">{{ $rentalData->count() }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Category Summary --}}
        <div class="col-4">
            <div class="table-card" style="padding:16px 20px;">
                <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:12px;">
                    Category Summary
                </div>
                @forelse($categorySummary as $cat)
                <div style="padding:8px 0; border-bottom:1px solid var(--border);">
                    <div style="font-size:13px; font-weight:600; color:var(--text-primary);">{{ $cat['category'] }}</div>
                    <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">
                        {{ $cat['item_count'] }} items · {{ $cat['total_bookings'] }} bookings
                    </div>
                    <div style="font-size:12px; font-weight:700; color:#276749;">
                        Rs. {{ number_format($cat['total_revenue'], 0) }}
                    </div>
                </div>
                @empty
                <div style="font-size:12px; color:var(--text-muted); text-align:center; padding:20px 0;">
                    No data for selected period
                </div>
                @endforelse
            </div>
        </div>

        {{-- Item Detail --}}
        <div class="col-8">
            <div class="table-card">
                <div class="table-card-header">
                    <span class="table-card-title">Item Booking Count</span>
                </div>
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th style="text-align:center;">Bookings</th>
                            <th style="text-align:right;">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rentalData as $item)
                        <tr>
                            <td><span class="tbl-code-badge">{{ $item['code'] }}</span></td>
                            <td style="font-size:13px; font-weight:600;">{{ $item['name'] }}</td>
                            <td style="font-size:12px; color:var(--text-muted);">{{ $item['category'] }}</td>
                            <td style="text-align:center; font-size:15px; font-weight:800; color:#2c5282;">
                                {{ $item['booking_count'] }}
                            </td>
                            <td style="text-align:right; font-weight:700; color:#276749;">
                                Rs. {{ number_format($item['revenue'], 0) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align:center; padding:20px; color:var(--text-muted);">
                                No bookings in selected period
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>