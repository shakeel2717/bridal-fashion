<div>
    @if(session('success'))
        <div class="alert alert-success py-2 mb-3" style="font-size:13px;">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">Expenses</div>
            <div class="page-subtitle">Track all shop expenses</div>
        </div>
        <button class="btn btn-primary btn-sm d-flex align-items-center gap-2"
                wire:click="openCreate">
            <i class="bi bi-plus-lg"></i> Add Expense
        </button>
    </div>

    {{-- Stats --}}
    <div class="d-flex gap-3 mb-3">
        <div style="background:#fff5f5; border:1px solid #fed7d7; border-radius:8px; padding:12px 20px;">
            <div style="font-size:10px; font-weight:700; color:#c53030; text-transform:uppercase;">This Month</div>
            <div style="font-size:20px; font-weight:800; color:#c53030;">
                Rs. {{ number_format($totalThisMonth, 0) }}
            </div>
        </div>
        @if($filterMonth || $filterCat)
        <div style="background:#fffff0; border:1px solid #f6e05e; border-radius:8px; padding:12px 20px;">
            <div style="font-size:10px; font-weight:700; color:#b7791f; text-transform:uppercase;">Filtered Total</div>
            <div style="font-size:20px; font-weight:800; color:#b7791f;">
                Rs. {{ number_format($totalFiltered, 0) }}
            </div>
        </div>
        @endif
    </div>

    <div class="row g-3">

        {{-- Form --}}
        @if($showForm)
        <div class="col-4">
            <div class="table-card">
                <div class="table-card-header">
                    <span class="table-card-title">
                        {{ $editId ? 'Edit Expense' : 'New Expense' }}
                    </span>
                    <button class="btn btn-sm btn-outline-secondary action-btn"
                            wire:click="resetForm">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <div style="padding:16px;">

                    {{-- Category --}}
                    <div class="mb-3">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        @if(!$showCatForm)
                        <div class="d-flex gap-2">
                            <select wire:model="categoryId"
                                    class="form-select @error('categoryId') is-invalid @enderror">
                                <option value="">Select category...</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            <button type="button"
                                    wire:click="$set('showCatForm', true)"
                                    class="btn btn-outline-secondary"
                                    style="padding:0 10px;" title="Add category">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>
                        @error('categoryId') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        @else
                        <div style="background:#ebf8ff; border:1px solid #bee3f8; border-radius:8px; padding:10px;">
                            <div class="row g-2">
                                <div class="col-7">
                                    <input type="text" wire:model="catName"
                                           class="form-control form-control-sm"
                                           placeholder="Category name *">
                                </div>
                                <div class="col-5">
                                    <input type="color" wire:model="catColor"
                                           class="form-control form-control-sm form-control-color"
                                           style="height:34px;">
                                </div>
                                <div class="col-12 d-flex gap-2">
                                    <button wire:click="saveCategory"
                                            class="btn btn-sm btn-primary flex-fill">Save</button>
                                    <button wire:click="$set('showCatForm', false)"
                                            class="btn btn-sm btn-outline-secondary">Cancel</button>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Account --}}
                    <div class="mb-3">
                        <label class="form-label">Pay From <span class="text-danger">*</span></label>
                        <select wire:model="accountId"
                                class="form-select @error('accountId') is-invalid @enderror">
                            <option value="">Select account...</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">
                                    {{ $acc->name }}
                                    (Rs. {{ number_format($acc->current_balance, 0) }})
                                </option>
                            @endforeach
                        </select>
                        @error('accountId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Amount --}}
                    <div class="mb-3">
                        <label class="form-label">Amount (Rs.) <span class="text-danger">*</span></label>
                        <input type="number" wire:model="amount"
                               class="form-control @error('amount') is-invalid @enderror"
                               min="1" placeholder="0">
                        @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Date --}}
                    <div class="mb-3">
                        <label class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" wire:model="expenseDate"
                               class="form-control @error('expenseDate') is-invalid @enderror">
                        @error('expenseDate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Description --}}
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea wire:model="description"
                                  class="form-control" rows="2"
                                  placeholder="Details..."></textarea>
                    </div>

                    {{-- Reference --}}
                    <div class="mb-4">
                        <label class="form-label">Reference #</label>
                        <input type="text" wire:model="reference"
                               class="form-control" placeholder="Bill # or receipt #">
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary btn-sm flex-fill"
                                wire:click="saveExpense"
                                wire:loading.attr="disabled">
                            <span wire:loading wire:target="saveExpense">
                                <span class="spinner-border spinner-border-sm me-1"></span>
                            </span>
                            {{ $editId ? 'Update' : 'Save Expense' }}
                        </button>
                        <button class="btn btn-outline-secondary btn-sm"
                                wire:click="resetForm">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Table --}}
        <div class="{{ $showForm ? 'col-8' : 'col-12' }}">
            <div class="table-card">
                <div class="table-card-header" style="flex-wrap:wrap; gap:10px;">
                    <div class="d-flex gap-2 align-items-center">
                        <select wire:model.live="filterCat"
                                class="form-select form-select-sm" style="width:160px;">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <input type="month" wire:model.live="filterMonth"
                               class="form-control form-control-sm" style="width:150px;">
                    </div>
                    <div style="width:220px;">
                        <input type="text"
                               wire:model.live.debounce.400ms="search"
                               class="form-control form-control-sm"
                               placeholder="Search description...">
                    </div>
                </div>

                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Account</th>
                            <th>Description</th>
                            <th>Ref #</th>
                            <th style="text-align:right;">Amount</th>
                            <th>By</th>
                            <th style="width:70px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                        <tr>
                            <td style="font-size:12px;">
                                {{ $expense->expense_date->format('d/m/Y') }}
                            </td>
                            <td>
                                <span style="font-size:11px; font-weight:600; padding:2px 8px; border-radius:4px; background:{{ $expense->category->color }}22; color:{{ $expense->category->color }};">
                                    {{ $expense->category->name }}
                                </span>
                            </td>
                            <td style="font-size:12px;">{{ $expense->account->name }}</td>
                            <td style="font-size:12px;">{{ $expense->description ?? '—' }}</td>
                            <td style="font-size:11px; font-family:monospace; color:var(--text-muted);">
                                {{ $expense->reference ?? '—' }}
                            </td>
                            <td style="text-align:right; font-weight:700; font-size:13px; color:#c53030;">
                                Rs. {{ number_format($expense->amount, 0) }}
                            </td>
                            <td style="font-size:11px; color:var(--text-muted);">
                                {{ $expense->createdBy?->name ?? '—' }}
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-outline-danger action-btn"
                                            wire:click="confirmDelete({{ $expense->id }})"
                                            title="Delete">
                                        <i class="bi bi-trash" style="font-size:11px;"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" style="text-align:center; padding:30px; color:var(--text-muted); font-size:13px;">
                                <i class="bi bi-receipt" style="font-size:32px; display:block; margin-bottom:8px;"></i>
                                No expenses recorded
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($expenses->hasPages())
                <div style="padding:12px 16px; border-top:1px solid var(--border);">
                    {{ $expenses->links('vendor.pagination.simple-bootstrap-5') }}
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Delete Confirm --}}
    @if($deleteId)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered" style="max-width:380px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Delete Expense</h6>
                </div>
                <div class="modal-body" style="font-size:13px;">
                    This will delete the expense and restore the account balance.
                </div>
                <div class="modal-footer gap-2">
                    <button class="btn btn-sm btn-outline-secondary"
                            wire:click="$set('deleteId', null)">Cancel</button>
                    <button class="btn btn-sm btn-danger"
                            wire:click="delete()">Yes, Delete</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>