<div>
    @if(session('success'))
        <div class="alert alert-success py-2 mb-3" style="font-size:13px;">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">Alerts & Notifications</div>
            <div class="page-subtitle">
                {{ now()->format('l, d F Y') }} — Daily Operations
            </div>
        </div>

        {{-- Received By Selector --}}
        <div class="d-flex align-items-center gap-2">
            <span style="font-size:12px; color:var(--text-muted);">Received by:</span>
            <select wire:model.live="receivedBy" class="received-by-select">
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Summary Stat Bars --}}
    <div class="row g-2 mb-3">
        <div class="col-2">
            <div style="background:#fff5f5; border:1.5px solid #fed7d7; border-radius:8px; padding:10px 14px; text-align:center;">
                <div style="font-size:24px; font-weight:800; color:#c53030;">{{ $counts['overdue'] }}</div>
                <div style="font-size:10px; font-weight:700; color:#e53e3e; text-transform:uppercase;">Overdue</div>
            </div>
        </div>
        <div class="col-2">
            <div style="background:#fffff0; border:1.5px solid #f6e05e; border-radius:8px; padding:10px 14px; text-align:center;">
                <div style="font-size:24px; font-weight:800; color:#b7791f;">{{ $counts['return_today'] }}</div>
                <div style="font-size:10px; font-weight:700; color:#d69e2e; text-transform:uppercase;">Return Today</div>
            </div>
        </div>
        <div class="col-2">
            <div style="background:#ebf8ff; border:1.5px solid #bee3f8; border-radius:8px; padding:10px 14px; text-align:center;">
                <div style="font-size:24px; font-weight:800; color:#2c5282;">{{ $counts['return_tomorrow'] }}</div>
                <div style="font-size:10px; font-weight:700; color:#3182ce; text-transform:uppercase;">Return Tmrw</div>
            </div>
        </div>
        <div class="col-2">
            <div style="background:#fffff0; border:1.5px solid #f6e05e; border-radius:8px; padding:10px 14px; text-align:center;">
                <div style="font-size:24px; font-weight:800; color:#b7791f;">{{ $counts['pickup_today'] }}</div>
                <div style="font-size:10px; font-weight:700; color:#d69e2e; text-transform:uppercase;">Pickup Today</div>
            </div>
        </div>
        <div class="col-2">
            <div style="background:#ebf8ff; border:1.5px solid #bee3f8; border-radius:8px; padding:10px 14px; text-align:center;">
                <div style="font-size:24px; font-weight:800; color:#2c5282;">{{ $counts['pickup_tomorrow'] }}</div>
                <div style="font-size:10px; font-weight:700; color:#3182ce; text-transform:uppercase;">Pickup Tmrw</div>
            </div>
        </div>
        <div class="col-2">
            <div style="background:#f0fff4; border:1.5px solid #9ae6b4; border-radius:8px; padding:10px 14px; text-align:center;">
                <div style="font-size:24px; font-weight:800; color:#276749;">{{ $counts['ready'] }}</div>
                <div style="font-size:10px; font-weight:700; color:#38a169; text-transform:uppercase;">Ready</div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="table-card mb-0">
        <div style="display:flex; border-bottom:1px solid var(--border);">
            <button class="notif-tab-btn tab-red {{ $activeTab === 'overdue' ? 'active' : '' }}"
                    wire:click="setTab('overdue')">
                <i class="bi bi-exclamation-triangle me-1"></i>
                Overdue
                <span class="tab-count">{{ $counts['overdue'] }}</span>
            </button>
            <button class="notif-tab-btn {{ $activeTab === 'return_today' ? 'active' : '' }}"
                    wire:click="setTab('return_today')">
                <i class="bi bi-calendar-check me-1"></i>
                Return Today
                <span class="tab-count">{{ $counts['return_today'] }}</span>
            </button>
            <button class="notif-tab-btn {{ $activeTab === 'return_tomorrow' ? 'active' : '' }}"
                    wire:click="setTab('return_tomorrow')">
                <i class="bi bi-calendar2 me-1"></i>
                Return Tomorrow
                <span class="tab-count">{{ $counts['return_tomorrow'] }}</span>
            </button>
            <button class="notif-tab-btn {{ $activeTab === 'pickup_today' ? 'active' : '' }}"
                    wire:click="setTab('pickup_today')">
                <i class="bi bi-box-arrow-up me-1"></i>
                Pickup Today
                <span class="tab-count">{{ $counts['pickup_today'] }}</span>
            </button>
            <button class="notif-tab-btn {{ $activeTab === 'pickup_tomorrow' ? 'active' : '' }}"
                    wire:click="setTab('pickup_tomorrow')">
                <i class="bi bi-box me-1"></i>
                Pickup Tomorrow
                <span class="tab-count">{{ $counts['pickup_tomorrow'] }}</span>
            </button>
            <button class="notif-tab-btn {{ $activeTab === 'ready' ? 'active' : '' }}"
                    wire:click="setTab('ready')">
                <i class="bi bi-check-circle me-1"></i>
                Ready
                <span class="tab-count">{{ $counts['ready'] }}</span>
            </button>
        </div>

        <div style="padding:16px 20px;">

            {{-- OVERDUE --}}
            @if($activeTab === 'overdue')
            @if($overdue->isEmpty())
                <div style="text-align:center; padding:30px; color:var(--text-muted); font-size:13px;">
                    <i class="bi bi-check-circle" style="font-size:36px; color:#68d391; display:block; margin-bottom:8px;"></i>
                    No overdue rentals — great!
                </div>
            @else
            <div class="alert-section-title">
                Overdue Returns
                <span class="alert-count-pill-red">{{ $overdue->count() }}</span>
            </div>
            @foreach($overdue as $rental)
            @php
                $daysLate = \Carbon\Carbon::parse($rental->return_date)->diffInDays(now());
            @endphp
            <div class="alert-rental-card overdue">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="arc-customer">{{ $rental->customer_name }}</div>
                        <div class="arc-phone">
                            {{ $rental->customer_phone1 }}
                            @if($rental->customer_phone2) · {{ $rental->customer_phone2 }} @endif
                            @if($rental->customer_whatsapp)
                                <span style="color:#25d366; font-weight:600;"> · WA: {{ $rental->customer_whatsapp }}</span>
                            @endif
                        </div>
                        <div class="arc-meta">
                            <span style="font-family:monospace; background:#f0f2f5; padding:1px 6px; border-radius:3px;">
                                {{ $rental->bill_ref ?? '#' . $rental->id }}
                            </span>
                            · Items:
                            @foreach($rental->items as $item)
                                <span style="font-family:monospace; font-size:10px; background:var(--gold-light); color:var(--gold-hover); padding:1px 5px; border-radius:3px;">
                                    {{ $item->product_code }}
                                </span>
                            @endforeach
                        </div>
                        <div class="arc-meta">
                            Return was: <strong>{{ \Carbon\Carbon::parse($rental->return_date)->format('d/m/Y') }}</strong>
                        </div>
                    </div>
                    <div class="d-flex flex-column align-items-end gap-2">
                        <span class="arc-days-badge red">
                            {{ $daysLate }} day{{ $daysLate > 1 ? 's' : '' }} late
                        </span>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-success action-btn"
                                    wire:click="markReturned({{ $rental->id }})"
                                    title="Mark as Returned">
                                <i class="bi bi-box-arrow-in-down me-1"></i> Returned
                            </button>
                            <a href="{{ route('rentals.show', $rental->id) }}"
                               class="btn btn-sm btn-outline-secondary action-btn">
                                <i class="bi bi-eye" style="font-size:12px;"></i>
                            </a>
                        </div>
                        @if($rental->remaining_balance > 0)
                        <div style="font-size:11px; color:#e53e3e; font-weight:600;">
                            Bal: Rs. {{ number_format($rental->remaining_balance, 0) }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
            @endif
            @endif

            {{-- RETURN TODAY --}}
            @if($activeTab === 'return_today')
            @if($returnToday->isEmpty())
                <div style="text-align:center; padding:30px; color:var(--text-muted); font-size:13px;">
                    <i class="bi bi-calendar-check" style="font-size:36px; color:#68d391; display:block; margin-bottom:8px;"></i>
                    No returns expected today
                </div>
            @else
            <div class="alert-section-title">
                Expected Returns Today
                <span class="alert-count-pill">{{ $returnToday->count() }}</span>
            </div>
            @foreach($returnToday as $rental)
            <div class="alert-rental-card today">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="arc-customer">{{ $rental->customer_name }}</div>
                        <div class="arc-phone">
                            {{ $rental->customer_phone1 }}
                            @if($rental->customer_whatsapp)
                                <span style="color:#25d366; font-weight:600;"> · WA: {{ $rental->customer_whatsapp }}</span>
                            @endif
                        </div>
                        <div class="arc-meta">
                            <span style="font-family:monospace; background:#f0f2f5; padding:1px 6px; border-radius:3px;">
                                {{ $rental->bill_ref ?? '#' . $rental->id }}
                            </span>
                            · Items:
                            @foreach($rental->items as $item)
                                <span style="font-family:monospace; font-size:10px; background:var(--gold-light); color:var(--gold-hover); padding:1px 5px; border-radius:3px;">
                                    {{ $item->product_code }}
                                </span>
                            @endforeach
                        </div>
                        <div class="arc-meta">
                            Status: <strong>{{ ucfirst(str_replace('_',' ',$rental->status)) }}</strong>
                        </div>
                    </div>
                    <div class="d-flex flex-column align-items-end gap-2">
                        <span class="arc-days-badge yellow">Due Today</span>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-success action-btn"
                                    wire:click="markReturned({{ $rental->id }})">
                                <i class="bi bi-box-arrow-in-down me-1"></i> Returned
                            </button>
                            <a href="{{ route('rentals.show', $rental->id) }}"
                               class="btn btn-sm btn-outline-secondary action-btn">
                                <i class="bi bi-eye" style="font-size:12px;"></i>
                            </a>
                        </div>
                        @if($rental->remaining_balance > 0)
                        <div style="font-size:11px; color:#e53e3e; font-weight:600;">
                            Bal: Rs. {{ number_format($rental->remaining_balance, 0) }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
            @endif
            @endif

            {{-- RETURN TOMORROW --}}
            @if($activeTab === 'return_tomorrow')
            @if($returnTomorrow->isEmpty())
                <div style="text-align:center; padding:30px; color:var(--text-muted); font-size:13px;">
                    <i class="bi bi-calendar2" style="font-size:36px; color:#63b3ed; display:block; margin-bottom:8px;"></i>
                    No returns expected tomorrow
                </div>
            @else
            <div class="alert-section-title">
                Expected Returns Tomorrow
                <span class="alert-count-pill">{{ $returnTomorrow->count() }}</span>
            </div>
            @foreach($returnTomorrow as $rental)
            <div class="alert-rental-card tomorrow">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="arc-customer">{{ $rental->customer_name }}</div>
                        <div class="arc-phone">
                            {{ $rental->customer_phone1 }}
                            @if($rental->customer_whatsapp)
                                <span style="color:#25d366; font-weight:600;"> · WA: {{ $rental->customer_whatsapp }}</span>
                            @endif
                        </div>
                        <div class="arc-meta">
                            <span style="font-family:monospace; background:#f0f2f5; padding:1px 6px; border-radius:3px;">
                                {{ $rental->bill_ref ?? '#' . $rental->id }}
                            </span>
                            · Items:
                            @foreach($rental->items as $item)
                                <span style="font-family:monospace; font-size:10px; background:var(--gold-light); color:var(--gold-hover); padding:1px 5px; border-radius:3px;">
                                    {{ $item->product_code }}
                                </span>
                            @endforeach
                        </div>
                        <div class="arc-meta">
                            Status: <strong>{{ ucfirst(str_replace('_',' ',$rental->status)) }}</strong>
                        </div>
                    </div>
                    <div class="d-flex flex-column align-items-end gap-2">
                        <span class="arc-days-badge blue">Tomorrow</span>
                        <a href="{{ route('rentals.show', $rental->id) }}"
                           class="btn btn-sm btn-outline-secondary action-btn">
                            <i class="bi bi-eye me-1" style="font-size:12px;"></i> View
                        </a>
                        @if($rental->remaining_balance > 0)
                        <div style="font-size:11px; color:#e53e3e; font-weight:600;">
                            Bal: Rs. {{ number_format($rental->remaining_balance, 0) }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
            @endif
            @endif

            {{-- PICKUP TODAY --}}
            @if($activeTab === 'pickup_today')
            @if($pickupToday->isEmpty())
                <div style="text-align:center; padding:30px; color:var(--text-muted); font-size:13px;">
                    <i class="bi bi-box-arrow-up" style="font-size:36px; color:#68d391; display:block; margin-bottom:8px;"></i>
                    No pickups scheduled today
                </div>
            @else
            <div class="alert-section-title">
                Pickups Today
                <span class="alert-count-pill">{{ $pickupToday->count() }}</span>
            </div>
            @foreach($pickupToday as $rental)
            <div class="alert-rental-card today">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="arc-customer">{{ $rental->customer_name }}</div>
                        <div class="arc-phone">
                            {{ $rental->customer_phone1 }}
                            @if($rental->customer_whatsapp)
                                <span style="color:#25d366; font-weight:600;"> · WA: {{ $rental->customer_whatsapp }}</span>
                            @endif
                        </div>
                        <div class="arc-meta">
                            <span style="font-family:monospace; background:#f0f2f5; padding:1px 6px; border-radius:3px;">
                                {{ $rental->bill_ref ?? '#' . $rental->id }}
                            </span>
                            · Items:
                            @foreach($rental->items as $item)
                                <span style="font-family:monospace; font-size:10px; background:var(--gold-light); color:var(--gold-hover); padding:1px 5px; border-radius:3px;">
                                    {{ $item->product_code }}
                                </span>
                            @endforeach
                        </div>
                        <div class="arc-meta">
                            Status:
                            <span class="rental-status-badge {{ $rental->status }}">
                                {{ ucfirst(str_replace('_',' ',$rental->status)) }}
                            </span>
                        </div>
                    </div>
                    <div class="d-flex flex-column align-items-end gap-2">
                        <span class="arc-days-badge yellow">Pickup Today</span>
                        <div class="d-flex gap-2">
                            @if($rental->status !== 'ready')
                            <button class="btn btn-sm btn-outline-warning action-btn"
                                    wire:click="markReady({{ $rental->id }})">
                                <i class="bi bi-check me-1"></i> Mark Ready
                            </button>
                            @endif
                            <a href="{{ route('rentals.show', $rental->id) }}"
                               class="btn btn-sm btn-outline-secondary action-btn">
                                <i class="bi bi-eye" style="font-size:12px;"></i>
                            </a>
                        </div>
                        @if($rental->remaining_balance > 0)
                        <div style="font-size:11px; color:#e53e3e; font-weight:600;">
                            Bal: Rs. {{ number_format($rental->remaining_balance, 0) }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
            @endif
            @endif

            {{-- PICKUP TOMORROW --}}
            @if($activeTab === 'pickup_tomorrow')
            @if($pickupTomorrow->isEmpty())
                <div style="text-align:center; padding:30px; color:var(--text-muted); font-size:13px;">
                    <i class="bi bi-box" style="font-size:36px; color:#63b3ed; display:block; margin-bottom:8px;"></i>
                    No pickups scheduled tomorrow
                </div>
            @else
            <div class="alert-section-title">
                Pickups Tomorrow
                <span class="alert-count-pill">{{ $pickupTomorrow->count() }}</span>
            </div>
            @foreach($pickupTomorrow as $rental)
            <div class="alert-rental-card tomorrow">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="arc-customer">{{ $rental->customer_name }}</div>
                        <div class="arc-phone">
                            {{ $rental->customer_phone1 }}
                            @if($rental->customer_whatsapp)
                                <span style="color:#25d366; font-weight:600;"> · WA: {{ $rental->customer_whatsapp }}</span>
                            @endif
                        </div>
                        <div class="arc-meta">
                            <span style="font-family:monospace; background:#f0f2f5; padding:1px 6px; border-radius:3px;">
                                {{ $rental->bill_ref ?? '#' . $rental->id }}
                            </span>
                            · Items:
                            @foreach($rental->items as $item)
                                <span style="font-family:monospace; font-size:10px; background:var(--gold-light); color:var(--gold-hover); padding:1px 5px; border-radius:3px;">
                                    {{ $item->product_code }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    <div class="d-flex flex-column align-items-end gap-2">
                        <span class="arc-days-badge blue">Tomorrow</span>
                        <a href="{{ route('rentals.show', $rental->id) }}"
                           class="btn btn-sm btn-outline-secondary action-btn">
                            <i class="bi bi-eye me-1" style="font-size:12px;"></i> View
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
            @endif
            @endif

            {{-- READY FOR PICKUP --}}
            @if($activeTab === 'ready')
            @if($readyForPickup->isEmpty())
                <div style="text-align:center; padding:30px; color:var(--text-muted); font-size:13px;">
                    <i class="bi bi-check-circle" style="font-size:36px; color:#68d391; display:block; margin-bottom:8px;"></i>
                    No items marked ready
                </div>
            @else
            <div class="alert-section-title">
                Ready for Pickup
                <span class="alert-count-pill">{{ $readyForPickup->count() }}</span>
            </div>
            @foreach($readyForPickup as $rental)
            <div class="alert-rental-card ready">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="arc-customer">{{ $rental->customer_name }}</div>
                        <div class="arc-phone">
                            {{ $rental->customer_phone1 }}
                            @if($rental->customer_whatsapp)
                                <span style="color:#25d366; font-weight:600;"> · WA: {{ $rental->customer_whatsapp }}</span>
                            @endif
                        </div>
                        <div class="arc-meta">
                            <span style="font-family:monospace; background:#f0f2f5; padding:1px 6px; border-radius:3px;">
                                {{ $rental->bill_ref ?? '#' . $rental->id }}
                            </span>
                            · Items:
                            @foreach($rental->items as $item)
                                <span style="font-family:monospace; font-size:10px; background:var(--gold-light); color:var(--gold-hover); padding:1px 5px; border-radius:3px;">
                                    {{ $item->product_code }}
                                </span>
                            @endforeach
                        </div>
                        @if($rental->pickup_date)
                        <div class="arc-meta">
                            Pickup date: <strong>{{ \Carbon\Carbon::parse($rental->pickup_date)->format('d/m/Y') }}</strong>
                        </div>
                        @endif
                    </div>
                    <div class="d-flex flex-column align-items-end gap-2">
                        <span class="arc-days-badge green">Ready</span>
                        <a href="{{ route('rentals.show', $rental->id) }}"
                           class="btn btn-sm btn-outline-secondary action-btn">
                            <i class="bi bi-eye me-1" style="font-size:12px;"></i> View
                        </a>
                        @if($rental->remaining_balance > 0)
                        <div style="font-size:11px; color:#e53e3e; font-weight:600;">
                            Bal: Rs. {{ number_format($rental->remaining_balance, 0) }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
            @endif
            @endif

        </div>
    </div>
</div>