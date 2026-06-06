<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\ProductExpense;
use Livewire\Attributes\On;
use Livewire\Component;

class ProductExpenses extends Component
{
    public ?int    $productId   = null;
    public ?string $productName = null;
    public string  $amount      = '';
    public string  $expenseDate = '';
    public string  $note        = '';
    public ?int    $deleteId    = null;

    #[On('load-expenses-for')]
    public function loadExpenses(int $productId): void
    {
        $product            = Product::findOrFail($productId);
        $this->productId    = $productId;
        $this->productName  = $product->name . ' (' . $product->code . ')';
        $this->expenseDate  = now()->format('Y-m-d');
        $this->amount       = '';
        $this->note         = '';
        $this->deleteId     = null;
        $this->resetValidation();
    }

    public function saveExpense(): void
    {
        $this->validate([
            'amount'      => 'required|numeric|min:1',
            'expenseDate' => 'required|date',
            'note'        => 'nullable|string|max:500',
        ]);

        ProductExpense::create([
            'product_id'   => $this->productId,
            'amount'       => $this->amount,
            'expense_date' => $this->expenseDate,
            'note'         => $this->note ?: null,
            'created_by'   => auth()->id(),
            'updated_by'   => auth()->id(),
        ]);

        $this->amount      = '';
        $this->note        = '';
        $this->expenseDate = now()->format('Y-m-d');
        $this->resetValidation();
    }

    public function confirmDeleteExpense(int $id): void
    {
        $this->deleteId = $id;
    }

    public function deleteExpense(): void
    {
        ProductExpense::findOrFail($this->deleteId)->delete();
        $this->deleteId = null;
    }

    public function render()
    {
        $expenses = $this->productId
            ? ProductExpense::where('product_id', $this->productId)
                ->latest('expense_date')
                ->get()
            : collect();

        $totalExpenses = $expenses->sum('amount');

        return view('livewire.products.product-expenses', compact('expenses', 'totalExpenses'));
    }
}