<?php

namespace App\Livewire\Products;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductExpense;
use App\Models\ProductGroup;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ProductList extends Component
{
    use WithFileUploads, WithPagination;

    public string $filterGroup = '';

    // ── Edit Modal ────────────────────────────────────────
    public ?int $editId = null;

    public $editPhoto = null;

    public string $editName = '';

    public string $editCategoryId = '';

    public string $editGroupId = '';

    public string $editCode = '';

    public string $editColor = '';

    public string $editSize = '';

    public string $editSalePrice = '';

    public string $editRentalPrice = '';

    public string $editNotes = '';

    public bool $editIsActive = true;

    public bool $editIsAbandoned = false;

    public string $editAbandonedPrice = '';

    public string $editAbandonedDate = '';

    public string $editAbandonedNote = '';

    public ?string $editExistingPhoto = null;

    public string $editType = '';

    public string $editFabricUnit = 'meter';

    public string $filterStock = 'zero';

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

    public function updatedFilterStock(): void
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

    public function openEditModal(int $id): void
{
    $product = Product::findOrFail($id);
    $this->editId = $id;
    $this->editName = $product->name;
    $this->editCategoryId = (string) $product->category_id;
    $this->editGroupId = (string) ($product->group_id ?? '');
    $this->editCode = $product->code ?? '';
    $this->editColor = $product->color ?? '';
    $this->editSize = $product->size ?? '';
    $this->editSalePrice = (string) $product->sale_price;
    $this->editRentalPrice = (string) $product->rental_price;
    $this->editNotes = $product->notes ?? '';
    $this->editIsActive = $product->is_active;
    $this->editIsAbandoned = $product->is_abandoned;
    $this->editAbandonedPrice = (string) ($product->abandoned_price ?? '');
    $this->editAbandonedDate = $product->abandoned_date?->format('Y-m-d') ?? '';
    $this->editAbandonedNote = $product->abandoned_note ?? '';
    $this->editExistingPhoto = $product->photo;
    $this->editType = $product->type;
    $this->editFabricUnit = $product->fabric_unit ?? 'meter';
    $this->editPhoto = null;
    $this->resetValidation();
}

public function closeEditModal(): void
{
    $this->editId = null;
    $this->editPhoto = null;
    $this->resetValidation();
}

public function saveEdit(): void
{
    $this->validate([
        'editName'          => 'required|string|max:200',
        'editCategoryId'    => 'required|exists:categories,id',
        'editCode'          => 'required|string|max:50',
        'editSalePrice'     => 'nullable|numeric|min:0',
        'editRentalPrice'   => 'nullable|numeric|min:0',
        'editNotes'         => 'nullable|string|max:1000',
        'editPhoto'         => 'nullable|image|max:3072',
        'editAbandonedPrice'=> 'nullable|numeric|min:0',
        'editAbandonedDate' => 'nullable|date',
    ], [
        'editName.required'       => 'Name is required.',
        'editCategoryId.required' => 'Category is required.',
        'editCode.required'       => 'Code is required.',
    ]);

    $code = strtoupper(trim($this->editCode));
    $exists = Product::where('code', $code)
        ->where('id', '!=', $this->editId)
        ->exists();
    if ($exists) {
        $this->addError('editCode', "Code {$code} already exists.");
        return;
    }

    $photoPath = $this->editExistingPhoto;
    if ($this->editPhoto) {
        $photoPath = $this->editPhoto->store('products', 'public');
    }

    Product::findOrFail($this->editId)->update([
        'name'           => $this->editName,
        'category_id'    => $this->editCategoryId,
        'group_id'       => $this->editGroupId ?: null,
        'code'           => $code,
        'color'          => $this->editColor ?: null,
        'size'           => $this->editSize ?: null,
        'sale_price'     => $this->editSalePrice ?: 0,
        'rental_price'   => $this->editRentalPrice ?: 0,
        'notes'          => $this->editNotes ?: null,
        'is_active'      => $this->editIsActive,
        'is_abandoned'   => $this->editIsAbandoned,
        'abandoned_price'=> $this->editIsAbandoned ? ($this->editAbandonedPrice ?: 0) : 0,
        'abandoned_date' => $this->editIsAbandoned ? ($this->editAbandonedDate ?: null) : null,
        'abandoned_note' => $this->editIsAbandoned ? ($this->editAbandonedNote ?: null) : null,
        'fabric_unit'    => $this->editType === 'fabric' ? $this->editFabricUnit : null,
        'photo'          => $photoPath,
        'updated_by'     => auth()->id(),
    ]);

    $this->closeEditModal();
    session()->flash('success', 'Product updated.');
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
            ->when($this->filterStock === 'zero', fn ($q) => $q->where(function ($inner) {
                $inner->whereNotIn('type', ['fabric', 'service'])->where('stock_qty', 0);
            })->orWhere(function ($inner) {
                $inner->where('type', 'fabric')->where('stock_decimal', 0);
            })
            )
            ->when($this->filterStatus === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($this->filterStatus === 'abandoned', fn ($q) => $q->where('is_abandoned', true))
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
            'zero_stock' => Product::where(function ($q) {
                $q->whereNotIn('type', ['fabric', 'service'])->where('stock_qty', 0);
            })->orWhere(function ($q) {
                $q->where('type', 'fabric')->where('stock_decimal', 0);
            })->count(),
        ];

        $expenses = $this->expenseProductId
            ? ProductExpense::where('product_id', $this->expenseProductId)->latest('expense_date')->get()
            : collect();

        $totalExpenses = $expenses->sum('amount');

        return view('livewire.products.product-list',
            compact('products', 'categories', 'counts', 'expenses', 'totalExpenses', 'groups'));
    }
}
