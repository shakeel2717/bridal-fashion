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
use Livewire\Attributes\Computed;
use Livewire\Component;

class PurchaseOrderCreate extends Component
{
    public ?int $purchaseOrderId = null;

    public bool $isEditMode = false;

    public string $vendorId = '';

    public string $vendorBillNumber = '';

    public string $newItemQty = '1';

    public ?int $pendingProductId = null;

    public string $pendingProductName = '';

    public string $pendingProductCode = '';

    public string $newItemPrice = '0';

    public string $orderDate = '';

    public string $notes = '';

    public string $discount = '0';

    public string $initialPaymentAccountId = '';

    public string $initialPaymentDate = '';

    public string $initialPayment = '0';

    public string $paymentMethod = 'cash';

    public array $items = [];

    public string $productSearch = '';

    public array $searchResults = [];

    // Inline vendor
    public bool $showVendorForm = false;

    public string $newVendorName = '';

    public string $newVendorPhone = '';

    public function mount(?PurchaseOrder $purchaseOrder = null): void
    {
        if ($purchaseOrder && $purchaseOrder->exists) {
            $this->isEditMode = true;
            $this->purchaseOrderId = $purchaseOrder->id;

            $this->vendorId = (string) $purchaseOrder->vendor_id;
            $this->vendorBillNumber = $purchaseOrder->vendor_bill_number ?? '';
            $this->orderDate = Carbon::parse($purchaseOrder->order_date)->format('Y-m-d');
            $this->discount = (string) $purchaseOrder->discount;
            $this->notes = $purchaseOrder->notes ?? '';

            // Load items
            $this->items = $purchaseOrder->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'item_name'  => $item->item_name,
                'item_code'  => $item->item_code ?? '',
                'qty'        => (string) $item->qty,
                'unit_price' => (string) $item->unit_price,
                'total_price' => (string) $item->total_price,
                'notes'      => $item->notes ?? '',
            ])->toArray();

