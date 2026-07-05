<?php

namespace App\Livewire\Reports;

use App\Models\Rental;
use App\Models\RentalItem;
use Livewire\Component;
use Livewire\WithPagination;

class RentalReport extends Component
{
    use WithPagination;

    public string $activeFilter = 'this_month';

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $activeReport = '';

    public string $search = '';

    public string $statusFilter = '';

    public string $sortBy = 'booking_date';

    public string $sortDir = 'desc';

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    // ─── Report menu definition ───────────────────────────────────────────────
    public function getReportMenu(): array
    {
        return [
            'bookings' => [
                'label' => 'Bookings',
                'icon' => 'bi-journal-bookmark',
                'items' => [
                    'all_rentals' => ['label' => 'All Rentals',       'icon' => 'bi-list-ul'],
                    'daily_summary' => ['label' => 'Day-wise Summary',  'icon' => 'bi-calendar3'],
                    'monthly_summary' => ['label' => 'Month-wise Summary', 'icon' => 'bi-calendar-month'],
                    'late_returns' => ['label' => 'Late Returns',      'icon' => 'bi-alarm'],
                    'cancelled' => ['label' => 'Cancelled',         'icon' => 'bi-x-circle'],
                    'free_items' => ['label' => 'Top Free Items (Rs. 0)', 'icon' => 'bi-gift'],
                ],
            ],
            'hisaab' => [
                'label' => 'Hisaab / Payment',
                'icon' => 'bi-cash-coin',
                'items' => [
                    'outstanding_dues' => ['label' => 'Baqi (Dues)',        'icon' => 'bi-exclamation-circle'],
                    'fully_paid' => ['label' => 'Full Paid',          'icon' => 'bi-check2-circle'],
                    'fines' => ['label' => 'Jurmana (Fines)',    'icon' => 'bi-shield-exclamation'],
                    'overpaid' => ['label' => 'Advance / Ziyada',   'icon' => 'bi-arrow-up-circle'],
                    'revenue_summary' => ['label' => 'Revenue Summary',    'icon' => 'bi-graph-up-arrow'],
                ],
            ],
            'customers' => [
                'label' => 'Customers',
                'icon' => 'bi-people',
                'items' => [
                    'customer_wise' => ['label' => 'By Customer',       'icon' => 'bi-person-lines-fill'],
                    'repeat_customers' => ['label' => 'Repeat Customers',  'icon' => 'bi-arrow-repeat'],
                ],
            ],
            'items' => [
                'label' => 'Items / Maal',
                'icon' => 'bi-box-seam',
                'items' => [
                    'top_items' => ['label' => 'Most Rented Items',  'icon' => 'bi-trophy'],
                    'item_wise' => ['label' => 'Item-wise Revenue',  'icon' => 'bi-bar-chart'],
                    'item_detail' => ['label' => 'Full Item Detail',   'icon' => 'bi-card-list'],
                ],
            ],
            'schedule' => [
                'label' => 'Schedule',
                'icon' => 'bi-calendar-event',
                'items' => [
                    'pickup_schedule' => ['label' => 'Pickup Schedule',    'icon' => 'bi-box-arrow-up-right'],
                    'return_schedule' => ['label' => 'Return Schedule',    'icon' => 'bi-box-arrow-in-down'],
                ],
            ],
        ];
    }

    private function reportFreeItems(): array
    {
        $from = $this->dateFrom ?: '2000-01-01';
        $to = $this->dateTo ?: now()->format('Y-m-d');

        return [
            'paginated' => true,
            'data' => RentalItem::whereHas('rental', fn ($q) => $q->whereBetween('booking_date', [$from, $to])
                ->whereNotIn('status', ['cancelled', 'abandoned'])
            )
                ->where('rental_price', 0)
                ->when($this->search, fn ($q) => $q->where(fn ($q) => $q->where('product_code', 'like', "%{$this->search}%")
                ->orWhere('product_name', 'like', "%{$this->search}%")
                ))
                ->selectRaw('product_code, product_name, COUNT(*) as times_rented, SUM(rental_price) as total_revenue')
                ->groupBy('product_code', 'product_name')
                ->orderByDesc('times_rented')
                ->paginate(25),
            'columns' => 'item_rank',
            'title' => 'Top Free Items (Zero Price)',
        ];
    }

