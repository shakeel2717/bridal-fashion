<div>
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
        <div>
            <div class="page-title">Single Item Report</div>
            <div class="page-subtitle">Full booking history for one product</div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="table-card mb-3" style="padding:16px 20px; overflow:visible;">
        <div class="row g-3">
            <div class="col-5" style="position:relative;">
                <label class="form-label">Search Product</label>
                <input type="text"
                       wire:model.live.debounce.300ms="productSearch"
                       wire:keyup="searchProducts"
                       class="form-control"
                       placeholder="Type code or name...">
                @if(count($searchResults) > 0)
                <div class="product-search-dropdown">
                    @foreach($searchResults as $r)
                    <div class="search-item" wire:click="selectProduct({{ $r['id'] }})">
                        <span class="search-item-code">{{ $r['code'] }}</span>
                        <div class="search-item-name">{{ $r['name'] }}</div>
                        <div class="search-item-category">{{ ucfirst($r['type']) }}</div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            <div class="col-3">
                <label class="form-label">From Date</label>
                <input type="date" wire:model.live="dateFrom" class="form-control">
            </div>
            <div class="col-3">
                <label class="form-label">To Date</label>
                <input type="date" wire:model.live="dateTo" class="form-control">
            </div>
        </div>
    </div>

    @if($selectedProduct)

    {{-- Product Info --}}
    <div class="table-card mb-3" style="padding:14px 20px;">
        <div class="d-flex align-items-center gap-3">
            @if($selectedProduct['photo'])
                <img src="{{ Storage::url($selectedProduct['photo']) }}"
                     style="width:50px; height:50px; object-fit:cover; border-radius:8px; border:1px solid var(--border);">
            @endif
            <div>
                <div style="font-size:15px; font-weight:700; color:var(--navy);">{{ $selectedProduct['name'] }}</div>
                <div style="font-size:12px; color:var(--text-muted);">
                    <span class="tbl-code-badge">{{ $selectedProduct['code'] }}</span>
                    {{ $selectedProduct['category'] }}
                    · {{ ucfirst($selectedProduct['type']) }}
                </div>
            </div>
        </div>
    </div>

    @if(!empty($stats))
    {{-- Stats --}}
    <div class="row g-3 mb-3">
        <div class="col-3">
            <div style="background:#ebf8ff; border:1.5px solid #bee3f8; border-radius:10px; padding:14px 18px;">
                <div style="font-size:10px; font-weight:700; text-transform:uppercase; color:#2c5282; margin-bottom:4px;">Rental Bookings</div>
                <div style="font-size:24px; font-weight:800; color:#2c5282;">{{ $stats['total_rental_bookings'] }}</div>
            </div>
        </div>
        <div class="col-3">
            <div style="background:#f0fff4; border:1.5px solid #9ae6b4; border-radius:10px; padding:14px 18px;">
                <div style="font-size:10px; font-weight:700; text-transform:uppercase; color:#276749; margin-bottom:4px;">Rental Revenue</div>
                <div style="font-size:20px; font-weight:800; color:#276749;">Rs. {{ number_format($stats['rental_revenue'], 0) }}</div>
            </div>
        </div>
        <div class="col-3">
            <div style="background:#faf5ff; border:1.5px solid #d6bcfa; border-radius:10px; padding:14px 18px;">
                <div style="font-size:10px; font-weight:700; text-transform:uppercase; color:#553c9a; margin-bottom:4px;">Sale Count</div>
                <div style="font-size:24px; font-weight:800; color:#553c9a;">{{ $stats['total_sale_bookings'] }}</div>
            </div>
        </div>
        <div class="col-3">
            <div style="background:#fffff0; border:1.5px solid #f6e05e; border-radius:10px; padding:14px 18px;">
                <div style="font-size:10px; font-weight:700; text-transform:uppercase; color:#b7791f; margin-bottom:4px;">Sale Revenue</div>
                <div style="font-size:20px; font-weight:800; color:#b7791f;">Rs. {{ number_format($stats['sale_revenue'], 0) }}</div>
            </div>
        </div>
    </div>

    {{-- Rental Bookings Table --}}
    @if($rentalBookings->count() > 0)
    <div class="table-card mb-3">
        <div class="table-card-header">
            <span class="table-card-title">Rental Bookings ({{ $rentalBookings->count() }})</span>
        </div>
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Rental #</th>
                    <th>Customer</th>
                    <th>Booking Date</th>
                    <th>Pickup</th>
                    <th>Return</th>
                    <th>Status</th>
                    <th style="text-align:right;">Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rentalBookings as $item)
                <tr>
                    <td>
                        <a href="{{ route('rentals.show', $item->rental_id) }}"
                           style="font-family:monospace; font-size:12px; font-weight:700; color:var(--navy);">
                            #{{ $item->rental_id }}
                        </a>
                    </td>
                    <td style="font-size:13px; font-weight:600;">{{ $item->rental->customer_name }}</td>
                    <td style="font-size:12px;">{{ \Carbon\Carbon::parse($item->rental->booking_date)->format('d/m/Y') }}</td>
                    <td style="font-size:12px;">{{ \Carbon\Carbon::parse($item->rental->pickup_date)->format('d/m/Y') }}</td>
                    <td style="font-size:12px;">{{ \Carbon\Carbon::parse($item->rental->return_date)->format('d/m/Y') }}</td>
                    <td>
                        <span class="badge-status {{ $item->rental->status }}">
                            {{ ucfirst($item->rental->status) }}
                        </span>
                    </td>
                    <td style="text-align:right; font-weight:700;">
                        Rs. {{ number_format($item->rental_price, 0) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Sale Bookings --}}
    @if($saleBookings->count() > 0)
    <div class="table-card">
        <div class="table-card-header">
            <span class="table-card-title">Sales ({{ $saleBookings->count() }})</span>
        </div>
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Sale #</th>
                    <th>Customer</th>
                    <th>Sale Date</th>
                    <th>Qty</th>
                    <th>Status</th>
                    <th style="text-align:right;">Revenue</th>
                </tr>
            </thead>
            <tbody>
                @foreach($saleBookings as $item)
                <tr>
                    <td>
                        <a href="{{ route('sales.show', $item->sale_id) }}"
                           style="font-family:monospace; font-size:12px; font-weight:700; color:var(--navy);">
                            #{{ $item->sale_id }}
                        </a>
                    </td>
                    <td style="font-size:13px; font-weight:600;">{{ $item->sale->customer_name }}</td>
                    <td style="font-size:12px;">{{ \Carbon\Carbon::parse($item->sale->sale_date)->format('d/m/Y') }}</td>
                    <td style="font-size:13px; font-weight:600; text-align:center;">{{ $item->qty }}</td>
                    <td>
                        <span class="badge-status {{ $item->sale->status }}">
                            {{ ucfirst($item->sale->status) }}
                        </span>
                    </td>
                    <td style="text-align:right; font-weight:700;">
                        Rs. {{ number_format($item->qty * $item->unit_price, 0) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @else
    <div style="text-align:center; padding:30px; color:var(--text-muted); font-size:13px;">
        No bookings found for selected date range.
    </div>
    @endif

    @else
    <div class="table-card" style="padding:40px; text-align:center; color:var(--text-muted);">
        <i class="bi bi-search" style="font-size:40px; display:block; margin-bottom:12px; color:var(--gold);"></i>
        Search for a product above to see its booking history.
    </div>
    @endif
</div>