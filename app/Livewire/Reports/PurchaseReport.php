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

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $search = '';

    public string $statusFilter = '';

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function setFilter(string $filter): void
    {
        $this->activeFilter = $filter;
        $this->resetPage();

        match ($filter) {
            'today' => [$this->dateFrom, $this->dateTo] = [now()->format('Y-m-d'), now()->format('Y-m-d')],
            'yesterday' => [$this->dateFrom, $this->dateTo] = [now()->subDay()->format('Y-m-d'), now()->subDay()->format('Y-m-d')],
            'this_week' => [$this->dateFrom, $this->dateTo] = [now()->startOfWeek(0)->format('Y-m-d'), now()->format('Y-m-d')],
            'this_month' => [$this->dateFrom, $this->dateTo] = [now()->startOfMonth()->format('Y-m-d'), now()->format('Y-m-d')],
            'last_month' => [$this->dateFrom, $this->dateTo] = [now()->subMonthNoOverflow()->startOfMonth()->format('Y-m-d'), now()->subMonthNoOverflow()->endOfMonth()->format('Y-m-d')],
            'this_year' => [$this->dateFrom, $this->dateTo] = [now()->startOfYear()->format('Y-m-d'), now()->format('Y-m-d')],
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

    private function baseQuery()
    {
        return PurchaseOrder::with(['vendor', 'items'])
            ->whereBetween('order_date', [$this->dateFrom, $this->dateTo])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('po_number', 'like', "%{$this->search}%")
                    ->orWhereHas('vendor', fn ($q) => $q->where('name', 'like', "%{$this->search}%"));
            }))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter));
    }

    public function render()
    {
        $orders = $this->baseQuery()->latest('order_date')->paginate(20);

        $all = $this->baseQuery()->get();

        $summary = [
            'total_orders' => $all->count(),
            'total_value' => $all->sum('total_amount'),
            'total_paid' => $all->sum('amount_paid'),
            'total_due' => $all->sum('balance_due'),  // was remaining_balance
            'total_items' => $all->sum(fn ($po) => $po->items->sum('qty')),
            'pending_orders' => $all->whereIn('status', ['draft', 'ordered', 'partial'])->count(),
        ];

        // Top vendors by spend
        $topVendors = PurchaseOrder::with('vendor')
            ->whereBetween('order_date', [$this->dateFrom, $this->dateTo])
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->selectRaw('vendor_id, SUM(total_amount) as total_spend, COUNT(*) as order_count')
            ->groupBy('vendor_id')
            ->orderByDesc('total_spend')
            ->limit(5)
            ->get();

        // Fix column names for PurchaseOrderItem
        $topProducts = PurchaseOrderItem::whereHas('purchaseOrder', fn ($q) => $q->whereBetween('order_date', [$this->dateFrom, $this->dateTo])
        )
            ->selectRaw('item_name, item_code, SUM(qty) as total_qty, SUM(unit_price * qty) as total_cost')
            ->groupBy('item_name', 'item_code')
            ->orderByDesc('total_cost')
            ->limit(5)
            ->get();

        // Daily trend
        $dailyTrend = PurchaseOrder::whereBetween('order_date', [$this->dateFrom, $this->dateTo])
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->selectRaw('DATE(order_date) as date, COUNT(*) as count, SUM(total_amount) as spend')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('livewire.reports.purchase-report',
            compact('orders', 'summary', 'topVendors', 'topProducts', 'dailyTrend'));
    }
}