    // ─── Date setters ─────────────────────────────────────────────────────────
    public function setFilter(string $filter): void
    {
        $this->activeFilter = $filter;
        $this->resetPage();
        match ($filter) {
            'today' => [$this->dateFrom, $this->dateTo] = [now()->format('Y-m-d'), now()->format('Y-m-d')],
            'yesterday' => [$this->dateFrom, $this->dateTo] = [now()->subDay()->format('Y-m-d'), now()->subDay()->format('Y-m-d')],
            'this_week' => [$this->dateFrom, $this->dateTo] = [now()->startOfWeek(0)->format('Y-m-d'), now()->endOfWeek(6)->format('Y-m-d')],
            'last_week' => [$this->dateFrom, $this->dateTo] = [now()->subWeek()->startOfWeek(0)->format('Y-m-d'), now()->subWeek()->endOfWeek(6)->format('Y-m-d')],
            'this_month' => [$this->dateFrom, $this->dateTo] = [now()->startOfMonth()->format('Y-m-d'), now()->format('Y-m-d')],
            'last_month' => [$this->dateFrom, $this->dateTo] = [now()->subMonthNoOverflow()->startOfMonth()->format('Y-m-d'), now()->subMonthNoOverflow()->endOfMonth()->format('Y-m-d')],
            'last_3_months' => [$this->dateFrom, $this->dateTo] = [now()->subMonths(3)->startOfMonth()->format('Y-m-d'), now()->format('Y-m-d')],
            'last_6_months' => [$this->dateFrom, $this->dateTo] = [now()->subMonths(6)->startOfMonth()->format('Y-m-d'), now()->format('Y-m-d')],
            'this_quarter' => [$this->dateFrom, $this->dateTo] = [now()->startOfQuarter()->format('Y-m-d'), now()->format('Y-m-d')],
            'last_quarter' => [$this->dateFrom, $this->dateTo] = [now()->subQuarter()->startOfQuarter()->format('Y-m-d'), now()->subQuarter()->endOfQuarter()->format('Y-m-d')],
            'this_year' => [$this->dateFrom, $this->dateTo] = [now()->startOfYear()->format('Y-m-d'), now()->format('Y-m-d')],
            'last_year' => [$this->dateFrom, $this->dateTo] = [now()->subYear()->startOfYear()->format('Y-m-d'), now()->subYear()->endOfYear()->format('Y-m-d')],
            'all_time' => [$this->dateFrom, $this->dateTo] = ['2000-01-01', now()->format('Y-m-d')],
            default => null,
        };
    }

