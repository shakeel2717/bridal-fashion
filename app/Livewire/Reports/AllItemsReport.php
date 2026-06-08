<?php

namespace App\Livewire\Reports;

use App\Models\Category;
use App\Models\Product;
use App\Models\RentalItem;
use App\Models\SaleItem;
use Carbon\Carbon;
use Livewire\Component;

class AllItemsReport extends Component
{
    public string $dateFrom      = '';
    public string $dateTo        = '';
    public string $filterCategory = '';
    public string $filterType    = 'rental';

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo   = now()->format('Y-m-d');
    }

    public function render()
    {
        $categories = Category::active()->orderBy('name')->get();

        // Get all rental bookings in date range grouped by product
        $rentalData = RentalItem::with(['product.category', 'rental'])
            ->whereHas('rental', function($q) {
                $q->whereRaw('DATE(booking_date) >= ?', [$this->dateFrom])
                  ->whereRaw('DATE(booking_date) <= ?', [$this->dateTo])
                  ->whereNotIn('status', ['cancelled', 'abandoned']);
            })
            ->when($this->filterCategory, function($q) {
                $q->whereHas('product', fn($p) =>
                    $p->where('category_id', $this->filterCategory)
                );
            })
            ->get()
            ->groupBy('product_id')
            ->map(function($group) {
                $product = $group->first()->product;
                return [
                    'product_id'   => $product?->id,
                    'code'         => $product?->code,
                    'name'         => $product?->name,
                    'category'     => $product?->category?->name ?? '—',
                    'type'         => $product?->type,
                    'booking_count'=> $group->count(),
                    'revenue'      => $group->sum('rental_price'),
                ];
            })
            ->sortByDesc('booking_count')
            ->values();

        // Category summary
        $categorySummary = $rentalData->groupBy('category')
            ->map(fn($g) => [
                'category'      => $g->first()['category'],
                'total_bookings'=> $g->sum('booking_count'),
                'total_revenue' => $g->sum('revenue'),
                'item_count'    => $g->count(),
            ])
            ->sortByDesc('total_bookings')
            ->values();

        $totalBookings = $rentalData->sum('booking_count');
        $totalRevenue  = $rentalData->sum('revenue');

        return view('livewire.reports.all-items-report',
            compact('rentalData', 'categorySummary',
                    'totalBookings', 'totalRevenue', 'categories'));
    }
}