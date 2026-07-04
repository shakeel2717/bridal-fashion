<div>

    {{-- PAGE HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">Rental Reports</div>
            <div class="page-subtitle">
                {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }}
                &mdash;
                {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
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
            @foreach([
                ['today',      'Today'],
                ['yesterday',  'Yesterday'],
                ['this_week',  'This Week'],
                ['last_week',  'Last Week'],
                ['this_month', 'This Month'],
                ['last_month', 'Last Month'],
            ] as [$key, $lbl])
                <span wire:click="setFilter('{{ $key }}')"
                    class="rpt-pill {{ $activeFilter === $key ? 'active' : '' }}">
                    {{ $lbl }}
                </span>
            @endforeach
        </div>
        <div class="d-flex flex-wrap gap-2">
            @foreach([
                ['last_3_months','Last 3 Months'],
                ['last_6_months','Last 6 Months'],
                ['this_quarter', 'This Quarter'],
                ['last_quarter', 'Last Quarter'],
                ['this_year',    'This Year'],
                ['last_year',    'Last Year'],
                ['all_time',     'All Time'],
                ['custom',       'Custom ✦'],
            ] as [$key, $lbl])
                <span wire:click="setFilter('{{ $key }}')"
                    class="rpt-pill {{ $activeFilter === $key ? 'active' : '' }} {{ $key === 'custom' ? 'custom' : '' }}">
                    {{ $lbl }}
                </span>
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

        {{-- LEFT: REPORT MENU --}}
        <div class="rpt-sidebar">
            @foreach($menu as $groupKey => $group)
                <div class="rpt-group">
                    <div class="rpt-group-label">
                        <i class="bi {{ $group['icon'] }}"></i>
                        {{ $group['label'] }}
                    </div>
                    @foreach($group['items'] as $key => $item)
                        <div wire:click="selectReport('{{ $key }}')"
                            class="rpt-link {{ $activeReport === $key ? 'active' : '' }}">
                            <i class="bi {{ $item['icon'] }}"></i>
                            {{ $item['label'] }}
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>

        {{-- RIGHT: REPORT AREA --}}
        <div>
            @if(!$report)
                {{-- Empty state --}}
                <div class="table-card" style="padding:60px 20px; text-align:center; color:var(--text-muted);">
                    <i class="bi bi-hand-index-thumb" style="font-size:40px; color:#e2e8f0; display:block; margin-bottom:12px;"></i>
                    <div style="font-size:14px; font-weight:600; color:#a0aec0;">Select a report from the left</div>
                    <div style="font-size:12px; margin-top:4px;">Choose any option to load the report</div>
                </div>

            @else

                {{-- REPORT CARD --}}
                <div class="table-card">

                    {{-- Report header + toolbar --}}
                    <div style="padding:10px 14px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                        <div style="flex:1; min-width:160px;">
                            <div style="font-size:13px; font-weight:700; color:var(--navy);">{{ $report['title'] }}</div>
                            <div style="font-size:11px; color:var(--text-muted);">
                                {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
                            </div>
                        </div>

                        {{-- Search --}}
                        @if(!in_array($report['columns'], ['revenue_summary','period_summary']))
                            <input type="text" wire:model.live.debounce.400ms="search"
                                class="form-control form-control-sm"
                                placeholder="Search..."
                                style="width:200px;">
                        @endif

                        {{-- Status filter --}}
                        @if(in_array($report['columns'], ['rental_list','dues_list','schedule']))
                            <select wire:model.live="statusFilter" class="form-select form-select-sm" style="width:140px;">
                                <option value="">All Status</option>
                                <option value="booked">Booked</option>
                                <option value="ready">Ready</option>
                                <option value="picked_up">Picked Up</option>
                                <option value="partially_picked_up">Partially Picked</option>
                                <option value="returned">Returned</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="abandoned">Abandoned</option>
                            </select>
                        @endif
                    </div>

                    {{-- ══ REVENUE SUMMARY ══ --}}
                    @if($report['columns'] === 'revenue_summary')
                        @php
                            $rows       = $report['data'];
                            $total      = $rows->sum('total_amount');
                            $collected  = $rows->sum('payments_sum_amount');
                            $due        = $rows->sum(fn($r) => max(0, $r->total_amount - (float)($r->payments_sum_amount ?? 0)));
                            $count      = $rows->count();
                            $pct        = $total > 0 ? round($collected / $total * 100) : 0;
                        @endphp
                        <div style="padding:20px; display:grid; grid-template-columns:repeat(4,1fr); gap:14px;">
                            @foreach([
                                ['Total Rentals', $count,     '#3182ce', '#ebf8ff', 'num'],
                                ['Total Amount',  $total,     '#38a169', '#f0fff4', 'money'],
                                ['Collected',     $collected, '#805ad5', '#faf5ff', 'money'],
                                ['Baqi / Due',    $due,       '#c53030', '#fff5f5', 'money'],
                            ] as [$lbl, $val, $col, $bg, $fmt])
                                <div style="background:{{ $bg }}; border:1.5px solid {{ $col }}22; border-radius:10px; padding:16px 14px;">
                                    <div style="font-size:10px; font-weight:700; color:{{ $col }}; text-transform:uppercase; margin-bottom:6px;">{{ $lbl }}</div>
                                    <div style="font-size:{{ $fmt==='money' ? '15px' : '24px' }}; font-weight:800; color:{{ $col }};">
                                        {{ $fmt==='money' ? 'Rs. '.number_format($val,0) : number_format($val) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div style="padding:0 20px 20px;">
                            <div style="font-size:11px; font-weight:700; color:var(--navy); margin-bottom:6px;">Collection Rate</div>
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div style="flex:1; height:10px; background:#e2e8f0; border-radius:99px; overflow:hidden;">
                                    <div style="width:{{ $pct }}%; height:100%; background:linear-gradient(90deg,#38a169,#68d391); border-radius:99px;"></div>
                                </div>
                                <span style="font-size:18px; font-weight:800; color:#38a169;">{{ $pct }}%</span>
                            </div>
                        </div>

                    {{-- ══ PERIOD SUMMARY (daily / monthly) ══ --}}
                    @elseif($report['columns'] === 'period_summary')
                        @php $maxRev = $report['data']->max('revenue') ?: 1; @endphp
                        <table class="table table-striped table-hover mb-0" id="reportTable" style="font-size:12px;">
                            <thead>
                                <tr>
                                    <th>{{ $report['period_label'] ?? 'Period' }}</th>
                                    <th class="text-center">Bookings</th>
                                    <th class="text-end">Revenue</th>
                                    <th style="width:180px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($report['data'] as $row)
                                    <tr>
                                        <td style="font-weight:600;">
                                            {{ $row->period_display ?? \Carbon\Carbon::parse($row->period . '-01')->format('F Y') }}
                                        </td>
                                        <td class="text-center">
                                            <span style="background:var(--navy); color:#fff; border-radius:20px; padding:2px 10px; font-weight:700; font-size:11px;">
                                                {{ $row->count }}
                                            </span>
                                        </td>
                                        <td class="text-end" style="font-weight:700; color:#38a169;">Rs. {{ number_format($row->revenue,0) }}</td>
                                        <td style="padding-right:16px; vertical-align:middle;">
                                            <div style="background:#e2e8f0; border-radius:99px; height:7px; overflow:hidden;">
                                                <div style="width:{{ round($row->revenue/$maxRev*100) }}%; height:100%; background:linear-gradient(90deg,var(--navy),var(--gold)); border-radius:99px;"></div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="rpt-empty">No data for this period</td></tr>
                                @endforelse
                            </tbody>
                            @if($report['data']->count())
                                <tfoot><tr class="rpt-tfoot">
                                    <td>Total</td>
                                    <td class="text-center">{{ $report['data']->sum('count') }}</td>
                                    <td class="text-end">Rs. {{ number_format($report['data']->sum('revenue'),0) }}</td>
                                    <td></td>
                                </tr></tfoot>
                            @endif
                        </table>
                        @if($report['paginated'] && method_exists($report['data'],'hasPages') && $report['data']->hasPages())
                            <div style="padding:10px 14px; border-top:1px solid var(--border);">
                                {{ $report['data']->links('vendor.pagination.simple-bootstrap-5') }}
                            </div>
                        @endif

                    {{-- ══ RENTAL LIST ══ --}}
                    @elseif($report['columns'] === 'rental_list')
                        <table class="table table-striped table-hover mb-0" id="reportTable" style="font-size:12px;">
                            <thead>
                                <tr>
                                    <th wire:click="sortByColumn('bill_ref')" class="rpt-sort">Bill Ref</th>
                                    <th wire:click="sortByColumn('booking_date')" class="rpt-sort">Booking</th>
                                    <th wire:click="sortByColumn('customer_name')" class="rpt-sort">Customer</th>
                                    <th>Pickup</th>
                                    <th>Return</th>
                                    @if(($report['highlight'] ?? '') === 'overdue')
                                        <th class="text-center" style="color:#c53030;">Late By</th>
                                    @endif
                                    <th wire:click="sortByColumn('total_amount')" class="rpt-sort text-end">Amount</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Baqi</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($report['data'] as $rental)
                                    @php
                                        $paid = (float)($rental->payments_sum_amount ?? 0);
                                        $due  = max(0, $rental->total_amount - $paid);
                                        $late = $rental->return_date && $rental->return_date < now()->toDateString() && !in_array($rental->status,['returned','cancelled','abandoned']);
                                    @endphp
                                    <tr class="{{ ($report['highlight'] ?? '') === 'overdue' ? 'table-danger' : '' }}">
                                        <td>
                                            <a href="{{ route('rentals.show', $rental->id) }}"
                                                style="font-family:monospace; font-weight:700; color:var(--navy); text-decoration:none;">
                                                {{ $rental->bill_ref ?? '#'.$rental->id }}
                                            </a>
                                        </td>
                                        <td style="white-space:nowrap;">{{ \Carbon\Carbon::parse($rental->booking_date)->format('d/m/Y') }}</td>
                                        <td>
                                            <div style="font-weight:600;">{{ $rental->customer_name }}</div>
                                            <div style="font-size:10px; color:var(--text-muted);">{{ $rental->customer_phone1 }}</div>
                                        </td>
                                        <td style="white-space:nowrap; font-size:11px;">{{ $rental->pickup_date ? \Carbon\Carbon::parse($rental->pickup_date)->format('d/m/Y') : '—' }}</td>
                                        <td style="white-space:nowrap; font-size:11px;">{{ $rental->return_date ? \Carbon\Carbon::parse($rental->return_date)->format('d/m/Y') : '—' }}</td>
                                        @if(($report['highlight'] ?? '') === 'overdue')
                                            <td class="text-center">
                                                <span style="background:#c53030; color:#fff; border-radius:20px; padding:2px 9px; font-size:11px; font-weight:700;">
                                                    {{ now()->diffInDays(\Carbon\Carbon::parse($rental->return_date)) }}d
                                                </span>
                                            </td>
                                        @endif
                                        <td class="text-end" style="font-weight:700;">Rs. {{ number_format($rental->total_amount,0) }}</td>
                                        <td class="text-end" style="color:#38a169; font-weight:600;">Rs. {{ number_format($paid,0) }}</td>
                                        <td class="text-end" style="color:{{ $due>0 ? '#c53030' : '#38a169' }}; font-weight:700;">
                                            {{ $due>0 ? 'Rs. '.number_format($due,0) : '✓' }}
                                        </td>
                                        <td>
                                            <span class="rental-status-badge {{ $rental->status }}" style="font-size:9px; padding:1px 6px;">
                                                {{ ucfirst(str_replace('_',' ',$rental->status)) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="10" class="rpt-empty">No records found</td></tr>
                                @endforelse
                            </tbody>
                            @php
                                $items = is_a($report['data'], \Illuminate\Pagination\LengthAwarePaginator::class)
                                    ? $report['data']
                                    : collect($report['data']);
                                $tAmt  = $items->sum('total_amount');
                                $tPaid = $items->sum('payments_sum_amount');
                                $tDue  = $items->sum(fn($r) => max(0,$r->total_amount-(float)($r->payments_sum_amount??0)));
                            @endphp
                            @if($items->count())
                                <tfoot><tr class="rpt-tfoot">
                                    <td colspan="{{ ($report['highlight']??'') === 'overdue' ? 7 : 6 }}">
                                        {{ $items->count() }} record(s)
                                        @if(method_exists($report['data'],'total')) · {{ $report['data']->total() }} total @endif
                                    </td>
                                    <td class="text-end">Rs. {{ number_format($tAmt,0) }}</td>
                                    <td class="text-end" style="color:#9ae6b4;">Rs. {{ number_format($tPaid,0) }}</td>
                                    <td class="text-end" style="color:{{ $tDue>0 ? '#feb2b2' : '#9ae6b4' }};">Rs. {{ number_format($tDue,0) }}</td>
                                    <td></td>
                                </tr></tfoot>
                            @endif
                        </table>
                        @if($report['paginated'] && method_exists($report['data'],'hasPages') && $report['data']->hasPages())
                            <div style="padding:10px 14px; border-top:1px solid var(--border);">
                                {{ $report['data']->links('vendor.pagination.simple-bootstrap-5') }}
                            </div>
                        @endif

                    {{-- ══ DUES LIST ══ --}}
                    @elseif($report['columns'] === 'dues_list')
                        <table class="table table-striped table-hover mb-0" id="reportTable" style="font-size:12px;">
                            <thead>
                                <tr>
                                    <th>Bill Ref</th>
                                    <th>Customer</th>
                                    <th>Booking</th>
                                    <th>Return Date</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end" style="color:#c53030;">Baqi</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($report['data'] as $rental)
                                    @php $paid = (float)($rental->payments_sum_amount??0); $due = $rental->total_amount - $paid; @endphp
                                    <tr>
                                        <td><a href="{{ route('rentals.show',$rental->id) }}" style="font-family:monospace; font-weight:700; color:var(--navy); text-decoration:none;">{{ $rental->bill_ref ?? '#'.$rental->id }}</a></td>
                                        <td>
                                            <div style="font-weight:600;">{{ $rental->customer_name }}</div>
                                            <div style="font-size:10px; color:var(--text-muted);">{{ $rental->customer_phone1 }}</div>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($rental->booking_date)->format('d/m/Y') }}</td>
                                        <td>{{ $rental->return_date ? \Carbon\Carbon::parse($rental->return_date)->format('d/m/Y') : '—' }}</td>
                                        <td class="text-end" style="font-weight:700;">Rs. {{ number_format($rental->total_amount,0) }}</td>
                                        <td class="text-end" style="color:#38a169;">Rs. {{ number_format($paid,0) }}</td>
                                        <td class="text-end"><span style="background:#fff5f5; color:#c53030; border-radius:5px; padding:2px 9px; font-weight:800;">Rs. {{ number_format($due,0) }}</span></td>
                                        <td><span class="rental-status-badge {{ $rental->status }}" style="font-size:9px; padding:1px 6px;">{{ ucfirst(str_replace('_',' ',$rental->status)) }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="rpt-empty">
                                        <i class="bi bi-check2-all" style="font-size:28px; color:#38a169; display:block; margin-bottom:6px;"></i>
                                        Sab paid — koi baqi nahi!
                                    </td></tr>
                                @endforelse
                            </tbody>
                            @if(count($report['data']))
                                <tfoot><tr class="rpt-tfoot">
                                    <td colspan="6">{{ count($report['data']) }} record(s)</td>
                                    <td class="text-end" style="color:#feb2b2;">Rs. {{ number_format($report['data']->sum(fn($r)=>$r->total_amount-(float)($r->payments_sum_amount??0)),0) }}</td>
                                    <td></td>
                                </tr></tfoot>
                            @endif
                        </table>

                    {{-- ══ FINES LIST ══ --}}
                    @elseif($report['columns'] === 'fines_list')
                        <table class="table table-striped table-hover mb-0" id="reportTable" style="font-size:12px;">
                            <thead>
                                <tr>
                                    <th>Bill Ref</th>
                                    <th>Customer</th>
                                    <th>Booking</th>
                                    <th class="text-end">Rental</th>
                                    <th class="text-end" style="color:#c05621;">Jurmana</th>
                                    <th class="text-end">Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($report['data'] as $rental)
                                    <tr>
                                        <td><a href="{{ route('rentals.show',$rental->id) }}" style="font-family:monospace; font-weight:700; color:var(--navy); text-decoration:none;">{{ $rental->bill_ref ?? '#'.$rental->id }}</a></td>
                                        <td>
                                            <div style="font-weight:600;">{{ $rental->customer_name }}</div>
                                            <div style="font-size:10px; color:var(--text-muted);">{{ $rental->customer_phone1 }}</div>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($rental->booking_date)->format('d/m/Y') }}</td>
                                        <td class="text-end">Rs. {{ number_format($rental->total_amount - $rental->fine_amount,0) }}</td>
                                        <td class="text-end"><span style="background:#fffaf0; color:#c05621; border-radius:5px; padding:2px 9px; font-weight:800;">Rs. {{ number_format($rental->fine_amount,0) }}</span></td>
                                        <td class="text-end" style="font-weight:700;">Rs. {{ number_format($rental->total_amount,0) }}</td>
                                        <td><span class="rental-status-badge {{ $rental->status }}" style="font-size:9px; padding:1px 6px;">{{ ucfirst(str_replace('_',' ',$rental->status)) }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="rpt-empty">No fines in this period</td></tr>
                                @endforelse
                            </tbody>
                            @if($report['data']->count())
                                <tfoot><tr class="rpt-tfoot">
                                    <td colspan="4">{{ $report['data']->total() }} record(s)</td>
                                    <td class="text-end" style="color:#fbd38d;">Rs. {{ number_format($report['data']->sum('fine_amount'),0) }}</td>
                                    <td class="text-end">Rs. {{ number_format($report['data']->sum('total_amount'),0) }}</td>
                                    <td></td>
                                </tr></tfoot>
                            @endif
                        </table>
                        @if($report['paginated'] && $report['data']->hasPages())
                            <div style="padding:10px 14px; border-top:1px solid var(--border);">{{ $report['data']->links('vendor.pagination.simple-bootstrap-5') }}</div>
                        @endif

                    {{-- ══ OVERPAID ══ --}}
                    @elseif($report['columns'] === 'overpaid_list')
                        <table class="table table-striped table-hover mb-0" id="reportTable" style="font-size:12px;">
                            <thead>
                                <tr>
                                    <th>Bill Ref</th>
                                    <th>Customer</th>
                                    <th>Booking</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end" style="color:#805ad5;">Ziyada / Advance</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($report['data'] as $rental)
                                    @php $paid=(float)($rental->payments_sum_amount??0); $excess=$paid-$rental->total_amount; @endphp
                                    <tr>
                                        <td><a href="{{ route('rentals.show',$rental->id) }}" style="font-family:monospace; font-weight:700; color:var(--navy); text-decoration:none;">{{ $rental->bill_ref ?? '#'.$rental->id }}</a></td>
                                        <td>
                                            <div style="font-weight:600;">{{ $rental->customer_name }}</div>
                                            <div style="font-size:10px; color:var(--text-muted);">{{ $rental->customer_phone1 }}</div>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($rental->booking_date)->format('d/m/Y') }}</td>
                                        <td class="text-end" style="font-weight:700;">Rs. {{ number_format($rental->total_amount,0) }}</td>
                                        <td class="text-end" style="color:#38a169;">Rs. {{ number_format($paid,0) }}</td>
                                        <td class="text-end"><span style="background:#faf5ff; color:#805ad5; border-radius:5px; padding:2px 9px; font-weight:800;">Rs. {{ number_format($excess,0) }}</span></td>
                                        <td><span class="rental-status-badge {{ $rental->status }}" style="font-size:9px; padding:1px 6px;">{{ ucfirst(str_replace('_',' ',$rental->status)) }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="rpt-empty">No overpaid rentals</td></tr>
                                @endforelse
                            </tbody>
                        </table>

                    {{-- ══ CUSTOMER WISE ══ --}}
                    @elseif($report['columns'] === 'customer_wise')
                        <table class="table table-striped table-hover mb-0" id="reportTable" style="font-size:12px;">
                            <thead>
                                <tr>
                                    <th style="width:36px;">#</th>
                                    <th>Customer</th>
                                    <th>Phone</th>
                                    <th class="text-center">Rentals</th>
                                    <th class="text-end">Revenue</th>
                                    <th>Last Booking</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($report['data'] as $row)
                                    <tr>
                                        <td style="font-weight:700; color:var(--text-muted);">
                                            {{ ($report['data']->currentPage()-1)*$report['data']->perPage()+$loop->index+1 }}
                                        </td>
                                        <td style="font-weight:600;">{{ $row->customer_name }}</td>
                                        <td style="font-size:11px; color:var(--text-muted);">{{ $row->customer_phone1 }}</td>
                                        <td class="text-center">
                                            <span style="background:var(--navy); color:#fff; border-radius:20px; padding:2px 10px; font-weight:700; font-size:11px;">{{ $row->count }}</span>
                                        </td>
                                        <td class="text-end" style="font-weight:700; color:#38a169;">Rs. {{ number_format($row->revenue,0) }}</td>
                                        <td style="font-size:11px;">{{ isset($row->last_booking) ? \Carbon\Carbon::parse($row->last_booking)->format('d/m/Y') : '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="rpt-empty">No customer data</td></tr>
                                @endforelse
                            </tbody>
                            @if($report['data']->count())
                                <tfoot><tr class="rpt-tfoot">
                                    <td colspan="3">{{ $report['data']->total() }} customers</td>
                                    <td class="text-center">{{ $report['data']->sum('count') }}</td>
                                    <td class="text-end">Rs. {{ number_format($report['data']->sum('revenue'),0) }}</td>
                                    <td></td>
                                </tr></tfoot>
                            @endif
                        </table>
                        @if($report['paginated'] && $report['data']->hasPages())
                            <div style="padding:10px 14px; border-top:1px solid var(--border);">{{ $report['data']->links('vendor.pagination.simple-bootstrap-5') }}</div>
                        @endif

                    {{-- ══ ITEM RANK ══ --}}
                    @elseif(in_array($report['columns'], ['item_rank','item_revenue']))
                        <table class="table table-striped table-hover mb-0" id="reportTable" style="font-size:12px;">
                            <thead>
                                <tr>
                                    <th style="width:36px;">#</th>
                                    <th>Code</th>
                                    <th>Product Name</th>
                                    <th class="text-center">Times Rented</th>
                                    @if($report['columns']==='item_revenue')
                                        <th class="text-end">Avg Price</th>
                                    @endif
                                    <th class="text-end">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($report['data'] as $item)
                                    <tr>
                                        <td style="font-weight:800; color:{{ $loop->index<3 ? 'var(--gold)' : 'var(--text-muted)' }}; font-size:{{ $loop->index<3 ? '15px' : '12px' }};">
                                            {{ ($report['data']->currentPage()-1)*$report['data']->perPage()+$loop->index+1 }}
                                        </td>
                                        <td><span style="font-family:monospace; background:#f0f0f0; padding:2px 7px; border-radius:4px; font-weight:700;">{{ $item->product_code }}</span></td>
                                        <td>{{ $item->product_name }}</td>
                                        <td class="text-center"><span style="background:#faf5ff; color:#805ad5; border-radius:20px; padding:2px 10px; font-weight:700;">{{ $item->times_rented }}×</span></td>
                                        @if($report['columns']==='item_revenue')
                                            <td class="text-end" style="color:var(--text-muted); font-size:11px;">Rs. {{ number_format($item->avg_price,0) }}</td>
                                        @endif
                                        <td class="text-end" style="font-weight:800; color:#38a169;">Rs. {{ number_format($item->total_revenue,0) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="rpt-empty">No item data for this period</td></tr>
                                @endforelse
                            </tbody>
                            @if($report['data']->count())
                                <tfoot><tr class="rpt-tfoot">
                                    <td colspan="{{ $report['columns']==='item_revenue' ? 4 : 3 }}">{{ $report['data']->total() }} products</td>
                                    <td class="text-center">{{ $report['data']->sum('times_rented') }}×</td>
                                    <td class="text-end">Rs. {{ number_format($report['data']->sum('total_revenue'),0) }}</td>
                                </tr></tfoot>
                            @endif
                        </table>
                        @if($report['paginated'] && $report['data']->hasPages())
                            <div style="padding:10px 14px; border-top:1px solid var(--border);">{{ $report['data']->links('vendor.pagination.simple-bootstrap-5') }}</div>
                        @endif

                    {{-- ══ ITEM DETAIL ══ --}}
                    @elseif($report['columns'] === 'item_detail')
                        <table class="table table-striped table-hover mb-0" id="reportTable" style="font-size:12px;">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Product</th>
                                    <th>Bill</th>
                                    <th>Customer</th>
                                    <th>Booking</th>
                                    <th>Pickup</th>
                                    <th>Return</th>
                                    <th class="text-end">Price</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($report['data'] as $item)
                                    <tr>
                                        <td><span style="font-family:monospace; background:#f0f0f0; padding:2px 7px; border-radius:4px; font-weight:700;">{{ $item->product_code }}</span></td>
                                        <td>{{ \Str::limit($item->product_name,26) }}</td>
                                        <td><a href="{{ route('rentals.show',$item->rental->id) }}" style="font-family:monospace; color:var(--navy); text-decoration:none; font-weight:600;">{{ $item->rental->bill_ref ?? '#'.$item->rental->id }}</a></td>
                                        <td style="font-size:11px;">{{ $item->rental->customer_name }}</td>
                                        <td style="font-size:11px; white-space:nowrap;">{{ \Carbon\Carbon::parse($item->rental->booking_date)->format('d/m/Y') }}</td>
                                        <td style="font-size:11px; white-space:nowrap;">{{ $item->rental->pickup_date ? \Carbon\Carbon::parse($item->rental->pickup_date)->format('d/m/Y') : '—' }}</td>
                                        <td style="font-size:11px; white-space:nowrap;">{{ $item->rental->return_date ? \Carbon\Carbon::parse($item->rental->return_date)->format('d/m/Y') : '—' }}</td>
                                        <td class="text-end" style="font-weight:700;">Rs. {{ number_format($item->rental_price,0) }}</td>
                                        <td><span class="rental-status-badge {{ $item->rental->status }}" style="font-size:9px; padding:1px 6px;">{{ ucfirst(str_replace('_',' ',$item->rental->status)) }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9" class="rpt-empty">No item records for this period</td></tr>
                                @endforelse
                            </tbody>
                            @if($report['data']->count())
                                <tfoot><tr class="rpt-tfoot">
                                    <td colspan="7">{{ $report['data']->total() }} item lines</td>
                                    <td class="text-end">Rs. {{ number_format($report['data']->sum('rental_price'),0) }}</td>
                                    <td></td>
                                </tr></tfoot>
                            @endif
                        </table>
                        @if($report['paginated'] && $report['data']->hasPages())
                            <div style="padding:10px 14px; border-top:1px solid var(--border);">{{ $report['data']->links('vendor.pagination.simple-bootstrap-5') }}</div>
                        @endif

                    {{-- ══ SCHEDULE ══ --}}
                    @elseif($report['columns'] === 'schedule')
                        @php $today = now()->toDateString(); $dc = $report['date_col']; @endphp
                        <table class="table table-striped table-hover mb-0" id="reportTable" style="font-size:12px;">
                            <thead>
                                <tr>
                                    <th>{{ $dc === 'pickup_date' ? 'Pickup Date' : 'Return Date' }}</th>
                                    <th>Bill Ref</th>
                                    <th>Customer</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end">Baqi</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($report['data'] as $rental)
                                    @php
                                        $paid = (float)($rental->payments_sum_amount??0);
                                        $due  = max(0,$rental->total_amount-$paid);
                                        $sd   = $rental->$dc;
                                        $isToday = $sd === $today;
                                        $isPast  = $sd && $sd < $today;
                                    @endphp
                                    <tr class="{{ $isToday ? 'table-warning' : ($isPast && !in_array($rental->status,['returned','picked_up']) ? 'table-danger' : '') }}">
                                        <td style="font-weight:700; white-space:nowrap;">
                                            @if($isToday)
                                                <span style="background:#b7791f; color:#fff; border-radius:4px; padding:1px 6px; font-size:9px; margin-right:4px;">TODAY</span>
                                            @elseif($isPast)
                                                <span style="background:#c53030; color:#fff; border-radius:4px; padding:1px 6px; font-size:9px; margin-right:4px;">PAST</span>
                                            @endif
                                            {{ $sd ? \Carbon\Carbon::parse($sd)->format('d/m/Y') : '—' }}
                                        </td>
                                        <td><a href="{{ route('rentals.show',$rental->id) }}" style="font-family:monospace; font-weight:700; color:var(--navy); text-decoration:none;">{{ $rental->bill_ref ?? '#'.$rental->id }}</a></td>
                                        <td>
                                            <div style="font-weight:600;">{{ $rental->customer_name }}</div>
                                            <div style="font-size:10px; color:var(--text-muted);">{{ $rental->customer_phone1 }}</div>
                                        </td>
                                        <td class="text-end" style="font-weight:700;">Rs. {{ number_format($rental->total_amount,0) }}</td>
                                        <td class="text-end" style="color:{{ $due>0 ? '#c53030' : '#38a169' }}; font-weight:700;">
                                            {{ $due>0 ? 'Rs. '.number_format($due,0) : '✓' }}
                                        </td>
                                        <td><span class="rental-status-badge {{ $rental->status }}" style="font-size:9px; padding:1px 6px;">{{ ucfirst(str_replace('_',' ',$rental->status)) }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="rpt-empty">No schedule data for this period</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                        @if($report['paginated'] && $report['data']->hasPages())
                            <div style="padding:10px 14px; border-top:1px solid var(--border);">{{ $report['data']->links('vendor.pagination.simple-bootstrap-5') }}</div>
                        @endif

                    @endif
                </div>
            @endif
        </div>

    </div>

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
        doc.text(document.querySelector('.table-card .fw-bold, .table-card div[style*="font-weight:700"]')?.innerText || 'Rental Report', 14, 14);
        doc.setFontSize(8);
        doc.text('{{ \Carbon\Carbon::parse($dateFrom)->format("d M Y") }} – {{ \Carbon\Carbon::parse($dateTo)->format("d M Y") }}', 14, 20);
        const table = document.getElementById('reportTable');
        if (!table) return;
        const heads = Array.from(table.querySelectorAll('thead th')).map(th => th.innerText.trim());
        const rows  = Array.from(table.querySelectorAll('tbody tr')).map(tr =>
            Array.from(tr.querySelectorAll('td')).map(td => td.innerText.trim())
        ).filter(r => r.length > 1);
        doc.autoTable({ head:[heads], body:rows, startY:25, styles:{fontSize:7}, headStyles:{fillColor:[26,54,93]}, alternateRowStyles:{fillColor:[245,247,250]} });
        doc.save('rental-report-{{ now()->format("Y-m-d") }}.pdf');
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
        XLSX.writeFile(wb, 'rental-report-{{ now()->format("Y-m-d") }}.xlsx');
    }
</script>
@endpush