@php
    $catLabels = [
        'opening_balance' => 'Opening Balance',
        'cash_in' => 'Cash In',
        'cash_out' => 'Cash Out',
        'owner_investment' => 'Owner Investment',
        'other_income' => 'Other Income',
        'owner_withdrawal' => 'Owner Withdrawal',
        'expense' => 'Expense',
        'salary' => 'Salary',
        'rental_payment' => 'Rental Payment',
        'rental_payment_reversal' => 'Rental Payment Reversal',
        'rental_fine' => 'Rental Fine',
        'sale_payment' => 'Sale Payment',
        'sale_return_refund' => 'Sale Return Refund',
        'purchase_return_refund' => 'Purchase Return Refund',
        'vendor_payment' => 'Vendor Payment',
        'loan_received' => 'Loan Received',
        'loan_repayment' => 'Loan Repayment',
        'transfer_in' => 'Transfer In',
        'transfer_out' => 'Transfer Out',
    ];
    $label = fn ($c) => $catLabels[$c] ?? ucwords(str_replace('_', ' ', (string) $c ?: 'Other'));
@endphp

<div>
    @if (session('success'))
        <div class="alert alert-success py-2 mb-3" style="font-size:13px;">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger py-2 mb-3" style="font-size:13px;">
            <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">Cashbook</div>
            <div class="page-subtitle" style="margin-bottom:0;">
                Every rupee in and out — {{ $this->periodRangeLabel() }}
            </div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-sm cb-btn-in" wire:click="openEntry('in')">
                <i class="bi bi-arrow-down-left me-1"></i> Money In
            </button>
            <button class="btn btn-sm cb-btn-out" wire:click="openEntry('out')">
                <i class="bi bi-arrow-up-right me-1"></i> Money Out
            </button>
        </div>
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
    <div class="cb-summary">
        <div class="cb-sum-card">
            <div class="cb-sum-label">Opening</div>
            <div class="cb-sum-value {{ $opening < 0 ? 'is-negative' : '' }}">
                {{ $opening < 0 ? '−' : '' }}Rs. {{ number_format(abs($opening), 0) }}
            </div>
            <div class="cb-sum-sub">Before {{ $this->periodStart()->format('d M Y') }}</div>
        </div>

        <div class="cb-sum-card cb-in">
            <div class="cb-sum-label"><i class="bi bi-arrow-down-left me-1"></i> Money In</div>
            <div class="cb-sum-value">Rs. {{ number_format($moneyIn, 0) }}</div>
            <div class="cb-sum-sub">
                @if ($prevIn > 0)
                    @php $d = round((($moneyIn - $prevIn) / $prevIn) * 100); @endphp
                    <i class="bi {{ $d >= 0 ? 'bi-arrow-up-short' : 'bi-arrow-down-short' }}"></i>
                    {{ abs($d) }}% vs previous
                @else
                    Previous: Rs. {{ number_format($prevIn, 0) }}
                @endif
            </div>
        </div>

        <div class="cb-sum-card cb-out">
            <div class="cb-sum-label"><i class="bi bi-arrow-up-right me-1"></i> Money Out</div>
            <div class="cb-sum-value">Rs. {{ number_format($moneyOut, 0) }}</div>
            <div class="cb-sum-sub">
                @if ($prevOut > 0)
                    @php $d = round((($moneyOut - $prevOut) / $prevOut) * 100); @endphp
                    <i class="bi {{ $d >= 0 ? 'bi-arrow-up-short' : 'bi-arrow-down-short' }}"></i>
                    {{ abs($d) }}% vs previous
                @else
                    Previous: Rs. {{ number_format($prevOut, 0) }}
                @endif
            </div>
        </div>

        <div class="cb-sum-card cb-net">
            <div class="cb-sum-label">Net Flow</div>
            <div class="cb-sum-value {{ $netFlow < 0 ? 'is-negative' : '' }}">
                {{ $netFlow < 0 ? '−' : '' }}Rs. {{ number_format(abs($netFlow), 0) }}
            </div>
            <div class="cb-sum-sub">
                Closing: {{ $closing < 0 ? '−' : '' }}Rs. {{ number_format(abs($closing), 0) }}
            </div>
        </div>

        <div class="cb-sum-card cb-accent">
            <div class="cb-sum-label">Cash On Hand</div>
            <div class="cb-sum-value {{ $totalBalance < 0 ? 'is-negative' : '' }}">
                {{ $totalBalance < 0 ? '−' : '' }}Rs. {{ number_format(abs($totalBalance), 0) }}
            </div>
            <div class="cb-sum-sub">All accounts, live balance</div>
        </div>
    </div>

    {{-- In / Out flow bar --}}
    @if ($moneyIn > 0 || $moneyOut > 0)
        @php
            $flowTotal = $moneyIn + $moneyOut;
            $inPct = $flowTotal > 0 ? round(($moneyIn / $flowTotal) * 100) : 0;
        @endphp
        <div class="cb-flowbar" title="{{ $inPct }}% in / {{ 100 - $inPct }}% out">
            <div class="cb-flowbar-in" style="width:{{ $inPct }}%;"></div>
            <div class="cb-flowbar-out" style="width:{{ 100 - $inPct }}%;"></div>
        </div>
    @endif

    {{-- Entry form --}}
    @if ($entryMode)
        <div class="cb-entry-box {{ $entryMode === 'in' ? 'is-in' : 'is-out' }}">
            <div class="cb-entry-head">
                <span>
                    <i class="bi {{ $entryMode === 'in' ? 'bi-arrow-down-left' : 'bi-arrow-up-right' }} me-2"></i>
                    Record {{ $entryMode === 'in' ? 'Money In' : 'Money Out' }}
                </span>
                <button class="btn btn-sm btn-outline-secondary action-btn" wire:click="resetEntry">
                    <i class="bi bi-x"></i>
                </button>
            </div>

            <div class="row g-3">
                <div class="col-2">
                    <label class="form-label">Amount (Rs.) <span class="text-danger">*</span></label>
                    <input type="number" min="1" wire:model="entryAmount" autofocus
                        class="form-control @error('entryAmount') is-invalid @enderror" placeholder="0">
                    @error('entryAmount')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-3">
                    <label class="form-label">
                        {{ $entryMode === 'in' ? 'Into Account' : 'Pay From' }} <span class="text-danger">*</span>
                    </label>
                    <select wire:model="entryAccountId"
                        class="form-select @error('entryAccountId') is-invalid @enderror">
                        <option value="">Select account...</option>
                        @foreach ($accounts as $acc)
                            <option value="{{ $acc->id }}">
                                {{ $acc->name }} (Rs. {{ number_format($acc->current_balance, 0) }})
                            </option>
                        @endforeach
                    </select>
                    @error('entryAccountId')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-3">
                    @if ($entryMode === 'in')
                        <label class="form-label">Type</label>
                        <select wire:model="entryCategory" class="form-select">
                            <option value="cash_in">Cash In (general)</option>
                            <option value="other_income">Other Income</option>
                            <option value="owner_investment">Owner Investment</option>
                        </select>
                    @else
                        <label class="form-label">Expense Category</label>
                        <select wire:model.live="entryExpenseCategoryId" class="form-select">
                            <option value="">Not an expense — general cash out</option>
                            @foreach ($expenseCategories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <div class="col-2">
                    <label class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" wire:model="entryDate"
                        class="form-control @error('entryDate') is-invalid @enderror">
                    @error('entryDate')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-2">
                    <label class="form-label">Reference #</label>
                    <input type="text" wire:model="entryReference" class="form-control" placeholder="Optional">
                </div>

                <div class="col-8">
                    <label class="form-label">Description <span class="text-danger">*</span></label>
                    <input type="text" wire:model="entryDescription"
                        class="form-control @error('entryDescription') is-invalid @enderror"
                        placeholder="What is this payment for?">
                    @error('entryDescription')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-4 d-flex align-items-end gap-2">
                    <button class="btn btn-sm flex-fill {{ $entryMode === 'in' ? 'cb-btn-in' : 'cb-btn-out' }}"
                        wire:click="saveEntry" wire:loading.attr="disabled" wire:target="saveEntry">
                        <span wire:loading wire:target="saveEntry">
                            <span class="spinner-border spinner-border-sm me-1"></span>
                        </span>
                        Save {{ $entryMode === 'in' ? 'Money In' : 'Money Out' }}
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" wire:click="resetEntry">Cancel</button>
                </div>

                @if ($entryMode === 'out' && $entryExpenseCategoryId)
                    <div class="col-12" style="font-size:11px; color:var(--text-muted);">
                        <i class="bi bi-info-circle me-1"></i>
                        This will also be saved in the Expenses module.
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class="row g-3">
        {{-- Transactions --}}
        <div class="col-9">
            <div class="table-card">
                <div class="table-card-header" style="flex-wrap:wrap; gap:10px;">
                    <div class="d-flex gap-2 align-items-center">
                        <div class="cb-flow-toggle">
                            <button type="button" class="{{ $flow === '' ? 'active' : '' }}"
                                wire:click="$set('flow', '')">All</button>
                            <button type="button" class="{{ $flow === 'in' ? 'active' : '' }}"
                                wire:click="$set('flow', 'in')">In</button>
                            <button type="button" class="{{ $flow === 'out' ? 'active' : '' }}"
                                wire:click="$set('flow', 'out')">Out</button>
                        </div>

                        <select wire:model.live="filterCategory" class="form-select form-select-sm"
                            style="width:170px;">
                            <option value="">All Types</option>
                            @foreach ($usedCategories as $cat)
                                <option value="{{ $cat }}">{{ $label($cat) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="width:230px;">
                        <input type="text" wire:model.live.debounce.400ms="search"
                            class="form-control form-control-sm" placeholder="Search description...">
                    </div>
                </div>

                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width:92px;">Date</th>
                            <th style="width:130px;">Type</th>
                            <th>Description</th>
                            <th style="width:130px;">Account</th>
                            <th style="width:120px; text-align:right;">In</th>
                            <th style="width:120px; text-align:right;">Out</th>
                            <th style="width:120px; text-align:right;">Balance</th>
                            <th style="width:46px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $lastDate = null; @endphp
                        @forelse ($transactions as $txn)
                            @php $d = $txn->transaction_date->toDateString(); @endphp
                            @if ($lastDate !== $d)
                                <tr class="cb-daygroup">
                                    <td colspan="8">
                                        {{ $txn->transaction_date->format('l, d M Y') }}
                                    </td>
                                </tr>
                                @php $lastDate = $d; @endphp
                            @endif

                            <tr>
                                <td style="font-size:12px;">{{ $txn->transaction_date->format('d/m/Y') }}</td>
                                <td>
                                    <span class="cb-cat">{{ $label($txn->category) }}</span>
                                </td>
                                <td style="font-size:12px;">
                                    {{ $txn->description ?? '—' }}
                                    @if ($txn->createdBy)
                                        <div style="font-size:10px; color:var(--text-muted);">
                                            by {{ $txn->createdBy->name }}
                                        </div>
                                    @endif
                                </td>
                                <td style="font-size:12px;">{{ $txn->account?->name ?? '—' }}</td>
                                <td class="cb-amt cb-amt-in">
                                    @if ($txn->type === 'credit')
                                        + {{ number_format($txn->amount, 0) }}
                                    @endif
                                </td>
                                <td class="cb-amt cb-amt-out">
                                    @if ($txn->type === 'debit')
                                        − {{ number_format($txn->amount, 0) }}
                                    @endif
                                </td>
                                <td class="cb-amt" style="color:var(--text-muted);">
                                    {{ number_format($txn->balance_after, 0) }}
                                </td>
                                <td>
                                    @if ($this->isManual($txn))
                                        <button class="btn btn-sm btn-outline-danger action-btn"
                                            wire:click="confirmDelete({{ $txn->id }})" title="Delete entry">
                                            <i class="bi bi-trash" style="font-size:11px;"></i>
                                        </button>
                                    @else
                                        <i class="bi bi-lock" style="font-size:11px; color:#a0aec0;"
                                            title="Created by another module"></i>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8"
                                    style="text-align:center; padding:34px; color:var(--text-muted); font-size:13px;">
                                    <i class="bi bi-journal-text"
                                        style="font-size:32px; display:block; margin-bottom:8px;"></i>
                                    No cash movement in this period
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($transactions->count())
                        <tfoot>
                            <tr>
                                <td colspan="4" style="text-align:right; font-size:12px;">
                                    {{ $isFiltered ? 'Filtered total' : 'Period total' }}
                                </td>
                                <td class="cb-amt cb-amt-in">+ {{ number_format($shownIn, 0) }}</td>
                                <td class="cb-amt cb-amt-out">− {{ number_format($shownOut, 0) }}</td>
                                <td class="cb-amt">{{ number_format($shownIn - $shownOut, 0) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>

                @if ($transactions->hasPages())
                    <div style="padding:12px 16px; border-top:1px solid var(--border);">
                        {{ $transactions->links('vendor.pagination.simple-bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Breakdown --}}
        <div class="col-3">
            <div class="table-card">
                <div class="table-card-header">
                    <span class="table-card-title">Breakdown</span>
                    <span style="font-size:11px; color:var(--text-muted);">{{ $breakdown->count() }} types</span>
                </div>
                <div style="padding:12px 14px;">
                    @php $maxTotal = (float) ($breakdown->max('total') ?: 1); @endphp
                    @forelse ($breakdown as $row)
                        <div class="cb-break-row">
                            <div class="cb-break-top">
                                <span class="cb-break-name">
                                    <i
                                        class="bi {{ $row->type === 'credit' ? 'bi-arrow-down-left' : 'bi-arrow-up-right' }} me-1"
                                        style="font-size:10px; color:{{ $row->type === 'credit' ? '#276749' : '#c53030' }};"></i>
                                    {{ $label($row->category) }}
                                </span>
                                <span class="cb-break-val">Rs. {{ number_format($row->total, 0) }}</span>
                            </div>
                            <div class="cb-break-bar">
                                <div class="{{ $row->type === 'credit' ? 'is-in' : 'is-out' }}"
                                    style="width:{{ max(3, round(($row->total / $maxTotal) * 100)) }}%;"></div>
                            </div>
                            <div class="cb-break-sub">{{ $row->entries }} entr{{ $row->entries == 1 ? 'y' : 'ies' }}</div>
                        </div>
                    @empty
                        <div style="font-size:12px; color:var(--text-muted); text-align:center; padding:20px 0;">
                            Nothing to break down
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="table-card mt-3">
                <div class="table-card-header">
                    <span class="table-card-title">Accounts</span>
                </div>
                <div style="padding:8px 14px 12px;">
                    @foreach ($balanceAccounts as $acc)
                        <div class="cb-acct-row">
                            <span>
                                {{ $acc->name }}
                                @unless ($acc->is_active)
                                    <span style="font-size:10px; color:var(--text-muted);">(inactive)</span>
                                @endunless
                            </span>
                            <strong style="{{ $acc->current_balance < 0 ? 'color:#a52a2a;' : '' }}">
                                Rs. {{ number_format($acc->current_balance, 0) }}
                            </strong>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Delete confirm --}}
    @if ($deleteId)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered" style="max-width:380px;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title">Delete Cashbook Entry</h6>
                    </div>
                    <div class="modal-body" style="font-size:13px;">
                        The entry will be removed and the account balance restored.
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
