<?php

namespace App\Livewire\Reports;

use App\Models\Sale;
use App\Models\SaleItem;
use Livewire\Component;
use Livewire\WithPagination;

class SalesReport extends Component
{
    use WithPagination;

    public string $activeFilter = 'this_month';
    public string $dateFrom     = '';
    public string $dateTo       = '';
    public string $activeReport = '';
    public string $search       = '';
    public string $statusFilter = '';
    public string $sortBy       = 'sale_date';
    public string $sortDir      = 'desc';

    public ?array $modalSale = null;

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo   = now()->format('Y-m-d');
    }

    public function getReportMenu(): array
    {
        return [
            'sales' => [
                'label' => 'Sales',
                'icon'  => 'bi-bag',
                'items' => [
                    'all_sales'      => ['label' => 'All Sales',         'icon' => 'bi-list-ul'],
                    'daily_summary'  => ['label' => 'Day-wise Summary',  'icon' => 'bi-calendar3'],
                    'monthly_summary'=> ['label' => 'Month-wise Summary','icon' => 'bi-calendar-month'],
                    'cancelled'      => ['label' => 'Cancelled Sales',   'icon' => 'bi-x-circle'],
                ],
            ],
            'hisaab' => [
                'label' => 'Hisaab / Payment',
                'icon'  => 'bi-cash-coin',
                'items' => [
                    'outstanding_dues'=> ['label' => 'Baqi (Dues)',       'icon' => 'bi-exclamation-circle'],
                    'fully_paid'      => ['label' => 'Fully Paid',        'icon' => 'bi-check2-circle'],
                    'discounts'       => ['label' => 'Discounts Given',   'icon' => 'bi-tag'],
                    'revenue_summary' => ['label' => 'Revenue Summary',   'icon' => 'bi-graph-up-arrow'],
                ],
            ],
            'items' => [
                'label' => 'Items / Maal',
                'icon'  => 'bi-box-seam',
                'items' => [
                    'top_items'   => ['label' => 'Top Selling Items',  'icon' => 'bi-trophy'],
                    'item_wise'   => ['label' => 'Item-wise Revenue',  'icon' => 'bi-bar-chart'],
                    'item_detail' => ['label' => 'Full Item Detail',   'icon' => 'bi-card-list'],
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
        $this->sortBy        = 'sale_date';
        $this->sortDir       = 'desc';
        $this->modalSale     = null;
        $this->resetPage();
    }

    public function sortByColumn(string $col): void
    {
        $this->sortDir = $this->sortBy === $col && $this->sortDir === 'desc' ? 'asc' : 'desc';
        $this->sortBy  = $col;
        $this->resetPage();
    }

    public function openModal(int $saleId): void
    {
        $sale = Sale::with(['items.product', 'employee'])->findOrFail($saleId);

        $this->modalSale = [
            'id'                => $sale->id,
            'bill_ref'          => $sale->bill_ref ?? '#' . $sale->id,
            'sale_date'         => $sale->sale_date,
            'customer_name'     => $sale->customer_name,
            'customer_phone1'   => $sale->customer_phone1,
            'customer_cnic'     => $sale->customer_cnic,
            'employee'          => $sale->employee?->name ?? '—',
            'status'            => $sale->status,
            'total_amount'      => $sale->total_amount,
            'discount'          => $sale->discount,
            'advance_paid'      => $sale->advance_paid,
            'remaining_balance' => $sale->remaining_balance,
            'notes'             => $sale->notes,
            'items'             => $sale->items->map(fn($i) => [
                'product_code' => $i->product_code,
                'product_name' => $i->product_name,
                'qty'          => $i->qty,
                'sale_price'   => $i->sale_price,
                'total'        => $i->qty * $i->sale_price,
                'pickup_status'=> $i->pickup_status ?? 'pending',
            ])->toArray(),
        ];
    }

    public function closeModal(): void
    {
        $this->modalSale = null;
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function from(): string { return $this->dateFrom ?: '2000-01-01'; }
    private function to(): string   { return $this->dateTo   ?: now()->format('Y-m-d'); }

    private function base()
    {
        return Sale::whereBetween('sale_date', [$this->from(), $this->to()])
            ->when($this->search, fn($q) => $q->where(fn($q) =>
                $q->where('customer_name', 'like', "%{$this->search}%")
                  ->orWhere('bill_ref',    'like', "%{$this->search}%")
                  ->orWhere('customer_phone1','like',"%{$this->search}%")
            ))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter));
    }

    // ─── Report builders ──────────────────────────────────────────────────────

    private function reportAllSales(): array
    {
        $allowed = ['sale_date','total_amount','customer_name','status','advance_paid','remaining_balance'];
        $col     = in_array($this->sortBy, $allowed) ? $this->sortBy : 'sale_date';

        return [
            'type'  => 'sale_list',
            'title' => 'All Sales',
            'data'  => $this->base()
                ->where('status', '!=', 'cancelled')
                ->with('items')
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
            'data'         => Sale::whereBetween('sale_date', [$this->from(), $this->to()])
                ->where('status', '!=', 'cancelled')
                ->selectRaw("DATE(sale_date) as period, COUNT(*) as count, SUM(total_amount) as revenue, SUM(discount) as discount_total")
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
            'data'         => Sale::whereBetween('sale_date', [$this->from(), $this->to()])
                ->where('status', '!=', 'cancelled')
                ->selectRaw("strftime('%Y-%m', sale_date) as period, COUNT(*) as count, SUM(total_amount) as revenue, SUM(discount) as discount_total")
                ->groupBy('period')
                ->orderBy('period', 'desc')
                ->paginate(24),
            'paginated' => true,
        ];
    }

    private function reportCancelled(): array
    {
        return [
            'type'  => 'sale_list',
            'title' => 'Cancelled Sales',
            'data'  => $this->base()
                ->where('status', 'cancelled')
                ->with('items')
                ->orderBy('sale_date', 'desc')
                ->paginate(25),
            'paginated' => true,
        ];
    }

    private function reportOutstandingDues(): array
    {
        $rows = Sale::whereBetween('sale_date', [$this->from(), $this->to()])
            ->where('status', '!=', 'cancelled')
            ->where('remaining_balance', '>', 0)
            ->with('items')
            ->when($this->search, fn($q) => $q->where(fn($q) =>
                $q->where('customer_name','like',"%{$this->search}%")
                  ->orWhere('bill_ref','like',"%{$this->search}%")
            ))
            ->orderByDesc('remaining_balance')
            ->paginate(25);

        return ['type' => 'sale_list', 'title' => 'Baqi (Outstanding Dues)', 'data' => $rows, 'paginated' => true, 'highlight' => 'due'];
    }

    private function reportFullyPaid(): array
    {
        $rows = Sale::whereBetween('sale_date', [$this->from(), $this->to()])
            ->where('status', '!=', 'cancelled')
            ->where('remaining_balance', '<=', 0)
            ->with('items')
            ->when($this->search, fn($q) => $q->where('customer_name','like',"%{$this->search}%"))
            ->orderByDesc('sale_date')
            ->paginate(25);

        return ['type' => 'sale_list', 'title' => 'Fully Paid Sales', 'data' => $rows, 'paginated' => true];
    }

    private function reportDiscounts(): array
    {
        $rows = Sale::whereBetween('sale_date', [$this->from(), $this->to()])
            ->where('status', '!=', 'cancelled')
            ->where('discount', '>', 0)
            ->with('items')
            ->when($this->search, fn($q) => $q->where('customer_name','like',"%{$this->search}%"))
            ->orderByDesc('discount')
            ->paginate(25);

        return ['type' => 'discount_list', 'title' => 'Discounts Given', 'data' => $rows, 'paginated' => true];
    }

    private function reportRevenueSummary(): array
    {
        $rows = Sale::whereBetween('sale_date', [$this->from(), $this->to()])
            ->where('status', '!=', 'cancelled')
            ->get();

        return [
            'type'  => 'revenue_cards',
            'title' => 'Revenue Summary',
            'data'  => [
                'count'    => $rows->count(),
                'revenue'  => $rows->sum('total_amount'),
                'discount' => $rows->sum('discount'),
                'paid'     => $rows->sum('advance_paid'),
                'due'      => $rows->sum('remaining_balance'),
                'avg'      => $rows->count() ? $rows->sum('total_amount') / $rows->count() : 0,
            ],
            'paginated' => false,
        ];
    }

    private function reportTopItems(): array
    {
        return [
            'type'  => 'item_rank',
            'title' => 'Top Selling Items',
            'data'  => SaleItem::whereHas('sale', fn($q) =>
                    $q->whereBetween('sale_date', [$this->from(), $this->to()])
                      ->where('status', '!=', 'cancelled')
                )
                ->when($this->search, fn($q) => $q->where(fn($q) =>
                    $q->where('product_code','like',"%{$this->search}%")
                      ->orWhere('product_name','like',"%{$this->search}%")
                ))
                ->selectRaw('product_code, product_name, SUM(qty) as total_qty, SUM(sale_price * qty) as total_revenue, COUNT(*) as times_sold')
                ->groupBy('product_code','product_name')
                ->orderByDesc('total_revenue')
                ->paginate(25),
            'paginated' => true,
        ];
    }

    private function reportItemWise(): array
    {
        return [
            'type'  => 'item_rank',
            'title' => 'Item-wise Revenue',
            'data'  => SaleItem::whereHas('sale', fn($q) =>
                    $q->whereBetween('sale_date', [$this->from(), $this->to()])
                      ->where('status', '!=', 'cancelled')
                )
                ->when($this->search, fn($q) => $q->where(fn($q) =>
                    $q->where('product_code','like',"%{$this->search}%")
                      ->orWhere('product_name','like',"%{$this->search}%")
                ))
                ->selectRaw('product_code, product_name, SUM(qty) as total_qty, SUM(sale_price * qty) as total_revenue, AVG(sale_price) as avg_price, COUNT(*) as times_sold')
                ->groupBy('product_code','product_name')
                ->orderByDesc('total_revenue')
                ->paginate(25),
            'paginated' => true,
        ];
    }

    private function reportItemDetail(): array
    {
        return [
            'type'  => 'item_detail',
            'title' => 'Full Item Detail',
            'data'  => SaleItem::with('sale')
                ->whereHas('sale', fn($q) =>
                    $q->whereBetween('sale_date', [$this->from(), $this->to()])
                      ->where('status', '!=', 'cancelled')
                )
                ->when($this->search, fn($q) => $q->where(fn($q) =>
                    $q->where('product_code','like',"%{$this->search}%")
                      ->orWhere('product_name','like',"%{$this->search}%")
                      ->orWhereHas('sale', fn($q) => $q->where('customer_name','like',"%{$this->search}%"))
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

        return view('livewire.reports.sales-report', compact('menu', 'report'));
    }
}