<?php

namespace App\Livewire\Reports;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Livewire\Component;
use Livewire\WithPagination;

class PurchaseReport extends Component
{
    use WithPagination;

    public string $activeFilter = 'this_month';
    public string $dateFrom     = '';
    public string $dateTo       = '';
    public string $activeReport = '';
    public string $search       = '';
    public string $statusFilter = '';
    public string $sortBy       = 'order_date';
    public string $sortDir      = 'desc';

    public ?array $modalPo = null;

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo   = now()->format('Y-m-d');
    }

    public function getReportMenu(): array
    {
        return [
            'orders' => [
                'label' => 'Purchase Orders',
                'icon'  => 'bi-bag-check',
                'items' => [
                    'all_orders'      => ['label' => 'All Orders',         'icon' => 'bi-list-ul'],
                    'daily_summary'   => ['label' => 'Day-wise Summary',   'icon' => 'bi-calendar3'],
                    'monthly_summary' => ['label' => 'Month-wise Summary', 'icon' => 'bi-calendar-month'],
                    'pending_orders'  => ['label' => 'Pending / Partial',  'icon' => 'bi-clock'],
                    'cancelled'       => ['label' => 'Cancelled Orders',   'icon' => 'bi-x-circle'],
                ],
            ],
            'hisaab' => [
                'label' => 'Hisaab / Payment',
                'icon'  => 'bi-cash-coin',
                'items' => [
                    'outstanding_dues' => ['label' => 'Baqi (Dues)',        'icon' => 'bi-exclamation-circle'],
                    'fully_paid'       => ['label' => 'Fully Paid',         'icon' => 'bi-check2-circle'],
                    'revenue_summary'  => ['label' => 'Spend Summary',      'icon' => 'bi-graph-up-arrow'],
                ],
            ],
            'vendors' => [
                'label' => 'Vendors',
                'icon'  => 'bi-shop',
                'items' => [
                    'vendor_wise' => ['label' => 'Vendor-wise Spend',   'icon' => 'bi-people'],
                    'top_vendors' => ['label' => 'Top Vendors',          'icon' => 'bi-trophy'],
                ],
            ],
            'items' => [
                'label' => 'Items / Maal',
                'icon'  => 'bi-box-seam',
                'items' => [
                    'top_items'   => ['label' => 'Most Purchased Items', 'icon' => 'bi-trophy'],
                    'item_detail' => ['label' => 'Full Item Detail',     'icon' => 'bi-card-list'],
                ],
            ],
        ];
    }

    public function setFilter(string $filter): void
    {
        $this->activeFilter = $filter;
        $this->resetPage();
        match ($filter) {
            'today'         => [$this->dateFrom, $this->dateTo] = [now()->format('Y-m-d'), now()->format('Y-m-d')],
            'yesterday'     => [$this->dateFrom, $this->dateTo] = [now()->subDay()->format('Y-m-d'), now()->subDay()->format('Y-m-d')],
            'this_week'     => [$this->dateFrom, $this->dateTo] = [now()->startOfWeek(0)->format('Y-m-d'), now()->endOfWeek(6)->format('Y-m-d')],
            'last_week'     => [$this->dateFrom, $this->dateTo] = [now()->subWeek()->startOfWeek(0)->format('Y-m-d'), now()->subWeek()->endOfWeek(6)->format('Y-m-d')],
            'this_month'    => [$this->dateFrom, $this->dateTo] = [now()->startOfMonth()->format('Y-m-d'), now()->format('Y-m-d')],
            'last_month'    => [$this->dateFrom, $this->dateTo] = [now()->subMonthNoOverflow()->startOfMonth()->format('Y-m-d'), now()->subMonthNoOverflow()->endOfMonth()->format('Y-m-d')],
            'last_3_months' => [$this->dateFrom, $this->dateTo] = [now()->subMonths(3)->startOfMonth()->format('Y-m-d'), now()->format('Y-m-d')],
            'last_6_months' => [$this->dateFrom, $this->dateTo] = [now()->subMonths(6)->startOfMonth()->format('Y-m-d'), now()->format('Y-m-d')],
            'this_quarter'  => [$this->dateFrom, $this->dateTo] = [now()->startOfQuarter()->format('Y-m-d'), now()->format('Y-m-d')],
            'last_quarter'  => [$this->dateFrom, $this->dateTo] = [now()->subQuarter()->startOfQuarter()->format('Y-m-d'), now()->subQuarter()->endOfQuarter()->format('Y-m-d')],
            'this_year'     => [$this->dateFrom, $this->dateTo] = [now()->startOfYear()->format('Y-m-d'), now()->format('Y-m-d')],
            'last_year'     => [$this->dateFrom, $this->dateTo] = [now()->subYear()->startOfYear()->format('Y-m-d'), now()->subYear()->endOfYear()->format('Y-m-d')],
            'all_time'      => [$this->dateFrom, $this->dateTo] = ['2000-01-01', now()->format('Y-m-d')],
            default         => null,
        };
    }

    public function updatedDateFrom(): void { $this->activeFilter = 'custom'; $this->resetPage(); }
    public function updatedDateTo(): void   { $this->activeFilter = 'custom'; $this->resetPage(); }
    public function updatedSearch(): void   { $this->resetPage(); }
    public function updatedStatusFilter(): void { $this->resetPage(); }

    public function selectReport(string $report): void
    {
        $this->activeReport  = $report;
        $this->search        = '';
        $this->statusFilter  = '';
        $this->sortBy        = 'order_date';
        $this->sortDir       = 'desc';
        $this->modalPo       = null;
        $this->resetPage();
    }

    public function sortByColumn(string $col): void
    {
        $this->sortDir = $this->sortBy === $col && $this->sortDir === 'desc' ? 'asc' : 'desc';
        $this->sortBy  = $col;
        $this->resetPage();
    }

    public function openModal(int $poId): void
    {
        $po = PurchaseOrder::with(['vendor', 'items', 'payments'])->findOrFail($poId);

        $this->modalPo = [
            'id'           => $po->id,
            'po_number'    => $po->po_number ?? '#' . $po->id,
            'order_date'   => $po->order_date,
            'vendor'       => $po->vendor?->name ?? '—',
            'vendor_bill'  => $po->vendor_bill_number,
            'status'       => $po->status,
            'total_amount' => $po->total_amount,
            'amount_paid'  => $po->amount_paid,
            'balance_due'  => $po->balance_due,
            'discount'     => $po->discount ?? 0,
            'notes'        => $po->notes,
            'items'        => $po->items->map(fn($i) => [
                'item_code'  => $i->item_code,
                'item_name'  => $i->item_name,
                'qty'        => $i->qty,
                'unit_price' => $i->unit_price,
                'total'      => $i->qty * $i->unit_price,
            ])->toArray(),
            'payments'     => $po->payments->map(fn($p) => [
                'payment_date'   => $p->payment_date,
                'amount'         => $p->amount,
                'payment_method' => $p->payment_method,
                'note'           => $p->note,
            ])->toArray(),
        ];
    }

    public function closeModal(): void
    {
        $this->modalPo = null;
    }

    private function from(): string { return $this->dateFrom ?: '2000-01-01'; }
    private function to(): string   { return $this->dateTo   ?: now()->format('Y-m-d'); }

    private function base()
    {
        return PurchaseOrder::whereBetween('order_date', [$this->from(), $this->to()])
            ->when($this->search, fn($q) => $q->where(fn($q) =>
                $q->where('po_number', 'like', "%{$this->search}%")
                  ->orWhereHas('vendor', fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter));
    }

    // ─── Report builders ──────────────────────────────────────────────────────

    private function reportAllOrders(): array
    {
        $allowed = ['order_date','total_amount','amount_paid','balance_due','status'];
        $col     = in_array($this->sortBy, $allowed) ? $this->sortBy : 'order_date';

        return [
            'type'      => 'po_list',
            'title'     => 'All Orders',
            'data'      => $this->base()
                ->where('status', '!=', 'cancelled')
                ->with(['vendor','items'])
                ->orderBy($col, $this->sortDir)
                ->paginate(25),
            'paginated' => true,
        ];
    }

    private function reportDailySummary(): array
    {
        return [
            'type'         => 'period_summary',
            'title'        => 'Day-wise Summary',
            'period_label' => 'Date',
            'data'         => PurchaseOrder::whereBetween('order_date', [$this->from(), $this->to()])
                ->where('status', '!=', 'cancelled')
                ->selectRaw("DATE(order_date) as period, COUNT(*) as count, SUM(total_amount) as spend, SUM(amount_paid) as paid_total")
                ->groupBy('period')
                ->orderBy('period', 'desc')
                ->paginate(31),
            'paginated' => true,
        ];
    }

    private function reportMonthlySummary(): array
    {
        return [
            'type'         => 'period_summary',
            'title'        => 'Month-wise Summary',
            'period_label' => 'Month',
            'data'         => PurchaseOrder::whereBetween('order_date', [$this->from(), $this->to()])
                ->where('status', '!=', 'cancelled')
                ->selectRaw("strftime('%Y-%m', order_date) as period, COUNT(*) as count, SUM(total_amount) as spend, SUM(amount_paid) as paid_total")
                ->groupBy('period')
                ->orderBy('period', 'desc')
                ->paginate(24),
            'paginated' => true,
        ];
    }

    private function reportPendingOrders(): array
    {
        return [
            'type'      => 'po_list',
            'title'     => 'Pending / Partial Orders',
            'data'      => $this->base()
                ->whereIn('status', ['draft','ordered','partial'])
                ->with(['vendor','items'])
                ->orderBy('order_date', 'asc')
                ->paginate(25),
            'paginated' => true,
            'highlight' => 'pending',
        ];
    }

    private function reportCancelled(): array
    {
        return [
            'type'      => 'po_list',
            'title'     => 'Cancelled Orders',
            'data'      => $this->base()
                ->where('status', 'cancelled')
                ->with(['vendor','items'])
                ->orderBy('order_date', 'desc')
                ->paginate(25),
            'paginated' => true,
        ];
    }

    private function reportOutstandingDues(): array
    {
        return [
            'type'      => 'po_list',
            'title'     => 'Baqi (Outstanding Dues)',
            'data'      => $this->base()
                ->where('status', '!=', 'cancelled')
                ->where('balance_due', '>', 0)
                ->with(['vendor','items'])
                ->orderByDesc('balance_due')
                ->paginate(25),
            'paginated' => true,
            'highlight' => 'due',
        ];
    }

    private function reportFullyPaid(): array
    {
        return [
            'type'      => 'po_list',
            'title'     => 'Fully Paid Orders',
            'data'      => $this->base()
                ->where('status', '!=', 'cancelled')
                ->where('balance_due', '<=', 0)
                ->with(['vendor','items'])
                ->orderByDesc('order_date')
                ->paginate(25),
            'paginated' => true,
        ];
    }

    private function reportRevenueSummary(): array
    {
        $rows = PurchaseOrder::whereBetween('order_date', [$this->from(), $this->to()])
            ->where('status', '!=', 'cancelled')
            ->get();

        return [
            'type'      => 'spend_cards',
            'title'     => 'Spend Summary',
            'data'      => [
                'count'   => $rows->count(),
                'total'   => $rows->sum('total_amount'),
                'paid'    => $rows->sum('amount_paid'),
                'due'     => $rows->sum('balance_due'),
                'items'   => $rows->sum(fn($po) => $po->items()->sum('qty') ?? 0),
                'pending' => $rows->whereIn('status', ['draft','ordered','partial'])->count(),
            ],
            'paginated' => false,
        ];
    }

    private function reportVendorWise(): array
    {
        return [
            'type'      => 'vendor_wise',
            'title'     => 'Vendor-wise Spend',
            'data'      => PurchaseOrder::whereBetween('order_date', [$this->from(), $this->to()])
                ->where('status', '!=', 'cancelled')
                ->with('vendor')
                ->when($this->search, fn($q) => $q->whereHas('vendor', fn($q) => $q->where('name','like',"%{$this->search}%")))
                ->selectRaw('vendor_id, COUNT(*) as order_count, SUM(total_amount) as total_spend, SUM(amount_paid) as paid_total, SUM(balance_due) as due_total')
                ->groupBy('vendor_id')
                ->orderByDesc('total_spend')
                ->paginate(25),
            'paginated' => true,
        ];
    }

    private function reportTopVendors(): array
    {
        return [
            'type'      => 'vendor_wise',
            'title'     => 'Top Vendors',
            'data'      => PurchaseOrder::whereBetween('order_date', [$this->from(), $this->to()])
                ->where('status', '!=', 'cancelled')
                ->with('vendor')
                ->selectRaw('vendor_id, COUNT(*) as order_count, SUM(total_amount) as total_spend, SUM(amount_paid) as paid_total, SUM(balance_due) as due_total')
                ->groupBy('vendor_id')
                ->orderByDesc('total_spend')
                ->paginate(20),
            'paginated' => true,
        ];
    }

    private function reportTopItems(): array
    {
        return [
            'type'      => 'item_rank',
            'title'     => 'Most Purchased Items',
            'data'      => PurchaseOrderItem::whereHas('purchaseOrder', fn($q) =>
                    $q->whereBetween('order_date', [$this->from(), $this->to()])
                      ->where('status', '!=', 'cancelled')
                )
                ->when($this->search, fn($q) => $q->where(fn($q) =>
                    $q->where('item_code','like',"%{$this->search}%")
                      ->orWhere('item_name','like',"%{$this->search}%")
                ))
                ->selectRaw('item_code, item_name, SUM(qty) as total_qty, SUM(unit_price * qty) as total_cost, COUNT(*) as times_ordered')
                ->groupBy('item_code','item_name')
                ->orderByDesc('total_qty')
                ->paginate(25),
            'paginated' => true,
        ];
    }

    private function reportItemDetail(): array
    {
        return [
            'type'      => 'item_detail',
            'title'     => 'Full Item Detail',
            'data'      => PurchaseOrderItem::with('purchaseOrder.vendor')
                ->whereHas('purchaseOrder', fn($q) =>
                    $q->whereBetween('order_date', [$this->from(), $this->to()])
                      ->where('status', '!=', 'cancelled')
                )
                ->when($this->search, fn($q) => $q->where(fn($q) =>
                    $q->where('item_code','like',"%{$this->search}%")
                      ->orWhere('item_name','like',"%{$this->search}%")
                ))
                ->orderByDesc('id')
                ->paginate(25),
            'paginated' => true,
        ];
    }

    public function render()
    {
        $menu   = $this->getReportMenu();
        $report = null;

        if ($this->activeReport) {
            $method = 'report' . str()->studly($this->activeReport);
            if (method_exists($this, $method)) {
                $report = $this->$method();
            }
        }

        return view('livewire.reports.purchase-report', compact('menu', 'report'));
    }
}