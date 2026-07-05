<?php

namespace App\Livewire\Sales;

use App\Models\Sale;
use Livewire\Component;
use Livewire\WithPagination;

class SaleList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $activeFilter = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function setActiveFilter(string $filter): void
    {
        $this->activeFilter = $this->activeFilter === $filter ? '' : $filter;
        $this->resetPage();
    }

    public function clearFilter(): void
    {
        $this->activeFilter = '';
        $this->resetPage();
    }

    public function render()
    {
        $today = now()->toDateString();

        $sales = Sale::with(['items'])
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q->where('customer_name', 'like', "%{$this->search}%")
                ->orWhere('customer_phone1', 'like', "%{$this->search}%")
                ->orWhere('customer_cnic', 'like', "%{$this->search}%")
                ->orWhere('bill_ref', 'like', "%{$this->search}%")
            ))
            ->when($this->dateFrom, fn ($q) => $q->whereRaw('DATE(sale_date) >= ?', [$this->dateFrom]))
            ->when($this->dateTo, fn ($q) => $q->whereRaw('DATE(sale_date) <= ?', [$this->dateTo]))
            // Status filters
            ->when(in_array($this->activeFilter, ['completed', 'pending', 'cancelled', 'refunded']),
                fn ($q) => $q->where('status', $this->activeFilter)
            )
            // Pickup filters — based on sale_items.pickup_date
            ->when($this->activeFilter === 'pickup_today', fn ($q) => $q->whereHas('items', fn ($q) => $q->whereDate('pickup_date', $today)
                ->where('pickup_status', '!=', 'taken')
            )
            )
            ->when($this->activeFilter === 'pickup_future', fn ($q) => $q->whereHas('items', fn ($q) => $q->whereDate('pickup_date', '>', $today)
                ->where('pickup_status', '!=', 'taken')
            )
            )
            ->when($this->activeFilter === 'pickup_overdue', fn ($q) => $q->whereHas('items', fn ($q) => $q->whereDate('pickup_date', '<', $today)
                ->where('pickup_status', '!=', 'taken')
            )
            )
            ->when($this->activeFilter === 'pickup_pending', fn ($q) => $q->whereHas('items', fn ($q) => $q->where('pickup_status', 'pending'))
                ->whereNotIn('status', ['cancelled', 'refunded'])
            )
            ->when($this->activeFilter === 'balance_due', fn ($q) => $q->where('remaining_balance', '>', 0)
                ->whereNotIn('status', ['cancelled', 'refunded'])
            )
            ->latest()
            ->paginate(15);

        $counts = [
            'completed' => Sale::where('status', 'completed')->count(),
            'pending' => Sale::where('status', 'pending')->count(),
            'cancelled' => Sale::where('status', 'cancelled')->count(),
            'refunded' => Sale::where('status', 'refunded')->count(),
            'pickup_today' => Sale::whereHas('items', fn ($q) => $q->whereDate('pickup_date', $today)->where('pickup_status', '!=', 'taken')
            )->count(),
            'pickup_future' => Sale::whereHas('items', fn ($q) => $q->whereDate('pickup_date', '>', $today)->where('pickup_status', '!=', 'taken')
            )->count(),
            'pickup_overdue' => Sale::whereHas('items', fn ($q) => $q->whereDate('pickup_date', '<', $today)->where('pickup_status', '!=', 'taken')
            )->count(),
            'pickup_pending' => Sale::whereHas('items', fn ($q) => $q->where('pickup_status', 'pending')
            )->whereNotIn('status', ['cancelled', 'refunded'])->count(),
            'balance_due' => Sale::where('remaining_balance', '>', 0)
                ->whereNotIn('status', ['cancelled', 'refunded'])->count(),
        ];

        return view('livewire.sales.sale-list', compact('sales', 'counts'));
    }
}