            // In edit mode, don't pre-fill payment fields — payments are managed on detail page
            $this->initialPayment = '0';
            $this->initialPaymentDate = now()->format('Y-m-d');
        } else {
            $this->orderDate = now()->format('Y-m-d');
            $this->initialPaymentDate = now()->format('Y-m-d');
        }

        $this->newItemQty = '1';
        $this->newItemPrice = '0';
        $defaultAccount = Account::where('is_default', true)->first()
            ?? Account::where('is_active', true)->first();
        $this->initialPaymentAccountId = $defaultAccount ? (string) $defaultAccount->id : '';
    }

    public function selectProductForRow(int $productId): void
    {
        $product = Product::findOrFail($productId);

        $this->pendingProductId = $product->id;
        $this->pendingProductName = $product->name;
        $this->pendingProductCode = $product->code;
        $this->newItemPrice = (string) $product->purchase_price;
        $this->productSearch = $product->code.' — '.$product->name;
        $this->searchResults = [];
        $this->dispatch('focus-po-qty');
    }

    public function addItemToTable(): void
    {
        if (! $this->pendingProductId && empty($this->productSearch)) {
            return;
        }

        $qty = max(1, (int) $this->newItemQty);
        $price = max(0, (float) $this->newItemPrice);

        $productId = $this->pendingProductId;
        $productName = $this->pendingProductName;
        $productCode = $this->pendingProductCode;

        if (! $productId) {
            return;
        }

        array_unshift($this->items, [
            'product_id' => $productId,
            'item_name' => $productName,
            'item_code' => $productCode,
            'qty' => $qty,
            'unit_price' => (string) $price,
            'total_price' => (string) ($qty * $price),
            'notes' => '',
        ]);

        $this->productSearch = '';
        $this->searchResults = [];
        $this->newItemQty = '1';
        $this->newItemPrice = '0';
        $this->pendingProductId = null;
        $this->pendingProductName = '';
        $this->pendingProductCode = '';
        $this->recalcItems();
        $this->dispatch('focus-po-search');
    }

    public function searchProducts(): void
    {
        if (strlen($this->productSearch) < 2) {
            $this->searchResults = [];

            return;
        }

        $alreadyAdded = collect($this->items)->pluck('product_id')->filter()->toArray();

        $directMatches = Product::with(['category', 'group'])
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('code', $this->productSearch)
                    ->orWhere('name', 'like', "%{$this->productSearch}%");
            })
            ->whereNotIn('id', $alreadyAdded)
            ->get();

        $groupIds = $directMatches
            ->whereNotNull('group_id')
            ->pluck('group_id')
            ->unique()
            ->filter()
            ->toArray();

        $groupProducts = collect();
        if (! empty($groupIds)) {
            $groupProducts = Product::with(['category', 'group'])
                ->where('is_active', true)
                ->whereIn('group_id', $groupIds)
                ->whereNotIn('id', $directMatches->pluck('id')->toArray())
                ->whereNotIn('id', $alreadyAdded)
                ->get();
        }

        $all = $directMatches->merge($groupProducts)->take(12);
        $directIds = $directMatches->pluck('id')->toArray();

        $this->searchResults = $all->map(fn ($p) => [
            'id' => $p->id,
            'code' => $p->code,
            'name' => $p->name,
            'purchase_price' => $p->purchase_price,
            'category' => $p->category->name ?? '',
            'group' => $p->group->name ?? null,
            'is_direct' => in_array($p->id, $directIds),
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

    public function updatedItems(): void
    {
        foreach ($this->items as $i => $item) {
            $qty = max(1, (int) ($item['qty'] ?? 1));
            $price = max(0, (float) ($item['unit_price'] ?? 0));
            $this->items[$i]['total_price'] = (string) ($qty * $price);
        }
    }

    public function recalcItems(): void
    {
        foreach ($this->items as $i => $item) {
            $qty = max(1, (int) ($item['qty'] ?? 1));
            $price = max(0, (float) ($item['unit_price'] ?? 0));
            $this->items[$i]['total_price'] = (string) ($qty * $price);
        }
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
        return max(0, $this->total - (float) $this->initialPayment);
    }

    public function saveVendor(): void
    {
        $this->validate([
            'newVendorName' => 'required|string|max:150',
        ], [
            'newVendorName.required' => 'Vendor name is required.',
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
        $this->resetValidation();
    }

    public function save(string $status = 'ordered'): void
    {
        $this->validate([
            'vendorId' => 'required|exists:vendors,id',
            'orderDate' => 'required|date',
        ]);

        if (empty($this->items)) {
            $this->addError('items', 'Please add at least one item.');

            return;
        }

        // Duplicate bill number check (exclude current PO in edit mode)
        $duplicateQuery = PurchaseOrder::where('vendor_id', $this->vendorId)
            ->where('vendor_bill_number', $this->vendorBillNumber);

        if ($this->isEditMode) {
            $duplicateQuery->where('id', '!=', $this->purchaseOrderId);
        }

        if ($duplicateQuery->exists()) {
            $this->addError('vendorBillNumber',
                'A PO with this vendor and bill number already exists. Please verify.');

            return;
        }

        foreach ($this->items as $i => $item) {
            if (empty($item['item_name'])) {
                $this->addError("items.{$i}.item_name", 'Item name required.');

                return;
            }
        }

        $total = $this->total;

        if ($this->isEditMode) {
            $po = PurchaseOrder::findOrFail($this->purchaseOrderId);

            $alreadyPaid = $po->amount_paid;
            $balance = max(0, $total - $alreadyPaid);

            $po->update([
                'vendor_id'          => $this->vendorId,
                'vendor_bill_number' => $this->vendorBillNumber ?: null,
                'order_date'         => Carbon::parse($this->orderDate)->toDateString(),
                'expected_date'      => Carbon::parse($this->orderDate)->toDateString(),
                'status'             => $status,
                'total_amount'       => $total,
                'balance_due'        => $balance,
                'discount'           => (float) $this->discount,
                'notes'              => $this->notes ?: null,
                'updated_by'         => auth()->id(),
            ]);

            // Replace items
            $po->items()->delete();
            foreach ($this->items as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id'        => $item['product_id'] ?: null,
                    'item_name'         => $item['item_name'],
                    'item_code'         => $item['item_code'] ?: null,
                    'qty'               => (int) ($item['qty'] ?? 1),
                    'unit_price'        => (float) ($item['unit_price'] ?? 0),
                    'total_price'       => (float) ($item['total_price'] ?? 0),
                    'received_qty'      => 0,
                    'returned_qty'      => 0,
                    'notes'             => $item['notes'] ?: null,
                ]);
            }

            session()->flash('success', "Purchase Order {$po->po_number} updated.");
            $this->redirect(route('purchase-orders.show', $po->id));

            return;
        }

        // ── Create mode ────────────────────────────────────────────
        $this->validate(['initialPayment' => 'nullable|numeric|min:0']);

        $lastPo = PurchaseOrder::latest()->first();
        $poNumber = 'PO-'.str_pad(($lastPo ? $lastPo->id + 1 : 1), 4, '0', STR_PAD_LEFT);

        $paid = (float) $this->initialPayment;
        $balance = max(0, $total - $paid);

        $po = PurchaseOrder::create([
            'po_number'          => $poNumber,
            'vendor_bill_number' => $this->vendorBillNumber ?: null,
            'vendor_id'          => $this->vendorId,
            'order_date'         => Carbon::parse($this->orderDate)->toDateString(),
            'expected_date'      => Carbon::parse($this->orderDate)->toDateString(),
            'status'             => $status,
            'total_amount'       => $total,
            'amount_paid'        => $paid,
            'balance_due'        => $balance,
            'discount'           => (float) $this->discount,
            'notes'              => $this->notes ?: null,
            'created_by'         => auth()->id(),
            'updated_by'         => auth()->id(),
        ]);

        foreach ($this->items as $item) {
            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'product_id'        => $item['product_id'] ?: null,
                'item_name'         => $item['item_name'],
                'item_code'         => $item['item_code'] ?: null,
                'qty'               => (int) ($item['qty'] ?? 1),
                'unit_price'        => (float) ($item['unit_price'] ?? 0),
                'total_price'       => (float) ($item['total_price'] ?? 0),
                'received_qty'      => 0,
                'returned_qty'      => 0,
                'notes'             => $item['notes'] ?: null,
            ]);
        }

        if ($paid > 0) {
            PurchaseOrderPayment::create([
                'purchase_order_id' => $po->id,
                'amount'            => $paid,
                'payment_date'      => $this->initialPaymentDate ?: now()->toDateString(),
                'payment_method'    => $this->paymentMethod,
                'type'              => 'payment',
                'note'              => 'Initial payment on order',
                'created_by'        => auth()->id(),
            ]);

            if (! empty($this->initialPaymentAccountId)) {
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