    public function updatedDateFrom(): void
    {
        $this->activeFilter = 'custom';
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->activeFilter = 'custom';
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function selectReport(string $report): void
    {
        $this->activeReport = $report;
        $this->search = '';
        $this->statusFilter = '';
        $this->sortBy = 'booking_date';
        $this->sortDir = 'desc';
        $this->resetPage();
    }

    public function sortByColumn(string $col): void
    {
        $this->sortBy = $this->sortBy === $col ? $this->sortBy : $col;
        $this->sortDir = $this->sortBy === $col && $this->sortDir === 'desc' ? 'asc' : 'desc';
        $this->sortBy = $col;
        $this->resetPage();
    }

    // ─── Base query ───────────────────────────────────────────────────────────
    private function base()
    {
        $from = $this->dateFrom ?: '2000-01-01';
        $to = $this->dateTo ?: now()->format('Y-m-d');

        return Rental::withSum('payments', 'amount')
            ->whereBetween('booking_date', [$from, $to]);
    }

    // ─── Individual report builders ───────────────────────────────────────────
    private function reportAllRentals()
    {
        $allowed = ['booking_date', 'total_amount', 'customer_name', 'status', 'pickup_date', 'return_date'];
        $col = in_array($this->sortBy, $allowed) ? $this->sortBy : 'booking_date';

        return [
            'paginated' => true,
            'data' => $this->base()
                ->with('items')
                ->when($this->search, fn ($q) => $q->where(fn ($q) => $q->where('customer_name', 'like', "%{$this->search}%")
                    ->orWhere('bill_ref', 'like', "%{$this->search}%")
                    ->orWhere('customer_phone1', 'like', "%{$this->search}%")
                ))
                ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
                ->orderBy($col, $this->sortDir)
                ->paginate(25),
            'columns' => 'rental_list',
            'title' => 'All Rentals',
        ];
    }

    private function reportDailySummary()
    {
        $from = $this->dateFrom ?: '2000-01-01';
        $to = $this->dateTo ?: now()->format('Y-m-d');

        return [
            'paginated' => true,
            'data' => Rental::whereBetween('booking_date', [$from, $to])
                ->selectRaw('DATE(booking_date) as period, COUNT(*) as count, SUM(total_amount) as revenue')
                ->groupBy('period')
                ->orderBy('period', 'desc')
                ->paginate(31),
            'columns' => 'period_summary',
            'title' => 'Day-wise Summary',
            'period_label' => 'Date',
        ];
    }

    private function reportMonthlySummary()
    {
        $from = $this->dateFrom ?: '2000-01-01';
        $to = $this->dateTo ?: now()->format('Y-m-d');

        return [
            'paginated' => true,
            'data' => Rental::whereBetween('booking_date', [$from, $to])
                ->selectRaw("strftime('%Y-%m', booking_date) as period, COUNT(*) as count, SUM(total_amount) as revenue")
                ->groupBy('period')
                ->orderBy('period', 'desc')
                ->paginate(24),
            'columns' => 'period_summary',
            'title' => 'Month-wise Summary',
            'period_label' => 'Month',
        ];
    }

    private function reportLateReturns()
    {
        $from = $this->dateFrom ?: '2000-01-01';
        $to = $this->dateTo ?: now()->format('Y-m-d');
        $today = now()->toDateString();

        return [
            'paginated' => true,
            'data' => Rental::withSum('payments', 'amount')
                ->with('items')
                ->whereBetween('booking_date', [$from, $to])
                ->whereNotNull('return_date')
                ->where('return_date', '<', $today)
                ->whereNotIn('status', ['returned', 'cancelled', 'abandoned'])
                ->when($this->search, fn ($q) => $q->where('customer_name', 'like', "%{$this->search}%"))
                ->orderBy('return_date', 'asc')
                ->paginate(25),
            'columns' => 'rental_list',
            'title' => 'Late Returns',
            'highlight' => 'overdue',
        ];
    }

    private function reportCancelled()
    {
        return [
            'paginated' => true,
            'data' => $this->base()
                ->with('items')
                ->whereIn('status', ['cancelled', 'abandoned'])
                ->when($this->search, fn ($q) => $q->where('customer_name', 'like', "%{$this->search}%"))
                ->orderBy('booking_date', 'desc')
                ->paginate(25),
            'columns' => 'rental_list',
            'title' => 'Cancelled Rentals',
        ];
    }

    private function reportOutstandingDues()
    {
        $rows = $this->base()
            ->with('items')
            ->whereNotIn('status', ['cancelled', 'abandoned'])
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q->where('customer_name', 'like', "%{$this->search}%")
                ->orWhere('bill_ref', 'like', "%{$this->search}%")
            ))
            ->get()
            ->filter(fn ($r) => ($r->total_amount - (float) ($r->payments_sum_amount ?? 0)) > 0)
            ->sortByDesc(fn ($r) => $r->total_amount - (float) ($r->payments_sum_amount ?? 0))
            ->values();

