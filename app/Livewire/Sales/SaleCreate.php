<?php

namespace App\Livewire\Sales;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Services\AccountService;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;

class SaleCreate extends Component
{
    // ── Customer ──────────────────────────────────────────
    public ?int $customerId = null;

    public string $customerSearch = '';

    public ?array $foundCustomers = null;

    // ── Header ────────────────────────────────────────────
    public string $saleDate = '';

    public string $billRef = '';

    public string $employeeId = '';

    // ── Product search row ────────────────────────────────
    public string $productSearch = '';

    public array $searchResults = [];

    public ?int $pendingProductId = null;

    public string $pendingProductName = '';

    public string $pendingProductCode = '';

    public string $newItemQty = '1';

    public string $newItemPrice = '0';

    // ── Items table ───────────────────────────────────────
    public array $items = [];

    // ── Payment ───────────────────────────────────────────
    public string $discount = '0';

    public string $advancePaid = '0';

    public string $paymentDate = '';

    public string $advanceAccountId = '';

    public function mount(): void
    {
        $this->saleDate    = now()->format('Y-m-d');
        $this->paymentDate = now()->format('Y-m-d');

        // Auto-select Walk-in Customer
        $walkin = Customer::where('is_walkin', true)->first();
        if ($walkin) {
            $this->customerId     = $walkin->id;
            $this->customerSearch = $walkin->name;
        }

        $defaultAccount = Account::where('is_default', true)->first()
            ?? Account::where('is_active', true)->first();
        $this->advanceAccountId = $defaultAccount ? (string) $defaultAccount->id : '';
    }

