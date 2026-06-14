<?php

namespace App\Livewire\Sales;

use App\Models\Account;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Services\AccountService;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;

class SaleReturnCreate extends Component
{
    public Sale $sale;

    public string $returnDate = '';

    public string $resolution = 'pending'; // pending | refund | replacement

    public string $refundAmount = '0';

    public string $refundAccountId = '';

    public string $refundDate = '';

    public string $notes = '';

    // Array of items available from the Sale, each has:
    // selected, sale_item_id, product_id, item_name, item_code,
    // max_qty, qty_returned, unit_price, total_price, reason, condition
    public array $returnItems = [];

    public function mount(Sale $sale): void
    {
        $this->sale = $sale;
        $this->returnDate = now()->format('Y-m-d');
        $this->refundDate = now()->format('Y-m-d');

        $defaultAccount = Account::where('is_default', true)->first()
            ?? Account::where('is_active', true)->first();
        $this->refundAccountId = $defaultAccount ? (string) $defaultAccount->id : '';

        $sale->load('items');
        $this->returnItems = $sale->items->map(fn ($i) => [
            'selected' => false,
            'sale_item_id' => $i->id,
            'product_id' => $i->product_id,
            'item_name' => $i->product_name ?? $i->product?->name ?? 'Item',
            'item_code' => $i->product_code ?? $i->product?->code ?? '',
            'max_qty' => $i->qty,
            'qty_returned' => '1',
            'unit_price' => (string) $i->sale_price,
            'total_price' => (string) $i->sale_price,
            'reason' => 'damage',
            'condition' => 'good', // good = restore to stock; damaged = don't restore
        ])->toArray();
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
                'refundAmount' => 'required|numeric|min:0',
                'refundDate' => 'required|date',
                'refundAccountId' => 'required|exists:accounts,id',
            ]);
        }

        // Generate return number
        $lastReturn = SaleReturn::latest()->first();
        $returnNumber = 'SR-'.str_pad(($lastReturn ? $lastReturn->id + 1 : 1), 4, '0', STR_PAD_LEFT);

        $total = collect($selected)->sum(fn ($i) => (float) $i['total_price']);

        $return = SaleReturn::create([
            'return_number' => $returnNumber,
            'sale_id' => $this->sale->id,
            'customer_name' => $this->sale->customer_name,
            'return_date' => Carbon::parse($this->returnDate)->toDateString(),
            'total_amount' => $total,
            'resolution' => $this->resolution,
            'refund_amount' => $this->resolution === 'refund' ? (float) $this->refundAmount : null,
            'refund_date' => $this->resolution === 'refund' ? Carbon::parse($this->refundDate)->toDateString() : null,
            'refund_account_id' => $this->resolution === 'refund' ? $this->refundAccountId : null,
            'status' => $this->resolution === 'pending' ? 'pending' : 'resolved',
            'notes' => $this->notes ?: null,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        foreach ($selected as $item) {
            $qty = (int) $item['qty_returned'];

            SaleReturnItem::create([
                'sale_return_id' => $return->id,
                'sale_item_id' => $item['sale_item_id'],
                'product_id' => $item['product_id'],
                'item_name' => $item['item_name'],
                'item_code' => $item['item_code'] ?: null,
                'qty_returned' => $qty,
                'unit_price' => (float) $item['unit_price'],
                'total_price' => (float) $item['total_price'],
                'reason' => $item['reason'] ?: null,
                'condition' => $item['condition'] ?: 'good',
            ]);

            // Step 1: Handle returned item stock
            if ($item['product_id'] && $item['condition'] === 'good') {
                // Customer returned it in good condition → goes back to shelf
                Product::where('id', $item['product_id'])
                    ->increment('stock_qty', $qty);
            }
            // If damaged → don't restore stock (item is unusable)

            // Step 2: Handle replacement stock separately
            if ($this->resolution === 'replacement' && $item['product_id']) {
                // We are sending customer a NEW item from stock
                // Only decrement if item is damaged (good condition already net-zero above)
                if ($item['condition'] === 'damaged') {
                    // Damaged returned (not restocked) + new one sent out = -1 net
                    Product::where('id', $item['product_id'])
                        ->decrement('stock_qty', $qty);
                }
                // Good condition: +1 from return, -1 for replacement sent = 0 net — already correct
            }
        }

        // If refund: we are paying customer back → debit our account
        if ($this->resolution === 'refund' && (float) $this->refundAmount > 0) {
            AccountService::debit(
                (int) $this->refundAccountId,
                (float) $this->refundAmount,
                'sale_return_refund',
                "Sale return refund — {$this->sale->customer_name} ({$returnNumber})",
                $this->refundDate,
                $return,
            );
        }

        session()->flash('success', "Sale Return {$returnNumber} recorded.");
        $this->redirect(route('sales.show', $this->sale->id));
    }

    public function render()
    {
        $accounts = Account::where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.sales.sale-return-create', compact('accounts'));
    }
}
