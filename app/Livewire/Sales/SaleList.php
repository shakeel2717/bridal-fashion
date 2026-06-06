<?php

namespace App\Livewire\Sales;

use App\Models\Sale;
use Livewire\Component;
use Livewire\WithPagination;

class SaleList extends Component
{
    use WithPagination;

    public string $search       = '';
    public string $filterStatus = '';
    public string $dateFrom     = '';
    public string $dateTo       = '';

    public function updatedSearch(): void       { $this->resetPage(); }
    public function updatedFilterStatus(): void { $this->resetPage(); }

    public function render()
    {
        $sales = Sale::with(['items', 'employee'])
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('customer_name', 'like', "%{$this->search}%")
                      ->orWhere('customer_phone1', 'like', "%{$this->search}%")
                      ->orWhere('customer_cnic', 'like', "%{$this->search}%")
                      ->orWhere('bill_ref', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->dateFrom, fn($q) => $q->whereRaw('DATE(sale_date) >= ?', [$this->dateFrom]))
            ->when($this->dateTo,   fn($q) => $q->whereRaw('DATE(sale_date) <= ?', [$this->dateTo]))
            ->latest()
            ->paginate(15);

        $counts = [
            'completed' => Sale::where('status', 'completed')->count(),
            'pending'   => Sale::where('status', 'pending')->count(),
            'cancelled' => Sale::where('status', 'cancelled')->count(),
            'refunded'  => Sale::where('status', 'refunded')->count(),
            'total'     => Sale::count(),
        ];

        return view('livewire.sales.sale-list', compact('sales', 'counts'));
    }
}