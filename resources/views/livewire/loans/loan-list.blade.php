<div>
    @if (session('success'))
        <div class="alert alert-success py-2 mb-3" style="font-size:13px;">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <div class="page-title">Loans & Borrowings</div>
            <div class="page-subtitle">Track money borrowed from family & friends</div>
        </div>
        <button class="btn btn-primary btn-sm d-flex align-items-center gap-2"
            wire:click="openCreate">
            <i class="bi bi-plus-lg"></i> Add Person
        </button>
    </div>

    {{-- Summary Cards --}}
    <div class="d-flex gap-3 mb-4">
        <div style="background:#fff5f5; border:1px solid #fed7d7; border-radius:10px; padding:14px 22px; min-width:160px;">
            <div style="font-size:10px; font-weight:700; color:#c53030; text-transform:uppercase; margin-bottom:4px;">
                Total Borrowed
            </div>
            <div style="font-size:22px; font-weight:800; color:#c53030;">
                Rs. {{ number_format($grandReceived, 0) }}
            </div>
        </div>
        <div style="background:#f0fff4; border:1px solid #9ae6b4; border-radius:10px; padding:14px 22px; min-width:160px;">
            <div style="font-size:10px; font-weight:700; color:#276749; text-transform:uppercase; margin-bottom:4px;">
                Total Paid Back
            </div>
            <div style="font-size:22px; font-weight:800; color:#276749;">
                Rs. {{ number_format($grandPaid, 0) }}
            </div>
        </div>
        <div style="background:{{ $grandOutstanding > 0 ? '#fffbeb' : '#f7fafc' }}; border:1px solid {{ $grandOutstanding > 0 ? '#f6e05e' : '#e2e8f0' }}; border-radius:10px; padding:14px 22px; min-width:160px;">
            <div style="font-size:10px; font-weight:700; color:{{ $grandOutstanding > 0 ? '#b7791f' : '#718096' }}; text-transform:uppercase; margin-bottom:4px;">
                Still Owed
            </div>
            <div style="font-size:22px; font-weight:800; color:{{ $grandOutstanding > 0 ? '#b7791f' : '#718096' }};">
                Rs. {{ number_format($grandOutstanding, 0) }}
            </div>
        </div>
    </div>

    <div class="row g-3">

        {{-- Add / Edit Form --}}
        @if ($showForm)
            <div class="col-4">
                <div class="table-card" style="padding:20px;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div style="font-size:13px; font-weight:700; color:var(--navy);">
                            <i class="bi bi-person-plus me-1"></i>
                            {{ $editId ? 'Edit Person' : 'Add Person' }}
                        </div>
                        <button class="btn btn-sm btn-outline-secondary action-btn"
                            wire:click="resetForm">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" wire:model="name"
                            class="form-control @error('name') is-invalid @enderror"
                            placeholder="e.g. Father, Wife, Bhai Jan" autofocus>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Relation</label>
                        <input type="text" wire:model="relation"
                            class="form-control"
                            placeholder="e.g. Father, Brother, Wife">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" wire:model="phone"
                            class="form-control"
                            placeholder="0300-0000000">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea wire:model="notes" class="form-control" rows="2"
                            placeholder="Any additional notes..."></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary btn-sm flex-fill"
                            wire:click="save" wire:loading.attr="disabled">
                            <span wire:loading wire:target="save">
                                <span class="spinner-border spinner-border-sm me-1"></span>
                            </span>
                            {{ $editId ? 'Update' : 'Save' }}
                        </button>
                        <button class="btn btn-outline-secondary btn-sm"
                            wire:click="resetForm">Cancel</button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Lenders Table --}}
        <div class="{{ $showForm ? 'col-8' : 'col-12' }}">
            <div class="table-card">
                <table class="table mb-0" style="font-size:13px;">
                    <thead>
                        <tr>
                            <th>Person</th>
                            <th>Relation</th>
                            <th style="text-align:right;">Total Borrowed</th>
                            <th style="text-align:right;">Paid Back</th>
                            <th style="text-align:right;">Still Owed</th>
                            <th style="width:120px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($lenders as $lender)
                            @php
                                $received    = $lender->totalReceived();
                                $paid        = $lender->totalPaid();
                                $outstanding = $lender->outstandingBalance();
                            @endphp
                            <tr style="{{ !$lender->is_active ? 'opacity:0.5;' : '' }}">
                                <td>
                                    <a href="{{ route('loans.show', $lender->id) }}"
                                        style="font-weight:700; color:var(--navy); text-decoration:none;">
                                        {{ $lender->name }}
                                    </a>
                                    @if ($lender->phone)
                                        <div style="font-size:11px; color:var(--text-muted);">{{ $lender->phone }}</div>
                                    @endif
                                    @if ($lender->notes)
                                        <div style="font-size:11px; color:var(--text-muted); font-style:italic; margin-top:2px;">
                                            {{ Str::limit($lender->notes, 50) }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if ($lender->relation)
                                        <span style="font-size:11px; background:#ebf8ff; color:#2c5282; padding:2px 8px; border-radius:4px; font-weight:600;">
                                            {{ $lender->relation }}
                                        </span>
                                    @else
                                        <span style="color:var(--text-muted);">—</span>
                                    @endif
                                </td>
                                <td style="text-align:right; font-weight:700; color:#c53030;">
                                    Rs. {{ number_format($received, 0) }}
                                </td>
                                <td style="text-align:right; font-weight:700; color:#276749;">
                                    Rs. {{ number_format($paid, 0) }}
                                </td>
                                <td style="text-align:right;">
                                    @if ($outstanding > 0)
                                        <span style="font-weight:800; color:#b7791f; font-size:14px;">
                                            Rs. {{ number_format($outstanding, 0) }}
                                        </span>
                                    @else
                                        <span style="font-size:13px; color:#276749; font-weight:700;">
                                            <i class="bi bi-check-circle-fill me-1"></i> Cleared
                                        </span>
                                    @endif
                                </td>
                                <td style="text-align:right;">
                                    <div class="d-flex gap-1 justify-content-end">
                                        <a href="{{ route('loans.show', $lender->id) }}"
                                            class="btn btn-sm btn-outline-primary action-btn"
                                            title="View Statement">
                                            <i class="bi bi-eye" style="font-size:12px;"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-secondary action-btn"
                                            wire:click="openEdit({{ $lender->id }})"
                                            title="Edit">
                                            <i class="bi bi-pencil" style="font-size:12px;"></i>
                                        </button>
                                        <button class="btn btn-sm action-btn"
                                            style="border:1px solid {{ $lender->is_active ? '#9ae6b4' : '#e2e8f0' }}; color:{{ $lender->is_active ? '#276749' : '#718096' }}; background:transparent;"
                                            wire:click="toggleActive({{ $lender->id }})"
                                            title="{{ $lender->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="bi bi-{{ $lender->is_active ? 'toggle-on' : 'toggle-off' }}" style="font-size:13px;"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align:center; padding:30px; color:var(--text-muted);">
                                    <i class="bi bi-people" style="font-size:28px; display:block; margin-bottom:8px;"></i>
                                    No lenders added yet. Click "Add Person" to start.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
