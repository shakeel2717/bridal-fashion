<?php

namespace App\Livewire\Products;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductExpense;
use App\Models\ProductGroup;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class ProductList extends Component
{
    use WithPagination;

    public string $filterGroup = '';

    public string $search = '';

    public string $filterType = '';

    public string $filterCategory = '';

    public string $filterStatus = 'active';

    public ?int $deleteId = null;

    // Expense modal state
    public ?int $expenseProductId = null;

    public ?string $expenseProductName = null;

    public string $expenseAmount = '';

    public string $expenseDate = '';

    public string $expenseNote = '';

    public ?int $expenseDeleteId = null;

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

    // ── Product Delete ────────────────────────────────────
    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
    }

    public function delete(): void
    {
        $product = Product::findOrFail($this->deleteId);

        if ($product->rentalItems()->count() > 0 || $product->saleItems()->count() > 0) {
            session()->flash('error', 'Cannot delete — product has rental/sale records.');
            $this->deleteId = null;

            return;
        }

        $product->delete();
        $this->deleteId = null;
        session()->flash('success', 'Product deleted.');
    }

    // ── Expenses ──────────────────────────────────────────
    public function openExpenses(int $id): void
    {
        $product = Product::findOrFail($id);
        $this->expenseProductId = $id;
        $this->expenseProductName = $product->name.' ('.$product->code.')';
        $this->expenseAmount = '';
        $this->expenseDate = now()->format('Y-m-d');
        $this->expenseNote = '';
        $this->expenseDeleteId = null;
        $this->resetValidation();
        $this->dispatch('show-expenses-modal');
    }

    public function saveExpense(): void
    {
        $this->validate([
            'expenseAmount' => 'required|numeric|min:1',
            'expenseDate' => 'required|date',
            'expenseNote' => 'nullable|string|max:500',
        ], [
            'expenseAmount.required' => 'Amount is required.',
            'expenseAmount.numeric' => 'Amount must be a number.',
            'expenseAmount.min' => 'Amount must be at least 1.',
            'expenseDate.required' => 'Date is required.',
        ]);

        ProductExpense::create([
            'product_id' => $this->expenseProductId,
            'amount' => $this->expenseAmount,
            'expense_date' => $this->expenseDate,
            'note' => $this->expenseNote ?: null,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        $this->expenseAmount = '';
        $this->expenseNote = '';
        $this->expenseDate = now()->format('Y-m-d');
        $this->resetValidation();
    }

    public function confirmDeleteExpense(int $id): void
    {
        $this->expenseDeleteId = $id;
    }

    public function deleteExpense(): void
    {
        ProductExpense::findOrFail($this->expenseDeleteId)->delete();
        $this->expenseDeleteId = null;
    }

    public function closeExpenses(): void
    {
        $this->expenseProductId = null;
        $this->expenseProductName = null;
        $this->expenseAmount = '';
        $this->expenseDate = '';
        $this->expenseNote = '';
        $this->expenseDeleteId = null;
        $this->resetValidation();
    }

    #[On('product-saved')]
    public function productSaved(): void
    {
        $this->resetPage();
    }

    public function updatedFilterGroup(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $products = Product::with(['category', 'vendor', 'group'])
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('code', 'like', "%{$this->search}%")
                        ->orWhere('size', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterType, fn ($q) => $q->where('type', $this->filterType))
            ->when($this->filterCategory, fn ($q) => $q->where('category_id', $this->filterCategory))
            ->when($this->filterStatus === 'active', fn ($q) => $q->where('is_active', true)->where('is_abandoned', false))
            ->when($this->filterStatus === 'abandoned', fn ($q) => $q->where('is_abandoned', true))
            ->when($this->filterStatus === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($this->filterGroup, fn ($q) => $q->where('group_id', $this->filterGroup))
            ->latest()
            ->paginate(15);

        $categories = Category::active()->orderBy('name')->get();

        $groups = ProductGroup::orderBy('name')->get();

        $counts = [
            'active' => Product::where('is_active', true)->where('is_abandoned', false)->count(),
            'abandoned' => Product::where('is_abandoned', true)->count(),
            'inactive' => Product::where('is_active', false)->count(),
            'total' => Product::count(),
        ];

        $expenses = $this->expenseProductId
            ? ProductExpense::where('product_id', $this->expenseProductId)->latest('expense_date')->get()
            : collect();

        $totalExpenses = $expenses->sum('amount');

        return view('livewire.products.product-list',
            compact('products', 'categories', 'counts', 'expenses', 'totalExpenses', 'groups'));
    }
}
