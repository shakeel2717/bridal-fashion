<?php

namespace App\Livewire\Rentals;

use App\Models\Rental;
use App\Models\RentalItem;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class RentalList extends Component
{
    use WithPagination;

    public string $search = '';

    // One of: '', 'booked', 'ready', 'picked_up', 'partially_picked_up',
    // 'due', 'overpaid', 'late_pickup', 'late_return', 'no_dates',
    // 'duplicate', 'stitching', 'late_stitching'
    public string $activeFilter = '';

    /** Filters that are computed rather than a plain status match. */
    private const COMPUTED_FILTERS = [
        'due', 'overpaid', 'late_pickup', 'late_return',
        'no_dates', 'duplicate', 'stitching', 'late_stitching',
    ];

    public string $codeSearch = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public ?int $quickStatusId = null;

    public string $newStatus = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCodeSearch(): void
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

    /**
     * Rentals that really clash over the same product, as product_id => rental ids.
     *
     * Clash condition: A.pickup < B.return AND A.return > B.pickup. It is strict because an
     * item returned and sent out again on the same day is not a duplicate. Only the rentals
     * in a clash are returned, not every booking of that product, and items already back
     * with us don't count.
     */
    private function conflictingRentals(): Collection
    {
        return \DB::table('rental_items as ri1')
            ->join('rental_items as ri2', function ($join) {
                $join->on('ri1.product_id', '=', 'ri2.product_id')
                     ->whereColumn('ri1.id', '<', 'ri2.id')
                     ->whereColumn('ri1.rental_id', '!=', 'ri2.rental_id');
            })
            ->join('rentals as r1', 'ri1.rental_id', '=', 'r1.id')
            ->join('rentals as r2', 'ri2.rental_id', '=', 'r2.id')
            ->whereNull('r1.deleted_at')
            ->whereNull('r2.deleted_at')
            ->whereNotNull('ri1.product_id')
            ->where('ri1.pickup_status', '!=', 'returned')
            ->where('ri2.pickup_status', '!=', 'returned')
            ->whereNotIn('r1.status', ['returned', 'cancelled', 'abandoned'])
            ->whereNotIn('r2.status', ['returned', 'cancelled', 'abandoned'])
            ->whereNotNull('r1.pickup_date')->whereNotNull('r1.return_date')
            ->whereNotNull('r2.pickup_date')->whereNotNull('r2.return_date')
            ->whereColumn('r1.pickup_date', '<', 'r2.return_date')
            ->whereColumn('r1.return_date', '>', 'r2.pickup_date')
            ->get(['ri1.product_id', 'ri1.rental_id as rental_a', 'ri2.rental_id as rental_b'])
            ->groupBy('product_id')
            ->map(fn ($pairs) => $pairs->flatMap(fn ($pair) => [$pair->rental_a, $pair->rental_b])->unique()->values());
    }

    public function render()
    {
        $today = now()->toDateString();

        $conflicts = $this->conflictingRentals();
        $dupRentalIds = $conflicts->flatten()->unique()->values();

        $rentals = Rental::with(['items', 'employee', 'tasks' => fn ($q) => $q->where('type', 'stitching')])
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
            // Exact item code — unlike the main search, 101 does not also match 1010 or 2101
            ->when(trim($this->codeSearch) !== '', fn ($q) => $q->whereHas('items',
                fn ($q) => $q->whereRaw('LOWER(TRIM(product_code)) = ?', [strtolower(trim($this->codeSearch))])
            ))
            ->when($this->dateFrom, fn ($q) => $q->where('pickup_date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->where('pickup_date', '<=', $this->dateTo))
            // Special computed filters
            // Due = item is back with us but the customer still owes money.
            ->when($this->activeFilter === 'due', function ($q) {
                $q->where('status', 'returned')
                    ->whereRaw('(SELECT COALESCE(SUM(amount), 0) FROM rental_payments WHERE rental_payments.rental_id = rentals.id) < rentals.total_amount - 1');
            })
            // Stitching alert = booking still needs stitching (not marked Done, not closed out).
            ->when($this->activeFilter === 'stitching', fn ($q) => $q->stitchingDue())
            // Late stitching = same, and the stitching date has passed.
            ->when($this->activeFilter === 'late_stitching', fn ($q) => $q->stitchingDue()->where('stitching_date', '<', $today))
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
            ->when($this->activeFilter === 'duplicate', fn ($q) => $q->whereIn('id', $dupRentalIds))
            ->when(
                $this->activeFilter && ! in_array($this->activeFilter, self::COMPUTED_FILTERS, true),
                fn ($q) => $q->where('status', $this->activeFilter)
            )
            // Stitching view is date-driven — soonest first is what the tailor needs.
            ->when(
                in_array($this->activeFilter, ['stitching', 'late_stitching'], true),
                fn ($q) => $q->orderBy('stitching_date'),
                fn ($q) => $q->latest()
            )
            ->paginate(15);

        $counts = [
            'booked' => Rental::where('status', 'booked')->withCount('items')->get()->sum('items_count'),
            'ready' => Rental::where('status', 'ready')->withCount('items')->get()->sum('items_count'),
            'picked_up' => Rental::where('status', 'picked_up')->withCount('items')->get()->sum('items_count'),
            'partially_picked_up' => Rental::where('status', 'partially_picked_up')->withCount('items')->get()->sum('items_count'),
            'due' => Rental::where('status', 'returned')
                ->whereRaw('(SELECT COALESCE(SUM(amount), 0) FROM rental_payments WHERE rental_payments.rental_id = rentals.id) < rentals.total_amount - 1')
                ->withCount('items')->get()->sum('items_count'),
            'stitching' => Rental::stitchingDue()
                ->withCount('items')->get()->sum('items_count'),
            'late_stitching' => Rental::stitchingDue()
                ->where('stitching_date', '<', $today)
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
            'duplicate' => $dupRentalIds->count(),
        ];

        // Duplicate bookings: load only the rentals in a clash for display
        $duplicateBookings = RentalItem::with('product:id,name,code')
            ->whereIn('product_id', $conflicts->keys())
            ->whereIn('rental_id', $dupRentalIds)
            ->get()
            ->filter(fn ($item) => $conflicts[$item->product_id]->contains($item->rental_id))
            ->groupBy('product_id')
            ->take(5);

        return view('livewire.rentals.rental-list', compact('rentals', 'counts', 'duplicateBookings'));
    }
}
