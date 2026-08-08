<div>
    @if(session('success'))
        <div class="alert alert-success py-2 mb-3" style="font-size:13px;">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">Advance Ledger</div>
            <div class="page-subtitle">Credit / Debit history per employee</div>
        </div>
        <button class="btn btn-primary btn-sm d-flex align-items-center gap-2"
                wire:click="openCreate">
            <i class="bi bi-plus-lg"></i> Add Entry
        </button>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-2 mb-3">
        <div class="col-3">
            <div style="background:#fff5f5; border:1px solid #fed7d7; border-radius:8px; padding:10px 14px;">
                <div style="font-size:11px; color:#c53030; font-weight:600; text-transform:uppercase;">Total Advances (Debit)</div>
                <div style="font-size:18px; font-weight:800; color:#c53030;">Rs. {{ number_format($totalAdvances, 0) }}</div>
            </div>
        </div>
        <div class="col-3">
            <div style="background:#f0fff4; border:1px solid #9ae6b4; border-radius:8px; padding:10px 14px;">
                <div style="font-size:11px; color:#276749; font-weight:600; text-transform:uppercase;">Salary Deductions</div>
                <div style="font-size:18px; font-weight:800; color:#276749;">Rs. {{ number_format($totalDeductions, 0) }}</div>
            </div>
        </div>
        <div class="col-3">
            <div style="background:#f0fff4; border:1px solid #9ae6b4; border-radius:8px; padding:10px 14px;">
                <div style="font-size:11px; color:#276749; font-weight:600; text-transform:uppercase;">Cash Repayments</div>
                <div style="font-size:18px; font-weight:800; color:#276749;">Rs. {{ number_format($totalRepayments, 0) }}</div>
            </div>
        </div>
        <div class="col-3">
            @php $balanced = $netBalance <= 0; @endphp
            <div style="background:{{ $balanced ? '#f0fff4' : '#fff5f5' }}; border:1px solid {{ $balanced ? '#9ae6b4' : '#fed7d7' }}; border-radius:8px; padding:10px 14px;">
                <div style="font-size:11px; color:{{ $balanced ? '#276749' : '#c53030' }}; font-weight:600; text-transform:uppercase;">Net Outstanding</div>
                <div style="font-size:18px; font-weight:800; color:{{ $balanced ? '#276749' : '#c53030' }};">
                    Rs. {{ number_format(max(0, $netBalance), 0) }}
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Form --}}
        @if($showForm)
        <div class="col-4">
            <div class="table-card">
                <div class="table-card-header">
                    <span class="table-card-title">New Ledger Entry</span>
                    <button class="btn btn-sm btn-outline-secondary action-btn" wire:click="$set('showForm', false)">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <div style="padding:16px;">
                    <div class="mb-3">
                        <label class="form-label">Employee <span class="text-danger">*</span></label>
                        <select wire:model="userId" class="form-select @error('userId') is-invalid @enderror">
                            <option value="">Select employee...</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                        @error('userId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select wire:model="type" class="form-select @error('type') is-invalid @enderror">
                            <option value="advance">Advance Given (Debit)</option>
                            <option value="salary_deduction">Salary Deduction (Credit)</option>
                            <option value="cash_repayment">Cash Repayment (Credit)</option>
                        </select>
                        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount (Rs.) <span class="text-danger">*</span></label>
                        <input type="number" wire:model="amount" min="1"
                               class="form-control @error('amount') is-invalid @enderror" placeholder="0">
                        @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" wire:model="ledgerDate"
                               class="form-control @error('ledgerDate') is-invalid @enderror">
                        @error('ledgerDate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <textarea wire:model="note" class="form-control" rows="2" placeholder="Optional note..."></textarea>
                    </div>
                    <button wire:click="save" wire:loading.attr="disabled" class="btn btn-primary btn-sm w-100">
                        <span wire:loading wire:target="save"><span class="spinner-border spinner-border-sm me-1"></span></span>
                        Save Entry
                    </button>
                </div>
            </div>
        </div>
        @endif

        {{-- Table --}}
        <div class="{{ $showForm ? 'col-8' : 'col-12' }}">
            <div class="table-card">
                <div class="table-card-header" style="flex-wrap:wrap; gap:10px;">
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <select wire:model.live="filterUser" class="form-select form-select-sm" style="width:160px;">
                            <option value="">All Employees</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                        <select wire:model.live="filterType" class="form-select form-select-sm" style="width:160px;">
                            <option value="">All Types</option>
                            <option value="advance">Advance Given</option>
                            <option value="salary_deduction">Salary Deduction</option>
                            <option value="cash_repayment">Cash Repayment</option>
                        </select>
                        <input type="date" wire:model.live="dateFrom" class="form-control form-control-sm" style="width:140px;">
                        <span style="font-size:12px; color:var(--text-muted);">to</span>
                        <input type="date" wire:model.live="dateTo" class="form-control form-control-sm" style="width:140px;">
                    </div>
                </div>

                <table class="table table-hover mb-0" style="font-size:13px;">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Employee</th>
                            <th>Type</th>
                            <th>Note</th>
                            <th class="text-end" style="color:#c53030;">Debit (Out)</th>
                            <th class="text-end" style="color:#276749;">Credit (In)</th>
                            <th style="width:60px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $entry)
                            <tr>
                                <td style="white-space:nowrap;">
                                    {{ \Carbon\Carbon::parse($entry->ledger_date)->format('d/m/Y') }}
                                </td>
                                <td style="font-weight:600;">{{ $entry->user->name ?? '—' }}</td>
                                <td>
                                    @if($entry->type === 'advance')
                                        <span style="background:#fff5f5; color:#c53030; padding:2px 8px; border-radius:5px; font-size:11px; font-weight:600;">
                                            Advance Given
                                        </span>
                                    @elseif($entry->type === 'salary_deduction')
                                        <span style="background:#f0fff4; color:#276749; padding:2px 8px; border-radius:5px; font-size:11px; font-weight:600;">
                                            Salary Deduction
                                        </span>
                                    @else
                                        <span style="background:#ebf8ff; color:#2c5282; padding:2px 8px; border-radius:5px; font-size:11px; font-weight:600;">
                                            Cash Repayment
                                        </span>
                                    @endif
                                </td>
                                <td style="color:var(--text-muted); font-size:12px;">{{ $entry->note ?? '—' }}</td>
                                <td class="text-end fw-700" style="color:{{ $entry->type === 'advance' ? '#c53030' : 'transparent' }};">
                                    @if($entry->type === 'advance')
                                        Rs. {{ number_format($entry->amount, 0) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-end fw-700" style="color:{{ $entry->type !== 'advance' ? '#276749' : 'transparent' }};">
                                    @if($entry->type !== 'advance')
                                        Rs. {{ number_format($entry->amount, 0) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    <button wire:click="confirmDelete({{ $entry->source_id }}, '{{ $entry->source }}')"
                                        class="btn btn-sm action-btn" style="color:#c53030;" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4" style="color:var(--text-muted); font-size:13px;">
                                    No entries found for the selected filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot style="background:#f8fafc; border-top:2px solid var(--border);">
                        <tr>
                            <td colspan="4" style="font-weight:700; font-size:12px; padding:10px 12px;">
                                Subtotals (filtered range)
                            </td>
                            <td class="text-end fw-700" style="color:#c53030; padding:10px 12px;">
                                Rs. {{ number_format($totalAdvances, 0) }}
                            </td>
                            <td class="text-end fw-700" style="color:#276749; padding:10px 12px;">
                                Rs. {{ number_format($totalCredits, 0) }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>

                <div style="padding:12px 16px;">
                    {{ $entries->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Delete confirm modal --}}
    @if($deleteId)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered" style="max-width:360px;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title">Confirm Delete</h6>
                        <button class="btn-close" wire:click="$set('deleteId', null)"></button>
                    </div>
                    <div class="modal-body" style="font-size:13px;">Delete this ledger entry?</div>
                    <div class="modal-footer gap-2">
                        <button class="btn btn-sm btn-outline-secondary" wire:click="$set('deleteId', null)">Cancel</button>
                        <button class="btn btn-sm btn-danger" wire:click="delete">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
