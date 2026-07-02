<div>
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">Rental Report</div>
            <div class="page-subtitle">
                {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} —
                {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
            </div>
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

    {{-- Quick Filters --}}
    <div class="d-flex gap-2 flex-wrap mb-3">
        @foreach([
            ['key' => 'today',      'label' => 'Today',      'icon' => 'bi-sun'],
            ['key' => 'yesterday',  'label' => 'Yesterday',  'icon' => 'bi-clock-history'],
            ['key' => 'this_week',  'label' => 'This Week',  'icon' => 'bi-calendar-week'],
            ['key' => 'this_month', 'label' => 'This Month', 'icon' => 'bi-calendar-month'],
            ['key' => 'last_month', 'label' => 'Last Month', 'icon' => 'bi-calendar2-minus'],
            ['key' => 'this_year',  'label' => 'This Year',  'icon' => 'bi-calendar-range'],
            ['key' => 'custom',     'label' => 'Custom',     'icon' => 'bi-sliders'],
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

    {{-- Status Filter Pills --}}
    <div class="d-flex gap-2 flex-wrap mb-3">
        @foreach([
            ['val' => '',                   'label' => 'All',          'color' => '#718096'],
            ['val' => 'booked',             'label' => 'Booked',       'color' => '#2c5282'],
            ['val' => 'ready',              'label' => 'Ready',        'color' => '#b7791f'],
            ['val' => 'picked_up',          'label' => 'Picked Up',    'color' => '#553c9a'],
            ['val' => 'partially_picked_up','label' => 'Partial',      'color' => '#c05621'],
            ['val' => 'returned',           'label' => 'Returned',     'color' => '#276749'],
            ['val' => 'cancelled',          'label' => 'Cancelled',    'color' => '#718096'],
        ] as $s)
            @php $isActive = $statusFilter === $s['val']; @endphp
            <div wire:click="$set('statusFilter', '{{ $s['val'] }}')"
                style="background:{{ $isActive ? $s['color'] : '#fff' }};
                       border:1.5px solid {{ $s['color'] }};
                       border-radius:20px; padding:3px 12px; font-size:11px; cursor:pointer;
                       color:{{ $isActive ? '#fff' : $s['color'] }}; font-weight:600;">
                {{ $s['label'] }}
            </div>
        @endforeach
    </div>

    {{-- Summary Cards --}}
    <div style="display:grid; grid-template-columns:repeat(6,1fr); gap:10px; margin-bottom:20px;">
        @foreach([
            ['label' => 'Total Bookings', 'value' => $summary['total_bookings'],  'icon' => 'bi-journal-bookmark', 'color' => '#3182ce', 'bg' => '#ebf8ff', 'format' => 'number'],
            ['label' => 'Total Value',    'value' => $summary['total_value'],     'icon' => 'bi-cash-stack',       'color' => '#38a169', 'bg' => '#f0fff4', 'format' => 'money'],
            ['label' => 'Collected',      'value' => $summary['total_collected'], 'icon' => 'bi-check-circle',     'color' => '#805ad5', 'bg' => '#faf5ff', 'format' => 'money'],
            ['label' => 'Pending Due',    'value' => $summary['total_due'],       'icon' => 'bi-hourglass-split',  'color' => '#c53030', 'bg' => '#fff5f5', 'format' => 'money'],
            ['label' => 'Items Rented',   'value' => $summary['total_items'],     'icon' => 'bi-box-seam',         'color' => '#d69e2e', 'bg' => '#fffff0', 'format' => 'number'],
            ['label' => 'Late Returns',   'value' => $summary['late_returns'],    'icon' => 'bi-alarm',            'color' => '#c05621', 'bg' => '#fffaf0', 'format' => 'number'],
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

        {{-- Status Breakdown --}}
        <div class="col-3">
            <div class="table-card" style="height:100%;">
                <div style="padding:12px 16px; border-bottom:1px solid var(--border); font-size:12px; font-weight:700; color:var(--navy);">
                    <i class="bi bi-pie-chart me-2" style="color:#805ad5;"></i> Status Breakdown
                </div>
                <table class="table table-sm table-striped mb-0" style="font-size:12px;">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th class="text-center">Count</th>
                            <th class="text-end">Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($statusBreakdown as $row)
                            <tr>
                                <td>
                                    <span class="rental-status-badge {{ $row->status }}" style="font-size:9px; padding:1px 6px;">
                                        {{ ucfirst(str_replace('_', ' ', $row->status)) }}
                                    </span>
                                </td>
                                <td class="text-center" style="font-weight:700;">{{ $row->count }}</td>
                                <td class="text-end" style="font-size:11px; color:var(--navy);">Rs. {{ number_format($row->value, 0) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">No data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Top Rented Items --}}
        <div class="col-4">
            <div class="table-card" style="height:100%;">
                <div style="padding:12px 16px; border-bottom:1px solid var(--border); font-size:12px; font-weight:700; color:var(--navy);">
                    <i class="bi bi-trophy-fill me-2" style="color:var(--gold);"></i> Most Rented Items
                </div>
                <table class="table table-sm table-striped mb-0" style="font-size:12px;">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th class="text-center">Times</th>
                            <th class="text-end">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topItems as $item)
                            <tr>
                                <td>
                                    <span style="font-family:monospace; font-size:11px; background:#f0f0f0; padding:1px 5px; border-radius:3px;">{{ $item->product_code }}</span>
                                    <div style="font-size:10px; color:var(--text-muted);">{{ \Str::limit($item->product_name, 22) }}</div>
                                </td>
                                <td class="text-center" style="font-weight:700; color:#805ad5;">{{ $item->times_rented }}</td>
                                <td class="text-end" style="font-weight:700; color:#38a169;">Rs. {{ number_format($item->total_revenue, 0) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">No data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Trend Chart --}}
        <div class="col-5">
            <div class="table-card" style="height:100%;">
                <div style="padding:12px 16px; border-bottom:1px solid var(--border); font-size:12px; font-weight:700; color:var(--navy);">
                    <i class="bi bi-graph-up me-2" style="color:#3182ce;"></i> Booking Trend
                </div>
                <div style="padding:12px;">
                    <canvas id="rentalTrendChart" height="140"></canvas>
                </div>
            </div>
        </div>

    </div>

    {{-- Rentals Table --}}
    <div class="table-card">
        <div class="table-card-header">
            <div style="width:260px;">
                <input type="text" wire:model.live.debounce.400ms="search"
                    class="form-control form-control-sm" placeholder="Search name, bill ref, phone, CNIC...">
            </div>
            <div style="font-size:12px; color:var(--text-muted);">
                {{ $rentals->total() }} rental(s) found
            </div>
        </div>

        <table class="table table-striped table-hover mb-0" id="rentalTable" style="font-size:12px;">
            <thead>
                <tr>
                    <th>Bill Ref</th>
                    <th>Booking Date</th>
                    <th>Customer</th>
                    <th>Pickup</th>
                    <th>Return</th>
                    <th class="text-center">Items</th>
                    <th class="text-end">Amount</th>
                    <th class="text-end">Paid</th>
                    <th class="text-end">Due</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rentals as $rental)
                    @php
                        $paid    = (float)($rental->payments_sum_amount ?? 0);
                        $due     = max(0, $rental->total_amount - $paid);
                    @endphp
                    <tr>
                        <td>
                            <a href="{{ route('rentals.show', $rental->id) }}"
                                style="font-family:monospace; font-weight:700; color:var(--navy); text-decoration:none;">
                                {{ $rental->bill_ref ?? '#' . $rental->id }}
                            </a>
                        </td>
                        <td style="white-space:nowrap;">{{ \Carbon\Carbon::parse($rental->booking_date)->format('d/m/Y') }}</td>
                        <td>
                            <div style="font-weight:600;">{{ $rental->customer_name }}</div>
                            <div style="font-size:10px; color:var(--text-muted);">{{ $rental->customer_phone1 }}</div>
                        </td>
                        <td style="font-size:11px; white-space:nowrap;">
                            {{ $rental->pickup_date ? \Carbon\Carbon::parse($rental->pickup_date)->format('d/m/Y') : '—' }}
                        </td>
                        <td style="font-size:11px; white-space:nowrap;">
                            {{ $rental->return_date ? \Carbon\Carbon::parse($rental->return_date)->format('d/m/Y') : '—' }}
                        </td>
                        <td class="text-center">
                            <div style="display:flex; flex-wrap:wrap; gap:2px; justify-content:center;">
                                @foreach($rental->items->take(3) as $item)
                                    <span style="font-size:10px; background:#f0f0f0; padding:1px 5px; border-radius:3px; font-family:monospace;">{{ $item->product_code }}</span>
                                @endforeach
                                @if($rental->items->count() > 3)
                                    <span style="font-size:10px; color:var(--text-muted);">+{{ $rental->items->count() - 3 }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-end" style="font-weight:700;">Rs. {{ number_format($rental->total_amount, 0) }}</td>
                        <td class="text-end" style="color:#38a169; font-weight:600;">Rs. {{ number_format($paid, 0) }}</td>
                        <td class="text-end" style="color:{{ $due > 0 ? '#c53030' : '#38a169' }}; font-weight:700;">
                            {{ $due > 0 ? 'Rs. ' . number_format($due, 0) : 'Paid' }}
                        </td>
                        <td>
                            <span class="rental-status-badge {{ $rental->status }}" style="font-size:9px; padding:1px 6px;">
                                {{ ucfirst(str_replace('_', ' ', $rental->status)) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-4" style="color:var(--text-muted); font-size:13px;">
                            <i class="bi bi-inbox" style="font-size:32px; display:block; margin-bottom:8px;"></i>
                            No rentals in this period
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($rentals->hasPages())
            <div style="padding:12px 16px; border-top:1px solid var(--border);">
                {{ $rentals->links('vendor.pagination.simple-bootstrap-5') }}
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
    const rentalTrend = @json($dailyTrend);

    function buildChart() {
        const canvas = document.getElementById('rentalTrendChart');
        if (!canvas) return;
        if (canvas._chartInstance) canvas._chartInstance.destroy();
        canvas._chartInstance = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: rentalTrend.map(d => {
                    const dt = new Date(d.date);
                    return dt.toLocaleDateString('en-PK', { day: '2-digit', month: 'short' });
                }),
                datasets: [
                    {
                        label: 'Bookings',
                        data: rentalTrend.map(d => d.count),
                        backgroundColor: 'rgba(49,130,206,0.7)',
                        borderColor: '#3182ce',
                        borderWidth: 1,
                        borderRadius: 4,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Revenue',
                        data: rentalTrend.map(d => d.revenue),
                        type: 'line',
                        borderColor: '#38a169',
                        backgroundColor: 'rgba(56,161,105,0.1)',
                        borderWidth: 2,
                        pointRadius: 3,
                        fill: false,
                        tension: 0.4,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: true, position: 'top', labels: { font: { size: 10 }, boxWidth: 12 } }
                },
                scales: {
                    y:  { position: 'left',  ticks: { font: { size: 9 }, stepSize: 1 }, title: { display: true, text: 'Bookings', font: { size: 9 } } },
                    y1: { position: 'right', ticks: { callback: v => 'Rs.' + (v/1000).toFixed(0) + 'k', font: { size: 9 } }, grid: { drawOnChartArea: false } },
                    x:  { ticks: { font: { size: 9 }, maxTicksLimit: 10 } }
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
        doc.text('Rental Report', 14, 15);
        doc.setFontSize(9);
        doc.text('Period: {{ \Carbon\Carbon::parse($dateFrom)->format("d M Y") }} – {{ \Carbon\Carbon::parse($dateTo)->format("d M Y") }}', 14, 22);
        const rows = [];
        document.querySelectorAll('#rentalTable tbody tr').forEach(tr => {
            const cells = tr.querySelectorAll('td');
            if (cells.length > 1) rows.push(Array.from(cells).map(c => c.innerText.trim()));
        });
        doc.autoTable({
            head: [['Bill Ref','Booking','Customer','Pickup','Return','Items','Amount','Paid','Due','Status']],
            body: rows,
            startY: 28,
            styles: { fontSize: 7 },
            headStyles: { fillColor: [26, 54, 93] },
            alternateRowStyles: { fillColor: [245, 247, 250] },
        });
        doc.save('rental-report-{{ now()->format("Y-m-d") }}.pdf');
    }

    function exportExcel() {
        const wb = XLSX.utils.book_new();
        const rows = [['Bill Ref','Booking','Customer','Pickup','Return','Items','Amount','Paid','Due','Status']];
        document.querySelectorAll('#rentalTable tbody tr').forEach(tr => {
            const cells = tr.querySelectorAll('td');
            if (cells.length > 1) rows.push(Array.from(cells).map(c => c.innerText.trim()));
        });
        const ws = XLSX.utils.aoa_to_sheet(rows);
        XLSX.utils.book_append_sheet(wb, ws, 'Rentals');
        XLSX.writeFile(wb, 'rental-report-{{ now()->format("Y-m-d") }}.xlsx');
    }
</script>
@endpush