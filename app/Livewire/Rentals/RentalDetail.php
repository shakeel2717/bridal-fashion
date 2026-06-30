<?php

namespace App\Livewire\Rentals;

use App\Models\Account;
use App\Models\Product;
use App\Models\Rental;
use App\Models\RentalItem;
use App\Models\RentalPayment;
use App\Models\RentalSecurityDeposit;
use App\Models\RentalTask;
use App\Models\Sale;
use App\Models\Transaction;
use App\Services\AccountService;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class RentalDetail extends Component
{
    public Rental $rental;

    public bool $showReturnModal = false;

    // Delete rental
    public bool $showDeleteConfirm = false;

    public string $deletePassword = '';

    public string $deletePasswordError = '';

    public bool $showPickupModal = false;

    public ?int $pickupItemId = null;

    public string $pickupGivenBy = '';

    // Cancel with hold
    public bool $showCancelHoldForm = false;

    public string $holdAmount = '';

    public string $holdNote = '';

    public string $holdReason = '';

    public ?int $returnItemId = null;

    public string $returnReceivedBy = '';

    public string $returnPasswordError = '';

    // Password confirm
    public bool $showCancelConfirm = false;

    public string $cancelPassword = '';

    public string $cancelPasswordError = '';

    public string $pendingAction = ''; // cancel | abandon

    // Task actions
    public ?int $taskActionId = null;

    public string $taskActionType = ''; // done | denied

    public string $taskNote = '';

    // Payment
    public bool $showPaymentForm = false;

    public string $paymentAmount = '';

    public string $paymentMethod = '';

    public string $depositNote = '';

    public string $paymentDate = '';

    public string $paymentNote = '';

    // Refund
    public bool $showRefundForm = false;

    public string $refundType = 'none';

    public string $refundAmount = '0';

    public string $refundNote = '';

    // ── Fine (Jurmana) ─────────────────────────────────────
    public bool $showFineModal = false;

    public ?int $fineItemId = null;

    public ?int $fineTaskId = null;

    public string $fineAmount = '';

    public string $fineAccountId = '';

    public string $fineNote = '';

    public function mount(Rental $rental): void
    {
        $this->rental = $rental;
        $this->paymentDate = now()->format('Y-m-d');

        $this->returnReceivedBy = (string) auth()->id();

        $this->pickupGivenBy = (string) auth()->id();

        $defaultAccount = Account::where('is_default', true)->first()
    ?? Account::where('is_active', true)->first();
        $this->paymentMethod = $defaultAccount ? (string) $defaultAccount->id : '';
        $this->fineAccountId = $defaultAccount ? (string) $defaultAccount->id : '';

        // Auto-show refund form if cancelled and no refund recorded yet
        if ($rental->status === 'cancelled' && ! $rental->refund_type) {
            $this->showRefundForm = true;
        }
    }

    public function openPaymentForm(): void
    {
        $this->showPaymentForm = true;
        $this->dispatch('focus-payment-amount');
    }

    // ── Tasks ─────────────────────────────────────────────
    public function openTaskAction(int $taskId, string $type): void
    {
        $this->taskActionId = $taskId;
        $this->taskActionType = $type;
        $this->taskNote = '';
    }

    public function applyTaskAction(): void
    {
        $this->validate([
            'taskNote' => 'nullable|string|max:500',
        ]);

        RentalTask::findOrFail($this->taskActionId)->update([
            'status' => $this->taskActionType,
            'note' => $this->taskNote ?: null,
            'actioned_by' => auth()->id(),
            'actioned_at' => now(),
        ]);

        $this->taskActionId = null;
        $this->taskActionType = '';
        $this->taskNote = '';
        $this->rental->refresh();
    }

    public function undoTask(int $taskId): void
    {
        RentalTask::findOrFail($taskId)->update([
            'status' => 'pending',
            'note' => null,
            'actioned_by' => null,
            'actioned_at' => null,
        ]);
        $this->rental->refresh();
    }

    // ── Item Pickup/Return ────────────────────────────────
    public function markItemPickedUp(int $itemId): void
    {
        $this->pickupItemId = $itemId;
        $this->pickupGivenBy = (string) auth()->id();
        $this->showPickupModal = true;
    }

    public function confirmItemPickedUp(): void
    {
        $this->validate([
            'pickupGivenBy' => 'required|exists:users,id',
        ], [
            'pickupGivenBy.required' => 'Please select who gave this item to the customer.',
        ]);

        RentalItem::findOrFail($this->pickupItemId)->update([
            'pickup_status' => 'picked_up',
            'picked_up_at' => now(),
            'picked_up_by' => $this->pickupGivenBy,
        ]);

        $this->showPickupModal = false;
        $this->pickupItemId = null;
        $this->updateRentalStatus();
        $this->rental->refresh();
    }

    public function markItemReturned(int $itemId): void
    {
        $this->openReturnModal($itemId);
    }

    public function updateRentalStatus(): void
    {
        $items = $this->rental->items;
        $total = $items->count();
        $picked = $items->where('pickup_status', 'picked_up')->count();
        $returned = $items->where('pickup_status', 'returned')->count();

        $status = $this->rental->status;

        if ($returned === $total) {
            $status = 'returned';
        } elseif ($picked === $total) {
            $status = 'picked_up';
        } elseif ($picked > 0 || $returned > 0) {
            $status = 'partially_picked_up';
        }

        $this->rental->update(['status' => $status, 'updated_by' => auth()->id()]);
    }

    public function markReady(): void
    {
        $this->rental->update(['status' => 'ready', 'updated_by' => auth()->id()]);
        $this->rental->refresh();
    }

    public function openDeleteConfirm(): void
    {
        $this->deletePassword = '';
        $this->deletePasswordError = '';
        $this->showDeleteConfirm = true;
    }

    public function executeDelete(): void
    {
        $this->deletePasswordError = '';

        if (! Hash::check($this->deletePassword, auth()->user()->password)) {
            $this->deletePasswordError = 'Incorrect password.';

            return;
        }

        $rental = $this->rental;

        // 1. Reverse account balances for all payments
        foreach ($rental->payments as $payment) {
            // Find the transaction linked to this rental payment and reverse it
            $accountName = $payment->payment_method; // stored as account name string
            $account = Account::where('name', $accountName)->first();
            if ($account) {
                $account->debit($payment->amount); // reverse the credit
                $account->save();
            }
        }

        // 1b. Reverse account balances for all fine credits
        foreach ($rental->tasks()->where('type', 'fine')->get() as $fineTask) {
            $fineTxn = Transaction::where('referenceable_type', RentalTask::class)
                ->where('referenceable_id', $fineTask->id)
                ->first();
            if ($fineTxn) {
                $fineAccount = Account::find($fineTxn->account_id);
                if ($fineAccount) {
                    $fineAccount->debit($fineTxn->amount);
                    $fineAccount->save();
                }
                $fineTxn->delete();
            }
        }

        // 2. Delete all transactions referencing this rental
        Transaction::where('referenceable_type', Rental::class)
            ->where('referenceable_id', $rental->id)
            ->delete();

        // 3. Handle linked sale — restore stock, delete transactions, then delete sale
        if ($rental->linkedSale) {
            $linkedSale = $rental->linkedSale;

            foreach ($linkedSale->items as $saleItem) {
                $product = Product::find($saleItem->product_id);
                if ($product) {
                    if ($product->isFabric()) {
                        $product->increment('stock_decimal', $saleItem->qty);
                    } else {
                        $product->increment('stock_qty', (int) $saleItem->qty);
                    }
                }
            }

            Transaction::where('referenceable_type', Sale::class)
                ->where('referenceable_id', $linkedSale->id)
                ->delete();

            $linkedSale->items()->delete();
            $linkedSale->payments()->delete();
            $linkedSale->delete();
        }

        // 4. Delete all child records
        $rental->tasks()->delete();
        $rental->items()->delete();
        $rental->securityDeposits()->delete();
        $rental->payments()->delete();

        // 5. Delete the rental itself
        $rental->delete();

        session()->flash('success', 'Rental permanently deleted.');
        $this->redirect(route('rentals.index'));
    }

    // ── Payment ───────────────────────────────────────────
    public function addPayment(): void
    {
        $this->validate([
            'paymentAmount' => 'required|numeric|min:1',
            'paymentDate' => 'required|date',
            'paymentMethod' => 'required|exists:accounts,id',
            'paymentNote' => 'nullable|string|max:500',
        ]);

        RentalPayment::create([
            'rental_id' => $this->rental->id,
            'amount' => $this->paymentAmount,
            'payment_date' => $this->paymentDate,
            'payment_method' => Account::find($this->paymentMethod)?->name ?? 'Cash',
            'note' => $this->paymentNote ?: null,
            'created_by' => auth()->id(),
        ]);

        // Credit the selected account directly
        AccountService::credit(
            (int) $this->paymentMethod,
            (float) $this->paymentAmount,
            'rental_payment',
            "Rental payment — {$this->rental->customer_name} (#{$this->rental->id})",
            $this->paymentDate,
            $this->rental,
        );

        $payments = RentalPayment::where('rental_id', $this->rental->id)->get();
        $totalPaid = $payments->sum('amount');
        $remaining = max(0, $this->rental->total_amount - $totalPaid);

        $this->rental->update([
            'advance_paid' => $totalPaid,
            'remaining_balance' => $remaining,
            'updated_by' => auth()->id(),
        ]);

        $this->paymentAmount = '';
        $this->paymentNote = '';
        $this->paymentDate = now()->format('Y-m-d');
        $this->showPaymentForm = false;
        $this->rental->refresh();
        session()->flash('success', 'Payment recorded.');
    }

    public function refundDeposit(int $depositId): void
    {
        RentalSecurityDeposit::findOrFail($depositId)->update([
            'is_refunded' => true,
            'refunded_at' => now(),
            'refunded_by' => auth()->id(),
        ]);

        $this->rental->refresh();
        session()->flash('success', 'Deposit marked as refunded.');
    }

    public function markDepositNotRefunded(int $depositId): void
    {
        RentalSecurityDeposit::findOrFail($depositId)->update([
            'is_refunded' => false,
            'refunded_at' => null,
            'refunded_by' => null,
        ]);

        $this->rental->refresh();
        session()->flash('success', 'Deposit updated.');
    }

    // ── Fine (Jurmana) ─────────────────────────────────────
    public function openFineModal(int $itemId): void
    {
        $this->fineItemId = $itemId;

        $existing = RentalItem::find($itemId)?->tasks->firstWhere('type', 'fine');

        if ($existing) {
            $this->fineTaskId = $existing->id;
            $this->fineAmount = (string) $existing->cost;
            $this->fineNote = $existing->note ?? '';

            $txn = Transaction::where('referenceable_type', RentalTask::class)
                ->where('referenceable_id', $existing->id)
                ->first();
            $this->fineAccountId = $txn ? (string) $txn->account_id : $this->fineAccountId;
        } else {
            $this->fineTaskId = null;
            $this->fineAmount = '';
            $this->fineNote = '';
            $defaultAccount = Account::where('is_default', true)->first()
                ?? Account::where('is_active', true)->first();
            $this->fineAccountId = $defaultAccount ? (string) $defaultAccount->id : '';
        }

        $this->showFineModal = true;
        $this->dispatch('focus-fine-amount');
    }

    public function saveFine(): void
    {
        $this->validate([
            'fineAmount' => 'required|numeric|min:1',
            'fineAccountId' => 'required|exists:accounts,id',
            'fineNote' => 'nullable|string|max:500',
        ]);

        $item = RentalItem::findOrFail($this->fineItemId);

        if ($this->fineTaskId) {
            $task = RentalTask::findOrFail($this->fineTaskId);

            // Reverse the previous account credit before re-applying
            $oldTxn = Transaction::where('referenceable_type', RentalTask::class)
                ->where('referenceable_id', $task->id)
                ->first();
            if ($oldTxn) {
                $oldAccount = Account::find($oldTxn->account_id);
                if ($oldAccount) {
                    $oldAccount->debit($oldTxn->amount);
                    $oldAccount->save();
                }
                $oldTxn->delete();
            }

            $task->update([
                'title' => 'Late Return Fine',
                'cost' => (float) $this->fineAmount,
                'note' => $this->fineNote ?: null,
                'actioned_by' => auth()->id(),
                'actioned_at' => now(),
            ]);
        } else {
            $task = RentalTask::create([
                'rental_id' => $this->rental->id,
                'rental_item_id' => $item->id,
                'type' => 'fine',
                'title' => 'Late Return Fine',
                'cost' => (float) $this->fineAmount,
                'status' => 'done',
                'note' => $this->fineNote ?: null,
                'actioned_by' => auth()->id(),
                'actioned_at' => now(),
                'created_by' => auth()->id(),
            ]);
        }

        AccountService::credit(
            (int) $this->fineAccountId,
            (float) $this->fineAmount,
            'rental_fine',
            "Rental fine — {$this->rental->customer_name} (#{$this->rental->id})",
            now()->toDateString(),
            $task,
        );

        $this->showFineModal = false;
        $this->fineItemId = null;
        $this->fineTaskId = null;
        $this->fineAmount = '';
        $this->fineNote = '';
        $this->rental->refresh();
        session()->flash('success', 'Fine recorded.');
    }

    // ── Cancel / Abandon ──────────────────────────────────
    public function cancelRental(): void
    {
        $this->openCancelConfirm('cancel');
    }

    public function markAbandoned(): void
    {
        $this->openCancelConfirm('abandon');
    }

    public function saveRefund(): void
    {
        $this->validate([
            'refundType' => 'required|in:full,partial,none',
            'refundAmount' => 'required|numeric|min:0',
            'refundNote' => 'nullable|string|max:500',
        ]);

        $amount = $this->refundType === 'full'
            ? $this->rental->advance_paid
            : (float) $this->refundAmount;

        $this->rental->update([
            'refund_type' => $this->refundType,
            'refund_amount' => $amount,
            'refund_date' => now()->toDateString(),
            'refund_note' => $this->refundNote ?: null,
            'updated_by' => auth()->id(),
        ]);

        $this->showRefundForm = false;
        $this->rental->refresh();
        session()->flash('success', 'Refund recorded.');
    }

    public function openCancelConfirm(string $action): void
    {
        $this->pendingAction = $action;
        $this->cancelPassword = '';
        $this->cancelPasswordError = '';
        $this->showCancelConfirm = true;
        $this->showCancelHoldForm = false;
    }

    public function executeCancelRental(): void
    {
        $this->showCancelHoldForm = true;
    }

    public function processCancelWithHold(): void
    {
        $this->validate([
            'holdAmount' => 'nullable|numeric|min:0',
            'holdNote' => 'nullable|string|max:500',
        ]);

        // Create hold task if amount entered
        if (! empty($this->holdAmount) && (float) $this->holdAmount > 0) {
            RentalTask::create([
                'rental_id' => $this->rental->id,
                'type' => 'cancellation_hold',
                'title' => $this->holdNote ?: 'Cancellation hold amount',
                'cost' => (float) $this->holdAmount,
                'status' => 'done',
                'note' => 'Held on cancellation: '.($this->holdReason ?: 'No reason given'),
                'actioned_by' => auth()->id(),
                'actioned_at' => now(),
                'created_by' => auth()->id(),
            ]);
        }

        // Now cancel the rental
        $this->rental->update([
            'status' => 'cancelled',
            'updated_by' => auth()->id(),
        ]);

        $this->showCancelHoldForm = false;
        $this->holdAmount = '';
        $this->holdNote = '';
        $this->holdReason = '';
        $this->showRefundForm = true;
        $this->rental->refresh();
    }

    public function skipHoldAndCancel(): void
    {
        $this->rental->update([
            'status' => 'cancelled',
            'updated_by' => auth()->id(),
        ]);

        $this->showCancelHoldForm = false;
        $this->holdAmount = '';
        $this->holdNote = '';
        $this->holdReason = '';
        $this->showRefundForm = true;
        $this->rental->refresh();
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

        if ($this->pendingAction === 'cancel') {
            $this->executeCancelRental();
        } elseif ($this->pendingAction === 'abandon') {
            $this->executeMarkAbandoned();
        }

        $this->pendingAction = '';
    }

    public function executeMarkAbandoned(): void
    {
        $this->rental->update(['status' => 'abandoned', 'updated_by' => auth()->id()]);
        $this->rental->refresh();
    }

    public function openReturnModal(int $itemId): void
    {
        $this->returnItemId = $itemId;
        $this->returnReceivedBy = (string) auth()->id();
        $this->returnPasswordError = '';
        $this->showReturnModal = true;
    }

    public function confirmItemReturned(): void
    {
        $this->validate([
            'returnReceivedBy' => 'required|exists:users,id',
        ], [
            'returnReceivedBy.required' => 'Please select who received this item.',
        ]);

        RentalItem::findOrFail($this->returnItemId)->update([
            'pickup_status' => 'returned',
            'returned_at' => now(),
            'returned_received_by' => $this->returnReceivedBy,
        ]);

        $this->showReturnModal = false;
        $this->returnItemId = null;
        $this->updateRentalStatus();
        $this->rental->refresh();
    }

    public function render()
    {
        $this->rental->load([
            'items.tasks.actionedBy',
            'items.tasks.createdBy',
            'items.pickedUpBy',
            'items.receivedBy',
            'linkedSale.items',
            'customer',
            'employee',
            'payments.createdBy',
            'securityDeposits.refundedBy',
        ]);

        // Stitching task
        $stitchingTask = $this->rental->tasks()
            ->where('type', 'stitching')
            ->first();

        $cancellationHolds = $this->rental->tasks()
            ->where('type', 'cancellation_hold')
            ->get();

        $totalHeld = $cancellationHolds->sum('cost');

        // All pending tasks count (blocks pickup)
        $pendingTasksCount = $this->rental->tasks()
            ->where('status', 'pending')
            ->where('type', '!=', 'fine')
            ->count();

        // Payments
        $payments = $this->rental->payments;
        $totalPaid = $payments->sum('amount');

        // Fines — tracked separately from rental total/payments
        $totalFines = $this->rental->tasks()->where('type', 'fine')->sum('cost');
        $grandTotal = $this->rental->total_amount + $totalFines;
        $remaining = max(0, $grandTotal - $totalPaid);
        $overpaid = max(0, $totalPaid - $grandTotal);

        $accounts = Account::where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get(['id', 'name', 'type']);

        return view('livewire.rentals.rental-detail',
            compact('stitchingTask', 'pendingTasksCount', 'payments',
                'totalPaid', 'remaining', 'overpaid', 'totalFines', 'grandTotal',
                'cancellationHolds', 'totalHeld', 'accounts'));
    }
}