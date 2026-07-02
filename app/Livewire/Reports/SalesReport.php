<?php

namespace App\Livewire\Reports;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class SalesReport extends Component
{
    use WithPagination;

    public string $activeFilter = 'this_month';
    public string $dateFrom     = '';
    public string $dateTo       = '';
    public string $search       = '';
    public string $groupBy      = 'none'; // none | product | employee | day

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
            'today'        => [$this->dateFrom, $this->dateTo] = [now()->format('Y-m-d'), now()->format('Y-m-d')],
            'yesterday'    => [$this->dateFrom, $this->dateTo] = [now()->subDay()->format('Y-m-d'), now()->subDay()->format('Y-m-d')],
            'this_week'    => [$this->dateFrom, $this->dateTo] = [now()->startOfWeek()->format('Y-m-d'), now()->format('Y-m-d')],
            'this_month'   => [$this->dateFrom, $this->dateTo] = [now()->startOfMonth()->format('Y-m-d'), now()->format('Y-m-d')],
            'last_month'   => [$this->dateFrom, $this->dateTo] = [now()->subMonth()->startOfMonth()->format('Y-m-d'), now()->subMonth()->endOfMonth()->format('Y-m-d')],
            'this_year'    => [$this->dateFrom, $this->dateTo] = [now()->startOfYear()->format('Y-m-d'), now()->format('Y-m-d')],
            'custom'       => null,
            default        => null,
        };
    }

    public function updatedDateFrom(): void { $this->activeFilter = 'custom'; $this->resetPage(); }
    public function updatedDateTo(): void   { $this->activeFilter = 'custom'; $this->resetPage(); }
    public function updatedSearch(): void   { $this->resetPage(); }

    private function baseQuery()
    {
        return Sale::with(['items.product', 'employee'])
            ->whereBetween('sale_date', [$this->dateFrom, $this->dateTo])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('customer_name', 'like', "%{$this->search}%")
                  ->orWhere('bill_ref', 'like', "%{$this->search}%")
                  ->orWhere('customer_phone1', 'like', "%{$this->search}%");
            }))
            ->where('status', '!=', 'cancelled');
    }

    public function render()
    {
        $sales = $this->baseQuery()->latest('sale_date')->paginate(20);

        $allSales = $this->baseQuery()->get();

        $summary = [
            'total_bills'    => $allSales->count(),
            'total_revenue'  => $allSales->sum('total_amount'),
            'total_discount' => $allSales->sum('discount'),
            'total_paid'     => $allSales->sum('advance_paid'),
            'total_due'      => $allSales->sum('remaining_balance'),
            'avg_bill'       => $allSales->count() ? $allSales->sum('total_amount') / $allSales->count() : 0,
        ];

        // Top products in period
        $topProducts = SaleItem::whereHas('sale', fn ($q) =>
                $q->whereBetween('sale_date', [$this->dateFrom, $this->dateTo])
                  ->where('status', '!=', 'cancelled')
            )
            ->selectRaw('product_name, product_code, SUM(qty) as total_qty, SUM(sale_price * qty) as total_revenue, COUNT(*) as times_sold')
            ->groupBy('product_name', 'product_code')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();

        // Daily trend for the period
        $dailyTrend = Sale::whereBetween('sale_date', [$this->dateFrom, $this->dateTo])
            ->where('status', '!=', 'cancelled')
            ->selectRaw('DATE(sale_date) as date, COUNT(*) as count, SUM(total_amount) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('livewire.reports.sales-report', compact('sales', 'summary', 'topProducts', 'dailyTrend'));
    }
}