        return [
            'paginated' => false,
            'data' => $rows,
            'columns' => 'dues_list',
            'title' => 'Baqi (Outstanding Dues)',
        ];
    }

    private function reportFullyPaid()
    {
        return [
            'paginated' => true,
            'data' => $this->base()
                ->with('items')
                ->whereNotIn('status', ['cancelled', 'abandoned'])
                ->when($this->search, fn ($q) => $q->where('customer_name', 'like', "%{$this->search}%"))
                ->get()
                ->filter(fn ($r) => ($r->total_amount - (float) ($r->payments_sum_amount ?? 0)) <= 0)
                ->sortByDesc('booking_date')
                ->values(),
            'paginated' => false,
            'columns' => 'rental_list',
            'title' => 'Fully Paid Rentals',
        ];
    }

    private function reportFines()
    {
        return [
            'paginated' => true,
            'data' => $this->base()
                ->with('items')
                ->where('fine_amount', '>', 0)
                ->when($this->search, fn ($q) => $q->where('customer_name', 'like', "%{$this->search}%"))
                ->orderByDesc('fine_amount')
                ->paginate(25),
            'columns' => 'fines_list',
            'title' => 'Jurmana (Fines) Report',
        ];
    }

    private function reportOverpaid()
    {
        $rows = $this->base()
            ->whereNotIn('status', ['cancelled'])
            ->when($this->search, fn ($q) => $q->where('customer_name', 'like', "%{$this->search}%"))
            ->get()
            ->filter(fn ($r) => (float) ($r->payments_sum_amount ?? 0) > $r->total_amount)
            ->values();

        return [
            'paginated' => false,
            'data' => $rows,
            'columns' => 'overpaid_list',
            'title' => 'Advance / Ziyada Paid',
        ];
    }

    private function reportRevenueSummary()
    {
        $rows = $this->base()
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->get();

        return [
            'paginated' => false,
            'data' => $rows,
            'columns' => 'revenue_summary',
            'title' => 'Revenue Summary',
        ];
    }

    private function reportCustomerWise()
    {
        $from = $this->dateFrom ?: '2000-01-01';
        $to = $this->dateTo ?: now()->format('Y-m-d');

        return [
            'paginated' => true,
            'data' => Rental::whereBetween('booking_date', [$from, $to])
                ->when($this->search, fn ($q) => $q->where('customer_name', 'like', "%{$this->search}%"))
                ->selectRaw('customer_name, customer_phone1, COUNT(*) as count, SUM(total_amount) as revenue, MAX(booking_date) as last_booking')
                ->groupBy('customer_name', 'customer_phone1')
                ->orderByDesc('revenue')
                ->paginate(25),
            'columns' => 'customer_wise',
            'title' => 'Rentals by Customer',
        ];
    }

    private function reportRepeatCustomers()
    {
        $from = $this->dateFrom ?: '2000-01-01';
        $to = $this->dateTo ?: now()->format('Y-m-d');

        return [
            'paginated' => true,
            'data' => Rental::whereBetween('booking_date', [$from, $to])
                ->when($this->search, fn ($q) => $q->where('customer_name', 'like', "%{$this->search}%"))
                ->selectRaw('customer_name, customer_phone1, COUNT(*) as count, SUM(total_amount) as revenue')
                ->groupBy('customer_name', 'customer_phone1')
                ->having('count', '>=', 2)
                ->orderByDesc('count')
                ->paginate(25),
            'columns' => 'customer_wise',
            'title' => 'Repeat Customers',
        ];
    }

    private function reportTopItems()
    {
        $from = $this->dateFrom ?: '2000-01-01';
        $to = $this->dateTo ?: now()->format('Y-m-d');

        return [
            'paginated' => true,
            'data' => RentalItem::whereHas('rental', fn ($q) => $q->whereBetween('booking_date', [$from, $to])
                ->whereNotIn('status', ['cancelled', 'abandoned'])
            )
                ->when($this->search, fn ($q) => $q->where(fn ($q) => $q->where('product_code', 'like', "%{$this->search}%")
                    ->orWhere('product_name', 'like', "%{$this->search}%")
                ))
                ->selectRaw('product_code, product_name, COUNT(*) as times_rented, SUM(rental_price) as total_revenue')
                ->groupBy('product_code', 'product_name')
                ->orderByDesc('times_rented')
                ->paginate(25),
            'columns' => 'item_rank',
            'title' => 'Most Rented Items',
        ];
    }

    private function reportItemWise()
    {
        $from = $this->dateFrom ?: '2000-01-01';
        $to = $this->dateTo ?: now()->format('Y-m-d');

        return [
            'paginated' => true,
            'data' => RentalItem::whereHas('rental', fn ($q) => $q->whereBetween('booking_date', [$from, $to])
                ->whereNotIn('status', ['cancelled', 'abandoned'])
            )
                ->when($this->search, fn ($q) => $q->where(fn ($q) => $q->where('product_code', 'like', "%{$this->search}%")
                    ->orWhere('product_name', 'like', "%{$this->search}%")
                ))
                ->selectRaw('product_code, product_name, COUNT(*) as times_rented, SUM(rental_price) as total_revenue, AVG(rental_price) as avg_price')
                ->groupBy('product_code', 'product_name')
                ->orderByDesc('total_revenue')
                ->paginate(25),
            'columns' => 'item_revenue',
            'title' => 'Item-wise Revenue',
        ];
    }

    private function reportItemDetail()
    {
        $from = $this->dateFrom ?: '2000-01-01';
        $to = $this->dateTo ?: now()->format('Y-m-d');

        return [
            'paginated' => true,
            'data' => RentalItem::with('rental')
                ->whereHas('rental', fn ($q) => $q->whereBetween('booking_date', [$from, $to]))
                ->when($this->search, fn ($q) => $q->where(fn ($q) => $q->where('product_code', 'like', "%{$this->search}%")
                    ->orWhere('product_name', 'like', "%{$this->search}%")
                    ->orWhereHas('rental', fn ($q) => $q->where('customer_name', 'like', "%{$this->search}%"))
                ))
                ->orderByDesc('id')
                ->paginate(25),
            'columns' => 'item_detail',
            'title' => 'Full Item Detail',
        ];
    }

    private function reportPickupSchedule()
    {
        $from = $this->dateFrom ?: '2000-01-01';
        $to = $this->dateTo ?: now()->format('Y-m-d');

        return [
            'paginated' => true,
            'data' => Rental::withSum('payments', 'amount')
                ->whereBetween('pickup_date', [$from, $to])
                ->when($this->search, fn ($q) => $q->where(fn ($q) => $q->where('customer_name', 'like', "%{$this->search}%")
                    ->orWhere('bill_ref', 'like', "%{$this->search}%")
                ))
                ->orderBy('pickup_date', 'asc')
                ->paginate(25),
            'columns' => 'schedule',
            'title' => 'Pickup Schedule',
            'date_col' => 'pickup_date',
        ];
    }

    private function reportReturnSchedule()
    {
        $from = $this->dateFrom ?: '2000-01-01';
        $to = $this->dateTo ?: now()->format('Y-m-d');

        return [
            'paginated' => true,
            'data' => Rental::withSum('payments', 'amount')
                ->whereBetween('return_date', [$from, $to])
                ->when($this->search, fn ($q) => $q->where(fn ($q) => $q->where('customer_name', 'like', "%{$this->search}%")
                    ->orWhere('bill_ref', 'like', "%{$this->search}%")
                ))
                ->orderBy('return_date', 'asc')
                ->paginate(25),
            'columns' => 'schedule',
            'title' => 'Return Schedule',
            'date_col' => 'return_date',
        ];
    }

    public function render()
    {
        $menu = $this->getReportMenu();
        $report = null;

        if ($this->activeReport) {
            $method = 'report'.str()->studly($this->activeReport);
            if (method_exists($this, $method)) {
                $report = $this->$method();
            }
        }

        return view('livewire.reports.rental-report', compact('menu', 'report'));
    }
}
