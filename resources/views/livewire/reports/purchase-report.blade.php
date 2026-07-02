<div>
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">Purchase Report</div>
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
        @foreach ([['key' => 'today', 'label' => 'Today', 'icon' => 'bi-sun'], ['key' => 'yesterday', 'label' => 'Yesterday', 'icon' => 'bi-clock-history'], ['key' => 'this_week', 'label' => 'This Week', 'icon' => 'bi-calendar-week'], ['key' => 'this_month', 'label' => 'This Month', 'icon' => 'bi-calendar-month'], ['key' => 'last_month', 'label' => 'Last Month', 'icon' => 'bi-calendar2-minus'], ['key' => 'this_year', 'label' => 'This Year', 'icon' => 'bi-calendar-range'], ['key' => 'custom', 'label' => 'Custom', 'icon' => 'bi-sliders']] as $f)
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
    @if ($activeFilter === 'custom')
        <div class="d-flex gap-2 align-items-center mb-3">
            <input type="date" wire:model.live="dateFrom" class="form-control form-control-sm" style="width:160px;">
            <span style="font-size:12px; color:var(--text-muted);">to</span>
            <input type="date" wire:model.live="dateTo" class="form-control form-control-sm" style="width:160px;">
        </div>
    @endif

    {{-- Status Filter Pills --}}
    <div class="d-flex gap-2 flex-wrap mb-3">
        @foreach ([['val' => '', 'label' => 'All', 'color' => '#718096'], ['val' => 'draft', 'label' => 'Draft', 'color' => '#718096'], ['val' => 'ordered', 'label' => 'Ordered', 'color' => '#3182ce'], ['val' => 'partial', 'label' => 'Partial', 'color' => '#d69e2e'], ['val' => 'received', 'label' => 'Received', 'color' => '#38a169'], ['val' => 'cancelled', 'label' => 'Cancelled', 'color' => '#c53030']] as $s)
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
        @foreach ([['label' => 'Total Orders', 'value' => $summary['total_orders'], 'icon' => 'bi-bag-check', 'color' => '#3182ce', 'bg' => '#ebf8ff', 'format' => 'number'], ['label' => 'Total Value', 'value' => $summary['total_value'], 'icon' => 'bi-cash-stack', 'color' => '#38a169', 'bg' => '#f0fff4', 'format' => 'money'], ['label' => 'Total Paid', 'value' => $summary['total_paid'], 'icon' => 'bi-check-circle', 'color' => '#805ad5', 'bg' => '#faf5ff', 'format' => 'money'], ['label' => 'Balance Due', 'value' => $summary['total_due'], 'icon' => 'bi-hourglass-split', 'color' => '#c53030', 'bg' => '#fff5f5', 'format' => 'money'], ['label' => 'Items Ordered', 'value' => $summary['total_items'], 'icon' => 'bi-boxes', 'color' => '#d69e2e', 'bg' => '#fffff0', 'format' => 'number'], ['label' => 'Pending Orders', 'value' => $summary['pending_orders'], 'icon' => 'bi-clock', 'color' => '#c05621', 'bg' => '#fffaf0', 'format' => 'number']] as $card)
            <div
                style="background:{{ $card['bg'] }}; border:1.5px solid {{ $card['color'] }}22; border-radius:10px; padding:12px 14px;">
                <div style="display:flex; align-items:center; gap:6px; margin-bottom:6px;">
                    <i class="bi {{ $card['icon'] }}" style="font-size:16px; color:{{ $card['color'] }};"></i>
                    <span
                        style="font-size:10px; color:{{ $card['color'] }}; font-weight:600; text-transform:uppercase;">{{ $card['label'] }}</span>
                </div>
                <div
                    style="font-size:{{ $card['format'] === 'money' ? '14px' : '22px' }}; font-weight:800; color:{{ $card['color'] }}; line-height:1;">
                    {{ $card['format'] === 'money' ? 'Rs. ' . number_format($card['value'], 0) : number_format($card['value']) }}
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3 mb-3">

        {{-- Top Vendors --}}
        <div class="col-5">
            <div class="table-card" style="height:100%;">
                <div
                    style="padding:12px 16px; border-bottom:1px solid var(--border); font-size:12px; font-weight:700; color:var(--navy);">
                    <i class="bi bi-shop-window me-2" style="color:var(--gold);"></i> Top Vendors (Period)
                </div>
                <table class="table table-sm table-striped mb-0" style="font-size:12px;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Vendor</th>
                            <th class="text-center">Orders</th>
                            <th class="text-end">Spend</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topVendors as $i => $v)
                            <tr>
                                <td style="font-weight:700; color:var(--text-muted);">{{ $i + 1 }}</td>
                                <td style="font-weight:600;">{{ $v->vendor?->name ?? '—' }}</td>
                                <td class="text-center">{{ $v->order_count }}</td>
                                <td class="text-end" style="font-weight:700; color:#c53030;">Rs.
                                    {{ number_format($v->total_spend, 0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">No data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Top Products Purchased --}}
        <div class="col-4">
            <div class="table-card" style="height:100%;">
                <div
                    style="padding:12px 16px; border-bottom:1px solid var(--border); font-size:12px; font-weight:700; color:var(--navy);">
                    <i class="bi bi-trophy-fill me-2" style="color:var(--gold);"></i> Top Products Purchased
                </div>
                <table class="table table-sm table-striped mb-0" style="font-size:12px;">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topProducts as $p)
                            <tr>
                                <td>
                                    <span
                                        style="font-family:monospace; font-size:11px; background:#f0f0f0; padding:1px 5px; border-radius:3px;">{{ $p->item_code }}</span>
                                    <div style="font-size:10px; color:var(--text-muted);">{{ $p->item_name }}</div>
                                </td>
                                <td class="text-center">{{ $p->total_qty }}</td>
                                <td class="text-end" style="font-weight:700; color:#c53030;">Rs.
                                    {{ number_format($p->total_cost, 0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">No data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Trend Chart --}}
        <div class="col-3">
            <div class="table-card" style="height:100%;">
                <div
                    style="padding:12px 16px; border-bottom:1px solid var(--border); font-size:12px; font-weight:700; color:var(--navy);">
                    <i class="bi bi-graph-up me-2" style="color:#c53030;"></i> Spend Trend
                </div>
                <div style="padding:12px;">
                    <canvas id="purchaseTrendChart"></canvas>
                </div>
            </div>
        </div>

    </div>

    {{-- Orders Table --}}
    <div class="table-card">
        <div class="table-card-header">
            <div style="width:260px;">
                <input type="text" wire:model.live.debounce.400ms="search" class="form-control form-control-sm"
                    placeholder="Search PO number, vendor...">
            </div>
            <div style="font-size:12px; color:var(--text-muted);">
                {{ $orders->total() }} order(s) found
            </div>
        </div>

        <table class="table table-striped table-hover mb-0" id="purchaseTable" style="font-size:12px;">
            <thead>
                <tr>
                    <th>PO #</th>
                    <th>Date</th>
                    <th>Vendor</th>
                    <th class="text-center">Items</th>
                    <th class="text-end">Total</th>
                    <th class="text-end">Paid</th>
                    <th class="text-end">Due</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $po)
                    @php
                        $statusColors = [
                            'draft' => ['bg' => '#f7f7f7', 'color' => '#718096'],
                            'ordered' => ['bg' => '#ebf8ff', 'color' => '#2b6cb0'],
                            'partial' => ['bg' => '#fffff0', 'color' => '#b7791f'],
                            'received' => ['bg' => '#f0fff4', 'color' => '#276749'],
                            'cancelled' => ['bg' => '#fff5f5', 'color' => '#c53030'],
                        ];
                        $sc = $statusColors[$po->status] ?? ['bg' => '#f7f7f7', 'color' => '#718096'];
                    @endphp
                    <tr>
                        <td>
                            <a href="{{ route('purchase-orders.show', $po->id) }}"
                                style="font-family:monospace; font-weight:700; color:var(--navy); text-decoration:none;">
                                {{ $po->po_number ?? '#' . $po->id }}
                            </a>
                        </td>
                        <td style="white-space:nowrap;">{{ \Carbon\Carbon::parse($po->order_date)->format('d/m/Y') }}
                        </td>
                        <td style="font-weight:600;">{{ $po->vendor?->name ?? '—' }}</td>
                        <td class="text-center">{{ $po->items->count() }}</td>
                        <td class="text-end" style="font-weight:700;">Rs. {{ number_format($po->total_amount, 0) }}
                        </td>
                        <td class="text-end" style="color:#38a169; font-weight:600;">Rs.
                            {{ number_format($po->amount_paid, 0) }}</td>
                        <td class="text-end"
                            style="color:{{ $po->remaining_balance > 0 ? '#c53030' : '#38a169' }}; font-weight:700;">
                            @if ($po->remaining_balance > 0)
                                Rs. {{ number_format($po->remaining_balance, 0) }}
                            @else
                                Paid
                            @endif
                        </td>
                        <td>
                            <span
                                style="font-size:10px; font-weight:600; padding:2px 8px; border-radius:20px;
                                background:{{ $sc['bg'] }}; color:{{ $sc['color'] }}; border:1px solid {{ $sc['color'] }}33;">
                                {{ ucfirst($po->status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4" style="color:var(--text-muted); font-size:13px;">
                            <i class="bi bi-inbox" style="font-size:32px; display:block; margin-bottom:8px;"></i>
                            No purchase orders in this period
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($orders->hasPages())
            <div style="padding:12px 16px; border-top:1px solid var(--border);">
                {{ $orders->links('vendor.pagination.simple-bootstrap-5') }}
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
        const purchaseTrend = @json($dailyTrend);

        function buildChart() {
            const canvas = document.getElementById('purchaseTrendChart');
            if (!canvas) return;
            if (canvas._chartInstance) canvas._chartInstance.destroy();
            canvas._chartInstance = new Chart(canvas, {
                type: 'line',
                data: {
                    labels: purchaseTrend.map(d => {
                        const dt = new Date(d.date);
                        return dt.toLocaleDateString('en-PK', {
                            day: '2-digit',
                            month: 'short'
                        });
                    }),
                    datasets: [{
                        label: 'Spend',
                        data: purchaseTrend.map(d => d.spend),
                        borderColor: '#c53030',
                        backgroundColor: 'rgba(197,48,48,0.1)',
                        borderWidth: 2,
                        pointRadius: 3,
                        fill: true,
                        tension: 0.4,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            ticks: {
                                callback: v => 'Rs.' + (v / 1000).toFixed(0) + 'k',
                                font: {
                                    size: 9
                                }
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    size: 9
                                },
                                maxTicksLimit: 6
                            }
                        }
                    }
                }
            });
        }

        document.addEventListener('livewire:initialized', buildChart);
        document.addEventListener('livewire:updated', buildChart);

        function exportPDF() {
            const {
                jsPDF
            } = window.jspdf;
            const doc = new jsPDF('l', 'mm', 'a4');
            doc.setFontSize(14);
            doc.text('Purchase Report', 14, 15);
            doc.setFontSize(9);
            doc.text(
                'Period: {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} – {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}',
                14, 22);

            const rows = [];
            document.querySelectorAll('#purchaseTable tbody tr').forEach(tr => {
                const cells = tr.querySelectorAll('td');
                if (cells.length > 1) {
                    rows.push(Array.from(cells).map(c => c.innerText.trim()));
                }
            });

            doc.autoTable({
                head: [
                    ['PO #', 'Date', 'Vendor', 'Items', 'Total', 'Paid', 'Due', 'Status']
                ],
                body: rows,
                startY: 28,
                styles: {
                    fontSize: 8
                },
                headStyles: {
                    fillColor: [26, 54, 93]
                },
                alternateRowStyles: {
                    fillColor: [245, 247, 250]
                },
            });

            doc.save('purchase-report-{{ now()->format('Y-m-d') }}.pdf');
        }

        function exportExcel() {
            const wb = XLSX.utils.book_new();
            const rows = [
                ['PO #', 'Date', 'Vendor', 'Items', 'Total', 'Paid', 'Due', 'Status']
            ];
            document.querySelectorAll('#purchaseTable tbody tr').forEach(tr => {
                const cells = tr.querySelectorAll('td');
                if (cells.length > 1) {
                    rows.push(Array.from(cells).map(c => c.innerText.trim()));
                }
            });
            const ws = XLSX.utils.aoa_to_sheet(rows);
            XLSX.utils.book_append_sheet(wb, ws, 'Purchases');
            XLSX.writeFile(wb, 'purchase-report-{{ now()->format('Y-m-d') }}.xlsx');
        }
    </script>
@endpush
