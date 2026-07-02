<?php

namespace App\Livewire\Reports;

use App\Models\Product;
use App\Models\Category;
use App\Models\RentalItem;
use App\Models\SaleItem;
use Livewire\Component;
use Livewire\WithPagination;

class StockReport extends Component
{
    use WithPagination;

    public string $activeView    = 'stock';   // stock | movement | abandoned
    public string $search        = '';
    public string $filterType    = '';
    public string $filterCategory = '';
    public string $filterStatus  = 'active';  // active | inactive | abandoned | all

    public function updatedSearch(): void        { $this->resetPage(); }
    public function updatedFilterType(): void    { $this->resetPage(); }
    public function updatedFilterCategory(): void { $this->resetPage(); }
    public function updatedFilterStatus(): void  { $this->resetPage(); }
    public function setView(string $view): void  { $this->activeView = $view; $this->resetPage(); }

    private function baseQuery()
    {
        return Product::with(['category', 'group'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('code', 'like', "%{$this->search}%");
            }))
            ->when($this->filterType, fn ($q) => $q->where('type', $this->filterType))
            ->when($this->filterCategory, fn ($q) => $q->where('category_id', $this->filterCategory))
            ->when($this->filterStatus === 'active',    fn ($q) => $q->where('is_active', true)->where('is_abandoned', false))
            ->when($this->filterStatus === 'inactive',  fn ($q) => $q->where('is_active', false))
            ->when($this->filterStatus === 'abandoned', fn ($q) => $q->where('is_abandoned', true));
    }

    public function render()
    {
        $categories = Category::active()->orderBy('name')->get();

        // ── Stock View ─────────────────────────────────────
        $products = $this->baseQuery()
            ->when($this->activeView === 'abandoned', fn ($q) => $q->where('is_abandoned', true))
            ->orderBy('code')
            ->paginate(25);

        $all = $this->baseQuery()->get();

        $summary = [
            'total_products'  => Product::count(),
            'active'          => Product::where('is_active', true)->where('is_abandoned', false)->count(),
            'abandoned'       => Product::where('is_abandoned', true)->count(),
            'zero_stock'      => Product::where(function ($q) {
                                    $q->whereNotIn('type', ['fabric', 'service'])->where('stock_qty', 0);
                                 })->orWhere(function ($q) {
                                    $q->where('type', 'fabric')->where('stock_decimal', 0);
                                 })->count(),
            'rental_items'    => Product::whereIn('type', ['rental', 'both'])->count(),
            'sale_items'      => Product::whereIn('type', ['sale', 'both'])->count(),
            'total_abandoned_value' => Product::where('is_abandoned', true)->sum('abandoned_price'),
            'total_rental_value'    => Product::whereIn('type', ['rental', 'both'])->sum('rental_price'),
        ];

        // ── Movement: most / least rented ─────────────────
        $mostRented = Product::withCount(['rentalItems as times_rented'])
            ->whereIn('type', ['rental', 'both'])
            ->where('is_abandoned', false)
            ->orderByDesc('times_rented')
            ->limit(10)
            ->get();

        $neverRented = Product::whereDoesntHave('rentalItems')
            ->whereIn('type', ['rental', 'both'])
            ->where('is_active', true)
            ->where('is_abandoned', false)
            ->orderBy('code')
            ->limit(10)
            ->get();

        // ── Category breakdown ─────────────────────────────
        $categoryBreakdown = Product::selectRaw('category_id, COUNT(*) as count, SUM(rental_price) as total_rental_value')
            ->with('category:id,name,code')
            ->where('is_abandoned', false)
            ->where('is_active', true)
            ->groupBy('category_id')
            ->orderByDesc('count')
            ->get();

        // ── Type breakdown ─────────────────────────────────
        $typeBreakdown = Product::selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->orderByDesc('count')
            ->get();

        return view('livewire.reports.stock-report',
            compact('products', 'summary', 'categories', 'mostRented',
                    'neverRented', 'categoryBreakdown', 'typeBreakdown'));
    }
}