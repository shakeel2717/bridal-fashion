<?php

namespace App\Livewire\Reports;

use App\Models\Rental;
use App\Models\RentalItem;
use App\Models\RentalPayment;
use Livewire\Component;
use Livewire\WithPagination;

class RentalReport extends Component
{
    use WithPagination;

    public string $activeFilter  = 'this_month';
    public string $dateFrom      = '';
    public string $dateTo        = '';
    public string $search        = '';
    public string $statusFilter  = '';

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo   = now()->format('Y-m-d');
    }

    public function setFilter(string $filter): void
    {
        $this->activeFilter = $filter;
        $this->resetPage();

        match ($filter) {
            'today'      => [$this->dateFrom, $this->dateTo] = [now()->format('Y-m-d'), now()->format('Y-m-d')],
            'yesterday'  => [$this->dateFrom, $this->dateTo] = [now()->subDay()->format('Y-m-d'), now()->subDay()->format('Y-m-d')],
            'this_week'  => [$this->dateFrom, $this->dateTo] = [now()->startOfWeek(0)->format('Y-m-d'), now()->format('Y-m-d')],
            'this_month' => [$this->dateFrom, $this->dateTo] = [now()->startOfMonth()->format('Y-m-d'), now()->format('Y-m-d')],
            'last_month' => [$this->dateFrom, $this->dateTo] = [now()->subMonthNoOverflow()->startOfMonth()->format('Y-m-d'), now()->subMonthNoOverflow()->endOfMonth()->format('Y-m-d')],
            'this_year'  => [$this->dateFrom, $this->dateTo] = [now()->startOfYear()->format('Y-m-d'), now()->format('Y-m-d')],
            default      => null,
        };
    }

    public function updatedDateFrom(): void { $this->activeFilter = 'custom'; $this->resetPage(); }
    public function updatedDateTo(): void   { $this->activeFilter = 'custom'; $this->resetPage(); }
    public function updatedSearch(): void   { $this->resetPage(); }
    public function updatedStatusFilter(): void { $this->resetPage(); }

    private function baseQuery()
    {
        return Rental::with(['items', 'employee'])
            ->withSum('payments', 'amount')
            ->whereBetween('booking_date', [$this->dateFrom, $this->dateTo])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('customer_name', 'like', "%{$this->search}%")
                  ->orWhere('bill_ref', 'like', "%{$this->search}%")
                  ->orWhere('customer_phone1', 'like', "%{$this->search}%")
                  ->orWhere('customer_cnic', 'like', "%{$this->search}%");
            }))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter));
    }

    public function render()
    {
        $rentals = $this->baseQuery()->latest('booking_date')->paginate(20);
        $all     = $this->baseQuery()->get();

        $today = now()->toDateString();

        $summary = [
            'total_bookings'  => $all->count(),
            'total_value'     => $all->sum('total_amount'),
            'total_collected' => $all->sum('payments_sum_amount'),
            'total_due'       => $all->sum(fn ($r) => max(0, $r->total_amount - (float)($r->payments_sum_amount ?? 0))),
            'total_items'     => $all->sum(fn ($r) => $r->items->count()),
            'late_returns'    => Rental::whereNotNull('return_date')
                ->where('return_date', '<', $today)
                ->whereNotIn('status', ['returned', 'cancelled', 'abandoned'])
                ->whereBetween('booking_date', [$this->dateFrom, $this->dateTo])
                ->count(),
        ];

        // Status breakdown
        $statusBreakdown = Rental::whereBetween('booking_date', [$this->dateFrom, $this->dateTo])
            ->when($this->search, fn ($q) => $q->where('customer_name', 'like', "%{$this->search}%"))
            ->selectRaw('status, COUNT(*) as count, SUM(total_amount) as value')
            ->groupBy('status')
            ->orderByDesc('count')
            ->get();

        // Top rented items
        $topItems = RentalItem::whereHas('rental', fn ($q) =>
                $q->whereBetween('booking_date', [$this->dateFrom, $this->dateTo])
                  ->whereNotIn('status', ['cancelled', 'abandoned'])
            )
            ->selectRaw('product_code, product_name, COUNT(*) as times_rented, SUM(rental_price) as total_revenue')
            ->groupBy('product_code', 'product_name')
            ->orderByDesc('times_rented')
            ->limit(8)
            ->get();

        // Daily booking trend
        $dailyTrend = Rental::whereBetween('booking_date', [$this->dateFrom, $this->dateTo])
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->selectRaw('DATE(booking_date) as date, COUNT(*) as count, SUM(total_amount) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('livewire.reports.rental-report',
            compact('rentals', 'summary', 'statusBreakdown', 'topItems', 'dailyTrend'));
    }
}