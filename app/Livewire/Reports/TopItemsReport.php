<?php

namespace App\Livewire\Reports;

use App\Models\Product;
use App\Models\RentalItem;
use App\Models\SaleItem;
use Livewire\Component;

class TopItemsReport extends Component
{
    public string $dateFrom   = '';
    public string $dateTo     = '';
    public string $filterType = 'rental';
    public int    $limit      = 10;

    public function mount(): void
    {
        $this->dateFrom = now()->startOfYear()->format('Y-m-d');
        $this->dateTo   = now()->format('Y-m-d');
    }

    public function render()
    {
        if ($this->filterType === 'rental') {
            $data = RentalItem::with(['product.category'])
                ->whereHas('rental', function($q) {
                    $q->whereRaw('DATE(booking_date) >= ?', [$this->dateFrom])
                      ->whereRaw('DATE(booking_date) <= ?', [$this->dateTo])
                      ->whereNotIn('status', ['cancelled', 'abandoned']);
                })
                ->get()
                ->groupBy('product_id')
                ->map(function($group) {
                    $product = $group->first()->product;
                    return [
                        'code'          => $product?->code ?? '—',
                        'name'          => $product?->name ?? 'Deleted Product',
                        'category'      => $product?->category?->name ?? '—',
                        'booking_count' => $group->count(),
                        'revenue'       => $group->sum('rental_price'),
                    ];
                })
                ->sortByDesc('booking_count')
                ->values();
        } else {
            $data = SaleItem::with(['product.category'])
                ->whereHas('sale', function($q) {
                    $q->whereRaw('DATE(sale_date) >= ?', [$this->dateFrom])
                      ->whereRaw('DATE(sale_date) <= ?', [$this->dateTo])
                      ->whereNotIn('status', ['cancelled']);
                })
                ->get()
                ->groupBy('product_id')
                ->map(function($group) {
                    $product = $group->first()->product;
                    return [
                        'code'          => $product?->code ?? '—',
                        'name'          => $product?->name ?? 'Deleted Product',
                        'category'      => $product?->category?->name ?? '—',
                        'booking_count' => $group->sum('qty'),
                        'revenue'       => $group->sum(fn($i) => $i->qty * $i->unit_price),
                    ];
                })
                ->sortByDesc('booking_count')
                ->values();
        }

        $topItems    = $data->take($this->limit);
        $lowestItems = $data->sortBy('booking_count')->take($this->limit)->values();

        return view('livewire.reports.top-items-report',
            compact('topItems', 'lowestItems'));
    }
}