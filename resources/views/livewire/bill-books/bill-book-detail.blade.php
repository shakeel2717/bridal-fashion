<div>
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">{{ $billBook->name }}</div>
            <div class="page-subtitle">
                Range: {{ $billBook->prefix }}{{ $billBook->range_from }} — {{ $billBook->prefix }}{{ $billBook->range_to }}
                @if($billBook->notes) · {{ $billBook->notes }} @endif
            </div>
        </div>
        <a href="{{ route('bill-books.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    {{-- Stats cards --}}
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:16px;">
        @foreach([
            ['Total Bills',   $total,   '#3182ce','#ebf8ff','bi-journal'],
            ['Used',          $used,    '#38a169','#f0fff4','bi-check-circle-fill'],
            ['Missing',       $missing, '#c53030','#fff5f5','bi-x-circle-fill'],
            ['% Used',        round($total > 0 ? $used/$total*100 : 0).'%', '#805ad5','#faf5ff','bi-pie-chart'],
        ] as [$lbl,$val,$col,$bg,$icon])
            <div style="background:{{ $bg }}; border:1.5px solid {{ $col }}22; border-radius:10px; padding:14px 16px;">
                <div style="font-size:10px; font-weight:700; color:{{ $col }}; text-transform:uppercase; margin-bottom:4px;">
                    <i class="bi {{ $icon }} me-1"></i>{{ $lbl }}
                </div>
                <div style="font-size:24px; font-weight:800; color:{{ $col }};">{{ $val }}</div>
            </div>
        @endforeach
    </div>

    <div class="table-card">
        {{-- Toolbar --}}
        <div style="padding:10px 14px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <div class="d-flex gap-2 flex-wrap flex-1">
                @foreach([
                    ['' ,        'All Bills',  ''],
                    ['used',     'Used Only',  '#38a169'],
                    ['missing',  'Missing',    '#c53030'],
                ] as [$val,$lbl,$col])
                    <span wire:click="$set('filterStatus','{{ $val }}')"
                        style="display:inline-flex; align-items:center; padding:4px 12px; border-radius:20px; font-size:11px; font-weight:600; cursor:pointer;
                               background:{{ $filterStatus===$val ? 'var(--navy)' : '#fff' }};
                               border:1.5px solid {{ $filterStatus===$val ? 'var(--navy)' : 'var(--border)' }};
                               color:{{ $filterStatus===$val ? '#fff' : ($col ?: 'var(--text-muted)') }};">
                        {{ $lbl }}
                    </span>
                @endforeach
            </div>
            <input type="text" wire:model.live.debounce.300ms="search"
                class="form-control form-control-sm" placeholder="Search ref or customer..."
                style="width:200px;">
        </div>

        {{-- Table --}}
        <table class="table table-hover mb-0" style="font-size:12px;">
            <thead>
                <tr>
                    <th style="width:80px;">#</th>
                    <th style="width:120px;">Bill Ref</th>
                    <th>Customer</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th class="text-end">Amount</th>
                    <th style="width:80px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr class="{{ !$row['is_used'] ? 'table-danger' : '' }}">
                        <td style="font-weight:700; color:var(--text-muted);">{{ $row['number'] }}</td>
                        <td>
                            <span style="font-family:monospace; font-weight:700; color:{{ $row['is_used'] ? 'var(--navy)' : '#c53030' }};">
                                {{ $row['ref'] }}
                            </span>
                        </td>

                        @if($row['rental'])
                            <td style="font-weight:600;">{{ $row['rental']['customer_name'] }}</td>
                            <td>
                                <span style="background:#faf5ff; color:#805ad5; border-radius:20px; padding:1px 8px; font-size:10px; font-weight:700;">Rental</span>
                            </td>
                            <td style="white-space:nowrap;">{{ \Carbon\Carbon::parse($row['rental']['date'])->format('d/m/Y') }}</td>
                            <td>
                                <span class="rental-status-badge {{ $row['rental']['status'] }}" style="font-size:10px; padding:1px 6px;">
                                    {{ ucfirst(str_replace('_',' ',$row['rental']['status'])) }}
                                </span>
                            </td>
                            <td class="text-end" style="font-weight:700;">Rs. {{ number_format($row['rental']['amount'],0) }}</td>
                            <td>
                                <a href="{{ route('rentals.show', $row['rental']['id']) }}" target="_blank"
                                    class="btn btn-sm btn-outline-secondary" style="padding:2px 8px; font-size:11px;">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </td>

                        @elseif($row['sale'])
                            <td style="font-weight:600;">{{ $row['sale']['customer_name'] }}</td>
                            <td>
                                <span style="background:#f0fff4; color:#276749; border-radius:20px; padding:1px 8px; font-size:10px; font-weight:700;">Sale</span>
                            </td>
                            <td style="white-space:nowrap;">{{ \Carbon\Carbon::parse($row['sale']['date'])->format('d/m/Y') }}</td>
                            <td>
                                <span style="background:#f0fff4; color:#276749; border-radius:4px; padding:1px 6px; font-size:10px; font-weight:700;">
                                    {{ ucfirst($row['sale']['status']) }}
                                </span>
                            </td>
                            <td class="text-end" style="font-weight:700;">Rs. {{ number_format($row['sale']['amount'],0) }}</td>
                            <td>
                                <a href="{{ route('sales.show', $row['sale']['id']) }}" target="_blank"
                                    class="btn btn-sm btn-outline-secondary" style="padding:2px 8px; font-size:11px;">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </td>

                        @else
                            <td colspan="5" style="color:#c53030; font-style:italic; font-size:11px;">
                                <i class="bi bi-dash-circle me-1"></i> Not used — missing from records
                            </td>
                            <td></td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center; padding:30px; color:var(--text-muted); font-size:13px;">
                            No records match the current filter
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>