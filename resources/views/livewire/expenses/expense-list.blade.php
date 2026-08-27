<div>
    @if (session('success'))
        <div class="alert alert-success py-2 mb-3" style="font-size:13px;">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">Expenses</div>
            <div class="page-subtitle" style="margin-bottom:0;">
                Track all shop expenses — {{ $this->periodRangeLabel() }}
            </div>
        </div>
        <button class="btn btn-primary btn-sm d-flex align-items-center gap-2" wire:click="openCreate">
            <i class="bi bi-plus-lg"></i> Add Expense
        </button>
    </div>

    {{-- Period filter + navigation --}}
    <x-period-filter :period="$period" :label="$this->periodLabel()" :range="$this->periodRangeLabel()"
        :can-next="$this->canGoNext()" :current="$this->isCurrentPeriod()">
        <select wire:model.live="filterAccount" class="form-select form-select-sm" style="width:170px;">
            <option value="">All Accounts</option>
            @foreach ($accounts as $acc)
                <option value="{{ $acc->id }}">{{ $acc->name }}</option>
            @endforeach
        </select>
    </x-period-filter>

    {{-- Summary --}}
    <div class="exp-summary">
        <div class="exp-sum-card is-alert">
            <div class="exp-sum-label">Total Spent</div>
            <div class="exp-sum-value">Rs. {{ number_format($periodTotal, 0) }}</div>
            <div class="exp-sum-sub">
                @if ($prevTotal > 0)
                    @php $d = round((($periodTotal - $prevTotal) / $prevTotal) * 100); @endphp
                    <i class="bi {{ $d >= 0 ? 'bi-arrow-up-short' : 'bi-arrow-down-short' }}"></i>
                    {{ abs($d) }}% vs previous period
                @else
                    Previous period: Rs. {{ number_format($prevTotal, 0) }}
                @endif
            </div>
        </div>

        <div class="exp-sum-card">
            <div class="exp-sum-label">Entries</div>
            <div class="exp-sum-value">{{ number_format($periodCount) }}</div>
            <div class="exp-sum-sub">
                Avg Rs. {{ number_format($periodCount ? $periodTotal / $periodCount : 0, 0) }} per entry
            </div>
        </div>

        <div class="exp-sum-card">
            <div class="exp-sum-label">Daily Average</div>
            <div class="exp-sum-value">Rs. {{ number_format($dailyAvg, 0) }}</div>
            <div class="exp-sum-sub">Across the selected range</div>
        </div>

        <div class="exp-sum-card is-accent">
            <div class="exp-sum-label">Biggest Category</div>
            <div class="exp-sum-value" style="font-size:15px;">
                {{ $topCategory ? ($categories->get($topCategory->expense_category_id)?->name ?? '—') : '—' }}
            </div>
            <div class="exp-sum-sub">
                @if ($topCategory)
                    Rs. {{ number_format($topCategory->total, 0) }}
                    ({{ $periodTotal > 0 ? round(($topCategory->total / $periodTotal) * 100) : 0 }}%)
                @else
                    Nothing recorded yet
                @endif
            </div>
        </div>
    </div>

    {{-- Category quick filters --}}
    @if ($breakdown->count())
        <div class="exp-cat-chips">
            @foreach ($breakdown as $row)
                @php $cat = $categories->get($row->expense_category_id); @endphp
                <button type="button"
                    class="exp-chip {{ (string) $filterCat === (string) $row->expense_category_id ? 'active' : '' }}"
                    wire:click="setCategoryFilter('{{ $row->expense_category_id }}')">
                    <span class="exp-chip-name">{{ $cat?->name ?? 'Uncategorised' }}</span>
                    <span class="exp-chip-val">Rs. {{ number_format($row->total, 0) }}</span>
                </button>
            @endforeach

            @if ($filterCat)
                <button type="button" class="exp-chip" wire:click="$set('filterCat', '')">
                    <span class="exp-chip-name">Clear</span>
                    <span class="exp-chip-val"><i class="bi bi-x-circle"></i></span>
                </button>
            @endif
        </div>
    @endif

    <div class="row g-3">

        {{-- Form --}}
        @if ($showForm)
            <div class="col-3">
                <div class="table-card">
                    <div class="table-card-header">
                        <span class="table-card-title">
                            {{ $editId ? 'Edit Expense' : 'New Expense' }}
                        </span>
                        <button class="btn btn-sm btn-outline-secondary action-btn" wire:click="resetForm">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <div style="padding:16px;">

                        {{-- Category --}}
                        <div class="mb-3">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            @if (!$showCatForm)
                                <div class="d-flex gap-2">
                                    <select wire:model="categoryId"
                                        class="form-select @error('categoryId') is-invalid @enderror">
                                        <option value="">Select category...</option>
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" wire:click="$set('showCatForm', true)"
                                        class="btn btn-outline-secondary" style="padding:0 10px;" title="Add category">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                                @error('categoryId')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            @else
                                <div style="background:#f7fafc; border:1.5px solid var(--border); border-radius:8px; padding:10px;">
                                    <div class="row g-2">
                                        <div class="col-7">
                                            <input type="text" wire:model="catName"
                                                class="form-control form-control-sm" placeholder="Category name *">
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
                                @foreach ($accounts as $acc)
                                    <option value="{{ $acc->id }}">
                                        {{ $acc->name }}
                                        (Rs. {{ number_format($acc->current_balance, 0) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('accountId')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Amount --}}
                        <div class="mb-3">
                            <label class="form-label">Amount (Rs.) <span class="text-danger">*</span></label>
                            <input type="number" wire:model="amount"
                                class="form-control @error('amount') is-invalid @enderror" min="1"
                                placeholder="0">
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Date --}}
                        <div class="mb-3">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" wire:model="expenseDate"
                                class="form-control @error('expenseDate') is-invalid @enderror">
                            @error('expenseDate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Description --}}
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea wire:model="description" class="form-control" rows="2" placeholder="Details..."></textarea>
                        </div>

                        {{-- Reference --}}
                        <div class="mb-4">
                            <label class="form-label">Reference #</label>
                            <input type="text" wire:model="reference" class="form-control"
                                placeholder="Bill # or receipt #">
                        </div>

                        <div class="d-flex gap-2">
                            <button class="btn btn-primary btn-sm flex-fill" wire:click="saveExpense"
                                wire:loading.attr="disabled" wire:target="saveExpense">
                                <span wire:loading wire:target="saveExpense">
                                    <span class="spinner-border spinner-border-sm me-1"></span>
                                </span>
                                {{ $editId ? 'Update' : 'Save Expense' }}
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" wire:click="resetForm">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Table --}}
        <div class="{{ $showForm ? 'col-6' : 'col-9' }}">
            <div class="table-card">
                <div class="table-card-header" style="flex-wrap:wrap; gap:10px;">
                    <div class="d-flex gap-2 align-items-center">
                        <select wire:model.live="filterCat" class="form-select form-select-sm" style="width:170px;">
                            <option value="">All Categories</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <span style="font-size:11px; color:var(--text-muted);">
                            {{ $expenses->total() }} result{{ $expenses->total() == 1 ? '' : 's' }}
                        </span>
                    </div>
                    <div style="width:220px;">
                        <input type="text" wire:model.live.debounce.400ms="search"
                            class="form-control form-control-sm" placeholder="Search description or ref...">
                    </div>
                </div>

                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width:88px;">Date</th>
                            <th style="width:140px;">Category</th>
                            <th>Description</th>
                            <th style="width:120px;">Account</th>
                            <th style="width:120px; text-align:right;">Amount</th>
                            <th style="width:78px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $lastDate = null; @endphp
                        @forelse ($expenses as $expense)
                            @php $d = $expense->expense_date->toDateString(); @endphp
                            @if ($lastDate !== $d)
                                <tr class="exp-daygroup">
                                    <td colspan="6">{{ $expense->expense_date->format('l, d M Y') }}</td>
                                </tr>
                                @php $lastDate = $d; @endphp
                            @endif

                            <tr>
                                <td style="font-size:12px;">
                                    {{ $expense->expense_date->format('d/m/Y') }}
                                </td>
                                <td>
                                    <span
                                        style="font-size:11px; font-weight:600; padding:2px 8px; border-radius:4px; background:{{ $expense->category->color }}22; color:{{ $expense->category->color }};">
                                        {{ $expense->category->name }}
                                    </span>
                                </td>
                                <td style="font-size:12px;">
                                    {{ $expense->description ?? '—' }}
                                    <div style="font-size:10px; color:var(--text-muted);">
                                        @if ($expense->reference)
                                            Ref {{ $expense->reference }} ·
                                        @endif
                                        {{ $expense->createdBy?->name ?? 'Unknown' }}
                                    </div>
                                </td>
                                <td style="font-size:12px;">{{ $expense->account->name }}</td>
                                <td
                                    style="text-align:right; font-weight:700; font-size:13px; color:#a52a2a; font-variant-numeric:tabular-nums;">
                                    Rs. {{ number_format($expense->amount, 0) }}
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-outline-secondary action-btn"
                                            wire:click="openEdit({{ $expense->id }})" title="Edit">
                                            <i class="bi bi-pencil" style="font-size:11px;"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger action-btn"
                                            wire:click="confirmDelete({{ $expense->id }})" title="Delete">
                                            <i class="bi bi-trash" style="font-size:11px;"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6"
                                    style="text-align:center; padding:34px; color:var(--text-muted); font-size:13px;">
                                    <i class="bi bi-receipt" style="font-size:32px; display:block; margin-bottom:8px;"></i>
                                    No expenses in this period
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($expenses->count())
                        <tfoot>
                            <tr>
                                <td colspan="4" style="text-align:right; font-size:12px;">Period total</td>
                                <td style="text-align:right; font-weight:800; color:#a52a2a;">
                                    Rs. {{ number_format($periodTotal, 0) }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>

                @if ($expenses->hasPages())
                    <div style="padding:12px 16px; border-top:1px solid var(--border);">
                        {{ $expenses->links('vendor.pagination.simple-bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Breakdown --}}
        <div class="col-3">
            <div class="table-card">
                <div class="table-card-header">
                    <span class="table-card-title">By Category</span>
                    <span style="font-size:11px; color:var(--text-muted);">{{ $breakdown->count() }}</span>
                </div>
                <div style="padding:12px 14px;">
                    @php $maxTotal = (float) ($breakdown->max('total') ?: 1); @endphp
                    @forelse ($breakdown as $row)
                        @php $cat = $categories->get($row->expense_category_id); @endphp
                        <div class="exp-break-row">
                            <div class="exp-break-top">
                                <span class="exp-break-name">{{ $cat?->name ?? 'Uncategorised' }}</span>
                                <span class="exp-break-val">Rs. {{ number_format($row->total, 0) }}</span>
                            </div>
                            <div class="exp-break-bar">
                                <div style="width:{{ max(3, round(($row->total / $maxTotal) * 100)) }}%;"></div>
                            </div>
                            <div class="exp-break-sub">
                                {{ $row->entries }} entr{{ $row->entries == 1 ? 'y' : 'ies' }} ·
                                {{ $periodTotal > 0 ? round(($row->total / $periodTotal) * 100) : 0 }}% of spend
                            </div>
                        </div>
                    @empty
                        <div style="font-size:12px; color:var(--text-muted); text-align:center; padding:20px 0;">
                            No spending in this period
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="table-card mt-3">
                <div class="table-card-header">
                    <span class="table-card-title">Accounts</span>
                </div>
                <div style="padding:8px 14px 12px;">
                    @foreach ($accounts as $acc)
                        <div class="cb-acct-row">
                            <span>{{ $acc->name }}</span>
                            <strong>Rs. {{ number_format($acc->current_balance, 0) }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Confirm --}}
    @if ($deleteId)
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
                        <button class="btn btn-sm btn-danger" wire:click="delete()">Yes, Delete</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
