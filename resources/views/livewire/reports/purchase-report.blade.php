<div>

    {{-- HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">Purchase Reports</div>
            <div class="page-subtitle">
                {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} &mdash; {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
            </div>
        </div>
        <div class="d-flex gap-2">
            @if($report && !in_array($report['type'], ['spend_cards']))
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
                    <div style="font-size:12px; margin-top:4px;">Click any order row to view full detail</div>
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

                        @if(!in_array($report['type'], ['spend_cards']))
                            <input type="text" wire:model.live.debounce.400ms="search"
                                class="form-control form-control-sm" placeholder="Search..."
                                style="width:200px;">
                        @endif

                        @if(in_array($report['type'], ['po_list']))
                            <select wire:model.live="statusFilter" class="form-select form-select-sm" style="width:130px;">
                                <option value="">All Status</option>
                                <option value="draft">Draft</option>
                                <option value="ordered">Ordered</option>
                                <option value="partial">Partial</option>
                                <option value="received">Received</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        @endif
                    </div>

                    {{-- ══ SPEND CARDS ══ --}}
                    @if($report['type'] === 'spend_cards')
                        @php $d = $report['data']; $pct = $d['total'] > 0 ? round($d['paid'] / $d['total'] * 100) : 0; @endphp
                        <div style="padding:20px; display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:4px;">
                            @foreach([
                                ['Total Orders',   $d['count'],   '#3182ce','#ebf8ff','bi-bag-check','num'],
                                ['Total Spend',    $d['total'],   '#c53030','#fff5f5','bi-cash-stack','money'],
                                ['Paid',           $d['paid'],    '#38a169','#f0fff4','bi-check-circle','money'],
                                ['Baqi / Due',     $d['due'],     $d['due']>0?'#c53030':'#38a169',$d['due']>0?'#fff5f5':'#f0fff4','bi-hourglass-split','money'],
                                ['Items Ordered',  $d['items'],   '#d69e2e','#fffff0','bi-boxes','num'],
                                ['Pending Orders', $d['pending'], '#c05621','#fffaf0','bi-clock','num'],
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
                            <div style="font-size:11px; font-weight:700; color:var(--navy); margin-bottom:6px;">Payment Rate</div>
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div style="flex:1; height:10px; background:#e2e8f0; border-radius:99px; overflow:hidden;">
                                    <div style="width:{{ $pct }}%; height:100%; background:linear-gradient(90deg,#38a169,#68d391); border-radius:99px;"></div>
                                </div>
                                <span style="font-size:18px; font-weight:800; color:#38a169;">{{ $pct }}%</span>
                            </div>
                        </div>

                    {{-- ══ PO LIST ══ --}}
                    @elseif($report['type'] === 'po_list')
                        @php
                            $statusColors = [
                                'draft'     => ['#f7f7f7','#718096'],
                                'ordered'   => ['#ebf8ff','#2b6cb0'],
                                'partial'   => ['#fffff0','#b7791f'],
                                'received'  => ['#f0fff4','#276749'],
                                'cancelled' => ['#fff5f5','#c53030'],
                            ];
                        @endphp
                        <table class="table table-striped table-hover mb-0" id="reportTable" style="font-size:12px;">
                            <thead>
                                <tr>
                                    <th wire:click="sortByColumn('po_number')" class="rpt-sort">PO #</th>
                                    <th wire:click="sortByColumn('order_date')" class="rpt-sort">Date</th>
                                    <th>Vendor</th>
                                    <th class="text-center">Items</th>
                                    <th wire:click="sortByColumn('total_amount')" class="rpt-sort text-end">Total</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Baqi</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($report['data'] as $po)
                                    @php $sc = $statusColors[$po->status] ?? ['#f7f7f7','#718096']; @endphp
                                    <tr wire:click="openModal({{ $po->id }})"
                                        style="cursor:pointer;"
                                        class="{{ ($report['highlight']??'')==='due' && $po->balance_due>0 ? 'table-danger' : '' }}">
                                        <td style="font-family:monospace; font-weight:700; color:var(--navy);">{{ $po->po_number ?? '#'.$po->id }}</td>
                                        <td style="white-space:nowrap;">{{ \Carbon\Carbon::parse($po->order_date)->format('d/m/Y') }}</td>
                                        <td style="font-weight:600;">{{ $po->vendor?->name ?? '—' }}</td>
                                        <td class="text-center">{{ $po->items->count() }}</td>
                                        <td class="text-end" style="font-weight:700;">Rs. {{ number_format($po->total_amount,0) }}</td>
                                        <td class="text-end" style="color:#38a169; font-weight:600;">Rs. {{ number_format($po->amount_paid,0) }}</td>
                                        <td class="text-end">
                                            @if($po->balance_due > 0)
                                                <span style="background:#fff5f5; color:#c53030; border-radius:5px; padding:2px 8px; font-weight:800; font-size:11px;">Rs. {{ number_format($po->balance_due,0) }}</span>
                                            @else
                                                <span style="color:#38a169; font-weight:700;">✓</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span style="font-size:10px; font-weight:600; padding:2px 8px; border-radius:20px; background:{{ $sc[0] }}; color:{{ $sc[1] }}; border:1px solid {{ $sc[1] }}33;">
                                                {{ ucfirst($po->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="rpt-empty">No records found</td></tr>
                                @endforelse
                            </tbody>
                            @if($report['data']->count())
                                <tfoot><tr class="rpt-tfoot">
                                    <td colspan="3">
                                        {{ $report['data']->count() }} record(s)
                                        @if(method_exists($report['data'],'total')) · {{ $report['data']->total() }} total @endif
                                    </td>
                                    <td class="text-center">{{ $report['data']->sum(fn($p)=>$p->items->count()) }}</td>
                                    <td class="text-end">Rs. {{ number_format($report['data']->sum('total_amount'),0) }}</td>
                                    <td class="text-end" style="color:#9ae6b4;">Rs. {{ number_format($report['data']->sum('amount_paid'),0) }}</td>
                                    <td class="text-end" style="color:#feb2b2;">Rs. {{ number_format($report['data']->sum('balance_due'),0) }}</td>
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
                        @php $maxSpend = $report['data']->max('spend') ?: 1; @endphp
                        <table class="table table-striped table-hover mb-0" id="reportTable" style="font-size:12px;">
                            <thead>
                                <tr>
                                    <th>{{ $report['period_label'] }}</th>
                                    <th class="text-center">Orders</th>
                                    <th class="text-end">Total Spend</th>
                                    <th class="text-end">Paid</th>
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
                                        <td class="text-end" style="font-weight:700; color:#c53030;">Rs. {{ number_format($row->spend,0) }}</td>
                                        <td class="text-end" style="color:#38a169;">Rs. {{ number_format($row->paid_total,0) }}</td>
                                        <td style="padding-right:16px; vertical-align:middle;">
                                            <div style="background:#e2e8f0; border-radius:99px; height:7px; overflow:hidden;">
                                                <div style="width:{{ round($row->spend/$maxSpend*100) }}%; height:100%; background:linear-gradient(90deg,#c53030,#fc8181); border-radius:99px;"></div>
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
                                    <td class="text-end">Rs. {{ number_format($report['data']->sum('spend'),0) }}</td>
                                    <td class="text-end">Rs. {{ number_format($report['data']->sum('paid_total'),0) }}</td>
                                    <td></td>
                                </tr></tfoot>
                            @endif
                        </table>
                        @if($report['paginated'] && $report['data']->hasPages())
                            <div style="padding:10px 14px; border-top:1px solid var(--border);">
                                {{ $report['data']->links('vendor.pagination.simple-bootstrap-5') }}
                            </div>
                        @endif

                    {{-- ══ VENDOR WISE ══ --}}
                    @elseif($report['type'] === 'vendor_wise')
                        <table class="table table-striped table-hover mb-0" id="reportTable" style="font-size:12px;">
                            <thead>
                                <tr>
                                    <th style="width:36px;">#</th>
                                    <th>Vendor</th>
                                    <th class="text-center">Orders</th>
                                    <th class="text-end">Total Spend</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Baqi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($report['data'] as $row)
                                    <tr>
                                        <td style="font-weight:800; color:{{ $loop->index<3?'var(--gold)':'var(--text-muted)' }}; font-size:{{ $loop->index<3?'15px':'12px' }};">
                                            {{ ($report['data']->currentPage()-1)*$report['data']->perPage()+$loop->index+1 }}
                                        </td>
                                        <td style="font-weight:600;">{{ $row->vendor?->name ?? '—' }}</td>
                                        <td class="text-center">
                                            <span style="background:#ebf8ff; color:#2c5282; border-radius:20px; padding:2px 10px; font-weight:700; font-size:11px;">{{ $row->order_count }}</span>
                                        </td>
                                        <td class="text-end" style="font-weight:700; color:#c53030;">Rs. {{ number_format($row->total_spend,0) }}</td>
                                        <td class="text-end" style="color:#38a169;">Rs. {{ number_format($row->paid_total,0) }}</td>
                                        <td class="text-end">
                                            @if($row->due_total > 0)
                                                <span style="background:#fff5f5; color:#c53030; border-radius:5px; padding:2px 8px; font-weight:800; font-size:11px;">Rs. {{ number_format($row->due_total,0) }}</span>
                                            @else
                                                <span style="color:#38a169; font-weight:700;">✓</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="rpt-empty">No vendor data for this period</td></tr>
                                @endforelse
                            </tbody>
                            @if($report['data']->count())
                                <tfoot><tr class="rpt-tfoot">
                                    <td colspan="2">{{ $report['data']->total() }} vendor(s)</td>
                                    <td class="text-center">{{ $report['data']->sum('order_count') }}</td>
                                    <td class="text-end">Rs. {{ number_format($report['data']->sum('total_spend'),0) }}</td>
                                    <td class="text-end" style="color:#9ae6b4;">Rs. {{ number_format($report['data']->sum('paid_total'),0) }}</td>
                                    <td class="text-end" style="color:#feb2b2;">Rs. {{ number_format($report['data']->sum('due_total'),0) }}</td>
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
                                    <th>Item Name</th>
                                    <th class="text-center">Total Qty</th>
                                    <th class="text-center">Times Ordered</th>
                                    <th class="text-end">Total Cost</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($report['data'] as $item)
                                    <tr>
                                        <td style="font-weight:800; color:{{ $loop->index<3?'var(--gold)':'var(--text-muted)' }}; font-size:{{ $loop->index<3?'15px':'12px' }};">
                                            {{ ($report['data']->currentPage()-1)*$report['data']->perPage()+$loop->index+1 }}
                                        </td>
                                        <td><span style="font-family:monospace; background:#f0f0f0; padding:2px 7px; border-radius:4px; font-weight:700;">{{ $item->item_code }}</span></td>
                                        <td>{{ $item->item_name }}</td>
                                        <td class="text-center" style="font-weight:700;">{{ number_format($item->total_qty,0) }}</td>
                                        <td class="text-center">
                                            <span style="background:#faf5ff; color:#805ad5; border-radius:20px; padding:2px 10px; font-weight:700;">{{ $item->times_ordered }}×</span>
                                        </td>
                                        <td class="text-end" style="font-weight:800; color:#c53030;">Rs. {{ number_format($item->total_cost,0) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="rpt-empty">No item data for this period</td></tr>
                                @endforelse
                            </tbody>
                            @if($report['data']->count())
                                <tfoot><tr class="rpt-tfoot">
                                    <td colspan="3">{{ $report['data']->total() }} products</td>
                                    <td class="text-center">{{ number_format($report['data']->sum('total_qty'),0) }}</td>
                                    <td class="text-center">{{ $report['data']->sum('times_ordered') }}</td>
                                    <td class="text-end">Rs. {{ number_format($report['data']->sum('total_cost'),0) }}</td>
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
                                    <th>Item Name</th>
                                    <th>PO #</th>
                                    <th>Vendor</th>
                                    <th>Date</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($report['data'] as $item)
                                    <tr>
                                        <td><span style="font-family:monospace; background:#f0f0f0; padding:2px 7px; border-radius:4px; font-weight:700;">{{ $item->item_code }}</span></td>
                                        <td>{{ \Str::limit($item->item_name,26) }}</td>
                                        <td>
                                            <a href="{{ route('purchase-orders.show',$item->purchaseOrder->id) }}" target="_blank"
                                                style="font-family:monospace; color:var(--navy); text-decoration:none; font-weight:600;">
                                                {{ $item->purchaseOrder->po_number ?? '#'.$item->purchaseOrder->id }}
                                            </a>
                                        </td>
                                        <td style="font-size:11px;">{{ $item->purchaseOrder->vendor?->name ?? '—' }}</td>
                                        <td style="font-size:11px; white-space:nowrap;">{{ \Carbon\Carbon::parse($item->purchaseOrder->order_date)->format('d/m/Y') }}</td>
                                        <td class="text-center">{{ number_format($item->qty,0) }}</td>
                                        <td class="text-end">Rs. {{ number_format($item->unit_price,0) }}</td>
                                        <td class="text-end" style="font-weight:700;">Rs. {{ number_format($item->qty*$item->unit_price,0) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="rpt-empty">No item records for this period</td></tr>
                                @endforelse
                            </tbody>
                            @if($report['data']->count())
                                <tfoot><tr class="rpt-tfoot">
                                    <td colspan="5">{{ $report['data']->total() }} item lines</td>
                                    <td class="text-center">{{ number_format($report['data']->sum('qty'),0) }}</td>
                                    <td></td>
                                    <td class="text-end">Rs. {{ number_format($report['data']->sum(fn($i)=>$i->qty*$i->unit_price),0) }}</td>
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
         PO DETAIL MODAL
    ══════════════════════════════════════════ --}}
    @if($modalPo)
        <div class="modal fade show" tabindex="-1"
            style="display:block; background:rgba(0,0,0,0.5);"
            wire:click.self="closeModal">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">

                    <div class="modal-header" style="background:var(--navy); color:#fff; padding:12px 20px;">
                        <div>
                            <div style="font-size:15px; font-weight:700;">
                                <i class="bi bi-receipt me-2" style="color:var(--gold);"></i>
                                {{ $modalPo['po_number'] }}
                                <span style="font-size:11px; font-weight:400; margin-left:8px; opacity:.8;">
                                    {{ \Carbon\Carbon::parse($modalPo['order_date'])->format('d M Y') }}
                                </span>
                            </div>
                            <div style="font-size:11px; color:#cbd5e0; margin-top:2px;">
                                {{ $modalPo['vendor'] }}
                                @if($modalPo['vendor_bill']) &nbsp;·&nbsp; Bill: {{ $modalPo['vendor_bill'] }} @endif
                            </div>
                        </div>
                        <button wire:click="closeModal" class="btn-close btn-close-white ms-auto" style="opacity:.8;"></button>
                    </div>

                    <div class="modal-body" style="padding:20px; background:#f8fafc;">

                        {{-- Summary pills --}}
                        <div class="d-flex gap-3 mb-4 flex-wrap">
                            @foreach([
                                ['Total',   'Rs. '.number_format($modalPo['total_amount'],0),  '#c53030','#fff5f5','bi-cash-stack'],
                                ['Paid',    'Rs. '.number_format($modalPo['amount_paid'],0),   '#276749','#f0fff4','bi-check-circle'],
                                ['Baqi',    'Rs. '.number_format($modalPo['balance_due'],0),   $modalPo['balance_due']>0?'#c53030':'#276749',$modalPo['balance_due']>0?'#fff5f5':'#f0fff4','bi-hourglass-split'],
                                ['Discount','Rs. '.number_format($modalPo['discount'],0),      '#d69e2e','#fffff0','bi-tag'],
                            ] as [$lbl,$val,$col,$bg,$icon])
                                <div style="background:{{ $bg }}; border:1.5px solid {{ $col }}22; border-radius:10px; padding:10px 14px; min-width:110px;">
                                    <div style="font-size:10px; font-weight:700; color:{{ $col }}; text-transform:uppercase; margin-bottom:4px;">
                                        <i class="bi {{ $icon }} me-1"></i>{{ $lbl }}
                                    </div>
                                    <div style="font-size:13px; font-weight:800; color:{{ $col }};">{{ $val }}</div>
                                </div>
                            @endforeach

                            @php
                                $sc = match($modalPo['status']) {
                                    'received'  => ['#f0fff4','#276749'],
                                    'ordered'   => ['#ebf8ff','#2b6cb0'],
                                    'partial'   => ['#fffff0','#b7791f'],
                                    'cancelled' => ['#fff5f5','#c53030'],
                                    default     => ['#f7f7f7','#718096'],
                                };
                            @endphp
                            <div style="background:{{ $sc[0] }}; border:1.5px solid {{ $sc[1] }}22; border-radius:10px; padding:10px 14px;">
                                <div style="font-size:10px; font-weight:700; color:{{ $sc[1] }}; text-transform:uppercase; margin-bottom:4px;">
                                    <i class="bi bi-flag me-1"></i>Status
                                </div>
                                <div style="font-size:13px; font-weight:800; color:{{ $sc[1] }};">{{ ucfirst($modalPo['status']) }}</div>
                            </div>
                        </div>

                        {{-- Items --}}
                        <div style="font-size:12px; font-weight:700; color:var(--navy); text-transform:uppercase; letter-spacing:.4px; margin-bottom:8px;">
                            <i class="bi bi-box-seam me-1" style="color:var(--gold);"></i>
                            Items ({{ count($modalPo['items']) }})
                        </div>
                        <div class="table-card mb-4" style="border-radius:8px; overflow:hidden;">
                            <table class="table table-sm table-striped mb-0" style="font-size:12px;">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Item Name</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Unit Price</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($modalPo['items'] as $item)
                                        <tr>
                                            <td><span style="font-family:monospace; background:#f0f0f0; padding:2px 7px; border-radius:4px; font-weight:700;">{{ $item['item_code'] }}</span></td>
                                            <td>{{ $item['item_name'] }}</td>
                                            <td class="text-center">{{ number_format($item['qty'],0) }}</td>
                                            <td class="text-end">Rs. {{ number_format($item['unit_price'],0) }}</td>
                                            <td class="text-end" style="font-weight:700;">Rs. {{ number_format($item['total'],0) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="rpt-tfoot">
                                        <td colspan="2">{{ count($modalPo['items']) }} item(s)</td>
                                        <td class="text-center">{{ collect($modalPo['items'])->sum('qty') }}</td>
                                        <td></td>
                                        <td class="text-end">Rs. {{ number_format(collect($modalPo['items'])->sum('total'),0) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        {{-- Payments --}}
                        @if(count($modalPo['payments']) > 0)
                            <div style="font-size:12px; font-weight:700; color:#276749; text-transform:uppercase; letter-spacing:.4px; margin-bottom:8px;">
                                <i class="bi bi-cash-coin me-1"></i>
                                Payments ({{ count($modalPo['payments']) }})
                            </div>
                            <div class="table-card" style="border-radius:8px; overflow:hidden;">
                                <table class="table table-sm table-striped mb-0" style="font-size:12px;">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th class="text-end">Amount</th>
                                            <th>Method</th>
                                            <th>Note</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($modalPo['payments'] as $pmt)
                                            <tr>
                                                <td style="white-space:nowrap;">{{ \Carbon\Carbon::parse($pmt['payment_date'])->format('d/m/Y') }}</td>
                                                <td class="text-end" style="font-weight:700; color:#38a169;">Rs. {{ number_format($pmt['amount'],0) }}</td>
                                                <td style="font-size:11px;">{{ $pmt['payment_method'] ?? '—' }}</td>
                                                <td style="font-size:11px; color:var(--text-muted);">{{ $pmt['note'] ?? '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="rpt-tfoot">
                                            <td>{{ count($modalPo['payments']) }} payment(s)</td>
                                            <td class="text-end">Rs. {{ number_format(collect($modalPo['payments'])->sum('amount'),0) }}</td>
                                            <td colspan="2"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @endif

                        @if($modalPo['notes'])
                            <div style="margin-top:14px; padding:12px 14px; background:#fff; border-radius:8px; border:1px solid var(--border); font-size:12px; color:var(--text-muted);">
                                <i class="bi bi-chat-left-text me-1"></i> {{ $modalPo['notes'] }}
                            </div>
                        @endif

                    </div>

                    <div class="modal-footer" style="padding:10px 20px; background:#f0f4f8;">
                        <a href="{{ route('purchase-orders.show', $modalPo['id']) }}" target="_blank"
                            class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Open PO
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
        doc.text('{{ addslashes($report["title"] ?? "Purchase Report") }}', 14, 14);
        doc.setFontSize(8);
        doc.text('{{ \Carbon\Carbon::parse($dateFrom)->format("d M Y") }} – {{ \Carbon\Carbon::parse($dateTo)->format("d M Y") }}', 14, 20);
        const table = document.getElementById('reportTable');
        if (!table) return;
        const heads = Array.from(table.querySelectorAll('thead th')).map(th => th.innerText.trim());
        const rows  = Array.from(table.querySelectorAll('tbody tr')).map(tr =>
            Array.from(tr.querySelectorAll('td')).map(td => td.innerText.trim())
        ).filter(r => r.length > 1);
        doc.autoTable({ head:[heads], body:rows, startY:25, styles:{fontSize:7}, headStyles:{fillColor:[26,54,93]}, alternateRowStyles:{fillColor:[245,247,250]} });
        doc.save('purchase-report-{{ now()->format("Y-m-d") }}.pdf');
    }

    function exportExcel() {
        const table = document.getElementById('reportTable');
        if (!table) return;
        const heads = Array.from(table.querySelectorAll('thead th')).map(th => th.innerText.trim());
        const rows  = [heads, ...Array.from(table.querySelectorAll('tbody tr')).map(tr =>
            Array.from(tr.querySelectorAll('td')).map(td => td.innerText.trim())
        ).filter(r => r.length > 1)];
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, XLSX.utils.aoa_to_sheet(rows), 'Purchases');
        XLSX.writeFile(wb, 'purchase-report-{{ now()->format("Y-m-d") }}.xlsx');
    }
</script>
@endpush