<?php

namespace App\Livewire\Rentals;

use App\Models\Rental;
use App\Models\RentalItem;
use Livewire\Component;
use Livewire\WithPagination;

class RentalList extends Component
{
    use WithPagination;

    public string $search = '';

    // One of: '', 'booked', 'ready', 'picked_up', 'partially_picked_up',
    // 'returned', 'cancelled', 'due', 'overpaid', 'late_pickup', 'late_return', 'no_dates'
    public string $activeFilter = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public ?int $quickStatusId = null;

    public string $newStatus = '';

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

    public function quickStatus(int $id, string $status): void
    {
        $this->quickStatusId = $id;
        $this->newStatus = $status;
    }

    public function applyStatus(): void
    {
        $rental = Rental::findOrFail($this->quickStatusId);

        $data = [
            'status' => $this->newStatus,
            'updated_by' => auth()->id(),
        ];

        if ($this->newStatus === 'picked_up') {
            $rental->items()->where('pickup_status', 'pending')
                ->update(['pickup_status' => 'picked_up', 'picked_up_at' => now()]);
        }

        if ($this->newStatus === 'returned') {
            $rental->items()->whereIn('pickup_status', ['picked_up', 'pending'])
                ->update([
                    'pickup_status' => 'returned',
                    'returned_at' => now(),
                    'returned_received_by' => auth()->id(),
                ]);
        }

        $rental->update($data);

        $this->quickStatusId = null;
        $this->newStatus = '';
        session()->flash('success', 'Rental status updated.');
    }

    public function render()
    {
        $today = now()->toDateString();

        $rentals = Rental::with(['items', 'employee'])
            ->withSum('payments', 'amount')
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('customer_name', 'like', "%{$this->search}%")
                        ->orWhere('customer_phone1', 'like', "%{$this->search}%")
                        ->orWhere('customer_cnic', 'like', "%{$this->search}%")
                        ->orWhere('bill_ref', 'like', "%{$this->search}%")
                        ->orWhereHas('items', function ($q) {
                            $q->where('product_code', 'like', "%{$this->search}%")
                                ->orWhere('product_name', 'like', "%{$this->search}%");
                        });
                });
            })
            ->when($this->dateFrom, fn ($q) => $q->where('booking_date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->where('booking_date', '<=', $this->dateTo))
            // Special computed filters
            ->when($this->activeFilter === 'due', function ($q) {
                $q->whereNotIn('status', ['returned', 'cancelled', 'abandoned'])
                    ->whereRaw('(SELECT COALESCE(SUM(amount), 0) FROM rental_payments WHERE rental_payments.rental_id = rentals.id) < rentals.total_amount - 1');
            })
            ->when($this->activeFilter === 'overpaid', function ($q) {
                $q->whereRaw('(SELECT COALESCE(SUM(amount), 0) FROM rental_payments WHERE rental_payments.rental_id = rentals.id) > rentals.total_amount');
            })
            ->when($this->activeFilter === 'late_pickup', function ($q) use ($today) {
                $q->whereNotNull('pickup_date')
                    ->where('pickup_date', '<', $today)
                    ->whereIn('status', ['booked', 'ready']);
            })
            ->when($this->activeFilter === 'late_return', function ($q) use ($today) {
                $q->whereNotNull('return_date')
                    ->where('return_date', '<', $today)
                    ->whereNotIn('status', ['returned', 'cancelled', 'abandoned']);
            })
            ->when($this->activeFilter === 'no_dates', function ($q) {
                $q->whereNotIn('status', ['returned', 'cancelled', 'abandoned'])
                    ->where(function ($q) {
                        $q->whereNull('pickup_date')->orWhereNull('return_date');
                    });
            })
            ->when($this->activeFilter === 'fined', function ($q) {
                $q->whereHas('tasks', fn ($q) => $q->where('type', 'fine'));
            })
            ->when(
                $this->activeFilter && ! in_array($this->activeFilter, ['due', 'overpaid', 'late_pickup', 'late_return', 'no_dates', 'fined']),
                fn ($q) => $q->where('status', $this->activeFilter)
            )
            ->latest()
            ->paginate(15);

        $counts = [
            'booked' => Rental::where('status', 'booked')->withCount('items')->get()->sum('items_count'),
            'ready' => Rental::where('status', 'ready')->withCount('items')->get()->sum('items_count'),
            'picked_up' => Rental::where('status', 'picked_up')->withCount('items')->get()->sum('items_count'),
            'partially_picked_up' => Rental::where('status', 'partially_picked_up')->withCount('items')->get()->sum('items_count'),
            'returned' => Rental::where('status', 'returned')->withCount('items')->get()->sum('items_count'),
            'cancelled' => Rental::where('status', 'cancelled')->withCount('items')->get()->sum('items_count'),
            'due' => Rental::whereNotIn('status', ['returned', 'cancelled', 'abandoned'])
                ->whereRaw('(SELECT COALESCE(SUM(amount), 0) FROM rental_payments WHERE rental_payments.rental_id = rentals.id) < rentals.total_amount - 1')
                ->withCount('items')->get()->sum('items_count'),
            'overpaid' => Rental::whereRaw('(SELECT COALESCE(SUM(amount), 0) FROM rental_payments WHERE rental_payments.rental_id = rentals.id) > rentals.total_amount')
                ->withCount('items')->get()->sum('items_count'),
            'late_pickup' => Rental::whereNotNull('pickup_date')
                ->where('pickup_date', '<', $today)
                ->whereIn('status', ['booked', 'ready'])
                ->withCount('items')->get()->sum('items_count'),
            'late_return' => Rental::whereNotNull('return_date')
                ->where('return_date', '<', $today)
                ->whereNotIn('status', ['returned', 'cancelled', 'abandoned'])
                ->withCount('items')->get()->sum('items_count'),
            'no_dates' => Rental::whereNotIn('status', ['returned', 'cancelled', 'abandoned'])
                ->where(function ($q) {
                    $q->whereNull('pickup_date')->orWhereNull('return_date');
                })
                ->withCount('items')->get()->sum('items_count'),
            'fined' => Rental::whereHas('tasks', fn ($q) => $q->where('type', 'fine'))
                ->withCount('items')->get()->sum('items_count'),
        ];

        // Duplicate bookings: same product active in multiple rentals
        $duplicateBookings = RentalItem::with(['product:id,name,code', 'rental:id,customer_name,pickup_date,return_date,status,bill_ref'])
            ->whereHas('rental', fn ($q) => $q->whereNotIn('status', ['returned', 'cancelled', 'abandoned']))
            ->get()
            ->groupBy('product_id')
            ->filter(fn ($group) => $group->count() > 1)
            ->take(5);

        return view('livewire.rentals.rental-list', compact('rentals', 'counts', 'duplicateBookings'));
    }
}
