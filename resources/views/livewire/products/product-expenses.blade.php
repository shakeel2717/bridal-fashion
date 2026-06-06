<div>
    <div class="modal fade" id="expensesModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">
                        <i class="bi bi-receipt me-2"></i> Product Expenses
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body" style="padding:0;">
                    @if ($productName)
                        <div
                            style="padding:12px 20px; background:#f7fafc; border-bottom:1px solid var(--border); font-size:13px; font-weight:600; color:var(--text-primary);">
                            {{ $productName }}
                        </div>
                    @endif

                    {{-- Add Expense Form --}}
                    <div style="padding:16px 20px; border-bottom:1px solid var(--border);">
                        <div
                            style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:10px;">
                            Add Expense
                        </div>
                        <div class="row g-2">
                            <div class="col-4">
                                <label class="form-label">Amount (Rs.) <span class="text-danger">*</span></label>
                                <input type="number" wire:model="amount"
                                    class="form-control form-control-sm @error('amount') is-invalid @enderror"
                                    placeholder="e.g. 500" min="1">
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-4">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" wire:model="expenseDate"
                                    class="form-control form-control-sm @error('expenseDate') is-invalid @enderror">
                                @error('expenseDate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-4">
                                <label class="form-label">Note</label>
                                <input type="text" wire:model="note" class="form-control form-control-sm"
                                    placeholder="e.g. repair, cleaning">
                            </div>
                            <div class="col-12">
                                <button class="btn btn-sm btn-primary" wire:click="saveExpense"
                                    wire:loading.attr="disabled">
                                    <span wire:loading wire:target="saveExpense">
                                        <span class="spinner-border spinner-border-sm me-1"></span>
                                    </span>
                                    Add Expense
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Expenses List --}}
                    <div style="padding:16px 20px; max-height:280px; overflow-y:auto;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div
                                style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted);">
                                Expense History
                            </div>
                            @if ($totalExpenses > 0)
                                <div style="font-size:12px; font-weight:700; color:#e53e3e;">
                                    Total: Rs. {{ number_format($totalExpenses, 0) }}
                                </div>
                            @endif
                        </div>

                        @forelse($expenses as $expense)
                            <div class="expense-item">
                                <div>
                                    <div style="font-weight:600; color:var(--text-primary);">
                                        {{ $expense->note ?? 'Expense' }}
                                    </div>
                                    <div class="expense-date">
                                        {{ $expense->expense_date->format('d/m/Y') }}
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="expense-amount">
                                        Rs. {{ number_format($expense->amount, 0) }}
                                    </span>
                                    @if ($deleteId === $expense->id)
                                        <button class="btn btn-sm btn-danger action-btn"
                                            wire:click="deleteExpense()">Confirm</button>
                                        <button class="btn btn-sm btn-outline-secondary action-btn"
                                            wire:click="$set('deleteId', null)">Cancel</button>
                                    @else
                                        <button class="btn btn-sm btn-outline-danger action-btn"
                                            wire:click="confirmDeleteExpense({{ $expense->id }})">
                                            <i class="bi bi-trash" style="font-size:11px;"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div style="text-align:center; padding:20px; color:var(--text-muted); font-size:12px;">
                                <i class="bi bi-receipt" style="font-size:24px; display:block; margin-bottom:6px;"></i>
                                No expenses recorded
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        const modalEl = document.getElementById('expensesModal');
        const modal   = new bootstrap.Modal(modalEl);

        // Listen for browser event from ProductList
        Livewire.on('open-expenses-modal', (data) => {
            const productId = data.productId ?? data[0]?.productId;

            // First tell the ProductExpenses component to load data
            Livewire.dispatch('load-expenses-for', { productId: productId });

            // Then show modal after short delay to allow Livewire to update
            setTimeout(() => modal.show(), 100);
        });
    });
</script>
@endpush