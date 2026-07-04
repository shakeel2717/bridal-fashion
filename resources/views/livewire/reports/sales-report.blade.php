<div>

    {{-- HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">Sales Reports</div>
            <div class="page-subtitle">
                {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} &mdash; {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
            </div>
        </div>
        <div class="d-flex gap-2">
            @if($report && !in_array($report['type'], ['revenue_cards']))
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
            @foreach([['last_3_months','Last 3 Months'],['last_6_months','Last 6 Months'],['this_quarter','This Quarter'],['last_quarter','Last Quarter'],['this_year','This Year'],['last_year','Last Year'],['all_time','All Time'],['custom','Custom ✦']] as [$key,$lbl])
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
                    <div style="font-size:12px; margin-top:4px;">Click any sale row to view full detail</div>
                </div>
            @else
                <div class="table-card">

                    {{-- Toolbar --}}
                    <div style="padding:10px 14px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                        <div style="flex:1;">
                            <div style="font-size:13px; font-weight:700; color:var(--navy);">{{ $report['title'] }}</div>
                            <div style="font-size:11px; color:var(--text-muted);">
                                {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
                                @if($report['paginated'] && method_exists($report['data'], 'total'))
                                    · {{ $report['data']->total() }} record(s)
                                @endif
                            </div>
                        </div>

                        @if(!in_array($report['type'], ['revenue_cards']))
                            <input type="text" wire:model.live.debounce.400ms="search"
                                class="form-control form-control-sm" placeholder="Search..."
                                style="width:200px;">
                        @endif

                        @if(in_array($report['type'], ['sale_list','discount_list']))
                            <select wire:model.live="statusFilter" class="form-select form-select-sm" style="width:130px;">
                                <option value="">All Status</option>
                                <option value="completed">Completed</option>
                                <option value="pending">Pending</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="refunded">Refunded</option>
                            </select>
                        @endif
                    </div>

                    {{-- ══ REVENUE CARDS ══ --}}
                    @if($report['type'] === 'revenue_cards')
                        @php $d = $report['data']; $pct = $d['revenue'] > 0 ? round($d['paid'] / $d['revenue'] * 100) : 0; @endphp
                        <div style="padding:20px; display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:4px;">
                            @foreach([
                                ['Total Sales',    $d['count'],   '#3182ce','#ebf8ff','bi-receipt','num'],
                                ['Total Revenue',  $d['revenue'], '#38a169','#f0fff4','bi-cash-stack','money'],
                                ['Avg Bill Value', $d['avg'],     '#319795','#e6fffa','bi-calculator','money'],
                                ['Collected',      $d['paid'],    '#805ad5','#faf5ff','bi-check-circle','money'],
                                ['Baqi / Due',     $d['due'],     '#c53030','#fff5f5','bi-hourglass-split','money'],
                                ['Discount Given', $d['discount'],'#d69e2e','#fffff0','bi-tag','money'],
                            ] as [$lbl,$val,$col,$bg,$icon,$fmt])
                                <div style="background:{{ $bg }}; border:1.5px solid {{ $col }}22; border-radius:10px; padding:16px 14px;">
                                    <div style="font-size:10px; font-weight:700; color:{{ $col }}; text-transform:uppercase; margin-bottom:6px;">
                                        <i class="bi {{ $icon }} me-1"></i>{{ $lbl }}
                                    </div>
                                    <div style="font-size:{{ $fmt==='money'?'15px':'24px' }}; font-weight:800; color:{{ $col }};">
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

                    {{-- ══ SALE LIST ══ --}}
                    @elseif(in_array($report['type'], ['sale_list','discount_list']))
                        <table class="table table-striped table-hover mb-0" id="reportTable" style="font-size:12px;">
                            <thead>
                                <tr>
                                    <th wire:click="sortByColumn('bill_ref')" class="rpt-sort">Bill Ref</th>
                                    <th wire:click="sortByColumn('sale_date')" class="rpt-sort">Date</th>
                                    <th wire:click="sortByColumn('customer_name')" class="rpt-sort">Customer</th>
                                    <th class="text-center">Items</th>
                                    <th wire:click="sortByColumn('total_amount')" class="rpt-sort text-end">Amount</th>
                                    @if($report['type'] === 'discount_list')
                                        <th class="text-end" style="color:#d69e2e;">Discount</th>
                                    @endif
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Baqi</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($report['data'] as $sale)
                                    <tr wire:click="openModal({{ $sale->id }})"
                                        style="cursor:pointer;"
                                        class="{{ ($report['highlight']??'')==='due' && $sale->remaining_balance>0 ? 'table-danger' : '' }}">
                                        <td style="font-family:monospace; font-weight:700; color:var(--navy);">
                                            {{ $sale->bill_ref ?? '#'.$sale->id }}
                                        </td>
                                        <td style="white-space:nowrap;">{{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') }}</td>
                                        <td>
                                            <div style="font-weight:600;">{{ $sale->customer_name }}</div>
                                            <div style="font-size:10px; color:var(--text-muted);">{{ $sale->customer_phone1 }}</div>
                                        </td>
                                        <td class="text-center">
                                            <span style="font-size:11px;">{{ $sale->items->count() }}</span>
                                        </td>
                                        <td class="text-end" style="font-weight:700;">Rs. {{ number_format($sale->total_amount,0) }}</td>
                                        @if($report['type'] === 'discount_list')
                                            <td class="text-end">
                                                <span style="color:#d69e2e; font-weight:700;">Rs. {{ number_format($sale->discount,0) }}</span>
                                            </td>
                                        @endif
                                        <td class="text-end" style="color:#38a169; font-weight:600;">Rs. {{ number_format($sale->advance_paid,0) }}</td>
                                        <td class="text-end">
                                            @if($sale->remaining_balance > 0)
                                                <span style="background:#fff5f5; color:#c53030; border-radius:5px; padding:2px 8px; font-weight:800; font-size:11px;">Rs. {{ number_format($sale->remaining_balance,0) }}</span>
                                            @else
                                                <span style="color:#38a169; font-weight:700;">✓</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $sc = match($sale->status) {
                                                    'completed' => ['#f0fff4','#276749'],
                                                    'cancelled' => ['#fff5f5','#c53030'],
                                                    'refunded'  => ['#faf5ff','#805ad5'],
                                                    default     => ['#fffff0','#b7791f'],
                                                };
                                            @endphp
                                            <span style="background:{{ $sc[0] }}; color:{{ $sc[1] }}; border-radius:4px; padding:2px 7px; font-size:10px; font-weight:700;">
                                                {{ ucfirst($sale->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="{{ $report['type']==='discount_list'?9:8 }}" class="rpt-empty">No records found</td></tr>
                                @endforelse
                            </tbody>
                            @if($report['data']->count())
                                <tfoot><tr class="rpt-tfoot">
                                    <td colspan="{{ $report['type']==='discount_list'?4:3 }}">
                                        {{ $report['data']->count() }} record(s)
                                        @if(method_exists($report['data'],'total')) · {{ $report['data']->total() }} total @endif
                                    </td>
                                    <td class="text-center">{{ $report['data']->sum(fn($s)=>$s->items->count()) }}</td>
                                    <td class="text-end">Rs. {{ number_format($report['data']->sum('total_amount'),0) }}</td>
                                    @if($report['type']==='discount_list')
                                        <td class="text-end" style="color:#fbd38d;">Rs. {{ number_format($report['data']->sum('discount'),0) }}</td>
                                    @endif
                                    <td class="text-end" style="color:#9ae6b4;">Rs. {{ number_format($report['data']->sum('advance_paid'),0) }}</td>
                                    <td class="text-end" style="color:#feb2b2;">Rs. {{ number_format($report['data']->sum('remaining_balance'),0) }}</td>
                                    <td></td>
                                </tr></tfoot>
                            @endif
                        </table>
                        @if($report['paginated'] && $report['data']->hasPages())
                            <div style="padding:10px 14px; border-top:1px solid var(--border);">
                                {{ $report['data']->links('vendor.pagination.simple-bootstrap-5') }}
                            </div>
                        @endif

                    {{-- ══ PERIOD SUMMARY ══ --}}
                    @elseif($report['type'] === 'period_summary')
                        @php $maxRev = $report['data']->max('revenue') ?: 1; @endphp
                        <table class="table table-striped table-hover mb-0" id="reportTable" style="font-size:12px;">
                            <thead>
                                <tr>
                                    <th>{{ $report['period_label'] }}</th>
                                    <th class="text-center">Sales</th>
                                    <th class="text-end">Revenue</th>
                                    <th class="text-end" style="color:#d69e2e;">Discount</th>
                                    <th style="width:180px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($report['data'] as $row)
                                    <tr>
                                        <td style="font-weight:600;">
                                            {{ $report['period_label']==='Date'
                                                ? \Carbon\Carbon::parse($row->period)->format('d M Y (l)')
                                                : \Carbon\Carbon::parse($row->period.'-01')->format('F Y') }}
                                        </td>
                                        <td class="text-center">
                                            <span style="background:var(--navy); color:#fff; border-radius:20px; padding:2px 10px; font-weight:700; font-size:11px;">{{ $row->count }}</span>
                                        </td>
                                        <td class="text-end" style="font-weight:700; color:#38a169;">Rs. {{ number_format($row->revenue,0) }}</td>
                                        <td class="text-end" style="color:#d69e2e;">
                                            {{ $row->discount_total > 0 ? 'Rs. '.number_format($row->discount_total,0) : '—' }}
                                        </td>
                                        <td style="padding-right:16px; vertical-align:middle;">
                                            <div style="background:#e2e8f0; border-radius:99px; height:7px; overflow:hidden;">
                                                <div style="width:{{ round($row->revenue/$maxRev*100) }}%; height:100%; background:linear-gradient(90deg,var(--navy),var(--gold)); border-radius:99px;"></div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="rpt-empty">No data for this period</td></tr>
                                @endforelse
                            </tbody>
                            @if($report['data']->count())
                                <tfoot><tr class="rpt-tfoot">
                                    <td>Total</td>
                                    <td class="text-center">{{ $report['data']->sum('count') }}</td>
                                    <td class="text-end">Rs. {{ number_format($report['data']->sum('revenue'),0) }}</td>
                                    <td class="text-end">Rs. {{ number_format($report['data']->sum('discount_total'),0) }}</td>
                                    <td></td>
                                </tr></tfoot>
                            @endif
                        </table>
                        @if($report['paginated'] && $report['data']->hasPages())
                            <div style="padding:10px 14px; border-top:1px solid var(--border);">
                                {{ $report['data']->links('vendor.pagination.simple-bootstrap-5') }}
                            </div>
                        @endif

                    {{-- ══ ITEM RANK ══ --}}
                    @elseif($report['type'] === 'item_rank')
                        <table class="table table-striped table-hover mb-0" id="reportTable" style="font-size:12px;">
                            <thead>
                                <tr>
                                    <th style="width:36px;">#</th>
                                    <th>Code</th>
                                    <th>Product Name</th>
                                    <th class="text-center">Qty Sold</th>
                                    <th class="text-center">Times Sold</th>
                                    @if($activeReport === 'item_wise')
                                        <th class="text-end">Avg Price</th>
                                    @endif
                                    <th class="text-end">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($report['data'] as $item)
                                    <tr>
                                        <td style="font-weight:800; color:{{ $loop->index<3?'var(--gold)':'var(--text-muted)' }}; font-size:{{ $loop->index<3?'15px':'12px' }};">
                                            {{ ($report['data']->currentPage()-1)*$report['data']->perPage()+$loop->index+1 }}
                                        </td>
                                        <td><span style="font-family:monospace; background:#f0f0f0; padding:2px 7px; border-radius:4px; font-weight:700;">{{ $item->product_code }}</span></td>
                                        <td>{{ $item->product_name }}</td>
                                        <td class="text-center" style="font-weight:700;">{{ number_format($item->total_qty, 2) }}</td>
                                        <td class="text-center">
                                            <span style="background:#faf5ff; color:#805ad5; border-radius:20px; padding:2px 10px; font-weight:700;">{{ $item->times_sold }}×</span>
                                        </td>
                                        @if($activeReport === 'item_wise')
                                            <td class="text-end" style="color:var(--text-muted); font-size:11px;">Rs. {{ number_format($item->avg_price,0) }}</td>
                                        @endif
                                        <td class="text-end" style="font-weight:800; color:#38a169;">Rs. {{ number_format($item->total_revenue,0) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="rpt-empty">No item data for this period</td></tr>
                                @endforelse
                            </tbody>
                            @if($report['data']->count())
                                <tfoot><tr class="rpt-tfoot">
                                    <td colspan="{{ $activeReport==='item_wise'?4:3 }}">{{ $report['data']->total() }} products</td>
                                    <td class="text-center">{{ number_format($report['data']->sum('total_qty'),2) }}</td>
                                    <td class="text-center">{{ $report['data']->sum('times_sold') }}</td>
                                    @if($activeReport === 'item_wise') <td></td> @endif
                                    <td class="text-end">Rs. {{ number_format($report['data']->sum('total_revenue'),0) }}</td>
                                </tr></tfoot>
                            @endif
                        </table>
                        @if($report['paginated'] && $report['data']->hasPages())
                            <div style="padding:10px 14px; border-top:1px solid var(--border);">
                                {{ $report['data']->links('vendor.pagination.simple-bootstrap-5') }}
                            </div>
                        @endif

                    {{-- ══ ITEM DETAIL ══ --}}
                    @elseif($report['type'] === 'item_detail')
                        <table class="table table-striped table-hover mb-0" id="reportTable" style="font-size:12px;">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Product</th>
                                    <th>Bill</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($report['data'] as $item)
                                    <tr>
                                        <td><span style="font-family:monospace; background:#f0f0f0; padding:2px 7px; border-radius:4px; font-weight:700;">{{ $item->product_code }}</span></td>
                                        <td>{{ \Str::limit($item->product_name,26) }}</td>
                                        <td>
                                            <a href="{{ route('sales.show',$item->sale->id) }}" target="_blank"
                                                style="font-family:monospace; color:var(--navy); text-decoration:none; font-weight:600;">
                                                {{ $item->sale->bill_ref ?? '#'.$item->sale->id }}
                                            </a>
                                        </td>
                                        <td style="font-size:11px;">{{ $item->sale->customer_name }}</td>
                                        <td style="font-size:11px; white-space:nowrap;">{{ \Carbon\Carbon::parse($item->sale->sale_date)->format('d/m/Y') }}</td>
                                        <td class="text-center">{{ number_format($item->qty,2) }}</td>
                                        <td class="text-end">Rs. {{ number_format($item->sale_price,0) }}</td>
                                        <td class="text-end" style="font-weight:700;">Rs. {{ number_format($item->qty*$item->sale_price,0) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="rpt-empty">No item records for this period</td></tr>
                                @endforelse
                            </tbody>
                            @if($report['data']->count())
                                <tfoot><tr class="rpt-tfoot">
                                    <td colspan="5">{{ $report['data']->total() }} item lines</td>
                                    <td class="text-center">{{ number_format($report['data']->sum('qty'),2) }}</td>
                                    <td></td>
                                    <td class="text-end">Rs. {{ number_format($report['data']->sum(fn($i)=>$i->qty*$i->sale_price),0) }}</td>
                                </tr></tfoot>
                            @endif
                        </table>
                        @if($report['paginated'] && $report['data']->hasPages())
                            <div style="padding:10px 14px; border-top:1px solid var(--border);">
                                {{ $report['data']->links('vendor.pagination.simple-bootstrap-5') }}
                            </div>
                        @endif
                    @endif

                </div>
            @endif
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         SALE DETAIL MODAL
    ══════════════════════════════════════════ --}}
    @if($modalSale)
        <div class="modal fade show" tabindex="-1"
            style="display:block; background:rgba(0,0,0,0.5);"
            wire:click.self="closeModal">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">

                    <div class="modal-header" style="background:var(--navy); color:#fff; padding:12px 20px;">
                        <div>
                            <div style="font-size:15px; font-weight:700;">
                                <i class="bi bi-bag me-2" style="color:var(--gold);"></i>
                                {{ $modalSale['bill_ref'] }}
                                <span style="font-size:11px; font-weight:400; margin-left:8px; opacity:.8;">
                                    {{ \Carbon\Carbon::parse($modalSale['sale_date'])->format('d M Y') }}
                                </span>
                            </div>
                            <div style="font-size:11px; color:#cbd5e0; margin-top:2px;">
                                {{ $modalSale['customer_name'] }}
                                @if($modalSale['customer_phone1']) &nbsp;·&nbsp; {{ $modalSale['customer_phone1'] }} @endif
                                @if($modalSale['customer_cnic']) &nbsp;·&nbsp; {{ $modalSale['customer_cnic'] }} @endif
                            </div>
                        </div>
                        <button wire:click="closeModal" class="btn-close btn-close-white ms-auto" style="opacity:.8;"></button>
                    </div>

                    <div class="modal-body" style="padding:20px; background:#f8fafc;">

                        {{-- Summary pills --}}
                        <div class="d-flex gap-3 mb-4 flex-wrap">
                            @foreach([
                                ['Amount',    'Rs. '.number_format($modalSale['total_amount'],0), '#1a365d','#e8edf5','bi-cash-stack'],
                                ['Discount',  'Rs. '.number_format($modalSale['discount'],0),     '#b7791f','#fffff0','bi-tag'],
                                ['Paid',      'Rs. '.number_format($modalSale['advance_paid'],0), '#276749','#f0fff4','bi-check-circle'],
                                ['Baqi',      'Rs. '.number_format($modalSale['remaining_balance'],0), $modalSale['remaining_balance']>0?'#c53030':'#276749', $modalSale['remaining_balance']>0?'#fff5f5':'#f0fff4','bi-hourglass-split'],
                                ['Employee',  $modalSale['employee'],                              '#4a5568','#f7fafc','bi-person'],
                            ] as [$lbl,$val,$col,$bg,$icon])
                                <div style="background:{{ $bg }}; border:1.5px solid {{ $col }}22; border-radius:10px; padding:10px 14px; min-width:110px;">
                                    <div style="font-size:10px; font-weight:700; color:{{ $col }}; text-transform:uppercase; margin-bottom:4px;">
                                        <i class="bi {{ $icon }} me-1"></i>{{ $lbl }}
                                    </div>
                                    <div style="font-size:13px; font-weight:800; color:{{ $col }};">{{ $val }}</div>
                                </div>
                            @endforeach

                            {{-- Status --}}
                            @php $sc = match($modalSale['status']) { 'completed'=>['#f0fff4','#276749'], 'cancelled'=>['#fff5f5','#c53030'], 'refunded'=>['#faf5ff','#805ad5'], default=>['#fffff0','#b7791f'] }; @endphp
                            <div style="background:{{ $sc[0] }}; border:1.5px solid {{ $sc[1] }}22; border-radius:10px; padding:10px 14px;">
                                <div style="font-size:10px; font-weight:700; color:{{ $sc[1] }}; text-transform:uppercase; margin-bottom:4px;">
                                    <i class="bi bi-flag me-1"></i>Status
                                </div>
                                <div style="font-size:13px; font-weight:800; color:{{ $sc[1] }};">{{ ucfirst($modalSale['status']) }}</div>
                            </div>
                        </div>

                        {{-- Items table --}}
                        <div style="font-size:12px; font-weight:700; color:var(--navy); text-transform:uppercase; letter-spacing:.4px; margin-bottom:8px;">
                            <i class="bi bi-box-seam me-1" style="color:var(--gold);"></i>
                            Items ({{ count($modalSale['items']) }})
                        </div>
                        <div class="table-card" style="border-radius:8px; overflow:hidden;">
                            <table class="table table-sm table-striped mb-0" style="font-size:12px;">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Product</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Unit Price</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-center">Pickup</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($modalSale['items'] as $item)
                                        <tr>
                                            <td><span style="font-family:monospace; background:#f0f0f0; padding:2px 7px; border-radius:4px; font-weight:700;">{{ $item['product_code'] }}</span></td>
                                            <td>{{ $item['product_name'] }}</td>
                                            <td class="text-center">{{ number_format($item['qty'],2) }}</td>
                                            <td class="text-end">Rs. {{ number_format($item['sale_price'],0) }}</td>
                                            <td class="text-end" style="font-weight:700;">Rs. {{ number_format($item['total'],0) }}</td>
                                            <td class="text-center">
                                                @if($item['pickup_status'] === 'taken')
                                                    <span style="background:#f0fff4; color:#276749; border-radius:4px; padding:1px 7px; font-size:10px; font-weight:700;">Taken</span>
                                                @else
                                                    <span style="background:#fff5f5; color:#c53030; border-radius:4px; padding:1px 7px; font-size:10px; font-weight:700;">Pending</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="rpt-tfoot">
                                        <td colspan="4">{{ count($modalSale['items']) }} item(s)</td>
                                        <td class="text-end">Rs. {{ number_format(collect($modalSale['items'])->sum('total'),0) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        @if($modalSale['notes'])
                            <div style="margin-top:14px; padding:12px 14px; background:#fff; border-radius:8px; border:1px solid var(--border); font-size:12px; color:var(--text-muted);">
                                <i class="bi bi-chat-left-text me-1"></i> {{ $modalSale['notes'] }}
                            </div>
                        @endif

                    </div>

                    <div class="modal-footer" style="padding:10px 20px; background:#f0f4f8;">
                        <a href="{{ route('sales.show', $modalSale['id']) }}" target="_blank"
                            class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Open Sale
                        </a>
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
        doc.text('{{ addslashes($report["title"] ?? "Sales Report") }}', 14, 14);
        doc.setFontSize(8);
        doc.text('{{ \Carbon\Carbon::parse($dateFrom)->format("d M Y") }} – {{ \Carbon\Carbon::parse($dateTo)->format("d M Y") }}', 14, 20);
        const table = document.getElementById('reportTable');
        if (!table) return;
        const heads = Array.from(table.querySelectorAll('thead th')).map(th => th.innerText.trim());
        const rows  = Array.from(table.querySelectorAll('tbody tr')).map(tr =>
            Array.from(tr.querySelectorAll('td')).map(td => td.innerText.trim())
        ).filter(r => r.length > 1);
        doc.autoTable({ head:[heads], body:rows, startY:25, styles:{fontSize:7}, headStyles:{fillColor:[26,54,93]}, alternateRowStyles:{fillColor:[245,247,250]} });
        doc.save('sales-report-{{ now()->format("Y-m-d") }}.pdf');
    }

    function exportExcel() {
        const table = document.getElementById('reportTable');
        if (!table) return;
        const heads = Array.from(table.querySelectorAll('thead th')).map(th => th.innerText.trim());
        const rows  = [heads, ...Array.from(table.querySelectorAll('tbody tr')).map(tr =>
            Array.from(tr.querySelectorAll('td')).map(td => td.innerText.trim())
        ).filter(r => r.length > 1)];
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, XLSX.utils.aoa_to_sheet(rows), 'Sales');
        XLSX.writeFile(wb, 'sales-report-{{ now()->format("Y-m-d") }}.xlsx');
    }
</script>
@endpush