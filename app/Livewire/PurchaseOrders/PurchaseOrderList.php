<?php

namespace App\Livewire\PurchaseOrders;

use App\Models\PurchaseOrder;
use App\Models\Vendor;
use Livewire\Component;
use Livewire\WithPagination;

class PurchaseOrderList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterStatus = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $filterVendor = '';

    public bool $hasSearched = false;

    public function updatedSearch(): void
    {
        $this->hasSearched = true;
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->hasSearched = true;
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->hasSearched = true;
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->hasSearched = true;
        $this->resetPage();
    }

    public function updatedFilterVendor(): void
    {
        $this->hasSearched = true;
        $this->resetPage();
    }

    public function render()
    {
        $orders = PurchaseOrder::with(['vendor', 'items'])
            ->when(! $this->hasSearched && ! $this->search && ! $this->filterStatus
                   && ! $this->filterVendor && ! $this->dateFrom,
                fn ($q) => $q->whereRaw('1=0') // return nothing by default
            )
            ->when($this->search, fn ($q) => $q->where('po_number', 'like', "%{$this->search}%")
                ->orWhere('vendor_bill_number', 'like', "%{$this->search}%")
                ->orWhereHas('vendor', fn ($v) => $v->where('name', 'like', "%{$this->search}%")
                )
            )
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->dateFrom, fn ($q) => $q->whereRaw('DATE(order_date) >= ?', [$this->dateFrom])
            )
            ->when($this->dateTo, fn ($q) => $q->whereRaw('DATE(order_date) <= ?', [$this->dateTo])
            )
            ->when($this->filterVendor, fn ($q) => $q->where('vendor_id', $this->filterVendor))
            ->latest()
            ->paginate(15);

        $vendors = Vendor::orderBy('name')->get(['id', 'name']);

        $counts = [
            'draft' => PurchaseOrder::where('status', 'draft')->count(),
            'ordered' => PurchaseOrder::where('status', 'ordered')->count(),
            'partial' => PurchaseOrder::where('status', 'partial')->count(),
            'received' => PurchaseOrder::where('status', 'received')->count(),
            'total' => PurchaseOrder::count(),
        ];

        $totalBalance = PurchaseOrder::whereNotIn('status', ['cancelled'])
            ->sum('balance_due');

        return view('livewire.purchase-orders.purchase-order-list',
            compact('orders', 'vendors', 'counts', 'totalBalance'));
    }
}
