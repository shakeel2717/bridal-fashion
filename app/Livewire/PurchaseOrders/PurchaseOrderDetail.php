<?php

namespace App\Livewire\PurchaseOrders;

use App\Models\Account;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderPayment;
use App\Services\AccountService;
use Livewire\Component;

class PurchaseOrderDetail extends Component
{
    public PurchaseOrder $po;

    public bool $showPaymentForm = false;

    public string $paymentAmount = '';

    public string $paymentAccountId = '';

    public string $paymentDate = '';

    public string $paymentNote = '';

    public bool $showReceiveForm = false;

    public function mount(PurchaseOrder $po): void
    {
        $this->po = $po;
        $this->paymentDate = now()->format('Y-m-d');
    }

    public function addPayment(): void
    {
        $this->validate([
            'paymentAmount' => 'required|numeric|min:1',
            'paymentDate' => 'required|date',
            'paymentAccountId' => 'required|exists:accounts,id',
        ]);

        PurchaseOrderPayment::create([
            'purchase_order_id' => $this->po->id,
            'amount' => $this->paymentAmount,
            'payment_date' => $this->paymentDate,
            'payment_method' => Account::find($this->paymentAccountId)?->name ?? 'Cash',
            'type' => 'payment',
            'note' => $this->paymentNote ?: null,
            'created_by' => auth()->id(),
        ]);

        // Debit account — we are paying vendor
        AccountService::debit(
            (int) $this->paymentAccountId,
            (float) $this->paymentAmount,
            'vendor_payment',
            "PO Payment — {$this->po->vendor->name} ({$this->po->po_number})",
            $this->paymentDate,
            $this->po,
        );

        $totalPaid = PurchaseOrderPayment::where('purchase_order_id', $this->po->id)
            ->where('type', 'payment')->sum('amount');

        $this->po->update([
            'amount_paid' => $totalPaid,
            'balance_due' => max(0, $this->po->total_amount - $totalPaid),
            'updated_by' => auth()->id(),
        ]);

        $this->paymentAmount = '';
        $this->paymentNote = '';
        $this->paymentDate = now()->format('Y-m-d');
        $this->showPaymentForm = false;
        $this->paymentAccountId = '';
        $this->po->refresh();
        session()->flash('success', 'Payment recorded.');
    }

    public function markReceived(): void
    {
        // Update all items received qty to full qty
        foreach ($this->po->items as $item) {
            $item->update(['received_qty' => $item->qty]);

            // Update product stock if linked
            if ($item->product_id) {
                Product::where('id', $item->product_id)
                    ->increment('stock_qty', $item->qty);
            }
        }

        $this->po->update([
            'status' => 'received',
            'received_date' => now()->toDateString(),
            'updated_by' => auth()->id(),
        ]);

        $this->po->refresh();
        session()->flash('success', 'Order marked as received. Stock updated.');
    }

    public function markItemReceived(int $itemId, int $qty): void
    {
        $item = $this->po->items()->findOrFail($itemId);
        $item->update(['received_qty' => min($qty, $item->qty)]);

        // Update stock
        if ($item->product_id) {
            $diff = $qty - ($item->received_qty ?? 0);
            if ($diff > 0) {
                Product::where('id', $item->product_id)->increment('stock_qty', $diff);
            }
        }

        // Check if all items received
        $this->po->refresh();
        $allReceived = $this->po->items->every(fn ($i) => $i->received_qty >= $i->qty);
        $anyReceived = $this->po->items->some(fn ($i) => $i->received_qty > 0);

        $this->po->update([
            'status' => $allReceived ? 'received' : ($anyReceived ? 'partial' : 'ordered'),
            'received_date' => $allReceived ? now()->toDateString() : null,
            'updated_by' => auth()->id(),
        ]);

        $this->po->refresh();
    }

    public function cancelOrder(): void
    {
        $this->po->update([
            'status' => 'cancelled',
            'updated_by' => auth()->id(),
        ]);
        $this->po->refresh();
        session()->flash('success', 'Order cancelled.');
    }

    public function render()
    {
        $this->po->load(['vendor', 'items.product', 'payments.createdBy', 'createdBy']);

        $totalPaid = $this->po->payments->where('type', 'payment')->sum('amount');
        $balanceDue = max(0, $this->po->total_amount - $totalPaid);

        $accounts = Account::where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.purchase-orders.purchase-order-detail',
            compact('totalPaid', 'balanceDue', 'accounts'));
    }
}
