<div>
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">Stock Report</div>
            <div class="page-subtitle">Inventory overview & product movement</div>
        </div>
        <div class="d-flex gap-2">
            <button onclick="exportExcel()" class="btn btn-sm btn-outline-success">
                <i class="bi bi-file-earmark-excel me-1"></i> Excel
            </button>
            <button onclick="exportPDF()" class="btn btn-sm btn-outline-danger">
                <i class="bi bi-file-earmark-pdf me-1"></i> PDF
            </button>
            <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Reports
            </a>
        </div>
    </div>

    {{-- View Tabs --}}
    <div class="d-flex gap-2 flex-wrap mb-3">
        @foreach([
            ['key' => 'stock',     'label' => 'Stock List',   'icon' => 'bi-tags'],
            ['key' => 'movement',  'label' => 'Movement',     'icon' => 'bi-arrow-left-right'],
            ['key' => 'abandoned', 'label' => 'Abandoned',    'icon' => 'bi-x-circle'],
        ] as $v)
            @php $isActive = $activeView === $v['key']; @endphp
            <div wire:click="setView('{{ $v['key'] }}')"
                style="background:{{ $isActive ? 'var(--navy)' : '#fff' }};
                       border:1.5px solid {{ $isActive ? 'var(--navy)' : 'var(--border)' }};
                       border-radius:9px; padding:7px 16px; font-size:12px; cursor:pointer;
                       color:{{ $isActive ? '#fff' : 'var(--text-muted)' }};
                       font-weight:{{ $isActive ? '700' : '500' }};
                       display:inline-flex; align-items:center; gap:6px;
                       {{ $isActive ? 'box-shadow:0 2px 8px rgba(0,0,0,0.15);' : '' }}">
                <i class="bi {{ $v['icon'] }}" style="font-size:13px;"></i>
                {{ $v['label'] }}
            </div>
        @endforeach
    </div>

    {{-- Summary Cards --}}
    <div style="display:grid; grid-template-columns:repeat(8,1fr); gap:10px; margin-bottom:20px;">
        @foreach([
            ['label' => 'Total Items',      'value' => $summary['total_products'],         'icon' => 'bi-tags',             'color' => '#3182ce', 'bg' => '#ebf8ff', 'format' => 'number'],
            ['label' => 'Active',           'value' => $summary['active'],                 'icon' => 'bi-check-circle',     'color' => '#38a169', 'bg' => '#f0fff4', 'format' => 'number'],
            ['label' => 'Abandoned',        'value' => $summary['abandoned'],              'icon' => 'bi-x-circle',         'color' => '#c53030', 'bg' => '#fff5f5', 'format' => 'number'],
            ['label' => 'Zero Stock',       'value' => $summary['zero_stock'],             'icon' => 'bi-exclamation-circle','color' => '#d69e2e', 'bg' => '#fffff0', 'format' => 'number'],
            ['label' => 'Rental Items',     'value' => $summary['rental_items'],           'icon' => 'bi-box-seam',         'color' => '#805ad5', 'bg' => '#faf5ff', 'format' => 'number'],
            ['label' => 'Sale Items',       'value' => $summary['sale_items'],             'icon' => 'bi-cart',             'color' => '#319795', 'bg' => '#e6fffa', 'format' => 'number'],
            ['label' => 'Write-off Value',  'value' => $summary['total_abandoned_value'],  'icon' => 'bi-dash-circle',      'color' => '#c05621', 'bg' => '#fffaf0', 'format' => 'money'],
            ['label' => 'Rental Portfolio', 'value' => $summary['total_rental_value'],     'icon' => 'bi-cash-stack',       'color' => '#2c7a7b', 'bg' => '#e6fffa', 'format' => 'money'],
        ] as $card)
            <div style="background:{{ $card['bg'] }}; border:1.5px solid {{ $card['color'] }}22; border-radius:10px; padding:10px 12px;">
                <div style="display:flex; align-items:center; gap:5px; margin-bottom:5px;">
                    <i class="bi {{ $card['icon'] }}" style="font-size:14px; color:{{ $card['color'] }};"></i>
                    <span style="font-size:9px; color:{{ $card['color'] }}; font-weight:600; text-transform:uppercase; line-height:1.2;">{{ $card['label'] }}</span>
                </div>
                <div style="font-size:{{ $card['format'] === 'money' ? '12px' : '20px' }}; font-weight:800; color:{{ $card['color'] }}; line-height:1;">
                    {{ $card['format'] === 'money' ? 'Rs. ' . number_format($card['value'], 0) : number_format($card['value']) }}
                </div>
            </div>
        @endforeach
    </div>

    {{-- ── STOCK LIST VIEW ── --}}
    @if($activeView === 'stock')

        {{-- Filters --}}
        <div class="d-flex gap-2 flex-wrap mb-3 align-items-center">
            <input type="text" wire:model.live.debounce.400ms="search"
                class="form-control form-control-sm" style="width:220px;" placeholder="Search code or name...">

            <select wire:model.live="filterType" class="form-select form-select-sm" style="width:130px;">
                <option value="">All Types</option>
                <option value="rental">Rental</option>
                <option value="sale">Sale</option>
                <option value="both">Both</option>
                <option value="service">Service</option>
                <option value="fabric">Fabric</option>
            </select>

            <select wire:model.live="filterCategory" class="form-select form-select-sm" style="width:160px;">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>

            @foreach([
                ['val' => 'active',   'label' => 'Active'],
                ['val' => 'inactive', 'label' => 'Inactive'],
                ['val' => 'all',      'label' => 'All'],
            ] as $s)
                <div wire:click="$set('filterStatus', '{{ $s['val'] }}')"
                    style="background:{{ $filterStatus === $s['val'] ? 'var(--navy)' : '#fff' }};
                           border:1.5px solid {{ $filterStatus === $s['val'] ? 'var(--navy)' : 'var(--border)' }};
                           border-radius:20px; padding:3px 12px; font-size:11px; cursor:pointer;
                           color:{{ $filterStatus === $s['val'] ? '#fff' : 'var(--text-muted)' }}; font-weight:600;">
                    {{ $s['label'] }}
                </div>
            @endforeach

            <span style="font-size:12px; color:var(--text-muted); margin-left:auto;">
                {{ $products->total() }} item(s)
            </span>
        </div>

        <div class="table-card">
            <table class="table table-striped table-hover mb-0" id="stockTable" style="font-size:12px;">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th>Color / Size</th>
                        <th class="text-end">Rental Price</th>
                        <th class="text-end">Sale Price</th>
                        <th class="text-center">Stock</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>
                                <span style="font-family:monospace; font-weight:700; font-size:12px;
                                    background:#f0f0f0; padding:2px 7px; border-radius:4px;">
                                    {{ $product->code }}
                                </span>
                            </td>
                            <td>
                                <div style="font-weight:600;">{{ $product->name }}</div>
                                @if($product->group)
                                    <div style="font-size:10px; color:#2c5282;">{{ $product->group->name }}</div>
                                @endif
                            </td>
                            <td style="font-size:11px;">
                                <span style="background:#f0f0f0; padding:1px 6px; border-radius:3px; font-weight:600;">{{ $product->category?->code }}</span>
                                <div style="color:var(--text-muted); font-size:10px;">{{ $product->category?->name }}</div>
                            </td>
                            <td>
                                <span class="product-type-badge {{ $product->type }}">{{ ucfirst($product->type) }}</span>
                            </td>
                            <td style="font-size:11px; color:var(--text-muted);">
                                {{ implode(' / ', array_filter([$product->color, $product->size])) ?: '—' }}
                            </td>
                            <td class="text-end" style="font-weight:600; color:#553c9a;">
                                @if($product->rental_price > 0) Rs. {{ number_format($product->rental_price, 0) }} @else — @endif
                            </td>
                            <td class="text-end" style="font-weight:600; color:#276749;">
                                @if($product->sale_price > 0) Rs. {{ number_format($product->sale_price, 0) }} @else — @endif
                            </td>
                            <td class="text-center" style="font-weight:700;">
                                @if($product->type === 'fabric')
                                    {{ number_format((float)$product->stock_decimal, 2) }}
                                    <div style="font-size:10px; color:var(--text-muted); font-weight:400;">{{ $product->fabric_unit }}</div>
                                @elseif($product->type === 'service')
                                    <span style="color:var(--text-muted);">—</span>
                                @else
                                    <span style="color:{{ $product->stock_qty == 0 ? '#c53030' : '#38a169' }};">
                                        {{ $product->stock_qty }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($product->is_abandoned)
                                    <span style="font-size:10px; background:#fff5f5; color:#c53030; padding:2px 7px; border-radius:10px; font-weight:600; border:1px solid #fed7d7;">Abandoned</span>
                                @elseif($product->is_active)
                                    <span style="font-size:10px; background:#f0fff4; color:#276749; padding:2px 7px; border-radius:10px; font-weight:600; border:1px solid #9ae6b4;">Active</span>
                                @else
                                    <span style="font-size:10px; background:#f7f7f7; color:#718096; padding:2px 7px; border-radius:10px; font-weight:600; border:1px solid #e2e8f0;">Inactive</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4" style="color:var(--text-muted);">
                                <i class="bi bi-tags" style="font-size:32px; display:block; margin-bottom:8px;"></i>
                                No products found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($products->hasPages())
                <div style="padding:12px 16px; border-top:1px solid var(--border);">
                    {{ $products->links('vendor.pagination.simple-bootstrap-5') }}
                </div>
            @endif
        </div>

    {{-- ── MOVEMENT VIEW ── --}}
    @elseif($activeView === 'movement')

        <div class="row g-3 mb-3">

            {{-- Category Breakdown --}}
            <div class="col-4">
                <div class="table-card" style="height:100%;">
                    <div style="padding:12px 16px; border-bottom:1px solid var(--border); font-size:12px; font-weight:700; color:var(--navy);">
                        <i class="bi bi-folder me-2" style="color:var(--gold);"></i> By Category
                    </div>
                    <table class="table table-sm table-striped mb-0" style="font-size:12px;">
                        <thead><tr><th>Category</th><th class="text-center">Items</th><th class="text-end">Rental Value</th></tr></thead>
                        <tbody>
                            @forelse($categoryBreakdown as $row)
                                <tr>
                                    <td>
                                        <span style="font-family:monospace; font-size:10px; background:#f0f0f0; padding:1px 5px; border-radius:3px;">{{ $row->category?->code }}</span>
                                        <div style="font-size:10px; color:var(--text-muted);">{{ $row->category?->name }}</div>
                                    </td>
                                    <td class="text-center" style="font-weight:700;">{{ $row->count }}</td>
                                    <td class="text-end" style="font-size:11px; color:#553c9a; font-weight:600;">Rs. {{ number_format($row->total_rental_value, 0) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-3">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Type Breakdown Chart --}}
            <div class="col-3">
                <div class="table-card" style="height:100%;">
                    <div style="padding:12px 16px; border-bottom:1px solid var(--border); font-size:12px; font-weight:700; color:var(--navy);">
                        <i class="bi bi-pie-chart me-2" style="color:#3182ce;"></i> By Type
                    </div>
                    <div style="padding:12px;">
                        <canvas id="typeChart" height="180"></canvas>
                    </div>
                </div>
            </div>

            {{-- Most Rented --}}
            <div class="col-5">
                <div class="table-card" style="height:100%;">
                    <div style="padding:12px 16px; border-bottom:1px solid var(--border); font-size:12px; font-weight:700; color:var(--navy);">
                        <i class="bi bi-trophy-fill me-2" style="color:var(--gold);"></i> Most Rented Items (All Time)
                    </div>
                    <table class="table table-sm table-striped mb-0" style="font-size:12px;">
                        <thead><tr><th>#</th><th>Item</th><th class="text-center">Times Rented</th></tr></thead>
                        <tbody>
                            @forelse($mostRented as $i => $p)
                                <tr>
                                    <td style="font-weight:700; color:{{ $i < 3 ? 'var(--gold)' : 'var(--text-muted)' }};">{{ $i + 1 }}</td>
                                    <td>
                                        <span style="font-family:monospace; font-size:11px; background:#f0f0f0; padding:1px 5px; border-radius:3px;">{{ $p->code }}</span>
                                        <div style="font-size:10px; color:var(--text-muted);">{{ \Str::limit($p->name, 28) }}</div>
                                    </td>
                                    <td class="text-center" style="font-weight:800; color:#805ad5;">{{ $p->times_rented }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-3">No rental data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Never Rented --}}
        <div class="table-card">
            <div style="padding:12px 16px; border-bottom:1px solid var(--border); font-size:12px; font-weight:700; color:#c53030;">
                <i class="bi bi-exclamation-triangle me-2"></i> Never Rented (Active Rental Items)
                <span style="background:#fff5f5; color:#c53030; padding:1px 8px; border-radius:10px; font-size:11px; margin-left:6px;">{{ count($neverRented) }}</span>
            </div>
            <table class="table table-sm table-striped mb-0" style="font-size:12px;">
                <thead><tr><th>Code</th><th>Name</th><th>Category</th><th>Color/Size</th><th class="text-end">Rental Price</th></tr></thead>
                <tbody>
                    @forelse($neverRented as $p)
                        <tr>
                            <td><span style="font-family:monospace; font-weight:700; background:#fff5f5; padding:2px 7px; border-radius:4px; color:#c53030;">{{ $p->code }}</span></td>
                            <td style="font-weight:600;">{{ $p->name }}</td>
                            <td style="font-size:11px; color:var(--text-muted);">{{ $p->category?->name }}</td>
                            <td style="font-size:11px; color:var(--text-muted);">{{ implode(' / ', array_filter([$p->color, $p->size])) ?: '—' }}</td>
                            <td class="text-end" style="font-weight:700; color:#553c9a;">Rs. {{ number_format($p->rental_price, 0) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3"><i class="bi bi-check-circle text-success me-1"></i> All active rental items have been rented at least once</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    {{-- ── ABANDONED VIEW ── --}}
    @elseif($activeView === 'abandoned')

        <div class="table-card">
            <div style="padding:12px 16px; border-bottom:1px solid var(--border);">
                <div style="font-size:12px; font-weight:700; color:#c53030; margin-bottom:4px;">
                    <i class="bi bi-x-circle me-2"></i> Abandoned / Written-off Items
                </div>
                <div style="font-size:11px; color:var(--text-muted);">
                    Total write-off value: <strong style="color:#c53030;">Rs. {{ number_format($summary['total_abandoned_value'], 0) }}</strong>
                </div>
            </div>
            <table class="table table-striped table-hover mb-0" id="abandonedTable" style="font-size:12px;">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Reason</th>
                        <th>Date</th>
                        <th class="text-end">Write-off Value</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td><span style="font-family:monospace; font-weight:700; background:#fff5f5; padding:2px 7px; border-radius:4px; color:#c53030;">{{ $product->code }}</span></td>
                            <td style="font-weight:600;">{{ $product->name }}</td>
                            <td style="font-size:11px; color:var(--text-muted);">{{ $product->category?->name }}</td>
                            <td style="font-size:11px; color:var(--text-muted);">{{ $product->abandoned_note ?: '—' }}</td>
                            <td style="font-size:11px;">{{ $product->abandoned_date ? \Carbon\Carbon::parse($product->abandoned_date)->format('d/m/Y') : '—' }}</td>
                            <td class="text-end" style="font-weight:700; color:#c53030;">Rs. {{ number_format($product->abandoned_price, 0) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4" style="color:var(--text-muted);">
                                <i class="bi bi-check-circle text-success" style="font-size:28px; display:block; margin-bottom:6px;"></i>
                                No abandoned items
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($products->hasPages())
                <div style="padding:12px 16px; border-top:1px solid var(--border);">
                    {{ $products->links('vendor.pagination.simple-bootstrap-5') }}
                </div>
            @endif
        </div>

    @endif

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
    const typeData = @json($typeBreakdown);

    function buildChart() {
        const canvas = document.getElementById('typeChart');
        if (!canvas) return;
        if (canvas._chartInstance) canvas._chartInstance.destroy();

        const colors = ['#3182ce','#38a169','#805ad5','#d69e2e','#319795'];
        canvas._chartInstance = new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: typeData.map(d => d.type.charAt(0).toUpperCase() + d.type.slice(1)),
                datasets: [{
                    data: typeData.map(d => d.count),
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom', labels: { font: { size: 10 }, boxWidth: 12 } }
                }
            }
        });
    }

    document.addEventListener('livewire:initialized', buildChart);
    document.addEventListener('livewire:updated', buildChart);

    function exportPDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('l', 'mm', 'a4');
        doc.setFontSize(14);
        doc.text('Stock Report', 14, 15);

        const tableId = document.getElementById('abandonedTable') ? 'abandonedTable' : 'stockTable';
        const rows = [];
        document.querySelectorAll(`#${tableId} tbody tr`).forEach(tr => {
            const cells = tr.querySelectorAll('td');
            if (cells.length > 1) rows.push(Array.from(cells).map(c => c.innerText.trim()));
        });

        const heads = tableId === 'abandonedTable'
            ? [['Code','Name','Category','Reason','Date','Write-off Value']]
            : [['Code','Name','Category','Type','Color/Size','Rental Price','Sale Price','Stock','Status']];

        doc.autoTable({
            head: heads, body: rows, startY: 22,
            styles: { fontSize: 8 },
            headStyles: { fillColor: [26, 54, 93] },
            alternateRowStyles: { fillColor: [245, 247, 250] },
        });
        doc.save('stock-report-{{ now()->format("Y-m-d") }}.pdf');
    }

    function exportExcel() {
        const wb = XLSX.utils.book_new();
        const tableId = document.getElementById('abandonedTable') ? 'abandonedTable' : 'stockTable';
        const rows = [];
        document.querySelectorAll(`#${tableId} thead tr`).forEach(tr => {
            rows.push(Array.from(tr.querySelectorAll('th')).map(c => c.innerText.trim()));
        });
        document.querySelectorAll(`#${tableId} tbody tr`).forEach(tr => {
            const cells = tr.querySelectorAll('td');
            if (cells.length > 1) rows.push(Array.from(cells).map(c => c.innerText.trim()));
        });
        const ws = XLSX.utils.aoa_to_sheet(rows);
        XLSX.utils.book_append_sheet(wb, ws, 'Stock');
        XLSX.writeFile(wb, 'stock-report-{{ now()->format("Y-m-d") }}.xlsx');
    }
</script>
@endpush