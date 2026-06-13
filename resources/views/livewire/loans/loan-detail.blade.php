<div>
    @if (session('success'))
        <div class="alert alert-success py-2 mb-3" style="font-size:13px;">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <div class="page-title">
                {{ $lender->name }}
                @if ($lender->relation)
                    <span style="font-size:13px; font-weight:500; color:var(--text-muted); margin-left:8px;">
                        ({{ $lender->relation }})
                    </span>
                @endif
            </div>
            <div class="page-subtitle">
                Loan Statement
                @if ($lender->phone)
                    · {{ $lender->phone }}
                @endif
            </div>
        </div>
        <a href="{{ route('loans.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="row g-3">

        {{-- Left: Transaction Form + History --}}
        <div class="col-8">

            {{-- Add Transaction Form --}}
            @if ($showForm)
                <div class="table-card mb-3" style="padding:20px; border:1.5px solid {{ $txnType === 'received' ? '#fed7d7' : '#9ae6b4' }};">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div style="font-size:13px; font-weight:700; color:var(--navy);">
                            <i class="bi bi-{{ $txnType === 'received' ? 'arrow-down-circle text-danger' : 'arrow-up-circle text-success' }} me-1"></i>
                            {{ $editTxnId ? 'Edit Transaction' : ($txnType === 'received' ? 'Record Borrowed Money' : 'Record Repayment') }}
                        </div>
                        <button class="btn btn-sm btn-outline-secondary action-btn"
                            wire:click="resetTxnForm">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>

                    <div class="row g-3 align-items-end">
                        <div class="col-3">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <select wire:model.live="txnType"
                                class="form-select @error('txnType') is-invalid @enderror">
                                <option value="received">Received (Borrowed)</option>
                                <option value="paid">Paid (Repayment)</option>
                            </select>
                        </div>
                        <div class="col-2">
                            <label class="form-label">Amount (Rs.) <span class="text-danger">*</span></label>
                            <input type="number" wire:model="txnAmount"
                                class="form-control @error('txnAmount') is-invalid @enderror"
                                min="1" placeholder="0" autofocus>
                            @error('txnAmount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-3">
                            <label class="form-label">Account <span class="text-danger">*</span></label>
                            <select wire:model="txnAccountId"
                                class="form-select @error('txnAccountId') is-invalid @enderror">
                                <option value="">Select account...</option>
                                @foreach ($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                                @endforeach
                            </select>
                            @error('txnAccountId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-2">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" wire:model="txnDate" class="form-control">
                        </div>
                        <div class="col-2">
                            <button class="btn btn-{{ $txnType === 'received' ? 'danger' : 'success' }} btn-sm w-100"
                                wire:click="saveTxn" wire:loading.attr="disabled">
                                <span wire:loading wire:target="saveTxn">
                                    <span class="spinner-border spinner-border-sm me-1"></span>
                                </span>
                                <i class="bi bi-check-lg me-1"></i>
                                {{ $editTxnId ? 'Update' : 'Save' }}
                            </button>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Note</label>
                            <input type="text" wire:model="txnNote" class="form-control"
                                placeholder="e.g. For buying stock from Khan Brothers, For salon payment...">
                        </div>
                    </div>

                    @if ($editTxnId)
                        <div class="alert alert-warning py-1 mt-3 mb-0" style="font-size:11px;">
                            <i class="bi bi-info-circle me-1"></i>
                            Editing only updates the record — account balance is not re-adjusted.
                            Delete and re-add the transaction if you need to correct the account.
                        </div>
                    @endif
                </div>
            @endif

            {{-- Transaction History --}}
            <div class="table-card" style="padding:20px;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted);">
                        <i class="bi bi-clock-history me-1"></i> Statement
                    </div>
                    @if (!$showForm)
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-danger action-btn"
                                wire:click="openAdd('received')">
                                <i class="bi bi-arrow-down-circle me-1"></i> Borrowed
                            </button>
                            <button class="btn btn-sm btn-outline-success action-btn"
                                wire:click="openAdd('paid')">
                                <i class="bi bi-arrow-up-circle me-1"></i> Paid Back
                            </button>
                        </div>
                    @endif
                </div>

                <table class="table mb-0" style="font-size:12px;">
                    <thead>
                        <tr>
                            <th style="width:90px;">Date</th>
                            <th style="width:90px;">Type</th>
                            <th style="text-align:right; width:110px;">Amount</th>
                            <th>Account</th>
                            <th>Note</th>
                            <th style="text-align:right; width:120px;">Balance</th>
                            <th style="width:60px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $txn)
                            <tr style="{{ $txn->type === 'received' ? 'background:#fff5f5;' : 'background:#f0fff4;' }}">
                                <td style="font-size:11px; color:var(--text-muted);">
                                    {{ $txn->date->format('d/m/Y') }}
                                </td>
                                <td>
                                    @if ($txn->type === 'received')
                                        <span style="font-size:11px; background:#fed7d7; color:#c53030; padding:2px 8px; border-radius:4px; font-weight:700;">
                                            <i class="bi bi-arrow-down-circle me-1"></i> Borrowed
                                        </span>
                                    @else
                                        <span style="font-size:11px; background:#9ae6b4; color:#1a4731; padding:2px 8px; border-radius:4px; font-weight:700;">
                                            <i class="bi bi-arrow-up-circle me-1"></i> Paid Back
                                        </span>
                                    @endif
                                </td>
                                <td style="text-align:right; font-weight:700; color:{{ $txn->type === 'received' ? '#c53030' : '#276749' }};">
                                    {{ $txn->type === 'received' ? '+' : '-' }}
                                    Rs. {{ number_format($txn->amount, 0) }}
                                </td>
                                <td style="font-size:11px; color:var(--text-muted);">
                                    {{ $txn->account?->name ?? '—' }}
                                </td>
                                <td style="color:var(--text-primary);">
                                    {{ $txn->note ?? '—' }}
                                </td>
                                <td style="text-align:right; font-weight:800; font-size:13px; color:{{ $txn->balance_after > 0 ? '#b7791f' : '#276749' }};">
                                    Rs. {{ number_format($txn->balance_after, 0) }}
                                    @if ($txn->balance_after <= 0)
                                        <i class="bi bi-check-circle-fill ms-1" style="font-size:11px;"></i>
                                    @endif
                                </td>
                                <td style="text-align:center;">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <button class="btn btn-sm action-btn"
                                            style="padding:2px 6px; border:1px solid var(--border);"
                                            wire:click="editTxn({{ $txn->id }})"
                                            title="Edit">
                                            <i class="bi bi-pencil" style="font-size:10px;"></i>
                                        </button>
                                        <button class="btn btn-sm action-btn"
                                            style="padding:2px 6px; border:1px solid #fed7d7; color:#c53030;"
                                            wire:click="deleteTxn({{ $txn->id }})"
                                            wire:confirm="Delete this transaction? Account balance will NOT be auto-reversed."
                                            title="Delete">
                                            <i class="bi bi-trash" style="font-size:10px;"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align:center; padding:30px; color:var(--text-muted);">
                                    No transactions yet. Use the buttons above to record borrowed or repaid amounts.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

        {{-- Right: Summary Card --}}
        <div class="col-4">

            <div style="background:var(--navy); border-radius:12px; padding:24px; margin-bottom:16px; color:#fff;">
                <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:rgba(255,255,255,0.5); margin-bottom:16px;">
                    Summary — {{ $lender->name }}
                </div>

                <div style="display:flex; justify-content:space-between; margin-bottom:10px; padding-bottom:10px; border-bottom:1px solid rgba(255,255,255,0.1);">
                    <span style="font-size:13px; color:rgba(255,255,255,0.7);">Total Borrowed</span>
                    <span style="font-size:14px; font-weight:800; color:#fc8181;">
                        Rs. {{ number_format($totalReceived, 0) }}
                    </span>
                </div>

                <div style="display:flex; justify-content:space-between; margin-bottom:10px; padding-bottom:10px; border-bottom:1px solid rgba(255,255,255,0.1);">
                    <span style="font-size:13px; color:rgba(255,255,255,0.7);">Total Paid Back</span>
                    <span style="font-size:14px; font-weight:800; color:#68d391;">
                        Rs. {{ number_format($totalPaid, 0) }}
                    </span>
                </div>

                <div style="display:flex; justify-content:space-between; margin-top:6px;">
                    <span style="font-size:14px; font-weight:700; color:rgba(255,255,255,0.9);">Still Owed</span>
                    <span style="font-size:20px; font-weight:900; color:{{ $outstanding > 0 ? '#fbd38d' : '#68d391' }};">
                        Rs. {{ number_format($outstanding, 0) }}
                        @if ($outstanding <= 0)
                            <i class="bi bi-check-circle-fill ms-1" style="font-size:14px;"></i>
                        @endif
                    </span>
                </div>
            </div>

            {{-- Quick Add Buttons --}}
            @if (!$showForm)
                <div class="d-flex flex-column gap-2 mb-3">
                    <button class="btn btn-outline-danger w-100"
                        wire:click="openAdd('received')"
                        style="font-size:13px; font-weight:600; padding:10px;">
                        <i class="bi bi-arrow-down-circle me-2"></i> Borrowed Money
                    </button>
                    <button class="btn btn-outline-success w-100"
                        wire:click="openAdd('paid')"
                        style="font-size:13px; font-weight:600; padding:10px;">
                        <i class="bi bi-arrow-up-circle me-2"></i> Paid Back
                    </button>
                </div>
            @endif

            {{-- Lender Info --}}
            @if ($lender->notes)
                <div class="table-card" style="padding:14px 16px;">
                    <div style="font-size:10px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:6px;">
                        Notes
                    </div>
                    <div style="font-size:12px; color:var(--text-primary); white-space:pre-line;">{{ $lender->notes }}</div>
                </div>
            @endif

        </div>
    </div>
</div>
