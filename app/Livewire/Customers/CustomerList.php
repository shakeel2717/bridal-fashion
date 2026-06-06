<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filter = 'regular'; // regular | walkin | all
    public ?int $deleteId = null;
    public ?int $viewId = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
    }

    public function delete(): void
    {
        $customer = Customer::findOrFail($this->deleteId);

        if ($customer->is_walkin) {
            session()->flash('error', 'Walk-in system record cannot be deleted.');
            $this->deleteId = null;
            return;
        }

        $customer->delete();
        $this->deleteId = null;
        session()->flash('success', 'Customer deleted successfully.');
    }

    public function openView(int $id): void
    {
        $this->viewId = $id;
        $this->dispatch('open-customer-view', id: $id);
    }

    #[On('customer-saved')]
    public function customerSaved(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Customer::withCount(['rentals', 'sales'])
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('phone1', 'like', "%{$this->search}%")
                      ->orWhere('phone2', 'like', "%{$this->search}%")
                      ->orWhere('cnic', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filter === 'regular', fn($q) => $q->regular())
            ->when($this->filter === 'walkin', fn($q) => $q->walkin())
            ->latest();

        $customers = $query->paginate(15);

        $counts = [
            'regular' => Customer::regular()->count(),
            'walkin'  => Customer::walkin()->count(),
            'all'     => Customer::count(),
        ];

        return view('livewire.customers.customer-list', compact('customers', 'counts'));
    }
}