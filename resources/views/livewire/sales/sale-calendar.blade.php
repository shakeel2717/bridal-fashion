<div>
    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">Sale Calendar</div>
            <div class="page-subtitle">Items to prepare by pickup date</div>
        </div>
        <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-list-ul me-1"></i> List View
        </a>
    </div>

    {{-- Search --}}
    <div class="mb-3" style="max-width:280px;">
        <div class="input-group input-group-sm">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" wire:model.live.debounce.400ms="search" class="form-control"
                placeholder="Filter by product code...">
            @if ($search)
                <button class="btn btn-outline-secondary" wire:click="$set('search', '')" type="button">
                    <i class="bi bi-x"></i>
                </button>
            @endif
        </div>
    </div>

    {{-- Pending Only Toggle --}}
    <div class="d-flex align-items-center gap-2 mb-3">
        <div wire:click="$toggle('showPendingOnly')"
            style="display:inline-flex; align-items:center; gap:8px; cursor:pointer;
               background:{{ $showPendingOnly ? '#f0fff4' : '#f7fafc' }};
               border:1.5px solid {{ $showPendingOnly ? '#38a169' : 'var(--border)' }};
               border-radius:8px; padding:6px 14px; font-size:12px; font-weight:600;
               color:{{ $showPendingOnly ? '#276749' : 'var(--text-muted)' }};
               transition:all .15s;">
            <span
                style="width:32px; height:18px; border-radius:99px; position:relative;
                     background:{{ $showPendingOnly ? '#38a169' : '#cbd5e0' }};
                     transition:background .15s; flex-shrink:0;">
                <span
                    style="position:absolute; top:3px; left:{{ $showPendingOnly ? '17px' : '3px' }};
                         width:12px; height:12px; border-radius:50%; background:#fff;
                         transition:left .15s;"></span>
            </span>
            Pending Only
            <span style="font-size:10px; font-weight:400; color:var(--text-muted);">
                {{ $showPendingOnly ? '(not taken yet)' : '(showing all)' }}
            </span>
        </div>
    </div>

    {{-- Calendar Card --}}
    <div class="table-card mb-4" style="overflow:hidden;">

        {{-- Month Nav --}}
        <div class="d-flex align-items-center justify-content-between px-3 py-2"
            style="border-bottom:1px solid var(--border); background:#f8f9fa;">
            <button wire:click="prevMonth" class="btn btn-sm btn-outline-secondary"
                style="width:34px; height:34px; padding:0;">
                <i class="bi bi-chevron-left"></i>
            </button>
            <div style="font-size:16px; font-weight:700; color:var(--navy);">{{ $monthLabel }}</div>
            <button wire:click="nextMonth" class="btn btn-sm btn-outline-secondary"
                style="width:34px; height:34px; padding:0;">
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>

        <div class="px-3 py-2" style="font-size:11px; border-bottom:1px solid var(--border); background:#fafafa; color:var(--text-muted);">
            <span style="display:inline-block; width:10px; height:10px; background:#38a169; border-radius:50%;"></span>
            Pending pickup
            <span style="display:inline-block; width:10px; height:10px; background:#a0aec0; border-radius:50%; margin-left:12px;"></span>
            Taken
            <span style="margin-left:12px;">Click any date to see details</span>
        </div>

        {{-- Day Headers --}}
        <div style="display:grid; grid-template-columns:repeat(7,1fr); border-bottom:1px solid var(--border);">
            @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                <div
                    style="text-align:center; padding:6px 2px; font-size:11px; font-weight:700;
                    color:{{ $day === 'Fri' ? '#c53030' : 'var(--text-muted)' }};
                    background:{{ $day === 'Fri' ? 'rgba(254,215,215,0.85)' : 'rgba(154, 230, 180, 0.6)' }};
                    border-right:1px solid var(--border);">
                    {{ $day }}
                </div>
            @endforeach
        </div>

        {{-- Calendar Grid --}}
        <div>
            @foreach ($weeks as $week)
                <div style="display:grid; grid-template-columns:repeat(7,1fr); border-bottom:1px solid var(--border);">
                    @foreach ($week as $cell)
                        @php
                            $count = count($cell['pickups']);
                            $isSelected = $selectedDate === $cell['date'];
                            $maxShow = 4;
                            $isPast = $cell['date'] < now()->toDateString();
                        @endphp
                        <div wire:click="{{ $count ? "selectDate('{$cell['date']}')" : '' }}"
                            style="min-height:100px; padding:4px 3px; border-right:1px solid var(--border);
                                background:{{ $isSelected ? '#ebf4ff' : ($cell['isToday'] ? '#fffbeb' : ($cell['inMonth'] ? (\Carbon\Carbon::parse($cell['date'])->isFriday() ? '#fff5f5' : '#fff') : '#efefef')) }};
                                cursor:{{ $count ? 'pointer' : 'default' }};
                                opacity:{{ $cell['inMonth'] ? '1' : '0.4' }};
                                {{ $isSelected ? 'outline:2px solid var(--navy); outline-offset:-2px;' : '' }}">

                            <div class="d-flex justify-content-between align-items-center" style="margin-bottom:4px;">
                                <span style="font-size:11px; font-weight:{{ $cell['isToday'] ? '800' : '600' }};
                                    color:{{ $cell['isToday'] ? 'var(--navy)' : 'var(--text-muted)' }};">
                                    @if ($cell['isToday'])
                                        <span style="background:var(--navy); color:#fff; border-radius:50%;
                                            width:18px; height:18px; display:inline-flex; align-items:center;
                                            justify-content:center; font-size:10px;">{{ $cell['day'] }}</span>
                                    @else
                                        {{ $cell['day'] }}
                                    @endif
                                </span>
                                @if ($count)
                                    <span style="font-size:9px; font-weight:700; background:var(--navy); color:#fff; border-radius:20px; padding:0 6px;">
                                        {{ $count }}
                                    </span>
                                @endif
                            </div>

                            @foreach (array_slice($cell['pickups'], 0, $maxShow) as $p)
                                @php $taken = $p['pickup_status'] === 'taken'; @endphp
                                <div style="margin-bottom:2px;">
                                    <span
                                        style="display:inline-block; font-size:9px; font-weight:700; color:#fff;
                                        background:{{ $taken ? '#a0aec0' : ($isPast ? '#c53030' : '#38a169') }};
                                        border-radius:4px; padding:1px 4px; line-height:1.4;
                                        max-width:100%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"
                                        title="{{ $p['code'] }} — {{ $p['name'] }} — {{ $p['customer'] }}">
                                        {{ $p['code'] }}{{ $p['qty'] != 1 ? ' ×'.rtrim(rtrim(number_format($p['qty'], 3, '.', ''), '0'), '.') : '' }}
                                    </span>
                                </div>
                            @endforeach
                            @if ($count > $maxShow)
                                <div style="font-size:9px; color:#38a169; font-weight:700;">+{{ $count - $maxShow }}</div>
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
            @foreach ([
                ['label' => 'Items Due', 'value' => $stats['total'], 'icon' => 'bi-box-seam', 'color' => '#3182ce', 'bg' => '#ebf8ff'],
                ['label' => 'To Prepare', 'value' => $stats['pending'], 'icon' => 'bi-clock', 'color' => '#38a169', 'bg' => '#f0fff4'],
                ['label' => 'Taken', 'value' => $stats['taken'], 'icon' => 'bi-check2-circle', 'color' => '#718096', 'bg' => '#f7fafc'],
                ['label' => 'Late Pickups', 'value' => $stats['late'], 'icon' => 'bi-clock-history', 'color' => '#c53030', 'bg' => '#fff5f5'],
            ] as $stat)
                <div style="background:{{ $stat['bg'] }}; border:1.5px solid {{ $stat['color'] }}22; border-radius:10px; padding:14px 16px;">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
                        <i class="bi {{ $stat['icon'] }}" style="font-size:18px; color:{{ $stat['color'] }};"></i>
                        <span style="font-size:11px; color:{{ $stat['color'] }}; font-weight:600;">{{ $stat['label'] }}</span>
                    </div>
                    <div style="font-size:28px; font-weight:800; color:{{ $stat['color'] }}; line-height:1;">{{ $stat['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Date Detail Modal --}}
    @if ($selectedDate && $modalData)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered" style="max-width:760px;">
                <div class="modal-content">
                    <div class="modal-header py-2">
                        <div>
                            <div style="font-size:15px; font-weight:700; color:white">
                                <i class="bi bi-calendar3 me-2"></i>
                                {{ \Carbon\Carbon::createFromFormat('Y-m-d', $selectedDate)->format('d F Y, l') }}
                            </div>
                            <div style="font-size:11px; color:white;">
                                {{ count($modalData) }} item{{ count($modalData) != 1 ? 's' : '' }} to prepare
                            </div>
                        </div>
                        <button wire:click="closeModal" class="btn-close"></button>
                    </div>
                    <div class="modal-body p-0" style="max-height:70vh; overflow-y:auto;">
                        <table class="table table-hover mb-0" style="font-size:12px;">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Item</th>
                                    <th class="text-end">Qty</th>
                                    <th>Bill</th>
                                    <th>Customer</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($modalData as $p)
                                    <tr>
                                        <td><span style="font-family:monospace; font-weight:700;">{{ $p['code'] }}</span></td>
                                        <td>{{ $p['name'] }}</td>
                                        <td class="text-end">{{ rtrim(rtrim(number_format($p['qty'], 3, '.', ''), '0'), '.') }}</td>
                                        <td>
                                            <a href="{{ route('sales.show', $p['sale_id']) }}" target="_blank"
                                                style="font-family:monospace; font-weight:700; color:var(--navy); text-decoration:none;">
                                                {{ $p['bill_ref'] }}
                                            </a>
                                        </td>
                                        <td>
                                            <div style="font-weight:600;">{{ $p['customer'] }}</div>
                                            <div style="font-size:10px; color:var(--text-muted);">{{ $p['phone'] }}</div>
                                        </td>
                                        <td>
                                            @if ($p['pickup_status'] === 'taken')
                                                <span style="background:#edf2f7; color:#4a5568; border-radius:4px; padding:1px 6px; font-size:10px; font-weight:700;">Taken</span>
                                            @else
                                                <span style="background:#f0fff4; color:#276749; border-radius:4px; padding:1px 6px; font-size:10px; font-weight:700;">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
