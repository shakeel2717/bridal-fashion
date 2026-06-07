<div>
    @if(session('success'))
        <div class="alert alert-success py-2 mb-3" style="font-size:13px;">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">Accounts</div>
            <div class="page-subtitle">Cash, bank and wallet balances</div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-warning"
                    wire:click="$set('showWithdrawalForm', true)">
                <i class="bi bi-cash-coin me-1"></i> Owner Withdrawal
            </button>
            <button class="btn btn-sm btn-outline-info"
                    wire:click="$set('showTransferForm', true)">
                <i class="bi bi-arrow-left-right me-1"></i> Transfer
            </button>
            <button class="btn btn-sm btn-primary"
                    wire:click="openCreate">
                <i class="bi bi-plus-lg me-1"></i> Add Account
            </button>
        </div>
    </div>

    {{-- Transfer Form --}}
    @if($showTransferForm)
    <div class="transfer-form-box">
        <div style="font-size:12px; font-weight:700; color:#2c5282; margin-bottom:14px;">
            <i class="bi bi-arrow-left-right me-2"></i> Transfer Between Accounts
        </div>
        <div class="row g-3">
            <div class="col-3">
                <label class="form-label">From Account <span class="text-danger">*</span></label>
                <select wire:model="transferFromId"
                        class="form-select @error('transferFromId') is-invalid @enderror">
                    <option value="">Select...</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}">
                            {{ $acc->name }} (Rs. {{ number_format($acc->current_balance, 0) }})
                        </option>
                    @endforeach
                </select>
                @error('transferFromId') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-3">
                <label class="form-label">To Account <span class="text-danger">*</span></label>
                <select wire:model="transferToId"
                        class="form-select @error('transferToId') is-invalid @enderror">
                    <option value="">Select...</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                    @endforeach
                </select>
                @error('transferToId') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-2">
                <label class="form-label">Amount (Rs.) <span class="text-danger">*</span></label>
                <input type="number" wire:model="transferAmount"
                       class="form-control @error('transferAmount') is-invalid @enderror"
                       min="1" placeholder="0">
                @error('transferAmount') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-2">
                <label class="form-label">Date</label>
                <input type="date" wire:model="transferDate" class="form-control">
            </div>
            <div class="col-2">
                <label class="form-label">Description</label>
                <input type="text" wire:model="transferDesc"
                       class="form-control" placeholder="Optional">
            </div>
            <div class="col-12 d-flex gap-2">
                <button class="btn btn-sm btn-primary"
                        wire:click="saveTransfer"
                        wire:loading.attr="disabled">
                    <span wire:loading wire:target="saveTransfer">
                        <span class="spinner-border spinner-border-sm me-1"></span>
                    </span>
                    Confirm Transfer
                </button>
                <button class="btn btn-sm btn-outline-secondary"
                        wire:click="$set('showTransferForm', false)">Cancel</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Owner Withdrawal Form --}}
    @if($showWithdrawalForm)
    <div class="withdrawal-form-box">
        <div style="font-size:12px; font-weight:700; color:#b7791f; margin-bottom:14px;">
            <i class="bi bi-cash-coin me-2"></i> Owner Withdrawal
        </div>
        <div class="row g-3">
            <div class="col-3">
                <label class="form-label">From Account <span class="text-danger">*</span></label>
                <select wire:model="withdrawalAccountId"
                        class="form-select @error('withdrawalAccountId') is-invalid @enderror">
                    <option value="">Select...</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}">
                            {{ $acc->name }} (Rs. {{ number_format($acc->current_balance, 0) }})
                        </option>
                    @endforeach
                </select>
                @error('withdrawalAccountId') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-2">
                <label class="form-label">Amount (Rs.) <span class="text-danger">*</span></label>
                <input type="number" wire:model="withdrawalAmount"
                       class="form-control @error('withdrawalAmount') is-invalid @enderror"
                       min="1" placeholder="0">
                @error('withdrawalAmount') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-2">
                <label class="form-label">Date</label>
                <input type="date" wire:model="withdrawalDate" class="form-control">
            </div>
            <div class="col-3">
                <label class="form-label">Description</label>
                <input type="text" wire:model="withdrawalDesc"
                       class="form-control" placeholder="e.g. Monthly personal expenses">
            </div>
            <div class="col-2" style="padding-top:24px;">
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-warning text-white"
                            wire:click="saveWithdrawal"
                            wire:loading.attr="disabled">
                        <span wire:loading wire:target="saveWithdrawal">
                            <span class="spinner-border spinner-border-sm me-1"></span>
                        </span>
                        Withdraw
                    </button>
                    <button class="btn btn-sm btn-outline-secondary"
                            wire:click="$set('showWithdrawalForm', false)">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Account Form --}}
    @if($showForm)
    <div class="table-card mb-3" style="padding:20px;">
        <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:14px;">
            {{ $editId ? 'Edit Account' : 'New Account' }}
        </div>
        <div class="row g-3">
            <div class="col-4">
                <label class="form-label">Account Name <span class="text-danger">*</span></label>
                <input type="text" wire:model="name"
                       class="form-control @error('name') is-invalid @enderror"
                       placeholder="e.g. HBL Main Account">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-3">
                <label class="form-label">Type <span class="text-danger">*</span></label>
                <select wire:model="type" class="form-select">
                    <option value="cash">Cash</option>
                    <option value="bank">Bank</option>
                    <option value="mobile_wallet">Mobile Wallet</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="col-3">
                <label class="form-label">Bank Name</label>
                <input type="text" wire:model="bankName"
                       class="form-control" placeholder="e.g. HBL">
            </div>
            <div class="col-2">
                <label class="form-label">Account Number</label>
                <input type="text" wire:model="accountNumber"
                       class="form-control" placeholder="Optional">
            </div>
            @if(!$editId)
            <div class="col-3">
                <label class="form-label">Opening Balance (Rs.)</label>
                <input type="number" wire:model="openingBalance"
                       class="form-control @error('openingBalance') is-invalid @enderror"
                       min="0" placeholder="0">
                @error('openingBalance') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            @endif
            <div class="col-3" style="padding-top:{{ $editId ? '0' : '24px' }};">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox"
                           wire:model="isDefault" id="isDefault">
                    <label class="form-check-label" for="isDefault" style="font-size:12px;">
                        Set as Default Account
                    </label>
                </div>
            </div>
            <div class="col-12 d-flex gap-2">
                <button class="btn btn-sm btn-primary"
                        wire:click="saveAccount"
                        wire:loading.attr="disabled">Save Account</button>
                <button class="btn btn-sm btn-outline-secondary"
                        wire:click="resetAccountForm">Cancel</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Total Balance --}}
    <div style="background:var(--navy); color:#fff; border-radius:10px; padding:16px 24px; margin-bottom:20px; display:flex; align-items:center; justify-content:space-between;">
        <div>
            <div style="font-size:11px; color:var(--navy-muted); text-transform:uppercase; font-weight:700; margin-bottom:4px;">
                Total Balance Across All Accounts
            </div>
            <div style="font-size:28px; font-weight:800; color:#fff;">
                Rs. {{ number_format($totalBalance, 0) }}
            </div>
        </div>
        <i class="bi bi-bank2" style="font-size:40px; color:rgba(255,255,255,0.15);"></i>
    </div>

    {{-- Account Cards --}}
    <div class="row g-3 mb-4">
        @forelse($accounts as $account)
        <div class="col-3">
            <div class="account-card {{ $account->is_default ? 'default-account' : '' }} {{ $selectedAccountId === $account->id ? 'border-gold' : '' }}"
                 wire:click="selectAccount({{ $account->id }})">
                <div class="account-type-icon {{ $account->type }}">
                    @if($account->type === 'cash') <i class="bi bi-cash-stack"></i>
                    @elseif($account->type === 'bank') <i class="bi bi-bank"></i>
                    @elseif($account->type === 'mobile_wallet') <i class="bi bi-phone"></i>
                    @else <i class="bi bi-wallet2"></i>
                    @endif
                </div>
                <div class="account-name">{{ $account->name }}</div>
                <div class="account-type-label">
                    {{ ucfirst(str_replace('_', ' ', $account->type)) }}
                    @if($account->is_default)
                        <span style="color:var(--gold); margin-left:4px;">★ Default</span>
                    @endif
                </div>
                <div class="account-balance {{ $account->current_balance < 0 ? 'negative' : '' }}">
                    Rs. {{ number_format($account->current_balance, 0) }}
                </div>
                <div class="account-balance-label">Current Balance</div>

                <div class="d-flex gap-1 mt-3">
                    <button class="btn btn-sm btn-outline-secondary action-btn"
                            wire:click.stop="openEdit({{ $account->id }})"
                            title="Edit">
                        <i class="bi bi-pencil" style="font-size:11px;"></i>
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12" style="text-align:center; padding:30px; color:var(--text-muted); font-size:13px;">
            No accounts yet — add your first account above.
        </div>
        @endforelse
    </div>

    {{-- Transactions Panel --}}
    @if($selectedAccountId)
    @php $selectedAccount = $accounts->find($selectedAccountId); @endphp
    <div class="table-card">
        <div class="table-card-header">
            <div class="table-card-title">
                <i class="bi bi-clock-history me-1"></i>
                Transactions — {{ $selectedAccount?->name }}
            </div>
            <button class="btn btn-sm btn-outline-secondary action-btn"
                    wire:click="selectAccount(null)">
                <i class="bi bi-x"></i>
            </button>
        </div>

        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th style="text-align:right;">Amount</th>
                    <th style="text-align:right;">Balance After</th>
                    <th>By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $txn)
                <tr>
                    <td style="font-size:12px;">
                        {{ $txn->transaction_date->format('d/m/Y') }}
                    </td>
                    <td>
                        <span class="txn-type-badge {{ $txn->type }}">
                            {{ $txn->type === 'credit' ? '↑ In' : '↓ Out' }}
                        </span>
                    </td>
                    <td>
                        <span class="txn-category-label">
                            {{ ucfirst(str_replace('_', ' ', $txn->category ?? 'other')) }}
                        </span>
                    </td>
                    <td style="font-size:12px;">{{ $txn->description ?? '—' }}</td>
                    <td style="text-align:right; font-weight:700; font-size:13px;
                               color:{{ $txn->type === 'credit' ? '#276749' : '#c53030' }};">
                        {{ $txn->type === 'credit' ? '+' : '-' }}
                        Rs. {{ number_format($txn->amount, 0) }}
                    </td>
                    <td style="text-align:right; font-size:12px; color:var(--text-muted);">
                        Rs. {{ number_format($txn->balance_after, 0) }}
                    </td>
                    <td style="font-size:11px; color:var(--text-muted);">
                        {{ $txn->createdBy?->name ?? 'System' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center; padding:20px; color:var(--text-muted); font-size:13px;">
                        No transactions yet
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif
</div>