<?php

namespace App\Livewire\Sales;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class SaleDetail extends Component
{
    public Sale $sale;

    public bool $showCancelConfirm = false;

    public string $cancelPassword = '';

    public string $cancelPasswordError = '';

    public bool $showPaymentForm = false;

    public string $paymentAmount = '';

    public string $paymentMethod = 'cash';

    public string $paymentDate = '';

    public string $paymentNote = '';

    public bool $showRefundForm = false;

    public string $refundType = 'none';

    public string $refundAmount = '0';

    public string $refundNote = '';

    public function mount(Sale $sale): void
    {
        $this->sale = $sale;
        $this->paymentDate = now()->format('Y-m-d');

        // Auto-show refund form if cancelled and no refund recorded yet
        if ($sale->status === 'cancelled' && ($sale->refund_amount == 0 && ! $sale->refund_date)) {
            $this->showRefundForm = true;
        }
    }

    public function addPayment(): void
    {
        $this->validate([
            'paymentAmount' => 'required|numeric|min:1',
            'paymentDate' => 'required|date',
            'paymentMethod' => 'required|string',
            'paymentNote' => 'nullable|string|max:500',
        ]);

        // We'll use rental_payments concept but for sales
        // Store directly on sale record for simplicity
        $newPaid = $this->sale->advance_paid + (float) $this->paymentAmount;
        $remaining = max(0, $this->sale->total_amount - $newPaid);

        $this->sale->update([
            'advance_paid' => $newPaid,
            'remaining_balance' => $remaining,
            'updated_by' => auth()->id(),
        ]);

        // Log payment in notes
        $note = now()->format('d/m/Y').': Rs. '.number_format((float) $this->paymentAmount, 0)
            .' received via '.$this->paymentMethod
            .($this->paymentNote ? ' — '.$this->paymentNote : '');

        $existingNotes = $this->sale->notes ?? '';
        $this->sale->update([
            'notes' => $existingNotes ? $existingNotes."\n".$note : $note,
        ]);

        $this->paymentAmount = '';
        $this->paymentNote = '';
        $this->paymentDate = now()->format('Y-m-d');
        $this->showPaymentForm = false;
        $this->sale->refresh();
        session()->flash('success', 'Payment recorded.');
    }

    public function cancelSale(): void
    {
        $this->openCancelConfirm();
    }

    public function saveRefund(): void
    {
        $this->validate([
            'refundType' => 'required|in:full,partial,none',
            'refundAmount' => 'required|numeric|min:0',
            'refundNote' => 'nullable|string|max:500',
        ]);

        $amount = $this->refundType === 'full'
            ? $this->sale->advance_paid
            : (float) $this->refundAmount;

        $this->sale->update([
            'refund_amount' => $amount,
            'refund_date' => now()->toDateString(),
            'refund_note' => $this->refundNote ?: null,
            'status' => 'refunded',
            'updated_by' => auth()->id(),
        ]);

        $this->showRefundForm = false;
        $this->sale->refresh();
        session()->flash('success', 'Refund recorded.');
    }

    public function openCancelConfirm(): void
    {
        $this->cancelPassword = '';
        $this->cancelPasswordError = '';
        $this->showCancelConfirm = true;
    }

    public function confirmWithPassword(): void
    {
        $this->cancelPasswordError = '';

        if (! Hash::check($this->cancelPassword, auth()->user()->password)) {
            $this->cancelPasswordError = 'Incorrect password. Please try again.';

            return;
        }

        $this->showCancelConfirm = false;
        $this->cancelPassword = '';
        $this->executeCancelSale();
    }

    public function executeCancelSale(): void
    {
        foreach ($this->sale->items as $item) {
            Product::where('id', $item->product_id)
                ->increment('stock_qty', $item->qty);
        }

        $this->sale->update([
            'status' => 'cancelled',
            'updated_by' => auth()->id(),
        ]);

        $this->showRefundForm = true;
        $this->sale->refresh();
    }

    public function render()
    {
        $this->sale->load(['items.product', 'customer', 'employee']);

        $remaining = max(0, $this->sale->total_amount - $this->sale->advance_paid);

        return view('livewire.sales.sale-detail', compact('remaining'));
    }
}
