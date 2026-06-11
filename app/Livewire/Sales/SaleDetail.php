<?php

namespace App\Livewire\Sales;

use App\Models\Account;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\AccountService;
use Carbon\Carbon;
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

    public string $paymentMethod = '';

    public string $paymentDate = '';

    public string $paymentNote = '';

    public bool $showRefundForm = false;

    public string $refundType = 'none';

    public string $refundAmount = '0';

    public string $refundNote = '';

    public array $editItems = [];

    public bool $showEditModal = false;

    public string $editBillRef = '';

    public string $editSaleDate = '';

    public string $editNotes = '';

    public string $editEmployeeId = '';

    public string $editStatus = '';

    public string $editCustomerName = '';

    public string $editCustomerPhone1 = '';

    public string $editCustomerPhone2 = '';

    public string $editCustomerCnic = '';

    public string $editTotalAmount = '';

    public string $editAdvancePaid = '';

    public string $editRemainingBalance = '';

    public function mount(Sale $sale): void
    {
        $this->sale = $sale;
        $this->paymentDate = now()->format('Y-m-d');

        $defaultAccount = Account::where('is_default', true)->first()
    ?? Account::where('is_active', true)->first();
        $this->paymentMethod = $defaultAccount ? (string) $defaultAccount->id : '';

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
            'paymentMethod' => 'required|exists:accounts,id',
            'paymentNote' => 'nullable|string|max:500',
        ]);

        $newPaid = $this->sale->advance_paid + (float) $this->paymentAmount;
        $remaining = max(0, $this->sale->total_amount - $newPaid);

        $this->sale->update([
            'advance_paid' => $newPaid,
            'remaining_balance' => $remaining,
            'updated_by' => auth()->id(),
        ]);

        $accountName = Account::find($this->paymentMethod)?->name ?? 'Cash';
        $note = now()->format('d/m/Y').': Rs. '.number_format((float) $this->paymentAmount, 0)
            .' via '.$accountName
            .($this->paymentNote ? ' — '.$this->paymentNote : '');

        $this->sale->update([
            'notes' => $this->sale->notes ? $this->sale->notes."\n".$note : $note,
        ]);

        AccountService::credit(
            (int) $this->paymentMethod,
            (float) $this->paymentAmount,
            'sale_payment',
            "Sale payment — {$this->sale->customer_name} (#{$this->sale->id})",
            $this->paymentDate,
            $this->sale,
        );

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

    public function markItemTaken(int $saleItemId): void
    {
        $item = SaleItem::findOrFail($saleItemId);
        $item->update([
            'pickup_status' => 'taken',
            'taken_at' => now()->toDateString(),
        ]);
    }

    public function markItemPending(int $saleItemId): void
    {
        $item = SaleItem::findOrFail($saleItemId);
        $item->update([
            'pickup_status' => 'pending',
            'taken_at' => null,
        ]);
    }

    public function openEdit(): void
    {
        $sale = $this->sale;
        $this->editBillRef = $sale->bill_ref ?? '';
        $this->editSaleDate = $sale->sale_date->format('Y-m-d');
        $this->editNotes = $sale->notes ?? '';
        $this->editEmployeeId = (string) ($sale->employee_id ?? '');
        $this->editStatus = $sale->status;
        $this->editCustomerName = $sale->customer_name;
        $this->editCustomerPhone1 = $sale->customer_phone1;
        $this->editCustomerPhone2 = $sale->customer_phone2 ?? '';
        $this->editCustomerCnic = $sale->customer_cnic ?? '';
        $this->editTotalAmount = (string) $sale->total_amount;
        $this->editAdvancePaid = (string) $sale->advance_paid;
        $this->editRemainingBalance = (string) $sale->remaining_balance;
        $this->showEditModal = true;

        $this->editItems = [];
        foreach ($this->sale->items as $item) {
            $this->editItems[$item->id] = [
                'qty' => $item->qty,
                'sale_price' => (string) $item->sale_price,
            ];
        }
    }

    public function saveEdit(): void
    {
        $this->validate([
            'editSaleDate' => 'required|date',
            'editStatus' => 'required|in:completed,cancelled,pending',
            'editCustomerName' => 'required|string|max:150',
            'editCustomerPhone1' => 'required|string|max:20',
            'editTotalAmount' => 'required|numeric|min:0',
            'editAdvancePaid' => 'required|numeric|min:0',
        ]);

        $remaining = max(0, (float) $this->editTotalAmount - (float) $this->editAdvancePaid);

        $this->sale->update([
            'bill_ref' => $this->editBillRef ?: null,
            'sale_date' => Carbon::parse($this->editSaleDate)->toDateString(),
            'notes' => $this->editNotes ?: null,
            'employee_id' => $this->editEmployeeId ?: null,
            'status' => $this->editStatus,
            'customer_name' => $this->editCustomerName,
            'customer_phone1' => $this->editCustomerPhone1,
            'customer_phone2' => $this->editCustomerPhone2 ?: null,
            'customer_cnic' => $this->editCustomerCnic ?: null,
            'total_amount' => (float) $this->editTotalAmount,
            'advance_paid' => (float) $this->editAdvancePaid,
            'remaining_balance' => $remaining,
            'updated_by' => auth()->id(),
        ]);

        foreach ($this->editItems as $itemId => $data) {
            SaleItem::where('id', $itemId)->update([
                'qty' => max(1, (int) ($data['qty'] ?? 1)),
                'sale_price' => max(0, (float) ($data['sale_price'] ?? 0)),
            ]);
        }
        $this->sale->load('items');

        $this->showEditModal = false;
        $this->sale->refresh();
        session()->flash('success', 'Sale updated.');
    }

    public function confirmDeleteSale(): void
    {
        $this->showDeleteConfirm = true;
    }

    public bool $showDeleteConfirm = false;

    public function deleteSale(): void
    {
        // Restore stock before deleting
        foreach ($this->sale->items as $item) {
            if ($item->product_id) {
                Product::where('id', $item->product_id)
                    ->increment('stock_qty', $item->qty);
            }
        }

        $this->sale->delete();
        session()->flash('success', 'Sale deleted.');
        $this->redirect(route('sales.index'));
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

        $accounts = Account::where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get(['id', 'name', 'type']);

        return view('livewire.sales.sale-detail', compact('remaining', 'accounts'));
    }
}
