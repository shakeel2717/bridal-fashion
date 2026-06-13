<?php

namespace App\Livewire\PurchaseOrders;

use App\Models\Account;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Services\AccountService;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PurchaseReturnCreate extends Component
{
    public PurchaseOrder $po;

    public string $returnDate = '';

    public string $resolution = 'pending'; // pending | refund | replacement

    public string $refundAmount = '0';

    public string $refundAccountId = '';

    public string $refundDate = '';

    public string $notes = '';

    // Array of items available from the PO, each has:
    // selected, purchase_order_item_id, product_id, item_name, item_code,
    // max_qty, qty_returned, unit_price, total_price, reason
    public array $returnItems = [];

    public function mount(PurchaseOrder $po): void
    {
        $this->po = $po;
        $this->returnDate = now()->format('Y-m-d');
        $this->refundDate = now()->format('Y-m-d');

        $defaultAccount = Account::where('is_default', true)->first()
            ?? Account::where('is_active', true)->first();
        $this->refundAccountId = $defaultAccount ? (string) $defaultAccount->id : '';

        // Load items from the PO — only items that have been received
        $po->load('items');
        $this->returnItems = $po->items
            ->filter(fn ($i) => $i->received_qty > 0) // can only return what was received
            ->map(fn ($i) => [
                'selected'               => false,
                'purchase_order_item_id' => $i->id,
                'product_id'             => $i->product_id,
                'item_name'              => $i->item_name,
                'item_code'              => $i->item_code ?? '',
                'max_qty'                => $i->received_qty - ($i->returned_qty ?? 0), // remaining returnable
                'qty_returned'           => '1',
                'unit_price'             => (string) $i->unit_price,
                'total_price'            => (string) $i->unit_price,
                'reason'                 => 'damage',
            ])
            ->filter(fn ($i) => $i['max_qty'] > 0) // skip already fully returned
            ->values()
            ->toArray();
    }

    public function updatedReturnItems(): void
    {
        $this->recalc();
    }

    public function recalc(): void
    {
        foreach ($this->returnItems as $i => $item) {
            $qty = max(1, min((int) ($item['qty_returned'] ?? 1), $item['max_qty']));
            $price = max(0, (float) ($item['unit_price'] ?? 0));
            $this->returnItems[$i]['qty_returned'] = (string) $qty;
            $this->returnItems[$i]['total_price'] = (string) ($qty * $price);
        }

        // Auto-fill refund amount with total of selected items
        if ($this->resolution === 'refund') {
            $this->refundAmount = (string) $this->total;
        }
    }

    #[Computed]
    public function selectedItems(): array
    {
        return array_filter($this->returnItems, fn ($i) => $i['selected']);
    }

    #[Computed]
    public function total(): float
    {
        return collect($this->returnItems)
            ->filter(fn ($i) => $i['selected'])
            ->sum(fn ($i) => (float) ($i['total_price'] ?? 0));
    }

    public function updatedResolution(): void
    {
        if ($this->resolution === 'refund') {
            $this->refundAmount = (string) $this->total;
        }
    }

    public function save(): void
    {
        $selected = array_filter($this->returnItems, fn ($i) => $i['selected']);

        if (empty($selected)) {
            $this->addError('returnItems', 'Select at least one item to return.');
            return;
        }

        $this->validate([
            'returnDate' => 'required|date',
            'resolution' => 'required|in:pending,refund,replacement',
        ]);

        if ($this->resolution === 'refund') {
            $this->validate([
                'refundAmount'    => 'required|numeric|min:0',
                'refundDate'      => 'required|date',
                'refundAccountId' => 'required|exists:accounts,id',
            ]);
        }

        // Generate return number
        $lastReturn = PurchaseReturn::latest()->first();
        $returnNumber = 'PR-'.str_pad(($lastReturn ? $lastReturn->id + 1 : 1), 4, '0', STR_PAD_LEFT);

        $total = collect($selected)->sum(fn ($i) => (float) $i['total_price']);

        $return = PurchaseReturn::create([
            'return_number'    => $returnNumber,
            'purchase_order_id' => $this->po->id,
            'vendor_id'        => $this->po->vendor_id,
            'return_date'      => Carbon::parse($this->returnDate)->toDateString(),
            'total_amount'     => $total,
            'resolution'       => $this->resolution,
            'refund_amount'    => $this->resolution === 'refund' ? (float) $this->refundAmount : null,
            'refund_date'      => $this->resolution === 'refund' ? Carbon::parse($this->refundDate)->toDateString() : null,
            'refund_account_id' => $this->resolution === 'refund' ? $this->refundAccountId : null,
            'status'           => $this->resolution === 'pending' ? 'pending' : 'resolved',
            'notes'            => $this->notes ?: null,
            'created_by'       => auth()->id(),
            'updated_by'       => auth()->id(),
        ]);

        foreach ($selected as $item) {
            $qty = (int) $item['qty_returned'];

            PurchaseReturnItem::create([
                'purchase_return_id'     => $return->id,
                'purchase_order_item_id' => $item['purchase_order_item_id'],
                'product_id'             => $item['product_id'],
                'item_name'              => $item['item_name'],
                'item_code'              => $item['item_code'] ?: null,
                'qty_returned'           => $qty,
                'unit_price'             => (float) $item['unit_price'],
                'total_price'            => (float) $item['total_price'],
                'reason'                 => $item['reason'] ?: null,
            ]);

            // Decrement stock — we're sending items back to vendor
            if ($item['product_id']) {
                Product::where('id', $item['product_id'])
                    ->decrement('stock_qty', $qty);
            }

            // Track returned_qty on the PO item
            $this->po->items()
                ->where('id', $item['purchase_order_item_id'])
                ->increment('returned_qty', $qty);
        }

        // If refund: vendor is sending us money back → credit our account
        if ($this->resolution === 'refund' && (float) $this->refundAmount > 0) {
            AccountService::credit(
                (int) $this->refundAccountId,
                (float) $this->refundAmount,
                'purchase_return_refund',
                "Return refund — {$this->po->vendor->name} ({$returnNumber})",
                $this->refundDate,
                $return,
            );
        }

        session()->flash('success', "Purchase Return {$returnNumber} recorded.");
        $this->redirect(route('purchase-orders.show', $this->po->id));
    }

    public function render()
    {
        $accounts = Account::where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.purchase-orders.purchase-return-create', compact('accounts'));
    }
}
