<?php

namespace App\Livewire\PurchaseOrders;

use App\Models\PurchaseOrder;
use Livewire\Component;
use Livewire\WithPagination;

class PurchaseOrderList extends Component
{
    use WithPagination;

    public string $search       = '';
    public string $filterStatus = '';
    public string $filterVendor = '';

    public function updatedSearch(): void       { $this->resetPage(); }
    public function updatedFilterStatus(): void { $this->resetPage(); }
    public function updatedFilterVendor(): void { $this->resetPage(); }

    public function render()
    {
        $orders = PurchaseOrder::with(['vendor', 'items'])
            ->when($this->search, fn($q) =>
                $q->where('po_number', 'like', "%{$this->search}%")
                  ->orWhere('vendor_bill_number', 'like', "%{$this->search}%")
                  ->orWhereHas('vendor', fn($v) =>
                      $v->where('name', 'like', "%{$this->search}%")
                  )
            )
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterVendor, fn($q) => $q->where('vendor_id', $this->filterVendor))
            ->latest()
            ->paginate(15);

        $vendors = \App\Models\Vendor::orderBy('name')->get(['id', 'name']);

        $counts = [
            'draft'     => PurchaseOrder::where('status', 'draft')->count(),
            'ordered'   => PurchaseOrder::where('status', 'ordered')->count(),
            'partial'   => PurchaseOrder::where('status', 'partial')->count(),
            'received'  => PurchaseOrder::where('status', 'received')->count(),
            'total'     => PurchaseOrder::count(),
        ];

        $totalBalance = PurchaseOrder::whereNotIn('status', ['cancelled'])
            ->sum('balance_due');

        return view('livewire.purchase-orders.purchase-order-list',
            compact('orders', 'vendors', 'counts', 'totalBalance'));
    }
}