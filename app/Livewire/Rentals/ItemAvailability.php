<?php

namespace App\Livewire\Rentals;

use App\Models\Product;
use App\Models\RentalItem;
use Livewire\Component;

class ItemAvailability extends Component
{
    public string $productSearch = '';

    // future | past | active
    public string $timeFilter = 'future';

    public array $suggestions = [];

    public ?int $selectedProductId = null;

    public ?array $selectedProduct = null;

    public function setFilter(string $filter): void
    {
        if (in_array($filter, ['future', 'past', 'active'])) {
            $this->timeFilter = $filter;
        }
    }

    public function updatedProductSearch(): void
    {
        // If the user edits the box, drop the locked selection and show suggestions
        $this->selectedProductId = null;
        $this->selectedProduct = null;

        if (strlen(trim($this->productSearch)) < 2) {
            $this->suggestions = [];

            return;
        }

        $this->suggestions = Product::where(function ($q) {
            $q->where('code', 'like', "%{$this->productSearch}%")
                ->orWhere('name', 'like', "%{$this->productSearch}%");
        })
            ->orderBy('code')
            ->limit(8)
            ->get(['id', 'code', 'name'])
            ->toArray();
    }

    public function selectProduct(int $id): void
    {
        $product = Product::find($id);
        if (! $product) {
            return;
        }
        $this->selectedProductId = $id;
        $this->selectedProduct = [
            'code' => $product->code,
            'name' => $product->name,
            'photo' => $product->photo,
        ];
        $this->productSearch = $product->code.' — '.$product->name;
        $this->suggestions = [];
    }

    public function clearSelection(): void
    {
        $this->reset(['productSearch', 'selectedProductId', 'selectedProduct', 'suggestions']);
    }

    public function render()
    {
        $today = now()->toDateString();
        $bookings = collect();

        // Resolve which products to match: a locked selection, or a free-text search
        $hasQuery = $this->selectedProductId || strlen(trim($this->productSearch)) >= 2;

        if ($hasQuery) {
            $items = RentalItem::with('rental')
                ->when($this->selectedProductId,
                    fn ($q) => $q->where('product_id', $this->selectedProductId),
                    fn ($q) => $q->where(function ($q) {
                        $q->where('product_code', 'like', "%{$this->productSearch}%")
                            ->orWhere('product_name', 'like', "%{$this->productSearch}%");
                    })
                )
                ->whereHas('rental', function ($q) use ($today) {
                    $q->whereNotIn('status', ['cancelled', 'abandoned']);

                    if ($this->timeFilter === 'future') {
                        $q->whereNotNull('pickup_date')->whereDate('pickup_date', '>=', $today);
                    } elseif ($this->timeFilter === 'past') {
                        $q->whereNotNull('return_date')->whereDate('return_date', '<', $today);
                    } else { // active
                        $q->whereNotNull('pickup_date')->whereNotNull('return_date')
                            ->whereDate('pickup_date', '<=', $today)
                            ->whereDate('return_date', '>=', $today);
                    }
                })
                ->get();

            $bookings = $items
                ->sortBy(fn ($i) => $i->rental->pickup_date ?? $i->rental->booking_date)
                ->values();
        }

        return view('livewire.rentals.item-availability', compact('bookings', 'hasQuery'));
    }
}
