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
use Livewire\Component;

class SaleCreate extends Component
{
    public string $advanceAccountId = '';

    public string $phone1Gender = 'male';

    public string $phone2Gender = 'male';

    // Customer
    public string $customerType = 'existing';

    public string $customerSearch = '';

    public ?int $customerId = null;

    public string $customerName = '';

    public string $customerPhone1 = '';

    public string $customerPhone2 = '';

    public string $customerCnic = '';

    public string $deliveryAddress = '';

    public ?array $foundCustomers = null;

    // Sale details
    public string $billRef = '';

    public string $saleDate = '';

    public string $employeeId = '';

    public string $notes = '';

    // Items
    public string $productSearch = '';

    public array $searchResults = [];

    public array $items = [];

    // Payment
    public string $totalAmount = '0';

    public string $advancePaid = '0';

    public function mount(): void
    {
        $this->saleDate = now()->format('Y-m-d');
        $defaultAccount = Account::where('is_default', true)->first()
            ?? Account::where('is_active', true)->first();
        $this->advanceAccountId = $defaultAccount ? (string) $defaultAccount->id : '';
    }

    // ── Customer ──────────────────────────────────────────
    public function setCustomerType(string $type): void
    {
        $this->customerType = $type;
        $this->customerId = null;
        $this->customerName = '';
        $this->customerPhone1 = '';
        $this->customerPhone2 = '';
        $this->customerCnic = '';
        $this->deliveryAddress = '';
        $this->customerSearch = '';
        $this->foundCustomers = null;
    }

    public function setGender(string $field, string $gender): void
    {
        $this->$field = $gender;
    }

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
        $customer = Customer::findOrFail($id);
        $this->customerId = $id;
        $this->customerName = $customer->name;
        $this->customerPhone1 = $customer->phone1;
        $this->customerPhone2 = $customer->phone2 ?? '';
        $this->customerCnic = $customer->cnic ?? '';
        $this->deliveryAddress = $customer->address ?? '';
        $this->customerSearch = $customer->name;
        $this->foundCustomers = null;
    }

    // ── Products ──────────────────────────────────────────
    public function searchProducts(): void
    {
        if (strlen($this->productSearch) < 2) {
            $this->searchResults = [];

            return;
        }

        $alreadyAdded = collect($this->items)->pluck('product_id')->toArray();

        $this->searchResults = Product::with('category')
            ->where('is_active', true)
            ->where('is_abandoned', false)
            ->whereIn('type', ['sale', 'both'])
            ->where('stock_qty', '>', 0)
            ->where(function ($q) {
                $q->where('code', 'like', "%{$this->productSearch}%")
                    ->orWhere('name', 'like', "%{$this->productSearch}%");
            })
            ->whereNotIn('id', $alreadyAdded)
            ->limit(8)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'code' => $p->code,
                'name' => $p->name,
                'sale_price' => $p->sale_price,
                'stock_qty' => $p->stock_qty,
                'size' => $p->size,
                'category' => $p->category->name ?? '',
            ])
            ->toArray();
    }

    public function addItem(int $productId): void
    {
        $product = Product::with('category')->findOrFail($productId);

        $this->items[] = [
            'product_id' => $product->id,
            'code' => $product->code,
            'name' => $product->name,
            'category' => $product->category->name ?? '',
            'size' => $product->size ?? '',
            'sale_price' => (string) $product->sale_price,
            'max_qty' => $product->stock_qty,
            'qty' => 1,
            'addons' => [],
        ];

        $this->productSearch = '';
        $this->searchResults = [];
        $this->recalcTotal();
    }

    public function removeItem(int $index): void
    {
        array_splice($this->items, $index, 1);
        $this->recalcTotal();
    }

    public function addAddon(int $itemIndex): void
    {
        $this->items[$itemIndex]['addons'][] = ['label' => '', 'price' => '0'];
    }

    public function removeAddon(int $itemIndex, int $addonIndex): void
    {
        array_splice($this->items[$itemIndex]['addons'], $addonIndex, 1);
        $this->recalcTotal();
    }

    public function recalcTotal(): void
    {
        $total = 0;
        foreach ($this->items as $item) {
            $qty = (int) ($item['qty'] ?? 1);
            $price = (float) ($item['sale_price'] ?? 0);
            $total += $price * $qty;
            foreach ($item['addons'] ?? [] as $addon) {
                $total += (float) ($addon['price'] ?? 0);
            }
        }
        $this->totalAmount = (string) $total;
    }

    // ── Save ──────────────────────────────────────────────
    public function save(): void
    {
        $this->validate([
            'customerName' => 'required|string|max:150',
            'customerPhone1' => 'required|string|max:20',
            'saleDate' => 'required|date',
            'advancePaid' => 'required|numeric|min:0',
            'employeeId' => 'nullable|exists:users,id',
        ]);

        if (empty($this->items)) {
            $this->addError('items', 'Please add at least one item.');

            return;
        }

        $this->recalcTotal();
        $total = (float) $this->totalAmount;
        $advance = (float) $this->advancePaid;
        $remaining = max(0, $total - $advance);

        $customerId = $this->customerId;
        if ($this->customerType === 'walkin' || ! $customerId) {
            $walkIn = Customer::where('is_walkin', true)->first();
            $customerId = $walkIn?->id;
        }

        $sale = Sale::create([
            'bill_ref' => $this->billRef ?: null,
            'customer_id' => $customerId,
            'customer_name' => $this->customerName,
            'customer_phone1' => $this->customerPhone1,
            'customer_phone2' => $this->customerPhone2 ?: null,
            'customer_cnic' => $this->customerCnic ?: null,
            'delivery_address' => $this->deliveryAddress ?: null,
            'sale_date' => Carbon::parse($this->saleDate)->toDateString(),
            'advance_payment_method' => Account::find($this->advanceAccountId)?->name ?? 'cash',
            'status' => 'completed',
            'total_amount' => $total,
            'phone1_gender' => $this->phone1Gender,
            'phone2_gender' => $this->phone2Gender,
            'advance_paid' => $advance,
            'remaining_balance' => $remaining,
            'employee_id' => $this->employeeId ?: null,
            'notes' => $this->notes ?: null,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        foreach ($this->items as $item) {
            $addonLabels = collect($item['addons'] ?? [])
                ->filter(fn ($a) => ! empty($a['label']))
                ->map(fn ($a) => $a['label'])
                ->join(', ');

            $addonTotal = collect($item['addons'] ?? [])
                ->sum(fn ($a) => (float) ($a['price'] ?? 0));

            $qty = (int) ($item['qty'] ?? 1);

            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $item['product_id'],
                'product_name' => $item['name'],
                'product_code' => $item['code'],
                'sale_price' => (float) $item['sale_price'],
                'qty' => $qty,
                'custom_option_label' => $addonLabels ?: null,
                'custom_option_price' => $addonTotal,
            ]);

            // Deduct stock
            Product::where('id', $item['product_id'])
                ->decrement('stock_qty', $qty);
        }

        if ($advance > 0 && $this->advanceAccountId) {
            AccountService::credit(
                (int) $this->advanceAccountId,
                $advance,
                'sale_payment',
                "Sale advance — {$this->customerName} (#{$sale->id})",
                now()->toDateString(),
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
            ->get(['id', 'name', 'type']);

        return view('livewire.sales.sale-create', compact('employees', 'accounts'));
    }
}