    // ── Customer Search ───────────────────────────────────
    public function searchCustomers(): void
    {
        if (strlen($this->customerSearch) < 2) {
            $this->foundCustomers = null;
            return;
        }

        $this->foundCustomers = Customer::where('is_walkin', false)
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->customerSearch}%")
                    ->orWhere('phone1', 'like', "%{$this->customerSearch}%")
                    ->orWhere('cnic', 'like', "%{$this->customerSearch}%");
            })
            ->limit(6)
            ->get(['id', 'name', 'phone1', 'cnic'])
            ->toArray();
    }

    public function selectCustomer(int $id): void
    {
        $customer             = Customer::findOrFail($id);
        $this->customerId     = $id;
        $this->customerSearch = $customer->name;
        $this->foundCustomers = null;
    }

    public function clearCustomer(): void
    {
        $walkin = Customer::where('is_walkin', true)->first();
        $this->customerId     = $walkin?->id;
        $this->customerSearch = $walkin?->name ?? 'Walk-in Customer';
        $this->foundCustomers = null;
    }

    // ── Product Search (PO-style with groups) ─────────────
    public function selectProductForRow(int $productId): void
    {
        $product = Product::findOrFail($productId);

        $this->pendingProductId   = $product->id;
        $this->pendingProductName = $product->name;
        $this->pendingProductCode = $product->code;
        $this->newItemPrice       = (string) $product->sale_price;
        $this->productSearch      = $product->code.' — '.$product->name;
        $this->searchResults      = [];
        $this->dispatch('focus-sale-qty');
    }

    public function addItemToTable(): void
    {
        if (! $this->pendingProductId) {
            return;
        }

        $qty   = max(1, (int) $this->newItemQty);
        $price = max(0, (float) $this->newItemPrice);

        array_unshift($this->items, [
            'product_id'  => $this->pendingProductId,
            'item_name'   => $this->pendingProductName,
            'item_code'   => $this->pendingProductCode,
            'qty'         => $qty,
            'unit_price'  => (string) $price,
            'total_price' => (string) ($qty * $price),
        ]);

        $this->productSearch      = '';
        $this->searchResults      = [];
        $this->newItemQty         = '1';
        $this->newItemPrice       = '0';
        $this->pendingProductId   = null;
        $this->pendingProductName = '';
        $this->pendingProductCode = '';
        $this->dispatch('focus-sale-search');
    }

    public function searchProducts(): void
    {
        if (strlen($this->productSearch) < 2) {
            $this->searchResults = [];
            return;
        }

        $alreadyAdded = collect($this->items)->pluck('product_id')->filter()->toArray();

        // Direct matches (exact code OR name partial)
        $directMatches = Product::with(['category', 'group'])
            ->where('is_active', true)
            ->where('is_abandoned', false)
            ->whereIn('type', ['sale', 'both'])
            ->where('stock_qty', '>', 0)
            ->where(function ($q) {
                $q->where('code', $this->productSearch)
                    ->orWhere('name', 'like', "%{$this->productSearch}%");
            })
            ->whereNotIn('id', $alreadyAdded)
            ->get();

        // Group siblings
        $groupIds = $directMatches
            ->whereNotNull('group_id')
            ->pluck('group_id')
            ->unique()->filter()->toArray();

        $groupProducts = collect();
        if (! empty($groupIds)) {
            $groupProducts = Product::with(['category', 'group'])
                ->where('is_active', true)
                ->where('is_abandoned', false)
                ->whereIn('type', ['sale', 'both'])
                ->where('stock_qty', '>', 0)
                ->whereIn('group_id', $groupIds)
                ->whereNotIn('id', $directMatches->pluck('id')->toArray())
                ->whereNotIn('id', $alreadyAdded)
                ->get();
        }

        $all      = $directMatches->merge($groupProducts)->take(12);
        $directIds = $directMatches->pluck('id')->toArray();

        $this->searchResults = $all->map(fn ($p) => [
            'id'             => $p->id,
            'code'           => $p->code,
            'name'           => $p->name,
            'sale_price'     => $p->sale_price,
            'stock_qty'      => $p->stock_qty,
            'category'       => $p->category->name ?? '',
            'group'          => $p->group->name ?? null,
            'is_direct'      => in_array($p->id, $directIds),
        ])->toArray();
    }

    public function addProduct(int $productId): void
    {
        $this->selectProductForRow($productId);
    }

    public function removeItem(int $index): void
    {
        array_splice($this->items, $index, 1);
    }

    public function recalcItems(): void
    {
        foreach ($this->items as $i => $item) {
            $qty   = max(1, (int) ($item['qty'] ?? 1));
            $price = max(0, (float) ($item['unit_price'] ?? 0));
            $this->items[$i]['total_price'] = (string) ($qty * $price);
        }
    }

    public function updatedItems(): void
    {
        $this->recalcItems();
    }

    #[Computed]
    public function subtotal(): float
    {
        return collect($this->items)->sum(fn ($i) => (float) ($i['total_price'] ?? 0));
    }

    #[Computed]
    public function total(): float
    {
        return max(0, $this->subtotal - (float) $this->discount);
    }

    #[Computed]
    public function balanceDue(): float
    {
        return max(0, $this->total - (float) $this->advancePaid);
    }

    // ── Save ──────────────────────────────────────────────
    public function save(): void
    {
        $this->validate([
            'customerId' => 'required|exists:customers,id',
            'saleDate'   => 'required|date',
            'advancePaid' => 'nullable|numeric|min:0',
            'employeeId'  => 'nullable|exists:users,id',
        ]);

        if (empty($this->items)) {
            $this->addError('items', 'Please add at least one item.');
            return;
        }

        $customer  = Customer::findOrFail($this->customerId);
        $total     = $this->total;
        $advance   = (float) $this->advancePaid;
        $remaining = max(0, $total - $advance);

        $sale = Sale::create([
            'bill_ref'               => $this->billRef ?: null,
            'customer_id'            => $this->customerId,
            'customer_name'          => $customer->name,
            'customer_phone1'        => $customer->phone1 ?? '0000-0000000',
            'customer_phone2'        => $customer->phone2 ?? null,
            'customer_cnic'          => $customer->cnic ?? null,
            'delivery_address'       => $customer->address ?? null,
            'sale_date'              => Carbon::parse($this->saleDate)->toDateString(),
            'advance_payment_method' => Account::find($this->advanceAccountId)?->name ?? 'cash',
            'status'                 => 'completed',
            'total_amount'           => $total,
            'discount'               => (float) $this->discount,
            'advance_paid'           => $advance,
            'remaining_balance'      => $remaining,
            'employee_id'            => $this->employeeId ?: null,
            'created_by'             => auth()->id(),
            'updated_by'             => auth()->id(),
        ]);

        foreach ($this->items as $item) {
            $qty = (int) ($item['qty'] ?? 1);

            SaleItem::create([
                'sale_id'      => $sale->id,
                'product_id'   => $item['product_id'],
                'product_name' => $item['item_name'],
                'product_code' => $item['item_code'],
                'sale_price'   => (float) $item['unit_price'],
                'qty'          => $qty,
            ]);

            Product::where('id', $item['product_id'])
                ->decrement('stock_qty', $qty);
        }

        if ($advance > 0 && $this->advanceAccountId) {
            AccountService::credit(
                (int) $this->advanceAccountId,
                $advance,
                'sale_payment',
                "Sale advance — {$customer->name} (#{$sale->id})",
                Carbon::parse($this->paymentDate)->toDateString(),
                $sale,
            );
        }

        session()->flash('success', "Sale #{$sale->id} recorded successfully.");
        $this->redirect(route('sales.show', $sale->id));
    }

    public function render()
    {
        $employees = User::where('is_active', true)
            ->orderBy('name')->get(['id', 'name']);

        $accounts = Account::where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.sales.sale-create', compact('employees', 'accounts'));
    }
}
