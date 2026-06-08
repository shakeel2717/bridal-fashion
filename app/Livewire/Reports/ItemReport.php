<?php

namespace App\Livewire\Reports;

use App\Models\Product;
use App\Models\RentalItem;
use App\Models\SaleItem;
use Carbon\Carbon;
use Livewire\Component;

class ItemReport extends Component
{
    public string $productSearch = '';
    public array  $searchResults = [];
    public ?int   $selectedProductId = null;
    public ?array $selectedProduct   = null;

    public string $dateFrom = '';
    public string $dateTo   = '';

    public function mount(): void
    {
        $this->dateFrom = now()->startOfYear()->format('Y-m-d');
        $this->dateTo   = now()->format('Y-m-d');
    }

    public function searchProducts(): void
    {
        if (strlen($this->productSearch) < 2) {
            $this->searchResults = [];
            return;
        }

        $this->searchResults = Product::where(function($q) {
                $q->where('code', 'like', "%{$this->productSearch}%")
                  ->orWhere('name', 'like', "%{$this->productSearch}%");
            })
            ->limit(8)
            ->get(['id', 'code', 'name', 'type', 'rental_price', 'sale_price'])
            ->toArray();
    }

    public function selectProduct(int $id): void
    {
        $product = Product::with('category')->findOrFail($id);
        $this->selectedProductId = $id;
        $this->selectedProduct   = [
            'id'       => $product->id,
            'code'     => $product->code,
            'name'     => $product->name,
            'type'     => $product->type,
            'category' => $product->category->name ?? '',
            'photo'    => $product->photo,
        ];
        $this->productSearch = $product->code . ' — ' . $product->name;
        $this->searchResults = [];
    }

    public function render()
    {
        $rentalBookings = collect();
        $saleBookings   = collect();
        $stats          = [];

        if ($this->selectedProductId && $this->dateFrom && $this->dateTo) {
            $rentalBookings = RentalItem::with(['rental'])
                ->where('product_id', $this->selectedProductId)
                ->whereHas('rental', function($q) {
                    $q->whereRaw('DATE(booking_date) >= ?', [$this->dateFrom])
                      ->whereRaw('DATE(booking_date) <= ?', [$this->dateTo])
                      ->whereNotIn('status', ['cancelled', 'abandoned']);
                })
                ->get();

            $saleBookings = SaleItem::with(['sale'])
                ->where('product_id', $this->selectedProductId)
                ->whereHas('sale', function($q) {
                    $q->whereRaw('DATE(sale_date) >= ?', [$this->dateFrom])
                      ->whereRaw('DATE(sale_date) <= ?', [$this->dateTo])
                      ->whereNotIn('status', ['cancelled']);
                })
                ->get();

            $stats = [
                'total_rental_bookings' => $rentalBookings->count(),
                'total_sale_bookings'   => $saleBookings->count(),
                'rental_revenue'        => $rentalBookings->sum('rental_price'),
                'sale_revenue'          => $saleBookings->sum(fn($i) => $i->qty * $i->unit_price),
            ];
        }

        return view('livewire.reports.item-report',
            compact('rentalBookings', 'saleBookings', 'stats'));
    }
}