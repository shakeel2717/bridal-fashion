<div>
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">Sales Report</div>
            <div class="page-subtitle">{{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}</div>
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

    {{-- Quick Filter Widgets --}}
    <div class="d-flex gap-2 flex-wrap mb-3">
        @foreach([
            ['key' => 'today',      'label' => 'Today',       'icon' => 'bi-sun'],
            ['key' => 'yesterday',  'label' => 'Yesterday',   'icon' => 'bi-clock-history'],
            ['key' => 'this_week',  'label' => 'This Week',   'icon' => 'bi-calendar-week'],
            ['key' => 'this_month', 'label' => 'This Month',  'icon' => 'bi-calendar-month'],
            ['key' => 'last_month', 'label' => 'Last Month',  'icon' => 'bi-calendar2-minus'],
            ['key' => 'this_year',  'label' => 'This Year',   'icon' => 'bi-calendar-range'],
            ['key' => 'custom',     'label' => 'Custom',      'icon' => 'bi-sliders'],
        ] as $f)
            @php $isActive = $activeFilter === $f['key']; @endphp
            <div wire:click="setFilter('{{ $f['key'] }}')"
                style="background:{{ $isActive ? 'var(--navy)' : '#fff' }};
                       border:1.5px solid {{ $isActive ? 'var(--navy)' : 'var(--border)' }};
                       border-radius:9px; padding:7px 14px; font-size:12px; cursor:pointer;
                       color:{{ $isActive ? '#fff' : 'var(--text-muted)' }};
                       font-weight:{{ $isActive ? '700' : '500' }};
                       display:inline-flex; align-items:center; gap:6px;
                       {{ $isActive ? 'box-shadow:0 2px 8px rgba(0,0,0,0.15);' : '' }}">
                <i class="bi {{ $f['icon'] }}" style="font-size:13px;"></i>
                {{ $f['label'] }}
            </div>
        @endforeach
    </div>

    {{-- Custom Date Range --}}
    @if($activeFilter === 'custom')
        <div class="d-flex gap-2 align-items-center mb-3">
            <input type="date" wire:model.live="dateFrom" class="form-control form-control-sm" style="width:160px;">
            <span style="font-size:12px; color:var(--text-muted);">to</span>
            <input type="date" wire:model.live="dateTo" class="form-control form-control-sm" style="width:160px;">
        </div>
    @endif

    {{-- Summary Cards --}}
    <div style="display:grid; grid-template-columns:repeat(6,1fr); gap:10px; margin-bottom:20px;">
        @foreach([
            ['label' => 'Total Bills',    'value' => $summary['total_bills'],                                   'icon' => 'bi-receipt',          'color' => '#3182ce', 'bg' => '#ebf8ff', 'format' => 'number'],
            ['label' => 'Revenue',        'value' => $summary['total_revenue'],                                  'icon' => 'bi-cash-stack',       'color' => '#38a169', 'bg' => '#f0fff4', 'format' => 'money'],
            ['label' => 'Discount Given', 'value' => $summary['total_discount'],                                 'icon' => 'bi-tag',              'color' => '#d69e2e', 'bg' => '#fffff0', 'format' => 'money'],
            ['label' => 'Collected',      'value' => $summary['total_paid'],                                     'icon' => 'bi-check-circle',     'color' => '#805ad5', 'bg' => '#faf5ff', 'format' => 'money'],
            ['label' => 'Pending',        'value' => $summary['total_due'],                                      'icon' => 'bi-hourglass-split',  'color' => '#c53030', 'bg' => '#fff5f5', 'format' => 'money'],
            ['label' => 'Avg Bill',       'value' => $summary['avg_bill'],                                       'icon' => 'bi-calculator',       'color' => '#319795', 'bg' => '#e6fffa', 'format' => 'money'],
        ] as $card)
            <div style="background:{{ $card['bg'] }}; border:1.5px solid {{ $card['color'] }}22; border-radius:10px; padding:12px 14px;">
                <div style="display:flex; align-items:center; gap:6px; margin-bottom:6px;">
                    <i class="bi {{ $card['icon'] }}" style="font-size:16px; color:{{ $card['color'] }};"></i>
                    <span style="font-size:10px; color:{{ $card['color'] }}; font-weight:600; text-transform:uppercase;">{{ $card['label'] }}</span>
                </div>
                <div style="font-size:{{ $card['format'] === 'money' ? '14px' : '22px' }}; font-weight:800; color:{{ $card['color'] }}; line-height:1;">
                    {{ $card['format'] === 'money' ? 'Rs. ' . number_format($card['value'], 0) : number_format($card['value']) }}
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3 mb-3">

        {{-- Top Products --}}
        <div class="col-5">
            <div class="table-card" style="height:100%;">
                <div style="padding:12px 16px; border-bottom:1px solid var(--border); font-size:12px; font-weight:700; color:var(--navy);">
                    <i class="bi bi-trophy-fill me-2" style="color:var(--gold);"></i> Top Products (Period)
                </div>
                <table class="table table-sm table-striped mb-0" style="font-size:12px;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topProducts as $i => $p)
                            <tr>
                                <td style="font-weight:700; color:var(--text-muted);">{{ $i + 1 }}</td>
                                <td>
                                    <span style="font-family:monospace; font-size:11px; background:#f0f0f0; padding:1px 5px; border-radius:3px;">{{ $p->product_code }}</span>
                                    <div style="font-size:11px; color:var(--text-muted);">{{ $p->product_name }}</div>
                                </td>
                                <td class="text-center">{{ $p->total_qty }}</td>
                                <td class="text-end" style="font-weight:700; color:#38a169;">Rs. {{ number_format($p->total_revenue, 0) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">No data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Daily Trend --}}
        <div class="col-7">
            <div class="table-card" style="height:100%;">
                <div style="padding:12px 16px; border-bottom:1px solid var(--border); font-size:12px; font-weight:700; color:var(--navy);">
                    <i class="bi bi-graph-up me-2" style="color:#3182ce;"></i> Daily Trend
                </div>
                <div style="padding:12px 16px;">
                    <canvas id="salesTrendChart" height="120"></canvas>
                </div>
            </div>
        </div>

    </div>

    {{-- Search + Table --}}
    <div class="table-card">
        <div class="table-card-header">
            <div style="width:260px;">
                <input type="text" wire:model.live.debounce.400ms="search"
                    class="form-control form-control-sm" placeholder="Search customer, bill ref, phone...">
            </div>
            <div style="font-size:12px; color:var(--text-muted);">
                {{ $sales->total() }} bill(s) found
            </div>
        </div>

        <table class="table table-striped table-hover mb-0" id="salesTable" style="font-size:12px;">
            <thead>
                <tr>
                    <th>Bill Ref</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th class="text-end">Amount</th>
                    <th class="text-end">Discount</th>
                    <th class="text-end">Paid</th>
                    <th class="text-end">Due</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                    <tr>
                        <td>
                            <a href="{{ route('sales.show', $sale->id) }}"
                                style="font-family:monospace; font-weight:700; color:var(--navy); text-decoration:none;">
                                {{ $sale->bill_ref ?? '#' . $sale->id }}
                            </a>
                        </td>
                        <td style="white-space:nowrap;">{{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') }}</td>
                        <td>
                            <div style="font-weight:600;">{{ $sale->customer_name }}</div>
                            <div style="font-size:10px; color:var(--text-muted);">{{ $sale->customer_phone1 }}</div>
                        </td>
                        <td>
                            <span style="font-size:11px; color:var(--text-muted);">{{ $sale->items->count() }} item(s)</span>
                            <div style="display:flex; flex-wrap:wrap; gap:2px; margin-top:2px;">
                                @foreach($sale->items->take(3) as $item)
                                    <span style="font-size:10px; background:#f0f0f0; padding:1px 5px; border-radius:3px; font-family:monospace;">{{ $item->product_code }}</span>
                                @endforeach
                                @if($sale->items->count() > 3)
                                    <span style="font-size:10px; color:var(--text-muted);">+{{ $sale->items->count() - 3 }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-end" style="font-weight:700;">Rs. {{ number_format($sale->total_amount, 0) }}</td>
                        <td class="text-end" style="color:#d69e2e;">
                            @if($sale->discount > 0) Rs. {{ number_format($sale->discount, 0) }} @else — @endif
                        </td>
                        <td class="text-end" style="color:#38a169; font-weight:600;">Rs. {{ number_format($sale->advance_paid, 0) }}</td>
                        <td class="text-end" style="color:{{ $sale->remaining_balance > 0 ? '#c53030' : '#38a169' }}; font-weight:700;">
                            @if($sale->remaining_balance > 0) Rs. {{ number_format($sale->remaining_balance, 0) }} @else Paid @endif
                        </td>
                        <td>
                            <span class="badge" style="font-size:10px; background:{{ $sale->status === 'completed' ? '#f0fff4' : '#fff5f5' }}; color:{{ $sale->status === 'completed' ? '#276749' : '#c53030' }}; border:1px solid {{ $sale->status === 'completed' ? '#9ae6b4' : '#fc8181' }};">
                                {{ ucfirst($sale->status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-4" style="color:var(--text-muted); font-size:13px;">
                            <i class="bi bi-inbox" style="font-size:32px; display:block; margin-bottom:8px;"></i>
                            No sales in this period
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($sales->hasPages())
            <div style="padding:12px 16px; border-top:1px solid var(--border);">
                {{ $sales->links('vendor.pagination.simple-bootstrap-5') }}
            </div>
        @endif
    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
    const trendData = @json($dailyTrend);

    function buildChart() {
        const canvas = document.getElementById('salesTrendChart');
        if (!canvas) return;
        if (canvas._chartInstance) canvas._chartInstance.destroy();

        canvas._chartInstance = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: trendData.map(d => {
                    const dt = new Date(d.date);
                    return dt.toLocaleDateString('en-PK', { day: '2-digit', month: 'short' });
                }),
                datasets: [{
                    label: 'Revenue (Rs.)',
                    data: trendData.map(d => d.revenue),
                    backgroundColor: 'rgba(49,130,206,0.7)',
                    borderColor: '#3182ce',
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { ticks: { callback: v => 'Rs.' + (v/1000).toFixed(0) + 'k', font: { size: 10 } } },
                    x: { ticks: { font: { size: 10 } } }
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
        doc.text('Sales Report', 14, 15);
        doc.setFontSize(9);
        doc.text('Period: {{ \Carbon\Carbon::parse($dateFrom)->format("d M Y") }} – {{ \Carbon\Carbon::parse($dateTo)->format("d M Y") }}', 14, 22);

        const rows = [];
        document.querySelectorAll('#salesTable tbody tr').forEach(tr => {
            const cells = tr.querySelectorAll('td');
            if (cells.length > 1) {
                rows.push([
                    cells[0].innerText.trim(),
                    cells[1].innerText.trim(),
                    cells[2].innerText.trim(),
                    cells[3].innerText.trim(),
                    cells[4].innerText.trim(),
                    cells[5].innerText.trim(),
                    cells[6].innerText.trim(),
                    cells[7].innerText.trim(),
                    cells[8].innerText.trim(),
                ]);
            }
        });

        doc.autoTable({
            head: [['Bill Ref', 'Date', 'Customer', 'Items', 'Amount', 'Discount', 'Paid', 'Due', 'Status']],
            body: rows,
            startY: 28,
            styles: { fontSize: 8 },
            headStyles: { fillColor: [26, 54, 93] },
            alternateRowStyles: { fillColor: [245, 247, 250] },
        });

        doc.save('sales-report-{{ now()->format("Y-m-d") }}.pdf');
    }

    function exportExcel() {
        const wb = XLSX.utils.book_new();
        const rows = [['Bill Ref', 'Date', 'Customer', 'Amount', 'Discount', 'Paid', 'Due', 'Status']];

        document.querySelectorAll('#salesTable tbody tr').forEach(tr => {
            const cells = tr.querySelectorAll('td');
            if (cells.length > 1) {
                rows.push([
                    cells[0].innerText.trim(),
                    cells[1].innerText.trim(),
                    cells[2].innerText.trim(),
                    cells[4].innerText.trim(),
                    cells[5].innerText.trim(),
                    cells[6].innerText.trim(),
                    cells[7].innerText.trim(),
                    cells[8].innerText.trim(),
                ]);
            }
        });

        const ws = XLSX.utils.aoa_to_sheet(rows);
        XLSX.utils.book_append_sheet(wb, ws, 'Sales');
        XLSX.writeFile(wb, 'sales-report-{{ now()->format("Y-m-d") }}.xlsx');
    }
</script>
@endpush