<?php

namespace App\Livewire\Rentals;

use App\Models\Rental;
use App\Models\RentalItem;
use App\Models\RentalPayment;
use App\Models\RentalTask;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class RentalDetail extends Component
{
    public Rental $rental;

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

    public string $paymentMethod = 'cash';

    public string $paymentDate = '';

    public string $paymentNote = '';

    // Refund
    public bool $showRefundForm = false;

    public string $refundType = 'none';

    public string $refundAmount = '0';

    public string $refundNote = '';

    public function mount(Rental $rental): void
    {
        $this->rental = $rental;
        $this->paymentDate = now()->format('Y-m-d');

        // Auto-show refund form if cancelled and no refund recorded yet
        if ($rental->status === 'cancelled' && ! $rental->refund_type) {
            $this->showRefundForm = true;
        }
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
        RentalItem::findOrFail($itemId)->update([
            'pickup_status' => 'picked_up',
            'picked_up_at' => now(),
        ]);
        $this->updateRentalStatus();
        $this->rental->refresh();
    }

    public function markItemReturned(int $itemId): void
    {
        RentalItem::findOrFail($itemId)->update([
            'pickup_status' => 'returned',
            'returned_at' => now(),
            'returned_received_by' => auth()->id(),
        ]);
        $this->updateRentalStatus();
        $this->rental->refresh();
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

    // ── Payment ───────────────────────────────────────────
    public function addPayment(): void
    {
        $this->validate([
            'paymentAmount' => 'required|numeric|min:1',
            'paymentDate' => 'required|date',
            'paymentMethod' => 'required|string',
            'paymentNote' => 'nullable|string|max:500',
        ]);

        RentalPayment::create([
            'rental_id' => $this->rental->id,
            'amount' => $this->paymentAmount,
            'payment_date' => $this->paymentDate,
            'payment_method' => $this->paymentMethod,
            'note' => $this->paymentNote ?: null,
            'created_by' => auth()->id(),
        ]);

        // Recalculate advance_paid and remaining_balance
        $payments = RentalPayment::where('rental_id', $this->rental->id)
            ->with('createdBy')
            ->latest('payment_date')
            ->get();

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

    public function executeCancelRental(): void
    {
        $this->rental->update(['status' => 'cancelled', 'updated_by' => auth()->id()]);
        $this->showRefundForm = true;
        $this->rental->refresh();
    }

    public function executeMarkAbandoned(): void
    {
        $this->rental->update(['status' => 'abandoned', 'updated_by' => auth()->id()]);
        $this->rental->refresh();
    }

    public function render()
    {
        $this->rental->load([
            'items.tasks.actionedBy',
            'items.tasks.createdBy',
            'customer',
            'employee',
            'payments.createdBy',
        ]);

        // Stitching task
        $stitchingTask = $this->rental->tasks()
            ->where('type', 'stitching')
            ->first();

        // All pending tasks count (blocks pickup)
        $pendingTasksCount = $this->rental->tasks()
            ->where('status', 'pending')
            ->count();

        // Payments
        $payments = $this->rental->payments;
        $totalPaid = $payments->sum('amount');
        $remaining = max(0, $this->rental->total_amount - $totalPaid);

        return view('livewire.rentals.rental-detail',
            compact('stitchingTask', 'pendingTasksCount', 'payments', 'totalPaid', 'remaining'));
    }
}
