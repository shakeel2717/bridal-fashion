<div>
    @if(session('success'))
        <div class="alert alert-success py-2 mb-3" style="font-size:13px;">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger py-2 mb-3" style="font-size:13px;">
            <i class="bi bi-exclamation-circle me-1"></i> {{ session('error') }}
        </div>
    @endif

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">Advances</div>
            <div class="page-subtitle">Employee salary advances</div>
        </div>
        <div class="d-flex align-items-center gap-3">
            @if($totalPending > 0)
            <div style="background:#fff5f5; border:1px solid #fed7d7; border-radius:7px; padding:6px 14px; font-size:12px;">
                <span style="color:#c53030; font-weight:700;">
                    Pending: Rs. {{ number_format($totalPending, 0) }}
                </span>
            </div>
            @endif
            <button class="btn btn-primary btn-sm d-flex align-items-center gap-2"
                    wire:click="openCreate">
                <i class="bi bi-plus-lg"></i> Add Advance
            </button>
        </div>
    </div>

    <div class="row g-3">
        @if($showForm)
        <div class="col-4">
            <div class="table-card">
                <div class="table-card-header">
                    <span class="table-card-title">
                        {{ $editId ? 'Edit Advance' : 'New Advance' }}
                    </span>
                    <button class="btn btn-sm btn-outline-secondary action-btn"
                            wire:click="resetForm">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <div style="padding:16px;">
                    <div class="mb-3">
                        <label class="form-label">Employee <span class="text-danger">*</span></label>
                        <select wire:model="userId"
                                class="form-select @error('userId') is-invalid @enderror">
                            <option value="">Select employee...</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                        @error('userId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Amount (Rs.) <span class="text-danger">*</span></label>
                        <input type="number"
                               wire:model="amount"
                               class="form-control @error('amount') is-invalid @enderror"
                               placeholder="e.g. 5000" min="1">
                        @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date"
                               wire:model="advanceDate"
                               class="form-control @error('advanceDate') is-invalid @enderror">
                        @error('advanceDate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Note</label>
                        <textarea wire:model="note"
                                  class="form-control" rows="2"
                                  placeholder="Reason for advance..."></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary btn-sm flex-fill"
                                wire:click="save"
                                wire:loading.attr="disabled">
                            <span wire:loading wire:target="save">
                                <span class="spinner-border spinner-border-sm me-1"></span>
                            </span>
                            {{ $editId ? 'Update' : 'Save Advance' }}
                        </button>
                        <button class="btn btn-outline-secondary btn-sm"
                                wire:click="resetForm">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="{{ $showForm ? 'col-8' : 'col-12' }}">
            <div class="table-card">
                <div class="table-card-header">
                    <div class="d-flex gap-2">
                        <select wire:model.live="filterUser"
                                class="form-select form-select-sm" style="width:160px;">
                            <option value="">All Employees</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                        <select wire:model.live="filterStatus"
                                class="form-select form-select-sm" style="width:150px;">
                            <option value="">All Status</option>
                            <option value="not_deducted">Pending</option>
                            <option value="deducted">Deducted</option>
                        </select>
                    </div>
                    <div style="width:220px;">
                        <input type="text"
                               wire:model.live.debounce.400ms="search"
                               class="form-control form-control-sm"
                               placeholder="Search employee...">
                    </div>
                </div>

                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Note</th>
                            <th>Status</th>
                            <th style="width:90px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($advances as $advance)
                        <tr>
                            <td>
                                <div style="font-weight:600; font-size:13px;">
                                    {{ $advance->user->name }}
                                </div>
                                <div style="font-size:11px; color:var(--text-muted);">
                                    {{ ucfirst($advance->user->role) }}
                                </div>
                            </td>
                            <td style="font-weight:700; font-size:13px; color:#e53e3e;">
                                Rs. {{ number_format($advance->amount, 0) }}
                            </td>
                            <td style="font-size:12px;">
                                {{ \Carbon\Carbon::parse($advance->advance_date)->format('d/m/Y') }}
                            </td>
                            <td style="font-size:12px; color:var(--text-muted);">
                                {{ $advance->note ?? '—' }}
                            </td>
                            <td>
                                <span class="advance-badge {{ $advance->is_deducted ? 'deducted' : 'not-deducted' }}">
                                    {{ $advance->is_deducted ? 'Deducted' : 'Pending' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    @if(!$advance->is_deducted)
                                    <button class="btn btn-sm btn-outline-secondary action-btn"
                                            wire:click="openEdit({{ $advance->id }})"
                                            title="Edit">
                                        <i class="bi bi-pencil" style="font-size:12px;"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger action-btn"
                                            wire:click="confirmDelete({{ $advance->id }})"
                                            title="Delete">
                                        <i class="bi bi-trash" style="font-size:12px;"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align:center; padding:30px; color:var(--text-muted); font-size:13px;">
                                <i class="bi bi-credit-card" style="font-size:32px; display:block; margin-bottom:8px;"></i>
                                No advances found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($advances->hasPages())
                <div style="padding:12px 16px; border-top:1px solid var(--border);">
                    {{ $advances->links('vendor.pagination.simple-bootstrap-5') }}
                </div>
                @endif
            </div>
        </div>
    </div>

    @if($deleteId)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered" style="max-width:380px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Confirm Delete</h6>
                </div>
                <div class="modal-body" style="font-size:13px;">
                    Are you sure you want to delete this advance record?
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