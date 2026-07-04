<div>

    {{-- HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">Stock Reports</div>
            <div class="page-subtitle">Inventory overview &amp; product movement</div>
        </div>
        <div class="d-flex gap-2">
            @if($report && !in_array($report['type'], ['type_breakdown']))
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

    {{-- SUMMARY PILLS — always visible --}}
    <div class="table-card mb-3" style="padding:14px 16px;">
        <div style="display:grid; grid-template-columns:repeat(6,1fr); gap:10px;">
            @foreach([
                ['Total Items',   $summary['total'],     '#3182ce','#ebf8ff','bi-tags'],
                ['Active',        $summary['active'],    '#38a169','#f0fff4','bi-check-circle'],
                ['Abandoned',     $summary['abandoned'], '#c53030','#fff5f5','bi-x-circle'],
                ['Zero Stock',    $summary['zero_stock'],'#d69e2e','#fffff0','bi-exclamation-circle'],
                ['Rental Items',  $summary['rental'],    '#805ad5','#faf5ff','bi-box-seam'],
                ['Sale Items',    $summary['sale'],      '#319795','#e6fffa','bi-cart'],
            ] as [$lbl,$val,$col,$bg,$icon])
                <div style="background:{{ $bg }}; border:1.5px solid {{ $col }}22; border-radius:10px; padding:10px 12px;">
                    <div style="font-size:9px; font-weight:700; color:{{ $col }}; text-transform:uppercase; margin-bottom:4px;">
                        <i class="bi {{ $icon }} me-1"></i>{{ $lbl }}
                    </div>
                    <div style="font-size:20px; font-weight:800; color:{{ $col }}; line-height:1;">{{ number_format($val) }}</div>
                </div>
            @endforeach
        </div>
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
                    <div style="font-size:12px; margin-top:4px;">No date filter needed — shows current inventory state</div>
                </div>
            @else
                <div class="table-card">

                    {{-- Toolbar --}}
                    <div style="padding:10px 14px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                        <div style="flex:1;">
                            <div style="font-size:13px; font-weight:700; color:var(--navy);">{{ $report['title'] }}</div>
                            @if(isset($report['data']) && method_exists($report['data'], 'total'))
                                <div style="font-size:11px; color:var(--text-muted);">{{ $report['data']->total() }} record(s)</div>
                            @endif
                        </div>

                        @if(!in_array($report['type'], ['type_breakdown','category_breakdown']))
                            <input type="text" wire:model.live.debounce.400ms="search"
                                class="form-control form-control-sm" placeholder="Search code or name..."
                                style="width:200px;">
                        @endif

                        {{-- Filters for stock_list only --}}
                        @if(($report['show_filters'] ?? false))
                            <select wire:model.live="filterType" class="form-select form-select-sm" style="width:120px;">
                                <option value="">All Types</option>
                                <option value="rental">Rental</option>
                                <option value="sale">Sale</option>
                                <option value="both">Both</option>
                                <option value="service">Service</option>
                                <option value="fabric">Fabric</option>
                            </select>
                            <select wire:model.live="filterCategory" class="form-select form-select-sm" style="width:150px;">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            <select wire:model.live="filterStatus" class="form-select form-select-sm" style="width:110px;">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="all">All</option>
                            </select>
                        @endif

                        {{-- Category filter for most_rented --}}
                        @if($report['type'] === 'most_rented')
                            <select wire:model.live="filterCategory" class="form-select form-select-sm" style="width:150px;">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    {{-- ══ PRODUCT LIST ══ --}}
                    @if($report['type'] === 'product_list')
                        <table class="table table-striped table-hover mb-0" id="reportTable" style="font-size:12px;">
                            <thead>
                                <tr>
                                    <th wire:click="sortByColumn('code')" class="rpt-sort">Code</th>
                                    <th wire:click="sortByColumn('name')" class="rpt-sort">Name</th>
                                    <th>Category</th>
                                    <th wire:click="sortByColumn('type')" class="rpt-sort">Type</th>
                                    <th>Color / Size</th>
                                    <th wire:click="sortByColumn('rental_price')" class="rpt-sort text-end">Rental Price</th>
                                    <th wire:click="sortByColumn('sale_price')" class="rpt-sort text-end">Sale Price</th>
                                    <th wire:click="sortByColumn('stock_qty')" class="rpt-sort text-center">Stock</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($report['data'] as $p)
                                    <tr>
                                        <td>
                                            <span style="font-family:monospace; font-weight:700; font-size:12px; background:#f0f0f0; padding:2px 7px; border-radius:4px;">
                                                {{ $p->code }}
                                            </span>
                                        </td>
                                        <td>
                                            <div style="font-weight:600;">{{ $p->name }}</div>
                                            @if($p->group)
                                                <div style="font-size:10px; color:#2c5282;">{{ $p->group->name }}</div>
                                            @endif
                                        </td>
                                        <td style="font-size:11px;">
                                            <span style="background:#f0f0f0; padding:1px 6px; border-radius:3px; font-weight:600; font-size:10px;">{{ $p->category?->code }}</span>
                                            <div style="color:var(--text-muted); font-size:10px;">{{ $p->category?->name }}</div>
                                        </td>
                                        <td><span class="product-type-badge {{ $p->type }}" style="font-size:10px;">{{ ucfirst($p->type) }}</span></td>
                                        <td style="font-size:11px; color:var(--text-muted);">
                                            {{ implode(' / ', array_filter([$p->color, $p->size])) ?: '—' }}
                                        </td>
                                        <td class="text-end" style="font-weight:600; color:#553c9a;">
                                            {{ $p->rental_price > 0 ? 'Rs. '.number_format($p->rental_price,0) : '—' }}
                                        </td>
                                        <td class="text-end" style="font-weight:600; color:#276749;">
                                            {{ $p->sale_price > 0 ? 'Rs. '.number_format($p->sale_price,0) : '—' }}
                                        </td>
                                        <td class="text-center" style="font-weight:700;">
                                            @if($p->type === 'fabric')
                                                {{ number_format((float)$p->stock_decimal,2) }}
                                                <div style="font-size:10px; color:var(--text-muted); font-weight:400;">{{ $p->fabric_unit }}</div>
                                            @elseif($p->type === 'service')
                                                <span style="color:var(--text-muted);">—</span>
                                            @else
                                                <span style="color:{{ $p->stock_qty == 0 ? '#c53030' : ($p->stock_qty <= 2 ? '#d69e2e' : '#38a169') }};">
                                                    {{ $p->stock_qty }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($p->is_abandoned)
                                                <span style="font-size:10px; background:#fff5f5; color:#c53030; padding:2px 7px; border-radius:10px; font-weight:600; border:1px solid #fed7d7;">Abandoned</span>
                                            @elseif($p->is_active)
                                                <span style="font-size:10px; background:#f0fff4; color:#276749; padding:2px 7px; border-radius:10px; font-weight:600; border:1px solid #9ae6b4;">Active</span>
                                            @else
                                                <span style="font-size:10px; background:#f7f7f7; color:#718096; padding:2px 7px; border-radius:10px; font-weight:600; border:1px solid #e2e8f0;">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9" class="rpt-empty">No products found</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                        @if($report['data']->hasPages())
                            <div style="padding:10px 14px; border-top:1px solid var(--border);">
                                {{ $report['data']->links('vendor.pagination.simple-bootstrap-5') }}
                            </div>
                        @endif

                    {{-- ══ MOST RENTED ══ --}}
                    @elseif($report['type'] === 'most_rented')
                        <table class="table table-striped table-hover mb-0" id="reportTable" style="font-size:12px;">
                            <thead>
                                <tr>
                                    <th style="width:36px;">#</th>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th class="text-center">Times Rented</th>
                                    <th class="text-end">Rental Price</th>
                                    <th class="text-center">Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($report['data'] as $p)
                                    <tr>
                                        <td style="font-weight:800; color:{{ $loop->index<3?'var(--gold)':'var(--text-muted)' }}; font-size:{{ $loop->index<3?'15px':'12px' }};">
                                            {{ ($report['data']->currentPage()-1)*$report['data']->perPage()+$loop->index+1 }}
                                        </td>
                                        <td><span style="font-family:monospace; background:#f0f0f0; padding:2px 7px; border-radius:4px; font-weight:700;">{{ $p->code }}</span></td>
                                        <td style="font-weight:600;">{{ $p->name }}</td>
                                        <td style="font-size:11px; color:var(--text-muted);">{{ $p->category?->name }}</td>
                                        <td class="text-center">
                                            <span style="background:#faf5ff; color:#805ad5; border-radius:20px; padding:2px 12px; font-weight:800; font-size:13px;">
                                                {{ $p->times_rented }}×
                                            </span>
                                        </td>
                                        <td class="text-end" style="font-weight:700; color:#553c9a;">Rs. {{ number_format($p->rental_price,0) }}</td>
                                        <td class="text-center" style="font-weight:700; color:{{ $p->stock_qty==0?'#c53030':'#38a169' }};">{{ $p->stock_qty }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="rpt-empty">No rental data found</td></tr>
                                @endforelse
                            </tbody>
                            @if($report['data']->count())
                                <tfoot><tr class="rpt-tfoot">
                                    <td colspan="4">{{ $report['data']->total() }} product(s)</td>
                                    <td class="text-center">{{ $report['data']->sum('times_rented') }}× total</td>
                                    <td colspan="2"></td>
                                </tr></tfoot>
                            @endif
                        </table>
                        @if($report['data']->hasPages())
                            <div style="padding:10px 14px; border-top:1px solid var(--border);">
                                {{ $report['data']->links('vendor.pagination.simple-bootstrap-5') }}
                            </div>
                        @endif

                    {{-- ══ ABANDONED LIST ══ --}}
                    @elseif($report['type'] === 'abandoned_list')
                        <div style="padding:8px 14px; background:#fff5f5; border-bottom:1px solid #fed7d7; font-size:12px; color:#c53030;">
                            <i class="bi bi-info-circle me-1"></i>
                            Total write-off value: <strong>Rs. {{ number_format($report['total_writeoff'],0) }}</strong>
                        </div>
                        <table class="table table-striped table-hover mb-0" id="reportTable" style="font-size:12px;">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Type</th>
                                    <th>Reason</th>
                                    <th>Date</th>
                                    <th class="text-end">Write-off Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($report['data'] as $p)
                                    <tr>
                                        <td><span style="font-family:monospace; font-weight:700; background:#fff5f5; padding:2px 7px; border-radius:4px; color:#c53030;">{{ $p->code }}</span></td>
                                        <td style="font-weight:600;">{{ $p->name }}</td>
                                        <td style="font-size:11px; color:var(--text-muted);">{{ $p->category?->name }}</td>
                                        <td><span class="product-type-badge {{ $p->type }}" style="font-size:10px;">{{ ucfirst($p->type) }}</span></td>
                                        <td style="font-size:11px; color:var(--text-muted);">{{ $p->abandoned_note ?: '—' }}</td>
                                        <td style="font-size:11px; white-space:nowrap;">{{ $p->abandoned_date ? \Carbon\Carbon::parse($p->abandoned_date)->format('d/m/Y') : '—' }}</td>
                                        <td class="text-end" style="font-weight:700; color:#c53030;">Rs. {{ number_format($p->abandoned_price,0) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="rpt-empty">
                                        <i class="bi bi-check-circle text-success" style="font-size:24px; display:block; margin-bottom:6px;"></i>
                                        No abandoned items
                                    </td></tr>
                                @endforelse
                            </tbody>
                            @if($report['data']->count())
                                <tfoot><tr class="rpt-tfoot">
                                    <td colspan="6">{{ $report['data']->total() }} item(s)</td>
                                    <td class="text-end">Rs. {{ number_format($report['data']->sum('abandoned_price'),0) }}</td>
                                </tr></tfoot>
                            @endif
                        </table>
                        @if($report['data']->hasPages())
                            <div style="padding:10px 14px; border-top:1px solid var(--border);">
                                {{ $report['data']->links('vendor.pagination.simple-bootstrap-5') }}
                            </div>
                        @endif

                    {{-- ══ CATEGORY BREAKDOWN ══ --}}
                    @elseif($report['type'] === 'category_breakdown')
                        <table class="table table-striped table-hover mb-0" id="reportTable" style="font-size:12px;">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Type</th>
                                    <th class="text-center">Count</th>
                                    <th class="text-center">Total Stock</th>
                                    <th class="text-end">Rental Value</th>
                                    <th class="text-end">Sale Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($report['data'] as $row)
                                    <tr>
                                        <td style="font-weight:600;">
                                            <span style="font-size:10px; background:#f0f0f0; padding:1px 5px; border-radius:3px; font-family:monospace; margin-right:4px;">{{ $row->category?->code }}</span>
                                            {{ $row->category?->name ?? 'Uncategorised' }}
                                        </td>
                                        <td><span class="product-type-badge {{ $row->type }}" style="font-size:10px;">{{ ucfirst($row->type) }}</span></td>
                                        <td class="text-center" style="font-weight:700;">{{ $row->count }}</td>
                                        <td class="text-center" style="color:#38a169; font-weight:700;">{{ $row->total_stock ?: '—' }}</td>
                                        <td class="text-end" style="color:#553c9a;">{{ $row->rental_value > 0 ? 'Rs. '.number_format($row->rental_value,0) : '—' }}</td>
                                        <td class="text-end" style="color:#276749;">{{ $row->sale_value > 0 ? 'Rs. '.number_format($row->sale_value,0) : '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="rpt-empty">No data</td></tr>
                                @endforelse
                            </tbody>
                            @if($report['data']->count())
                                <tfoot><tr class="rpt-tfoot">
                                    <td colspan="2">{{ $report['data']->total() }} rows</td>
                                    <td class="text-center">{{ $report['data']->sum('count') }}</td>
                                    <td class="text-center">{{ $report['data']->sum('total_stock') }}</td>
                                    <td class="text-end">Rs. {{ number_format($report['data']->sum('rental_value'),0) }}</td>
                                    <td class="text-end">Rs. {{ number_format($report['data']->sum('sale_value'),0) }}</td>
                                </tr></tfoot>
                            @endif
                        </table>
                        @if($report['data']->hasPages())
                            <div style="padding:10px 14px; border-top:1px solid var(--border);">
                                {{ $report['data']->links('vendor.pagination.simple-bootstrap-5') }}
                            </div>
                        @endif

                    {{-- ══ TYPE BREAKDOWN ══ --}}
                    @elseif($report['type'] === 'type_breakdown')
                        <div style="padding:20px; display:grid; grid-template-columns:repeat(3,1fr); gap:14px;">
                            @php
                                $typeColors = [
                                    'rental'  => ['#553c9a','#faf5ff'],
                                    'sale'    => ['#276749','#f0fff4'],
                                    'both'    => ['#2b6cb0','#ebf8ff'],
                                    'service' => ['#319795','#e6fffa'],
                                    'fabric'  => ['#b7791f','#fffff0'],
                                ];
                            @endphp
                            @foreach($report['data'] as $row)
                                @php [$col,$bg] = $typeColors[$row->type] ?? ['#718096','#f7fafc']; @endphp
                                <div style="background:{{ $bg }}; border:1.5px solid {{ $col }}22; border-radius:10px; padding:16px 18px;">
                                    <div style="font-size:11px; font-weight:700; color:{{ $col }}; text-transform:uppercase; margin-bottom:8px;">
                                        <span class="product-type-badge {{ $row->type }}" style="font-size:11px;">{{ ucfirst($row->type) }}</span>
                                    </div>
                                    <div style="font-size:28px; font-weight:800; color:{{ $col }}; margin-bottom:4px;">{{ number_format($row->count) }}</div>
                                    <div style="font-size:11px; color:{{ $col }}; opacity:.7;">items</div>
                                    @if($row->rental_value > 0)
                                        <div style="margin-top:8px; font-size:11px; color:{{ $col }};">
                                            Rental value: <strong>Rs. {{ number_format($row->rental_value,0) }}</strong>
                                        </div>
                                    @endif
                                    @if($row->sale_value > 0)
                                        <div style="font-size:11px; color:{{ $col }};">
                                            Sale value: <strong>Rs. {{ number_format($row->sale_value,0) }}</strong>
                                        </div>
                                    @endif
                                    @if($row->total_stock > 0)
                                        <div style="font-size:11px; color:{{ $col }};">
                                            Total stock: <strong>{{ number_format($row->total_stock) }}</strong>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
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
        doc.text('{{ addslashes($report["title"] ?? "Stock Report") }}', 14, 14);
        const table = document.getElementById('reportTable');
        if (!table) return;
        const heads = Array.from(table.querySelectorAll('thead th')).map(th => th.innerText.trim());
        const rows  = Array.from(table.querySelectorAll('tbody tr')).map(tr =>
            Array.from(tr.querySelectorAll('td')).map(td => td.innerText.trim())
        ).filter(r => r.length > 1);
        doc.autoTable({ head:[heads], body:rows, startY:20, styles:{fontSize:7}, headStyles:{fillColor:[26,54,93]}, alternateRowStyles:{fillColor:[245,247,250]} });
        doc.save('stock-report-{{ now()->format("Y-m-d") }}.pdf');
    }

    function exportExcel() {
        const table = document.getElementById('reportTable');
        if (!table) return;
        const heads = Array.from(table.querySelectorAll('thead th')).map(th => th.innerText.trim());
        const rows  = [heads, ...Array.from(table.querySelectorAll('tbody tr')).map(tr =>
            Array.from(tr.querySelectorAll('td')).map(td => td.innerText.trim())
        ).filter(r => r.length > 1)];
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, XLSX.utils.aoa_to_sheet(rows), 'Stock');
        XLSX.writeFile(wb, 'stock-report-{{ now()->format("Y-m-d") }}.xlsx');
    }
</script>
@endpush