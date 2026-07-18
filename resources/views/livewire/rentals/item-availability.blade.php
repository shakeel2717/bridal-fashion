<div>
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">Item Availability</div>
            <div class="page-subtitle">Search a product code to see its rental schedule</div>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    {{-- Time filter buttons --}}
    <div class="table-card mb-3" style="padding:12px 16px;">
        <div class="d-flex flex-wrap align-items-center gap-2">
            @php
                $btns = [
                    'future' => ['Future', 'bi-arrow-up-right-circle'],
                    'active' => ['Current Active', 'bi-play-circle'],
                    'past'   => ['Past', 'bi-clock-history'],
                ];
            @endphp
            @foreach ($btns as $key => $b)
                <button type="button" wire:click="setFilter('{{ $key }}')"
                    class="btn btn-sm {{ $timeFilter === $key ? 'btn-primary' : 'btn-outline-secondary' }}">
                    <i class="bi {{ $b[1] }} me-1"></i> {{ $b[0] }}
                </button>
            @endforeach
            <span style="font-size:11px; color:var(--text-muted); margin-left:6px;">
                @if ($timeFilter === 'future')
                    Upcoming bookings (today onward)
                @elseif ($timeFilter === 'active')
                    Currently out (picked up today / in progress)
                @else
                    Already returned / past bookings
                @endif
            </span>
        </div>
    </div>

    {{-- Product search --}}
    <div class="table-card mb-3" style="padding:14px 16px;">
        <label class="form-label">Product Code / Name</label>
        <div class="position-relative" style="max-width:420px;">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                <input type="text" wire:model.live.debounce.300ms="productSearch"
                    class="form-control" placeholder="Type a product code, e.g. R-1024" autocomplete="off">
                @if ($productSearch)
                    <button class="btn btn-outline-secondary" wire:click="clearSelection" title="Clear">
                        <i class="bi bi-x"></i>
                    </button>
                @endif
            </div>

            @if (count($suggestions) > 0)
                <ul class="list-group position-absolute w-100 shadow-sm"
                    style="z-index:1050; max-height:240px; overflow:auto;">
                    @foreach ($suggestions as $sug)
                        <li class="list-group-item list-group-item-action"
                            style="cursor:pointer; font-size:12px; padding:6px 10px;"
                            wire:click="selectProduct({{ $sug['id'] }})">
                            <span style="font-family:monospace; font-weight:700;">{{ $sug['code'] }}</span>
                            — {{ $sug['name'] }}
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    {{-- Results --}}
    @if (! $hasQuery)
        <div class="table-card" style="padding:50px; text-align:center; color:var(--text-muted);">
            <i class="bi bi-upc-scan" style="font-size:40px; display:block; margin-bottom:10px; color:#cbd5e0;"></i>
            <div style="font-size:14px; font-weight:600; color:#a0aec0;">
                Search a product code above to see its bookings
            </div>
        </div>
    @else
        <div class="table-card">
            <div class="table-card-header">
                <span class="table-card-title">
                    Bookings ({{ $bookings->count() }})
                    @if ($selectedProduct)
                        — <span style="font-family:monospace;">{{ $selectedProduct['code'] }}</span> {{ $selectedProduct['name'] }}
                    @endif
                </span>
            </div>
            <table class="table table-hover mb-0" style="font-size:12px;">
                <thead>
                    <tr>
                        <th>Bill / Rental</th>
                        <th>Item</th>
                        <th>Customer</th>
                        <th>Pickup</th>
                        <th>Return</th>
                        <th>Status</th>
                        <th>When</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $item)
                        @php
                            $pd = $item->rental->pickup_date ? \Carbon\Carbon::parse($item->rental->pickup_date) : null;
                            $rd = $item->rental->return_date ? \Carbon\Carbon::parse($item->rental->return_date) : null;
                            $today = \Carbon\Carbon::today();
                            if ($rd && $rd->lt($today))     { $when = ['Past', '#718096']; }
                            elseif ($pd && $pd->gt($today)) { $when = ['Upcoming', '#3182ce']; }
                            else                            { $when = ['Active', '#38a169']; }
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('rentals.show', $item->rental_id) }}"
                                   style="font-family:monospace; font-weight:700; color:var(--navy); text-decoration:none;">
                                    {{ $item->rental->bill_ref ?: '#' . $item->rental_id }}
                                </a>
                            </td>
                            <td>
                                <span style="font-family:monospace; font-weight:700;">{{ $item->product_code }}</span>
                                <div style="font-size:11px; color:var(--text-muted);">{{ \Str::limit($item->product_name, 26) }}</div>
                            </td>
                            <td>{{ $item->rental->customer_name }}</td>
                            <td style="white-space:nowrap;">{{ $pd ? $pd->format('d/m/Y') : '—' }}</td>
                            <td style="white-space:nowrap;">{{ $rd ? $rd->format('d/m/Y') : '—' }}</td>
                            <td>
                                <span class="rental-status-badge {{ $item->rental->status }}" style="font-size:9px; padding:1px 6px;">
                                    {{ ucfirst(str_replace('_', ' ', $item->rental->status)) }}
                                </span>
                            </td>
                            <td>
                                <span style="font-size:10px; font-weight:700; padding:2px 8px; border-radius:10px; color:#fff; background:{{ $when[1] }};">
                                    {{ $when[0] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center; padding:30px; color:var(--text-muted);">
                                No {{ $timeFilter === 'future' ? 'upcoming' : ($timeFilter === 'past' ? 'past' : 'currently active') }} bookings found for this item.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>
