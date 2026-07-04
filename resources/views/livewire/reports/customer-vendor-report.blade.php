<div>

    {{-- HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">Customer & Vendor Reports</div>
            <div class="page-subtitle">
                {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} &mdash; {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
            </div>
        </div>
        <div class="d-flex gap-2">
            @if($report)
                <button onclick="exportExcel()" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-file-earmark-excel me-1"></i> Excel
                </button>
                <button onclick="exportPDF()" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                </button>
            @endif
            <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    {{-- DATE FILTERS --}}
    <div class="table-card mb-3" style="padding:12px 16px;">
        <div class="d-flex flex-wrap gap-2 mb-2">
            @foreach([['today','Today'],['yesterday','Yesterday'],['this_week','This Week'],['last_week','Last Week'],['this_month','This Month'],['last_month','Last Month']] as [$key,$lbl])
                <span wire:click="setFilter('{{ $key }}')" class="rpt-pill {{ $activeFilter===$key?'active':'' }}">{{ $lbl }}</span>
            @endforeach
        </div>
        <div class="d-flex flex-wrap gap-2">
            @foreach([['last_3_months','Last 3 Months'],['last_6_months','Last 6 Months'],['this_year','This Year'],['last_year','Last Year'],['all_time','All Time'],['custom','Custom ✦']] as [$key,$lbl])
                <span wire:click="setFilter('{{ $key }}')" class="rpt-pill {{ $activeFilter===$key?'active':'' }} {{ $key==='custom'?'custom':'' }}">{{ $lbl }}</span>
            @endforeach
        </div>
        @if($activeFilter === 'custom')
            <div class="d-flex gap-2 align-items-center mt-2" style="padding-top:8px; border-top:1px dashed var(--border);">
                <span style="font-size:11px; color:var(--text-muted);">From</span>
                <input type="date" wire:model.live="dateFrom" class="form-control form-control-sm" style="width:150px;">
                <span style="font-size:11px; color:var(--text-muted);">To</span>
                <input type="date" wire:model.live="dateTo" class="form-control form-control-sm" style="width:150px;">
            </div>
        @endif
    </div>

    {{-- TWO-COLUMN LAYOUT --}}
    <div style="display:grid; grid-template-columns:220px 1fr; gap:16px; align-items:start;">

        {{-- LEFT SIDEBAR --}}
        <div class="rpt-sidebar">
            @foreach($menu as $groupKey => $group)
                <div class="rpt-group">
                    <div class="rpt-group-label">
                        <i class="bi {{ $group['icon'] }}"></i> {{ $group['label'] }}
                    </div>
                    @foreach($group['items'] as $key => $item)
                        <div wire:click="selectReport('{{ $key }}')" class="rpt-link {{ $activeReport===$key?'active':'' }}">
                            <i class="bi {{ $item['icon'] }}"></i> {{ $item['label'] }}
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>

        {{-- RIGHT CONTENT --}}
        <div>
            @if(!$report)
                <div class="table-card" style="padding:60px 20px; text-align:center; color:var(--text-muted);">
                    <i class="bi bi-hand-index-thumb" style="font-size:40px; color:#e2e8f0; display:block; margin-bottom:12px;"></i>
                    <div style="font-size:14px; font-weight:600; color:#a0aec0;">Select a report from the left</div>
                    <div style="font-size:12px; margin-top:4px;">Click any row to see full transaction history</div>
                </div>

            @else
                <div class="table-card">

                    {{-- Toolbar --}}
                    <div style="padding:10px 14px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                        <div style="flex:1;">
                            <div style="font-size:13px; font-weight:700; color:var(--navy);">{{ $report['title'] }}</div>
                            <div style="font-size:11px; color:var(--text-muted);">
                                {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
                                · {{ $report['data']->count() }} record(s)
                            </div>
                        </div>
                        <input type="text" wire:model.live.debounce.400ms="search"
                            class="form-control form-control-sm"
                            placeholder="{{ $report['type']==='vendor_table' ? 'Search vendor...' : 'Search customer / phone...' }}"
                            style="width:220px;">
                        <span style="font-size:11px; color:var(--text-muted); white-space:nowrap;">
                            <i class="bi bi-cursor me-1"></i> Click a row for details
                        </span>
                    </div>

                    {{-- ══ CUSTOMER TABLE ══ --}}
                    @if($report['type'] === 'customer_table')
                        <table class="table table-striped table-hover mb-0" id="reportTable" style="font-size:12px;">
                            <thead>
                                <tr>
                                    <th style="width:32px;">#</th>
                                    <th>Customer</th>
                                    <th>Phone</th>
                                    <th class="text-center">Rentals</th>
                                    <th class="text-center">Sales</th>
                                    <th class="text-end">Total Value</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Baqi</th>
                                    <th>Last Visit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($report['data'] as $i => $c)
                                    <tr wire:click="openModal({{ $i }})"
                                        style="cursor:pointer;"
                                        class="{{ ($report['highlight']??'')==='due' && $c['due']>0 ? 'table-danger' : '' }}">
                                        <td style="color:var(--text-muted); font-size:11px;">{{ $i+1 }}</td>
                                        <td>
                                            <div style="font-weight:600;">{{ $c['name'] }}</div>
                                            @if($c['cnic'])
                                                <div style="font-size:10px; color:var(--text-muted); font-family:monospace;">{{ $c['cnic'] }}</div>
                                            @endif
                                        </td>
                                        <td style="font-size:11px; color:var(--text-muted);">{{ $c['phone'] }}</td>
                                        <td class="text-center">
                                            @if($c['rental_count']>0)
                                                <span style="background:#ebf8ff; color:#2c5282; border-radius:20px; padding:2px 10px; font-weight:700; font-size:11px;">{{ $c['rental_count'] }}</span>
                                            @else <span style="color:var(--text-muted);">—</span> @endif
                                        </td>
                                        <td class="text-center">
                                            @if($c['sale_count']>0)
                                                <span style="background:#f0fff4; color:#276749; border-radius:20px; padding:2px 10px; font-weight:700; font-size:11px;">{{ $c['sale_count'] }}</span>
                                            @else <span style="color:var(--text-muted);">—</span> @endif
                                        </td>
                                        <td class="text-end" style="font-weight:700;">Rs. {{ number_format($c['revenue'],0) }}</td>
                                        <td class="text-end" style="color:#38a169; font-weight:600;">Rs. {{ number_format($c['paid'],0) }}</td>
                                        <td class="text-end">
                                            @if($c['due']>0)
                                                <span style="background:#fff5f5; color:#c53030; border-radius:5px; padding:2px 9px; font-weight:800;">Rs. {{ number_format($c['due'],0) }}</span>
                                            @else
                                                <span style="color:#38a169; font-weight:700;">✓ Paid</span>
                                            @endif
                                        </td>
                                        <td style="font-size:11px; white-space:nowrap; color:var(--text-muted);">
                                            {{ $c['last_visit'] ? \Carbon\Carbon::parse($c['last_visit'])->format('d/m/Y') : '—' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9" class="rpt-empty">No customer data for this period</td></tr>
                                @endforelse
                            </tbody>
                            @if($report['data']->count())
                                <tfoot><tr class="rpt-tfoot">
                                    <td colspan="5">{{ $report['data']->count() }} customer(s)</td>
                                    <td class="text-end">Rs. {{ number_format($report['data']->sum('revenue'),0) }}</td>
                                    <td class="text-end" style="color:#9ae6b4;">Rs. {{ number_format($report['data']->sum('paid'),0) }}</td>
                                    <td class="text-end" style="color:#feb2b2;">Rs. {{ number_format($report['data']->sum('due'),0) }}</td>
                                    <td></td>
                                </tr></tfoot>
                            @endif
                        </table>

                    {{-- ══ VENDOR TABLE ══ --}}
                    @elseif($report['type'] === 'vendor_table')
                        <table class="table table-striped table-hover mb-0" id="reportTable" style="font-size:12px;">
                            <thead>
                                <tr>
                                    <th style="width:32px;">#</th>
                                    <th>Vendor</th>
                                    <th>Phone</th>
                                    <th class="text-center">PO Count</th>
                                    <th class="text-end">Total PO</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Baqi</th>
                                    <th>Last Order</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($report['data'] as $i => $v)
                                    <tr wire:click="openModal({{ $i }})"
                                        style="cursor:pointer;"
                                        class="{{ ($report['highlight']??'')==='due' && $v['due']>0 ? 'table-danger' : '' }}">
                                        <td style="color:var(--text-muted); font-size:11px;">{{ $i+1 }}</td>
                                        <td style="font-weight:600;">{{ $v['name'] }}</td>
                                        <td style="font-size:11px; color:var(--text-muted);">{{ $v['phone'] }}</td>
                                        <td class="text-center">
                                            <span style="background:#ebf8ff; color:#2c5282; border-radius:20px; padding:2px 10px; font-weight:700; font-size:11px;">{{ $v['po_count'] }}</span>
                                        </td>
                                        <td class="text-end" style="font-weight:700;">Rs. {{ number_format($v['total'],0) }}</td>
                                        <td class="text-end" style="color:#38a169; font-weight:600;">Rs. {{ number_format($v['paid'],0) }}</td>
                                        <td class="text-end">
                                            @if($v['due']>0)
                                                <span style="background:#fff5f5; color:#c53030; border-radius:5px; padding:2px 9px; font-weight:800;">Rs. {{ number_format($v['due'],0) }}</span>
                                            @else
                                                <span style="color:#38a169; font-weight:700;">✓ Paid</span>
                                            @endif
                                        </td>
                                        <td style="font-size:11px; white-space:nowrap; color:var(--text-muted);">
                                            {{ $v['last_order'] ? \Carbon\Carbon::parse($v['last_order'])->format('d/m/Y') : '—' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="rpt-empty">No vendor data for this period</td></tr>
                                @endforelse
                            </tbody>
                            @if($report['data']->count())
                                <tfoot><tr class="rpt-tfoot">
                                    <td colspan="4">{{ $report['data']->count() }} vendor(s)</td>
                                    <td class="text-end">Rs. {{ number_format($report['data']->sum('total'),0) }}</td>
                                    <td class="text-end" style="color:#9ae6b4;">Rs. {{ number_format($report['data']->sum('paid'),0) }}</td>
                                    <td class="text-end" style="color:#feb2b2;">Rs. {{ number_format($report['data']->sum('due'),0) }}</td>
                                    <td></td>
                                </tr></tfoot>
                            @endif
                        </table>
                    @endif

                </div>
            @endif
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         DETAIL MODAL
    ══════════════════════════════════════════════════════════ --}}
    @if($modalRow)
        <div class="modal fade show" id="detailModal" tabindex="-1"
            style="display:block; background:rgba(0,0,0,0.5);"
            wire:click.self="closeModal">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">

                    {{-- Modal Header --}}
                    <div class="modal-header" style="background:var(--navy); color:#fff; padding:12px 20px;">
                        <div>
                            <div style="font-size:16px; font-weight:700;">
                                @if($modalType === 'customer')
                                    <i class="bi bi-person-circle me-2" style="color:var(--gold);"></i>
                                    {{ $modalRow['name'] }}
                                @else
                                    <i class="bi bi-shop me-2" style="color:var(--gold);"></i>
                                    {{ $modalRow['name'] }}
                                @endif
                            </div>
                            <div style="font-size:11px; color:#cbd5e0; margin-top:2px;">
                                @if($modalType === 'customer')
                                    {{ $modalRow['phone'] }}
                                    @if($modalRow['cnic']) &nbsp;·&nbsp; {{ $modalRow['cnic'] }} @endif
                                    &nbsp;·&nbsp;
                                    {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
                                @else
                                    {{ $modalRow['phone'] }}
                                    &nbsp;·&nbsp;
                                    {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
                                @endif
                            </div>
                        </div>
                        <button wire:click="closeModal" class="btn-close btn-close-white ms-auto" style="opacity:.8;"></button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="modal-body" style="padding:20px; background:#f8fafc;">

                        @if($modalType === 'customer')

                            {{-- Summary Pills --}}
                            <div class="d-flex gap-3 mb-4 flex-wrap">
                                @foreach([
                                    ['Rentals',    $modalRow['rental_count'], '#2c5282', '#ebf8ff', 'bi-journal-bookmark'],
                                    ['Sales',      $modalRow['sale_count'],   '#276749', '#f0fff4', 'bi-bag'],
                                    ['Total Value','Rs. '.number_format($modalRow['revenue'],0), '#1a365d', '#e8edf5', 'bi-cash-stack'],
                                    ['Paid',       'Rs. '.number_format($modalRow['paid'],0),    '#276749', '#f0fff4', 'bi-check-circle'],
                                    ['Baqi',       'Rs. '.number_format($modalRow['due'],0),     $modalRow['due']>0?'#c53030':'#276749', $modalRow['due']>0?'#fff5f5':'#f0fff4', 'bi-hourglass-split'],
                                ] as [$lbl,$val,$col,$bg,$icon])
                                    <div style="background:{{ $bg }}; border:1.5px solid {{ $col }}22; border-radius:10px; padding:10px 16px; min-width:120px;">
                                        <div style="font-size:10px; font-weight:700; color:{{ $col }}; text-transform:uppercase; margin-bottom:4px;">
                                            <i class="bi {{ $icon }} me-1"></i>{{ $lbl }}
                                        </div>
                                        <div style="font-size:16px; font-weight:800; color:{{ $col }};">{{ $val }}</div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Rentals --}}
                            @if(count($modalRow['rentals']) > 0)
                                <div style="font-size:12px; font-weight:700; color:var(--navy); text-transform:uppercase; letter-spacing:.4px; margin-bottom:8px;">
                                    <i class="bi bi-journal-bookmark me-1" style="color:var(--gold);"></i>
                                    Rentals ({{ count($modalRow['rentals']) }})
                                </div>
                                <div class="table-card mb-4" style="border-radius:8px; overflow:hidden;">
                                    <table class="table table-sm table-striped mb-0" style="font-size:12px;">
                                        <thead>
                                            <tr>
                                                <th>Bill Ref</th>
                                                <th>Booking</th>
                                                <th>Pickup</th>
                                                <th>Return</th>
                                                <th class="text-end">Amount</th>
                                                <th class="text-end">Paid</th>
                                                <th class="text-end">Baqi</th>
                                                <th>Status</th>
                                                <th style="width:32px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($modalRow['rentals'] as $r)
                                                <tr>
                                                    <td style="font-family:monospace; font-weight:700; color:var(--navy);">{{ $r['bill_ref'] }}</td>
                                                    <td style="white-space:nowrap;">{{ \Carbon\Carbon::parse($r['booking_date'])->format('d/m/Y') }}</td>
                                                    <td style="white-space:nowrap; color:var(--text-muted);">{{ $r['pickup_date'] ? \Carbon\Carbon::parse($r['pickup_date'])->format('d/m/Y') : '—' }}</td>
                                                    <td style="white-space:nowrap; color:var(--text-muted);">{{ $r['return_date'] ? \Carbon\Carbon::parse($r['return_date'])->format('d/m/Y') : '—' }}</td>
                                                    <td class="text-end" style="font-weight:700;">Rs. {{ number_format($r['total_amount'],0) }}</td>
                                                    <td class="text-end" style="color:#38a169;">Rs. {{ number_format($r['paid'],0) }}</td>
                                                    <td class="text-end">
                                                        @if($r['due']>0)
                                                            <span style="color:#c53030; font-weight:700;">Rs. {{ number_format($r['due'],0) }}</span>
                                                        @else
                                                            <span style="color:#38a169;">✓</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="rental-status-badge {{ $r['status'] }}" style="font-size:9px; padding:1px 6px;">
                                                            {{ ucfirst(str_replace('_',' ',$r['status'])) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('rentals.show', $r['id']) }}" target="_blank"
                                                            style="color:var(--navy); font-size:12px;" title="Open">
                                                            <i class="bi bi-box-arrow-up-right"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="rpt-tfoot">
                                                <td colspan="4">{{ count($modalRow['rentals']) }} rental(s)</td>
                                                <td class="text-end">Rs. {{ number_format(collect($modalRow['rentals'])->sum('total_amount'),0) }}</td>
                                                <td class="text-end" style="color:#9ae6b4;">Rs. {{ number_format(collect($modalRow['rentals'])->sum('paid'),0) }}</td>
                                                <td class="text-end" style="color:#feb2b2;">Rs. {{ number_format(collect($modalRow['rentals'])->sum('due'),0) }}</td>
                                                <td colspan="2"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @endif

                            {{-- Sales --}}
                            @if(count($modalRow['sales']) > 0)
                                <div style="font-size:12px; font-weight:700; color:#276749; text-transform:uppercase; letter-spacing:.4px; margin-bottom:8px;">
                                    <i class="bi bi-bag me-1"></i> Sales ({{ count($modalRow['sales']) }})
                                </div>
                                <div class="table-card" style="border-radius:8px; overflow:hidden;">
                                    <table class="table table-sm table-striped mb-0" style="font-size:12px;">
                                        <thead>
                                            <tr>
                                                <th>Bill Ref</th>
                                                <th>Sale Date</th>
                                                <th class="text-end">Amount</th>
                                                <th class="text-end">Paid</th>
                                                <th class="text-end">Baqi</th>
                                                <th>Status</th>
                                                <th style="width:32px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($modalRow['sales'] as $s)
                                                <tr>
                                                    <td style="font-family:monospace; font-weight:700; color:#276749;">{{ $s['bill_ref'] }}</td>
                                                    <td style="white-space:nowrap;">{{ \Carbon\Carbon::parse($s['sale_date'])->format('d/m/Y') }}</td>
                                                    <td class="text-end" style="font-weight:700;">Rs. {{ number_format($s['total_amount'],0) }}</td>
                                                    <td class="text-end" style="color:#38a169;">Rs. {{ number_format($s['paid'],0) }}</td>
                                                    <td class="text-end">
                                                        @if($s['due']>0)
                                                            <span style="color:#c53030; font-weight:700;">Rs. {{ number_format($s['due'],0) }}</span>
                                                        @else
                                                            <span style="color:#38a169;">✓</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span style="background:#e2e8f0; color:#4a5568; border-radius:4px; padding:1px 6px; font-size:9px; font-weight:600;">
                                                            {{ ucfirst($s['status']) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('sales.show', $s['id']) }}" target="_blank"
                                                            style="color:#276749; font-size:12px;" title="Open">
                                                            <i class="bi bi-box-arrow-up-right"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="rpt-tfoot">
                                                <td colspan="2">{{ count($modalRow['sales']) }} sale(s)</td>
                                                <td class="text-end">Rs. {{ number_format(collect($modalRow['sales'])->sum('total_amount'),0) }}</td>
                                                <td class="text-end" style="color:#9ae6b4;">Rs. {{ number_format(collect($modalRow['sales'])->sum('paid'),0) }}</td>
                                                <td class="text-end" style="color:#feb2b2;">Rs. {{ number_format(collect($modalRow['sales'])->sum('due'),0) }}</td>
                                                <td colspan="2"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @endif

                            @if(count($modalRow['rentals'])===0 && count($modalRow['sales'])===0)
                                <div style="text-align:center; padding:30px; color:var(--text-muted); font-size:13px;">
                                    No transactions found in this period.
                                </div>
                            @endif

                        @else
                            {{-- VENDOR MODAL --}}

                            {{-- Summary Pills --}}
                            <div class="d-flex gap-3 mb-4 flex-wrap">
                                @foreach([
                                    ['PO Count',  $modalRow['po_count'],                         '#2c5282', '#ebf8ff', 'bi-receipt'],
                                    ['Total PO',  'Rs. '.number_format($modalRow['total'],0),     '#1a365d', '#e8edf5', 'bi-cash-stack'],
                                    ['Paid',      'Rs. '.number_format($modalRow['paid'],0),      '#276749', '#f0fff4', 'bi-check-circle'],
                                    ['Baqi',      'Rs. '.number_format($modalRow['due'],0),       $modalRow['due']>0?'#c53030':'#276749', $modalRow['due']>0?'#fff5f5':'#f0fff4', 'bi-hourglass-split'],
                                ] as [$lbl,$val,$col,$bg,$icon])
                                    <div style="background:{{ $bg }}; border:1.5px solid {{ $col }}22; border-radius:10px; padding:10px 16px; min-width:120px;">
                                        <div style="font-size:10px; font-weight:700; color:{{ $col }}; text-transform:uppercase; margin-bottom:4px;">
                                            <i class="bi {{ $icon }} me-1"></i>{{ $lbl }}
                                        </div>
                                        <div style="font-size:16px; font-weight:800; color:{{ $col }};">{{ $val }}</div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- PO List --}}
                            @if(count($modalRow['pos']) > 0)
                                <div style="font-size:12px; font-weight:700; color:var(--navy); text-transform:uppercase; letter-spacing:.4px; margin-bottom:8px;">
                                    <i class="bi bi-receipt me-1" style="color:var(--gold);"></i>
                                    Purchase Orders ({{ count($modalRow['pos']) }})
                                </div>
                                <div class="table-card" style="border-radius:8px; overflow:hidden;">
                                    <table class="table table-sm table-striped mb-0" style="font-size:12px;">
                                        <thead>
                                            <tr>
                                                <th>PO #</th>
                                                <th>Order Date</th>
                                                <th class="text-end">Amount</th>
                                                <th class="text-end">Paid</th>
                                                <th class="text-end">Balance</th>
                                                <th>Status</th>
                                                <th style="width:32px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($modalRow['pos'] as $po)
                                                <tr>
                                                    <td style="font-family:monospace; font-weight:700; color:var(--navy);">{{ $po['po_number'] }}</td>
                                                    <td style="white-space:nowrap;">{{ \Carbon\Carbon::parse($po['order_date'])->format('d/m/Y') }}</td>
                                                    <td class="text-end" style="font-weight:700;">Rs. {{ number_format($po['total_amount'],0) }}</td>
                                                    <td class="text-end" style="color:#38a169;">Rs. {{ number_format($po['amount_paid'],0) }}</td>
                                                    <td class="text-end">
                                                        @if($po['balance_due']>0)
                                                            <span style="color:#c53030; font-weight:700;">Rs. {{ number_format($po['balance_due'],0) }}</span>
                                                        @else
                                                            <span style="color:#38a169;">✓</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span style="background:#e2e8f0; color:#4a5568; border-radius:4px; padding:1px 6px; font-size:9px; font-weight:600;">
                                                            {{ ucfirst($po['status']) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('purchase-orders.show', $po['id']) }}" target="_blank"
                                                            style="color:var(--navy); font-size:12px;" title="Open PO">
                                                            <i class="bi bi-box-arrow-up-right"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="rpt-tfoot">
                                                <td colspan="2">{{ count($modalRow['pos']) }} order(s)</td>
                                                <td class="text-end">Rs. {{ number_format(collect($modalRow['pos'])->sum('total_amount'),0) }}</td>
                                                <td class="text-end" style="color:#9ae6b4;">Rs. {{ number_format(collect($modalRow['pos'])->sum('amount_paid'),0) }}</td>
                                                <td class="text-end" style="color:#feb2b2;">Rs. {{ number_format(collect($modalRow['pos'])->sum('balance_due'),0) }}</td>
                                                <td colspan="2"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div style="text-align:center; padding:30px; color:var(--text-muted); font-size:13px;">
                                    No purchase orders in this period.
                                </div>
                            @endif
                        @endif

                    </div>

                    {{-- Modal Footer --}}
                    <div class="modal-footer" style="padding:10px 20px; background:#f0f4f8;">
                        <button wire:click="closeModal" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i> Close
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif

</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
    function exportPDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('l','mm','a4');
        doc.setFontSize(13);
        doc.text('{{ addslashes($report["title"] ?? "Report") }}', 14, 14);
        doc.setFontSize(8);
        doc.text('{{ \Carbon\Carbon::parse($dateFrom)->format("d M Y") }} – {{ \Carbon\Carbon::parse($dateTo)->format("d M Y") }}', 14, 20);
        const table = document.getElementById('reportTable');
        if (!table) return;
        const heads = Array.from(table.querySelectorAll('thead th')).map(th => th.innerText.trim());
        const rows  = Array.from(table.querySelectorAll('tbody tr')).map(tr =>
            Array.from(tr.querySelectorAll('td')).map(td => td.innerText.trim())
        ).filter(r => r.length > 1);
        doc.autoTable({ head:[heads], body:rows, startY:25, styles:{fontSize:7}, headStyles:{fillColor:[26,54,93]}, alternateRowStyles:{fillColor:[245,247,250]} });
        doc.save('report-{{ now()->format("Y-m-d") }}.pdf');
    }

    function exportExcel() {
        const table = document.getElementById('reportTable');
        if (!table) return;
        const heads = Array.from(table.querySelectorAll('thead th')).map(th => th.innerText.trim());
        const rows  = [heads, ...Array.from(table.querySelectorAll('tbody tr')).map(tr =>
            Array.from(tr.querySelectorAll('td')).map(td => td.innerText.trim())
        ).filter(r => r.length > 1)];
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, XLSX.utils.aoa_to_sheet(rows), 'Report');
        XLSX.writeFile(wb, 'report-{{ now()->format("Y-m-d") }}.xlsx');
    }
</script>
@endpush