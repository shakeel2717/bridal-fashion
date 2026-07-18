<div>

    {{-- ── Page Header ───────────────────────────────────────────────────── --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">Employee Reports</div>
            <div class="page-subtitle">
                {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }}
                –
                {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
            </div>
        </div>
        @if($activeReport && $report && $report['data']->count())
        <div class="d-flex gap-2">
            <button onclick="exportPDF()" class="action-btn">
                <i class="bi bi-file-earmark-pdf"></i> PDF
            </button>
            <button onclick="exportExcel()" class="action-btn">
                <i class="bi bi-file-earmark-excel"></i> Excel
            </button>
        </div>
        @endif
    </div>

    {{-- ── Date Filter Pills ──────────────────────────────────────────────── --}}
    <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
        @foreach([
            'today'         => 'Today',
            'this_week'     => 'This Week',
            'this_month'    => 'This Month',
            'last_month'    => 'Last Month',
            'last_3_months' => 'Last 3 Months',
            'this_year'     => 'This Year',
            'last_year'     => 'Last Year',
            'all_time'      => 'All Time',
            'custom'        => 'Custom',
        ] as $key => $label)
        <button wire:click="setFilter('{{ $key }}')"
                class="rpt-pill {{ $activeFilter === $key ? 'active' : '' }}">
            {{ $label }}
        </button>
        @endforeach

        @if($activeFilter === 'custom')
        <div class="d-flex align-items-center gap-2 ms-2">
            <input type="date" wire:model.live="dateFrom" class="form-control form-control-sm" style="width:145px">
            <span class="text-muted">—</span>
            <input type="date" wire:model.live="dateTo" class="form-control form-control-sm" style="width:145px">
        </div>
        @endif
    </div>

    {{-- ── Two-column layout ──────────────────────────────────────────────── --}}
    <div style="display:grid; grid-template-columns:220px 1fr; gap:16px; align-items:start;">

        {{-- LEFT SIDEBAR --}}
        <div class="rpt-sidebar">
            @foreach($menu as $groupKey => $group)
            <div class="rpt-group">
                <div class="rpt-group-label">
                    <i class="bi {{ $group['icon'] }} me-1"></i> {{ $group['label'] }}
                </div>
                @foreach($group['items'] as $key => $item)
                <a href="#" wire:click.prevent="selectReport('{{ $key }}')"
                   class="rpt-link {{ $activeReport === $key ? 'active' : '' }}">
                    <i class="bi {{ $item['icon'] }} me-1"></i>
                    {{ $item['label'] }}
                </a>
                @endforeach
            </div>
            @endforeach
        </div>

        {{-- RIGHT CONTENT --}}
        <div>

            {{-- No report selected --}}
            @if(! $activeReport)
            <div class="table-card">
                <div class="rpt-empty">
                    <i class="bi bi-person-badge" style="font-size:2.5rem; color:var(--gold); opacity:.5;"></i>
                    <div class="mt-2 text-muted">Select a report from the sidebar</div>
                </div>
            </div>

            @elseif($report)

            {{-- ── Employee selector (for per-employee reports) ──────── --}}
            @if(in_array($activeReport, ['attendance_detail', 'hr_financial']))
            <div class="table-card mb-3" style="padding:14px 18px;">
                <div class="d-flex align-items-center gap-3">
                    <label class="fw-semibold mb-0" style="white-space:nowrap; font-size:.85rem;">Select Employee</label>
                    <select wire:model.live="selectedEmployee" class="form-select form-select-sm" style="max-width:260px;">
                        <option value="">— Choose Employee —</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @endif

            {{-- ── Late threshold (for attendance reports) ────────────── --}}
            @if(in_array($activeReport, ['attendance_summary', 'attendance_late']))
            <div class="table-card mb-3" style="padding:14px 18px;">
                <div class="d-flex align-items-center gap-3">
                    <label class="fw-semibold mb-0" style="white-space:nowrap; font-size:.85rem;">Late if arrival after</label>
                    <input type="time" wire:model.live="lateThreshold" class="form-control form-control-sm" style="width:120px;">
                </div>
            </div>
            @endif

            <div class="table-card">
                {{-- Toolbar --}}
                <div class="table-card-header">
                    <div class="table-card-title">
                        {{ $report['title'] }}
                        @if($report['data']->count())
                        <span class="badge ms-2" style="background:var(--gold); color:#000; font-size:.7rem; font-weight:600;">
                            {{ $report['data']->count() }} records
                        </span>
                        @endif
                    </div>
                    @if(!in_array($activeReport, ['attendance_detail', 'hr_financial']))
                    <div>
                        <input type="search" wire:model.live.debounce.300ms="search"
                               class="form-control form-control-sm"
                               placeholder="Search…" style="width:200px;">
                    </div>
                    @endif
                </div>

                {{-- Empty state --}}
                @if($report['data']->isEmpty())
                <div class="rpt-empty">
                    @if(isset($report['empty']))
                    <i class="bi bi-person-fill-x" style="font-size:2rem; opacity:.3;"></i>
                    <div class="mt-2 text-muted">{{ $report['empty'] }}</div>
                    @else
                    <i class="bi bi-inbox" style="font-size:2rem; opacity:.3;"></i>
                    <div class="mt-2 text-muted">No records found for the selected period.</div>
                    @endif
                </div>

                @else

                <div class="table-responsive">
                <table class="table table-hover mb-0" id="reportTable">

                    {{-- ── ATTENDANCE SUMMARY ──────────────────── --}}
                    @if($report['type'] === 'attendance_summary')
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th class="text-center">Present</th>
                            <th class="text-center">Absent</th>
                            <th class="text-center">Half Day</th>
                            <th class="text-center">Leave</th>
                            <th class="text-center">Late</th>
                            <th class="text-center">On Time</th>
                            <th class="text-center">Avg Arrival</th>
                            <th class="text-center">Attendance %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['data'] as $i => $row)
                        <tr>
                            <td class="text-muted" style="font-size:.8rem;">{{ $i + 1 }}</td>
                            <td class="fw-semibold">{{ $row['name'] }}</td>
                            <td class="text-center">
                                <span class="badge" style="background:#d1fae5; color:#065f46;">{{ $row['present'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge" style="background:#fee2e2; color:#991b1b;">{{ $row['absent'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge" style="background:#fef9c3; color:#854d0e;">{{ $row['half_day'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge" style="background:#e0e7ff; color:#3730a3;">{{ $row['leave'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge" style="background:#fde8d8; color:#9a3412;">{{ $row['late'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge" style="background:#dcfce7; color:#166534;">{{ $row['on_time'] }}</span>
                            </td>
                            <td class="text-center text-muted" style="font-size:.85rem;">{{ $row['avg_time_in'] }}</td>
                            <td class="text-center">
                                <span class="fw-semibold" style="color:{{ $row['pct'] >= 80 ? 'var(--navy)' : ($row['pct'] >= 60 ? '#d97706' : '#dc2626') }}">
                                    {{ $row['pct'] }}%
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="rpt-tfoot">
                        <tr>
                            <td colspan="2" class="fw-semibold">Totals</td>
                            <td class="text-center fw-semibold">{{ $report['data']->sum('present') }}</td>
                            <td class="text-center fw-semibold">{{ $report['data']->sum('absent') }}</td>
                            <td class="text-center fw-semibold">{{ $report['data']->sum('half_day') }}</td>
                            <td class="text-center fw-semibold">{{ $report['data']->sum('leave') }}</td>
                            <td class="text-center fw-semibold">{{ $report['data']->sum('late') }}</td>
                            <td class="text-center fw-semibold">{{ $report['data']->sum('on_time') }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>

                    {{-- ── ATTENDANCE DETAIL ───────────────────── --}}
                    @elseif($report['type'] === 'attendance_detail')
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Day</th>
                            <th>Status</th>
                            <th class="text-center">Time In</th>
                            <th class="text-center">Time Out</th>
                            <th class="text-center">Hours</th>
                            <th class="text-center">Timing</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['data'] as $i => $row)
                        <tr>
                            <td class="text-muted" style="font-size:.8rem;">{{ $i + 1 }}</td>
                            <td style="font-size:.85rem;">{{ $row['date'] }}</td>
                            <td class="text-muted" style="font-size:.8rem;">{{ $row['day'] }}</td>
                            <td>
                                @php
                                    $sc = match($row['status']) {
                                        'present'  => ['#d1fae5','#065f46','Present'],
                                        'absent'   => ['#fee2e2','#991b1b','Absent'],
                                        'half_day' => ['#fef9c3','#854d0e','Half Day'],
                                        'leave'    => ['#e0e7ff','#3730a3','Leave'],
                                        default    => ['#f3f4f6','#374151', ucfirst($row['status'])],
                                    };
                                @endphp
                                <span class="badge" style="background:{{ $sc[0] }}; color:{{ $sc[1] }};">{{ $sc[2] }}</span>
                            </td>
                            <td class="text-center" style="font-size:.85rem; font-family:monospace;">{{ $row['time_in'] }}</td>
                            <td class="text-center" style="font-size:.85rem; font-family:monospace;">{{ $row['time_out'] }}</td>
                            <td class="text-center text-muted" style="font-size:.8rem;">{{ $row['hours'] }}</td>
                            <td class="text-center">
                                @if($row['late'])
                                <span class="badge" style="background:#fde8d8; color:#9a3412; font-size:.7rem;">Late</span>
                                @elseif($row['status'] === 'present')
                                <span class="badge" style="background:#dcfce7; color:#166534; font-size:.7rem;">On Time</span>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-muted" style="font-size:.8rem;">{{ $row['note'] ?: '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="rpt-tfoot">
                        <tr>
                            <td colspan="3" class="fw-semibold">Total Days: {{ $report['data']->count() }}</td>
                            <td colspan="6"></td>
                        </tr>
                    </tfoot>

                    {{-- ── ATTENDANCE LATE ─────────────────────── --}}
                    @elseif($report['type'] === 'attendance_late')
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Day</th>
                            <th class="text-center">Time In</th>
                            <th class="text-center">Minutes Late</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['data'] as $i => $row)
                        <tr>
                            <td class="text-muted" style="font-size:.8rem;">{{ $i + 1 }}</td>
                            <td class="fw-semibold">{{ $row['name'] }}</td>
                            <td style="font-size:.85rem;">{{ $row['date'] }}</td>
                            <td class="text-muted" style="font-size:.8rem;">{{ $row['day'] }}</td>
                            <td class="text-center" style="font-family:monospace; font-size:.85rem;">{{ $row['time_in'] }}</td>
                            <td class="text-center">
                                <span class="badge" style="background:#fde8d8; color:#9a3412;">+{{ $row['mins_late'] }} min</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="rpt-tfoot">
                        <tr>
                            <td colspan="5" class="fw-semibold">Total Late Instances</td>
                            <td class="text-center fw-semibold">{{ $report['data']->count() }}</td>
                        </tr>
                    </tfoot>

                    {{-- ── BUSINESS OVERVIEW ───────────────────── --}}
                    @elseif($report['type'] === 'business_overview')
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th class="text-center">Rentals</th>
                            <th class="text-end">Rental Value</th>
                            <th class="text-center">Sales</th>
                            <th class="text-end">Sale Value</th>
                            <th class="text-center">Total</th>
                            <th class="text-end">Total Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['data'] as $i => $row)
                        <tr>
                            <td class="text-muted" style="font-size:.8rem;">{{ $i + 1 }}</td>
                            <td class="fw-semibold">{{ $row['name'] }}</td>
                            <td class="text-center">{{ $row['rental_count'] }}</td>
                            <td class="text-end" style="font-size:.85rem;">{{ number_format($row['rental_value'], 0) }}</td>
                            <td class="text-center">{{ $row['sale_count'] }}</td>
                            <td class="text-end" style="font-size:.85rem;">{{ number_format($row['sale_value'], 0) }}</td>
                            <td class="text-center fw-semibold">{{ $row['total_count'] }}</td>
                            <td class="text-end fw-semibold" style="color:var(--navy);">{{ number_format($row['total_revenue'], 0) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="rpt-tfoot">
                        <tr>
                            <td colspan="2" class="fw-semibold">Totals</td>
                            <td class="text-center fw-semibold">{{ $report['data']->sum('rental_count') }}</td>
                            <td class="text-end fw-semibold">{{ number_format($report['data']->sum('rental_value'), 0) }}</td>
                            <td class="text-center fw-semibold">{{ $report['data']->sum('sale_count') }}</td>
                            <td class="text-end fw-semibold">{{ number_format($report['data']->sum('sale_value'), 0) }}</td>
                            <td class="text-center fw-semibold">{{ $report['data']->sum('total_count') }}</td>
                            <td class="text-end fw-semibold" style="color:var(--navy);">{{ number_format($report['data']->sum('total_revenue'), 0) }}</td>
                        </tr>
                    </tfoot>

                    {{-- ── BUSINESS RENTALS ────────────────────── --}}
                    @elseif($report['type'] === 'business_rentals')
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th class="text-center">Rentals Created</th>
                            <th class="text-end">Total Value (PKR)</th>
                            <th class="text-end">Avg per Rental</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['data'] as $i => $row)
                        <tr>
                            <td class="text-muted" style="font-size:.8rem;">{{ $i + 1 }}</td>
                            <td class="fw-semibold">{{ $row['name'] }}</td>
                            <td class="text-center">{{ $row['count'] }}</td>
                            <td class="text-end fw-semibold" style="color:var(--navy);">{{ number_format($row['total'], 0) }}</td>
                            <td class="text-end text-muted" style="font-size:.85rem;">{{ number_format($row['avg'], 0) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="rpt-tfoot">
                        <tr>
                            <td colspan="2" class="fw-semibold">Totals</td>
                            <td class="text-center fw-semibold">{{ $report['data']->sum('count') }}</td>
                            <td class="text-end fw-semibold">{{ number_format($report['data']->sum('total'), 0) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>

                    {{-- ── BUSINESS SALES ──────────────────────── --}}
                    @elseif($report['type'] === 'business_sales')
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th class="text-center">Sales Created</th>
                            <th class="text-end">Total Revenue (PKR)</th>
                            <th class="text-end">Avg per Sale</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['data'] as $i => $row)
                        <tr>
                            <td class="text-muted" style="font-size:.8rem;">{{ $i + 1 }}</td>
                            <td class="fw-semibold">{{ $row['name'] }}</td>
                            <td class="text-center">{{ $row['count'] }}</td>
                            <td class="text-end fw-semibold" style="color:var(--navy);">{{ number_format($row['total'], 0) }}</td>
                            <td class="text-end text-muted" style="font-size:.85rem;">{{ number_format($row['avg'], 0) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="rpt-tfoot">
                        <tr>
                            <td colspan="2" class="fw-semibold">Totals</td>
                            <td class="text-center fw-semibold">{{ $report['data']->sum('count') }}</td>
                            <td class="text-end fw-semibold">{{ number_format($report['data']->sum('total'), 0) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>

                    {{-- ── HR ADVANCES ──────────────────────────── --}}
                    @elseif($report['type'] === 'hr_advances')
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Date</th>
                            <th class="text-end">Amount (PKR)</th>
                            <th>Note</th>
                            <th class="text-center">Deducted</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['data'] as $i => $row)
                        <tr>
                            <td class="text-muted" style="font-size:.8rem;">{{ $i + 1 }}</td>
                            <td class="fw-semibold">{{ $row['name'] }}</td>
                            <td style="font-size:.85rem;">{{ $row['date'] }}</td>
                            <td class="text-end fw-semibold" style="color:var(--navy);">{{ number_format($row['amount'], 0) }}</td>
                            <td class="text-muted" style="font-size:.85rem;">{{ $row['note'] }}</td>
                            <td class="text-center">
                                @if($row['deducted'] === 'Yes')
                                <span class="badge" style="background:#d1fae5; color:#065f46;">Yes</span>
                                @else
                                <span class="badge" style="background:#fef9c3; color:#854d0e;">Pending</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="rpt-tfoot">
                        <tr>
                            <td colspan="3" class="fw-semibold">Total</td>
                            <td class="text-end fw-semibold">{{ number_format($report['data']->sum('amount'), 0) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>

                    {{-- ── HR SALARY ────────────────────────────── --}}
                    @elseif($report['type'] === 'hr_salary')
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Period</th>
                            <th class="text-center">Days</th>
                            <th class="text-end">Base</th>
                            <th class="text-end">Earned</th>
                            <th class="text-end">Advances</th>
                            <th class="text-end">Bonus</th>
                            <th class="text-end">Net (PKR)</th>
                            <th class="text-center">Status</th>
                            <th>Paid Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['data'] as $i => $row)
                        <tr>
                            <td class="text-muted" style="font-size:.8rem;">{{ $i + 1 }}</td>
                            <td class="fw-semibold">{{ $row['name'] }}</td>
                            <td style="font-size:.85rem;">{{ $row['period'] }}</td>
                            <td class="text-center">{{ $row['days'] }}</td>
                            <td class="text-end" style="font-size:.85rem;">{{ number_format($row['base'], 0) }}</td>
                            <td class="text-end" style="font-size:.85rem;">{{ number_format($row['earned'], 0) }}</td>
                            <td class="text-end" style="font-size:.85rem; color:#dc2626;">{{ number_format($row['advances'], 0) }}</td>
                            <td class="text-end" style="font-size:.85rem; color:#059669;">{{ number_format($row['bonus'], 0) }}</td>
                            <td class="text-end fw-semibold" style="color:var(--navy);">{{ number_format($row['net'], 0) }}</td>
                            <td class="text-center">
                                @php
                                    $sc = match($row['status'] ?? '') {
                                        'paid'    => ['#d1fae5','#065f46','Paid'],
                                        'pending' => ['#fef9c3','#854d0e','Pending'],
                                        default   => ['#f3f4f6','#374151', ucfirst($row['status'] ?? '—')],
                                    };
                                @endphp
                                <span class="badge" style="background:{{ $sc[0] }}; color:{{ $sc[1] }};">{{ $sc[2] }}</span>
                            </td>
                            <td style="font-size:.85rem;">{{ $row['paid_date'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="rpt-tfoot">
                        <tr>
                            <td colspan="4" class="fw-semibold">Totals</td>
                            <td class="text-end fw-semibold">{{ number_format($report['data']->sum('base'), 0) }}</td>
                            <td class="text-end fw-semibold">{{ number_format($report['data']->sum('earned'), 0) }}</td>
                            <td class="text-end fw-semibold" style="color:#dc2626;">{{ number_format($report['data']->sum('advances'), 0) }}</td>
                            <td class="text-end fw-semibold" style="color:#059669;">{{ number_format($report['data']->sum('bonus'), 0) }}</td>
                            <td class="text-end fw-semibold" style="color:var(--navy);">{{ number_format($report['data']->sum('net'), 0) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>

                    {{-- ── SINGLE EMPLOYEE FINANCIALS ─────────────── --}}
                    @elseif($report['type'] === 'hr_financial')
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th class="text-end">Earned</th>
                            <th class="text-end">Bonus</th>
                            <th class="text-end">Advance</th>
                            <th class="text-end">Net (PKR)</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['data'] as $i => $row)
                        <tr>
                            <td class="text-muted" style="font-size:.8rem;">{{ $i + 1 }}</td>
                            <td style="font-size:.85rem;">{{ $row['date'] }}</td>
                            <td>
                                @if($row['type'] === 'Salary')
                                    <span class="badge" style="background:#e0e7ff; color:#3730a3;">Salary</span>
                                @else
                                    <span class="badge" style="background:#fef3c7; color:#92400e;">Advance</span>
                                @endif
                            </td>
                            <td style="font-size:.85rem;">{{ $row['desc'] }}</td>
                            <td class="text-end" style="font-size:.85rem; color:#059669;">{{ $row['earned'] > 0 ? number_format($row['earned'], 0) : '—' }}</td>
                            <td class="text-end" style="font-size:.85rem; color:#059669;">{{ $row['bonus'] > 0 ? number_format($row['bonus'], 0) : '—' }}</td>
                            <td class="text-end" style="font-size:.85rem; color:#dc2626;">{{ $row['advance'] > 0 ? number_format($row['advance'], 0) : '—' }}</td>
                            <td class="text-end fw-semibold" style="color:var(--navy);">{{ $row['net'] > 0 ? number_format($row['net'], 0) : '—' }}</td>
                            <td class="text-center">
                                @php
                                    $sc = match($row['status'] ?? '') {
                                        'Paid'     => ['#d1fae5','#065f46'],
                                        'Pending'  => ['#fef9c3','#854d0e'],
                                        'Deducted' => ['#e0e7ff','#3730a3'],
                                        default    => ['#f3f4f6','#374151'],
                                    };
                                @endphp
                                <span class="badge" style="background:{{ $sc[0] }}; color:{{ $sc[1] }};">{{ $row['status'] }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="rpt-tfoot">
                        <tr>
                            <td colspan="4" class="fw-semibold">Totals</td>
                            <td class="text-end fw-semibold" style="color:#059669;">{{ number_format($report['data']->sum('earned'), 0) }}</td>
                            <td class="text-end fw-semibold" style="color:#059669;">{{ number_format($report['data']->sum('bonus'), 0) }}</td>
                            <td class="text-end fw-semibold" style="color:#dc2626;">{{ number_format($report['data']->sum('advance'), 0) }}</td>
                            <td class="text-end fw-semibold" style="color:var(--navy);">{{ number_format($report['data']->sum('net'), 0) }}</td>
                            <td></td>
                        </tr>
                        @if(isset($report['summary']) && ($report['summary']['advances_pending'] ?? 0) > 0)
                        <tr>
                            <td colspan="9" class="text-end" style="font-size:.8rem; color:#dc2626;">
                                Pending (not yet deducted) advances: Rs. {{ number_format($report['summary']['advances_pending'], 0) }}
                            </td>
                        </tr>
                        @endif
                    </tfoot>

                    @endif

                </table>
                </div>
                @endif
            </div>
            @endif

        </div>{{-- end right --}}
    </div>{{-- end grid --}}

</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
function exportPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ orientation: 'landscape' });
    doc.autoTable({ html: '#reportTable', startY: 10, styles: { fontSize: 8 } });
    doc.save('employee-report.pdf');
}
function exportExcel() {
    const wb  = XLSX.utils.book_new();
    const ws  = XLSX.utils.table_to_sheet(document.getElementById('reportTable'));
    XLSX.utils.book_append_sheet(wb, ws, 'Report');
    XLSX.writeFile(wb, 'employee-report.xlsx');
}
</script>
@endpush
