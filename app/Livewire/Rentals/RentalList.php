<?php

namespace App\Livewire\Rentals;

use App\Models\Rental;
use App\Models\RentalItem;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class RentalList extends Component
{
    use WithPagination;

    public string $search        = '';
    public string $filterStatus  = '';
    public string $filterDate    = '';
    public string $dateFrom      = '';
    public string $dateTo        = '';
    public ?int   $quickStatusId = null;
    public string $newStatus     = '';

    public function updatedSearch(): void       { $this->resetPage(); }
    public function updatedFilterStatus(): void { $this->resetPage(); }

    public function quickStatus(int $id, string $status): void
    {
        $this->quickStatusId = $id;
        $this->newStatus     = $status;
    }

    public function applyStatus(): void
    {
        $rental = Rental::findOrFail($this->quickStatusId);

        $data = [
            'status'     => $this->newStatus,
            'updated_by' => auth()->id(),
        ];

        // Auto-set pickup timestamps on items
        if ($this->newStatus === 'picked_up') {
            $rental->items()->where('pickup_status', 'pending')
                ->update(['pickup_status' => 'picked_up', 'picked_up_at' => now()]);
        }

        if ($this->newStatus === 'returned') {
            $rental->items()->whereIn('pickup_status', ['picked_up', 'pending'])
                ->update([
                    'pickup_status'       => 'returned',
                    'returned_at'         => now(),
                    'returned_received_by' => auth()->id(),
                ]);
        }

        $rental->update($data);

        $this->quickStatusId = null;
        $this->newStatus     = '';
        session()->flash('success', 'Rental status updated.');
    }

    public function render()
    {
        $rentals = Rental::with(['items', 'employee'])
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('customer_name', 'like', "%{$this->search}%")
                      ->orWhere('customer_phone1', 'like', "%{$this->search}%")
                      ->orWhere('customer_cnic', 'like', "%{$this->search}%")
                      ->orWhere('bill_ref', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->dateFrom, fn($q) => $q->where('booking_date', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->where('booking_date', '<=', $this->dateTo))
            ->latest()
            ->paginate(15);

        $counts = [
            'booked'              => Rental::where('status', 'booked')->count(),
            'ready'               => Rental::where('status', 'ready')->count(),
            'picked_up'           => Rental::where('status', 'picked_up')->count(),
            'partially_picked_up' => Rental::where('status', 'partially_picked_up')->count(),
            'returned'            => Rental::where('status', 'returned')->count(),
            'overdue'             => Rental::overdue()->count(),
        ];

        return view('livewire.rentals.rental-list', compact('rentals', 'counts'));
    }
}