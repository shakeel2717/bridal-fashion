<?php

namespace App\Livewire\PurchaseOrders;

use App\Models\Account;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderPayment;
use App\Models\PurchaseReturn;
use App\Services\AccountService;
use Carbon\Carbon;
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

    // Return resolve form
    public ?int $resolvingReturnId = null;

    public string $resolveResolution = 'refund';

    public string $resolveStatus = 'resolved';

    public string $resolveRefundAmount = '0';

    public string $resolveRefundAccountId = '';

    public string $resolveRefundDate = '';

    public function mount(PurchaseOrder $po): void
    {
        $this->po = $po;
        $this->paymentDate = now()->format('Y-m-d');
        $this->resolveRefundDate = now()->format('Y-m-d');

        $defaultAccount = Account::where('is_default', true)->first()
            ?? Account::where('is_active', true)->first();
        $this->resolveRefundAccountId = $defaultAccount ? (string) $defaultAccount->id : '';
    }

    public function openResolveReturn(int $returnId): void
    {
        $return = PurchaseReturn::with('items')->findOrFail($returnId);

        $this->resolvingReturnId = $returnId;
        $this->resolveResolution = 'refund';
        $this->resolveStatus = 'resolved';
        $this->resolveRefundAmount = (string) $return->total_amount;
        $this->resolveRefundDate = now()->format('Y-m-d');

        $defaultAccount = Account::where('is_default', true)->first()
            ?? Account::where('is_active', true)->first();
        $this->resolveRefundAccountId = $defaultAccount ? (string) $defaultAccount->id : '';

        $this->resetValidation();
    }

    public function cancelResolve(): void
    {
        $this->resolvingReturnId = null;
        $this->resetValidation();
    }

    public function saveReturnResolution(): void
    {
        $this->validate([
            'resolveResolution' => 'required|in:refund,replacement',
            'resolveStatus' => 'required|in:sent,resolved',
        ]);

        if ($this->resolveResolution === 'refund') {
            $this->validate([
                'resolveRefundAmount' => 'required|numeric|min:0',
                'resolveRefundDate' => 'required|date',
                'resolveRefundAccountId' => 'required|exists:accounts,id',
            ]);
        }

        $return = PurchaseReturn::with('items')->findOrFail($this->resolvingReturnId);
        $wasResolved = $return->status === 'resolved';

        $return->update([
            'resolution' => $this->resolveResolution,
            'status' => $this->resolveStatus,
            'refund_amount' => $this->resolveResolution === 'refund' ? (float) $this->resolveRefundAmount : null,
            'refund_date' => $this->resolveResolution === 'refund' ? Carbon::parse($this->resolveRefundDate)->toDateString() : null,
            'refund_account_id' => $this->resolveResolution === 'refund' ? (int) $this->resolveRefundAccountId : null,
            'updated_by' => auth()->id(),
        ]);

        // Credit account if refund
        if ($this->resolveResolution === 'refund' && (float) $this->resolveRefundAmount > 0) {
            AccountService::credit(
                (int) $this->resolveRefundAccountId,
                (float) $this->resolveRefundAmount,
                'purchase_return_refund',
                "Return refund — {$this->po->vendor->name} ({$return->return_number})",
                $this->resolveRefundDate,
                $return,
            );
        }

        // If replacement just marked resolved → restore stock
        if ($this->resolveResolution === 'replacement'
            && $this->resolveStatus === 'resolved'
            && ! $wasResolved) {
            foreach ($return->items as $ri) {
                if ($ri->product_id) {
                    Product::where('id', $ri->product_id)
                        ->increment('stock_qty', $ri->qty_returned);
                }
            }
            session()->flash('success', "Return {$return->return_number} resolved. Stock restored for replacement items.");
        } else {
            session()->flash('success', "Return {$return->return_number} updated.");
        }

        $this->resolvingReturnId = null;
        $this->po->refresh();
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
        foreach ($this->po->items as $item) {
            $oldQty = (int) $item->received_qty;
            $newQty = (int) $item->qty;
            $diff = $newQty - $oldQty;

            $item->update(['received_qty' => $newQty]);

            // Only increment by the difference not already received
            if ($item->product_id && $diff > 0) {
                Product::where('id', $item->product_id)
                    ->increment('stock_qty', $diff);
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

        // Bug: $item->received_qty is the OLD value, then we update it
        $oldQty = (int) $item->received_qty;
        $newQty = min($qty, $item->qty);
        $diff = $newQty - $oldQty;

        $item->update(['received_qty' => $newQty]);

        // Update stock only for the difference
        if ($item->product_id && $diff > 0) {
            Product::where('id', $item->product_id)->increment('stock_qty', $diff);
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
        session()->flash('success', 'Item marked as received. Stock updated.');
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
        $this->po->load(['vendor', 'items.product', 'payments.createdBy', 'returns.items', 'createdBy']);

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
