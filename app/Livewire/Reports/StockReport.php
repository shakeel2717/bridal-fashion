<?php

namespace App\Livewire\Reports;

use App\Models\Category;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class StockReport extends Component
{
    use WithPagination;

    public string $activeReport = '';

    public string $search = '';

    public string $filterType = '';

    public string $filterCategory = '';

    public string $filterStatus = 'active';

    public string $sortBy = 'code';

    public string $sortDir = 'asc';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterType(): void
    {
        $this->resetPage();
    }

    public function updatedFilterCategory(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function getReportMenu(): array
    {
        return [
            'inventory' => [
                'label' => 'Inventory',
                'icon' => 'bi-tags',
                'items' => [
                    'stock_list' => ['label' => 'Full Stock List',    'icon' => 'bi-list-ul'],
                    'zero_stock' => ['label' => 'Zero Stock Items',   'icon' => 'bi-exclamation-circle'],
                    'low_stock' => ['label' => 'Low Stock (≤ 2)',    'icon' => 'bi-arrow-down-circle'],
                    'by_category' => ['label' => 'By Category',        'icon' => 'bi-folder'],
                    'by_type' => ['label' => 'By Type',            'icon' => 'bi-grid-3x3'],
                ],
            ],
            'rental_stock' => [
                'label' => 'Rental Items',
                'icon' => 'bi-box-seam',
                'items' => [
                    'most_rented' => ['label' => 'Most Rented',        'icon' => 'bi-trophy'],
                    'never_rented' => ['label' => 'Never Rented',       'icon' => 'bi-slash-circle'],
                ],
            ],
            'write_offs' => [
                'label' => 'Write-offs',
                'icon' => 'bi-x-circle',
                'items' => [
                    'abandoned' => ['label' => 'Abandoned Items',    'icon' => 'bi-x-circle'],
                    'inactive' => ['label' => 'Inactive Items',     'icon' => 'bi-pause-circle'],
                ],
            ],
        ];
    }

    public function selectReport(string $report): void
    {
        $this->activeReport = $report;
        $this->search = '';
        $this->filterType = '';
        $this->filterCategory = '';
        $this->filterStatus = 'active';
        $this->sortBy = 'code';
        $this->sortDir = 'asc';
        $this->resetPage();
    }

    public function sortByColumn(string $col): void
    {
        $this->sortDir = $this->sortBy === $col && $this->sortDir === 'asc' ? 'desc' : 'asc';
        $this->sortBy = $col;
        $this->resetPage();
    }

    // ─── Summary (always shown) ───────────────────────────────────────────────

    public function getSummary(): array
    {
        return [
            'total' => Product::count(),
            'active' => Product::where('is_active', true)->where('is_abandoned', false)->count(),
            'abandoned' => Product::where('is_abandoned', true)->count(),
            'zero_stock' => Product::whereNotIn('type', ['fabric', 'service'])->where('stock_qty', 0)->where('is_abandoned', false)->count(),
            'rental' => Product::whereIn('type', ['rental', 'both'])->count(),
            'sale' => Product::whereIn('type', ['sale', 'both'])->count(),
        ];
    }

    // ─── Report builders ──────────────────────────────────────────────────────

    private function baseProducts()
    {
        $allowed = ['code', 'name', 'type', 'stock_qty', 'rental_price', 'sale_price'];
        $col = in_array($this->sortBy, $allowed) ? $this->sortBy : 'code';

        return Product::with(['category', 'group'])
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('code', 'like', "%{$this->search}%")
            ))
            ->when($this->filterType, fn ($q) => $q->where('type', $this->filterType))
            ->when($this->filterCategory, fn ($q) => $q->where('category_id', $this->filterCategory))
            ->orderBy($col, $this->sortDir);
    }

    private function reportStockList(): array
    {
        $allowed = ['code', 'name', 'type', 'stock_qty', 'rental_price', 'sale_price'];
        $col = in_array($this->sortBy, $allowed) ? $this->sortBy : 'code';

        $data = Product::with(['category', 'group'])
            ->withCount([
                'rentalItems as times_rented' => fn ($q) => $q->whereHas('rental', fn ($q) => $q->whereNotIn('status', ['cancelled', 'abandoned'])
                ),
                'saleItems as total_sold' => fn ($q) => $q->whereHas('sale', fn ($q) => $q->whereNotIn('status', ['cancelled'])
                ),
            ])
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                                ->orWhere('code', 'like', "%{$this->search}%")
            ))
            ->when($this->filterType, fn ($q) => $q->where('type', $this->filterType))
            ->when($this->filterCategory, fn ($q) => $q->where('category_id', $this->filterCategory))
            ->where('is_abandoned', false)
            ->when($this->filterStatus === 'active', fn ($q) => $q->where('is_active', true))
            ->when($this->filterStatus === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy($col, $this->sortDir)
            ->paginate(25);

        return [
            'type' => 'product_list',
            'title' => 'Full Stock List',
            'data' => $data,
            'show_filters' => true,
        ];
    }

    private function reportZeroStock(): array
    {
        return [
            'type' => 'product_list',
            'title' => 'Zero Stock Items',
            'data' => $this->baseProducts()
                ->where('is_abandoned', false)
                ->where('is_active', true)
                ->whereNotIn('type', ['fabric', 'service'])
                ->where('stock_qty', 0)
                ->paginate(25),
            'show_filters' => false,
        ];
    }

    private function reportLowStock(): array
    {
        return [
            'type' => 'product_list',
            'title' => 'Low Stock (≤ 2 units)',
            'data' => $this->baseProducts()
                ->where('is_abandoned', false)
                ->where('is_active', true)
                ->whereNotIn('type', ['fabric', 'service'])
                ->where('stock_qty', '<=', 2)
                ->where('stock_qty', '>', 0)
                ->paginate(25),
            'show_filters' => false,
        ];
    }

    private function reportByCategory(): array
    {
        return [
            'type' => 'category_breakdown',
            'title' => 'Stock by Category',
            'data' => Product::with('category:id,name,code')
                ->selectRaw('category_id, type, COUNT(*) as count, SUM(rental_price) as rental_value, SUM(sale_price) as sale_value, SUM(stock_qty) as total_stock')
                ->where('is_abandoned', false)
                ->where('is_active', true)
                ->when($this->filterType, fn ($q) => $q->where('type', $this->filterType))
                ->groupBy('category_id', 'type')
                ->orderBy('category_id')
                ->paginate(30),
        ];
    }

    private function reportByType(): array
    {
        return [
            'type' => 'type_breakdown',
            'title' => 'Stock by Type',
            'data' => Product::selectRaw('type, COUNT(*) as count, SUM(rental_price) as rental_value, SUM(sale_price) as sale_value, SUM(stock_qty) as total_stock')
                ->where('is_abandoned', false)
                ->where('is_active', true)
                ->groupBy('type')
                ->orderByDesc('count')
                ->get(),
        ];
    }

    private function reportMostRented(): array
    {
        return [
            'type' => 'most_rented',
            'title' => 'Most Rented Items (All Time)',
            'data' => Product::withCount(['rentalItems as times_rented'])
                ->with('category')
                ->whereIn('type', ['rental', 'both'])
                ->where('is_abandoned', false)
                ->when($this->search, fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('code', 'like', "%{$this->search}%")
                ))
                ->when($this->filterCategory, fn ($q) => $q->where('category_id', $this->filterCategory))
                ->orderByDesc('times_rented')
                ->paginate(25),
        ];
    }

    private function reportNeverRented(): array
    {
        return [
            'type' => 'product_list',
            'title' => 'Never Rented (Active Rental Items)',
            'data' => $this->baseProducts()
                ->whereIn('type', ['rental', 'both'])
                ->where('is_active', true)
                ->where('is_abandoned', false)
                ->whereDoesntHave('rentalItems')
                ->paginate(25),
            'show_filters' => false,
        ];
    }

    private function reportAbandoned(): array
    {
        return [
            'type' => 'abandoned_list',
            'title' => 'Abandoned / Written-off Items',
            'data' => Product::with('category')
                ->where('is_abandoned', true)
                ->when($this->search, fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('code', 'like', "%{$this->search}%")
                ))
                ->orderBy('code')
                ->paginate(25),
            'total_writeoff' => Product::where('is_abandoned', true)->sum('abandoned_price'),
        ];
    }

    private function reportInactive(): array
    {
        return [
            'type' => 'product_list',
            'title' => 'Inactive Items',
            'data' => $this->baseProducts()
                ->where('is_active', false)
                ->where('is_abandoned', false)
                ->paginate(25),
            'show_filters' => false,
        ];
    }

    public function render()
    {
        $menu = $this->getReportMenu();
        $summary = $this->getSummary();
        $categories = Category::orderBy('name')->get(['id', 'name', 'code']);
        $report = null;

        if ($this->activeReport) {
            $method = 'report'.str()->studly($this->activeReport);
            if (method_exists($this, $method)) {
                $report = $this->$method();
            }
        }

        return view('livewire.reports.stock-report', compact('menu', 'summary', 'categories', 'report'));
    }
}
