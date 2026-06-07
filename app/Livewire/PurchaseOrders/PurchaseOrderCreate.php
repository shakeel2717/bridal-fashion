<?php

namespace App\Livewire\PurchaseOrders;

use App\Models\Account;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseOrderPayment;
use App\Models\Vendor;
use App\Services\AccountService;
use Carbon\Carbon;
use Livewire\Component;

class PurchaseOrderCreate extends Component
{
    public string $vendorId = '';

    public string $vendorBillNumber = '';

    public string $orderDate = '';

    public string $expectedDate = '';

    public string $notes = '';

    public string $discount = '0';

    public string $initialPaymentAccountId = '';

    public string $initialPayment = '0';

    public string $paymentMethod = 'cash';

    public array $items = [];

    public string $productSearch = '';

    public array $searchResults = [];

    // Inline vendor
    public bool $showVendorForm = false;

    public string $newVendorName = '';

    public string $newVendorPhone = '';

    public function mount(): void
    {
        $this->orderDate = now()->format('Y-m-d');
        $defaultAccount = Account::where('is_default', true)->first()
    ?? Account::where('is_active', true)->first();
        $this->initialPaymentAccountId = $defaultAccount ? (string) $defaultAccount->id : '';
    }

    public function searchProducts(): void
    {
        if (strlen($this->productSearch) < 2) {
            $this->searchResults = [];

            return;
        }

        $alreadyAdded = collect($this->items)->pluck('product_id')->filter()->toArray();

        $this->searchResults = Product::with('category')
            ->where('is_active', true)
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
                'purchase_price' => $p->purchase_price,
                'category' => $p->category->name ?? '',
            ])
            ->toArray();
    }

    public function addProduct(int $productId): void
    {
        $product = Product::findOrFail($productId);

        $this->items[] = [
            'product_id' => $product->id,
            'item_name' => $product->name,
            'item_code' => $product->code,
            'qty' => 1,
            'unit_price' => (string) $product->purchase_price,
            'total_price' => (string) $product->purchase_price,
            'notes' => '',
        ];

        $this->productSearch = '';
        $this->searchResults = [];
    }

    public function addCustomItem(): void
    {
        $this->items[] = [
            'product_id' => null,
            'item_name' => '',
            'item_code' => '',
            'qty' => 1,
            'unit_price' => '0',
            'total_price' => '0',
            'notes' => '',
        ];
    }

    public function removeItem(int $index): void
    {
        array_splice($this->items, $index, 1);
    }

    public function updatedItems(): void
    {
        foreach ($this->items as $i => $item) {
            $qty = (int) ($item['qty'] ?? 1);
            $price = (float) ($item['unit_price'] ?? 0);
            $this->items[$i]['total_price'] = (string) ($qty * $price);
        }
    }

    public function getSubtotalProperty(): float
    {
        return collect($this->items)->sum(fn ($i) => (float) ($i['total_price'] ?? 0));
    }

    public function getTotalProperty(): float
    {
        return max(0, $this->subtotal - (float) $this->discount);
    }

    public function getBalanceDueProperty(): float
    {
        return max(0, $this->total - (float) $this->initialPayment);
    }

    // Inline vendor
    public function saveVendor(): void
    {
        $this->validate([
            'newVendorName' => 'required|string|max:150',
        ]);

        $vendor = Vendor::create([
            'name' => $this->newVendorName,
            'phone' => $this->newVendorPhone ?: null,
            'is_active' => true,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        $this->vendorId = (string) $vendor->id;
        $this->showVendorForm = false;
        $this->newVendorName = '';
        $this->newVendorPhone = '';
    }

    public function save(string $status = 'ordered'): void
    {
        $this->validate([
            'vendorId' => 'required|exists:vendors,id',
            'orderDate' => 'required|date',
            'initialPayment' => 'nullable|numeric|min:0',
        ]);

        if (empty($this->items)) {
            $this->addError('items', 'Please add at least one item.');

            return;
        }

        // Validate items
        foreach ($this->items as $i => $item) {
            if (empty($item['item_name'])) {
                $this->addError("items.{$i}.item_name", 'Item name required.');

                return;
            }
        }

        // Generate PO number
        $lastPo = PurchaseOrder::latest()->first();
        $poNumber = 'PO-'.str_pad(($lastPo ? $lastPo->id + 1 : 1), 4, '0', STR_PAD_LEFT);

        $total = $this->total;
        $paid = (float) $this->initialPayment;
        $balance = max(0, $total - $paid);

        $po = PurchaseOrder::create([
            'po_number' => $poNumber,
            'vendor_bill_number' => $this->vendorBillNumber ?: null,
            'vendor_id' => $this->vendorId,
            'order_date' => Carbon::parse($this->orderDate)->toDateString(),
            'expected_date' => $this->expectedDate ? Carbon::parse($this->expectedDate)->toDateString() : null,
            'status' => $status,
            'total_amount' => $total,
            'amount_paid' => $paid,
            'balance_due' => $balance,
            'discount' => (float) $this->discount,
            'notes' => $this->notes ?: null,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        foreach ($this->items as $item) {
            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'product_id' => $item['product_id'] ?: null,
                'item_name' => $item['item_name'],
                'item_code' => $item['item_code'] ?: null,
                'qty' => (int) ($item['qty'] ?? 1),
                'unit_price' => (float) ($item['unit_price'] ?? 0),
                'total_price' => (float) ($item['total_price'] ?? 0),
                'received_qty' => 0,
                'returned_qty' => 0,
                'notes' => $item['notes'] ?: null,
            ]);
        }

        if ($paid > 0) {
            PurchaseOrderPayment::create([
                'purchase_order_id' => $po->id,
                'amount' => $paid,
                'payment_date' => now()->toDateString(),
                'payment_method' => $this->paymentMethod,
                'type' => 'payment',
                'note' => 'Initial payment on order',
                'created_by' => auth()->id(),
            ]);

            if ($paid > 0 && ! empty($this->initialPaymentAccountId)) {
                AccountService::debit(
                    (int) $this->initialPaymentAccountId,
                    $paid,
                    'vendor_payment',
                    "PO initial payment — {$po->vendor->name} ({$po->po_number})",
                    now()->toDateString(),
                    $po,
                );
            }
        }

        session()->flash('success', "Purchase Order {$poNumber} created.");
        $this->redirect(route('purchase-orders.show', $po->id));
    }

    public function render()
    {
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();

        $accounts = Account::where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.purchase-orders.purchase-order-create',
            compact('vendors', 'accounts'));
    }
}
