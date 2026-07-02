<div>
    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">Rental Calendar</div>
            <div class="page-subtitle">Pickup & return schedule</div>
        </div>
        <a href="{{ route('rentals.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-list-ul me-1"></i> List View
        </a>
    </div>

    {{-- Search --}}
    <div class="mb-3" style="max-width:280px;">
        <div class="input-group input-group-sm">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" wire:model.live.debounce.400ms="search"
                class="form-control" placeholder="Filter by product code...">
            @if($search)
                <button class="btn btn-outline-secondary" wire:click="$set('search', '')" type="button">
                    <i class="bi bi-x"></i>
                </button>
            @endif
        </div>
    </div>

    {{-- Calendar Card --}}
    <div class="table-card mb-4" style="overflow:hidden;">

        {{-- Month Nav --}}
        <div class="d-flex align-items-center justify-content-between px-3 py-2"
            style="border-bottom:1px solid var(--border); background:#f8f9fa;">
            <button wire:click="prevMonth" class="btn btn-sm btn-outline-secondary" style="width:34px; height:34px; padding:0;">
                <i class="bi bi-chevron-left"></i>
            </button>
            <div style="font-size:16px; font-weight:700; color:var(--navy);">{{ $monthLabel }}</div>
            <button wire:click="nextMonth" class="btn btn-sm btn-outline-secondary" style="width:34px; height:34px; padding:0;">
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>

        {{-- Legend --}}
        <div class="d-flex gap-3 px-3 py-2" style="font-size:11px; border-bottom:1px solid var(--border); background:#fafafa;">
            <span><span style="display:inline-block; width:10px; height:10px; background:#3182ce; border-radius:50%; margin-right:4px;"></span>Pickup</span>
            <span><span style="display:inline-block; width:10px; height:10px; background:#e53e3e; border-radius:50%; margin-right:4px; border:2px solid currentColor;"></span>Return</span>
            <span style="color:var(--text-muted);">Click any date to see details</span>
        </div>

        {{-- Day Headers --}}
        <div style="display:grid; grid-template-columns:repeat(7,1fr); border-bottom:1px solid var(--border);">
            @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $day)
                <div style="text-align:center; padding:6px 2px; font-size:11px; font-weight:700;
                    color:var(--text-muted); background:#f8f9fa; border-right:1px solid var(--border);">
                    {{ $day }}
                </div>
            @endforeach
        </div>

        {{-- Calendar Grid --}}
        <div>
            @foreach($weeks as $week)
                <div style="display:grid; grid-template-columns:repeat(7,1fr); border-bottom:1px solid var(--border);">
                    @foreach($week as $cell)
                        @php
                            $hasEvents   = count($cell['pickups']) > 0 || count($cell['returns']) > 0;
                            $isSelected  = $selectedDate === $cell['date'];
                            $allPickups  = $cell['pickups'];
                            $allReturns  = $cell['returns'];
                            $maxShow     = 3;
                            $overflowP   = max(0, count($allPickups) - $maxShow);
                            $overflowR   = max(0, count($allReturns) - $maxShow);
                        @endphp
                        <div
                            wire:click="{{ $hasEvents ? "selectDate('{$cell['date']}')" : '' }}"
                            style="min-height:90px; padding:4px 5px; border-right:1px solid var(--border);
                                background:{{ $isSelected ? '#ebf4ff' : ($cell['isToday'] ? '#fffbeb' : ($cell['inMonth'] ? '#fff' : '#f9f9f9')) }};
                                cursor:{{ $hasEvents ? 'pointer' : 'default' }};
                                opacity:{{ $cell['inMonth'] ? '1' : '0.45' }};
                                {{ $isSelected ? 'outline:2px solid var(--navy); outline-offset:-2px;' : '' }}">

                            {{-- Day Number --}}
                            <div style="font-size:11px; font-weight:{{ $cell['isToday'] ? '800' : '600' }};
                                color:{{ $cell['isToday'] ? 'var(--navy)' : 'var(--text-muted)' }};
                                margin-bottom:3px;">
                                @if($cell['isToday'])
                                    <span style="background:var(--navy); color:#fff; border-radius:50%;
                                        width:18px; height:18px; display:inline-flex; align-items:center;
                                        justify-content:center; font-size:10px;">{{ $cell['day'] }}</span>
                                @else
                                    {{ $cell['day'] }}
                                @endif
                            </div>

                            {{-- Pickups --}}
                            @foreach(array_slice($allPickups, 0, $maxShow) as $p)
                                <div style="display:flex; align-items:center; gap:3px; margin-bottom:2px;">
                                    <span style="width:8px; height:8px; border-radius:50%;
                                        background:{{ $p['color'] }}; flex-shrink:0; display:inline-block;"></span>
                                    <span style="font-size:9px; font-weight:700; color:{{ $p['color'] }};
                                        white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:58px;"
                                        title="{{ $p['code'] }} - {{ $p['customer'] }}">
                                        ↑{{ $p['code'] }}
                                    </span>
                                </div>
                            @endforeach
                            @if($overflowP > 0)
                                <div style="font-size:9px; color:#3182ce; font-weight:700; margin-bottom:2px;">
                                    +{{ $overflowP }} pickup{{ $overflowP > 1 ? 's' : '' }}
                                </div>
                            @endif

                            {{-- Returns --}}
                            @foreach(array_slice($allReturns, 0, $maxShow) as $r)
                                <div style="display:flex; align-items:center; gap:3px; margin-bottom:2px;">
                                    <span style="width:8px; height:8px; border-radius:50%;
                                        background:transparent; border:2px solid {{ $r['color'] }};
                                        flex-shrink:0; display:inline-block;"></span>
                                    <span style="font-size:9px; font-weight:700; color:{{ $r['color'] }};
                                        white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:58px;"
                                        title="{{ $r['code'] }} - {{ $r['customer'] }}">
                                        ↓{{ $r['code'] }}
                                    </span>
                                </div>
                            @endforeach
                            @if($overflowR > 0)
                                <div style="font-size:9px; color:#e53e3e; font-weight:700;">
                                    +{{ $overflowR }} return{{ $overflowR > 1 ? 's' : '' }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>

    {{-- Monthly Stats --}}
    <div class="mb-4">
        <div style="font-size:13px; font-weight:700; color:var(--navy); margin-bottom:10px;">
            <i class="bi bi-bar-chart-line me-2"></i>{{ $monthLabel }} — Overview
        </div>
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(150px, 1fr)); gap:12px;">

            @foreach([
                ['label' => 'Pickups Due',     'value' => $stats['total_pickups'],  'icon' => 'bi-box-arrow-up',      'color' => '#3182ce', 'bg' => '#ebf8ff'],
                ['label' => 'Returns Due',     'value' => $stats['total_returns'],  'icon' => 'bi-box-arrow-in-down', 'color' => '#38a169', 'bg' => '#f0fff4'],
                ['label' => 'Picked Up',       'value' => $stats['picked_up'],      'icon' => 'bi-check2-circle',     'color' => '#805ad5', 'bg' => '#faf5ff'],
                ['label' => 'Returned',        'value' => $stats['returned'],       'icon' => 'bi-arrow-return-left', 'color' => '#319795', 'bg' => '#e6fffa'],
                ['label' => 'Pending Pickup',  'value' => $stats['pending_pickup'], 'icon' => 'bi-clock',             'color' => '#d69e2e', 'bg' => '#fffff0'],
                ['label' => 'Pending Return',  'value' => $stats['pending_return'], 'icon' => 'bi-hourglass-split',   'color' => '#dd6b20', 'bg' => '#fffaf0'],
                ['label' => 'Late Pickups',    'value' => $stats['late_pickups'],   'icon' => 'bi-clock-history',     'color' => '#c05621', 'bg' => '#fff5f5'],
                ['label' => 'Late Returns',    'value' => $stats['late_returns'],   'icon' => 'bi-alarm',             'color' => '#c53030', 'bg' => '#fff5f5'],
            ] as $stat)
                <div style="background:{{ $stat['bg'] }}; border:1.5px solid {{ $stat['color'] }}22;
                    border-radius:10px; padding:14px 16px;">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
                        <i class="bi {{ $stat['icon'] }}" style="font-size:18px; color:{{ $stat['color'] }};"></i>
                        <span style="font-size:11px; color:{{ $stat['color'] }}; font-weight:600;">{{ $stat['label'] }}</span>
                    </div>
                    <div style="font-size:28px; font-weight:800; color:{{ $stat['color'] }}; line-height:1;">
                        {{ $stat['value'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Date Detail Modal --}}
    @if($selectedDate && $modalData)
        @php
            $displayDate = \Carbon\Carbon::parse($selectedDate)->format('d F Y, l');
            $pickups     = $modalData['pickups'] ?? [];
            $returns     = $modalData['returns'] ?? [];
        @endphp
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width:640px;">
                <div class="modal-content">
                    <div class="modal-header py-2">
                        <div>
                            <div style="font-size:15px; font-weight:700; color:var(--navy);">
                                <i class="bi bi-calendar3 me-2"></i>{{ $displayDate }}
                            </div>
                            <div style="font-size:11px; color:var(--text-muted);">
                                {{ count($pickups) }} pickup{{ count($pickups) != 1 ? 's' : '' }},
                                {{ count($returns) }} return{{ count($returns) != 1 ? 's' : '' }}
                            </div>
                        </div>
                        <button wire:click="closeModal" class="btn-close"></button>
                    </div>
                    <div class="modal-body" style="max-height:65vh; overflow-y:auto;">

                        @if(count($pickups) > 0)
                            <div style="font-size:11px; font-weight:700; color:#3182ce; text-transform:uppercase;
                                letter-spacing:.5px; margin-bottom:8px; display:flex; align-items:center; gap:6px;">
                                <i class="bi bi-box-arrow-up"></i> Pickups ({{ count($pickups) }})
                            </div>
                            <div style="display:flex; flex-direction:column; gap:6px; margin-bottom:16px;">
                                @foreach($pickups as $p)
                                    <div style="display:flex; align-items:center; gap:10px; padding:8px 12px;
                                        border-radius:8px; border-left:4px solid {{ $p['color'] }};
                                        background:#fafafa; border:1px solid #eee; border-left:4px solid {{ $p['color'] }};">
                                        <div style="width:32px; height:32px; border-radius:50%; background:{{ $p['color'] }};
                                            display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                            <i class="bi bi-box-arrow-up" style="color:#fff; font-size:13px;"></i>
                                        </div>
                                        <div style="flex:1; min-width:0;">
                                            <div style="font-weight:700; font-size:13px; color:{{ $p['color'] }};">
                                                {{ $p['code'] }}
                                                @if($p['name'])
                                                    <span style="font-weight:400; color:var(--text-muted); font-size:11px;">
                                                        — {{ $p['name'] }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div style="font-size:12px; color:#2d3748;">{{ $p['customer'] }}</div>
                                            @if($p['phone'])
                                                <div style="font-size:11px; color:var(--text-muted);">{{ $p['phone'] }}</div>
                                            @endif
                                        </div>
                                        <div style="text-align:right; flex-shrink:0;">
                                            <a href="{{ route('rentals.show', $p['rental_id']) }}"
                                                style="font-size:11px; font-family:monospace; font-weight:700;
                                                color:var(--navy); text-decoration:none;"
                                                target="_blank">{{ $p['bill_ref'] }}</a>
                                            @if($p['return_date'])
                                                <div style="font-size:10px; color:var(--text-muted);">
                                                    Returns: {{ \Carbon\Carbon::parse($p['return_date'])->format('d/m/Y') }}
                                                </div>
                                            @endif
                                            <span class="rental-status-badge {{ $p['status'] }}"
                                                style="font-size:9px; padding:1px 6px;">
                                                {{ ucfirst(str_replace('_', ' ', $p['status'])) }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if(count($returns) > 0)
                            <div style="font-size:11px; font-weight:700; color:#38a169; text-transform:uppercase;
                                letter-spacing:.5px; margin-bottom:8px; display:flex; align-items:center; gap:6px;">
                                <i class="bi bi-box-arrow-in-down"></i> Returns ({{ count($returns) }})
                            </div>
                            <div style="display:flex; flex-direction:column; gap:6px;">
                                @foreach($returns as $r)
                                    <div style="display:flex; align-items:center; gap:10px; padding:8px 12px;
                                        border-radius:8px; background:#fafafa;
                                        border:1px solid #eee; border-left:4px solid {{ $r['color'] }};">
                                        <div style="width:32px; height:32px; border-radius:50%;
                                            background:transparent; border:3px solid {{ $r['color'] }};
                                            display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                            <i class="bi bi-box-arrow-in-down" style="color:{{ $r['color'] }}; font-size:13px;"></i>
                                        </div>
                                        <div style="flex:1; min-width:0;">
                                            <div style="font-weight:700; font-size:13px; color:{{ $r['color'] }};">
                                                {{ $r['code'] }}
                                                @if($r['name'])
                                                    <span style="font-weight:400; color:var(--text-muted); font-size:11px;">
                                                        — {{ $r['name'] }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div style="font-size:12px; color:#2d3748;">{{ $r['customer'] }}</div>
                                            @if($r['phone'])
                                                <div style="font-size:11px; color:var(--text-muted);">{{ $r['phone'] }}</div>
                                            @endif
                                        </div>
                                        <div style="text-align:right; flex-shrink:0;">
                                            <a href="{{ route('rentals.show', $r['rental_id']) }}"
                                                style="font-size:11px; font-family:monospace; font-weight:700;
                                                color:var(--navy); text-decoration:none;"
                                                target="_blank">{{ $r['bill_ref'] }}</a>
                                            @if($r['pickup_date'])
                                                <div style="font-size:10px; color:var(--text-muted);">
                                                    Picked: {{ \Carbon\Carbon::parse($r['pickup_date'])->format('d/m/Y') }}
                                                </div>
                                            @endif
                                            <span class="rental-status-badge {{ $r['status'] }}"
                                                style="font-size:9px; padding:1px 6px;">
                                                {{ ucfirst(str_replace('_', ' ', $r['status'])) }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if(count($pickups) === 0 && count($returns) === 0)
                            <div style="text-align:center; padding:30px; color:var(--text-muted); font-size:13px;">
                                No events on this date
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>