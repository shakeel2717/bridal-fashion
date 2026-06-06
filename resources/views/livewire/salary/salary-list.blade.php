<div>
    @if(session('success'))
        <div class="alert alert-success py-2 mb-3" style="font-size:13px;">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">Salary</div>
            <div class="page-subtitle">Monthly salary calculation & payment</div>
        </div>
        <select wire:model.live="filterRole"
                class="form-select form-select-sm" style="width:130px;">
            <option value="employee">Employees</option>
            <option value="admin">Admins</option>
            <option value="">All Staff</option>
        </select>
    </div>

    {{-- Month Navigator --}}
    <div class="table-card mb-3" style="padding:14px 16px;">
        <div class="d-flex align-items-center justify-content-between">
            <button class="month-nav-btn" wire:click="previousMonth">
                <i class="bi bi-chevron-left"></i> Prev
            </button>
            <div style="font-size:16px; font-weight:700; color:var(--text-primary);">
                {{ $currentMonth->format('F Y') }}
            </div>
            <button class="month-nav-btn" wire:click="nextMonth"
                    @if(!$canGoNext) disabled style="opacity:0.4; cursor:not-allowed;" @endif>
                Next <i class="bi bi-chevron-right"></i>
            </button>
        </div>
    </div>

    <div class="table-card">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Salary Type</th>
                    <th>Base Salary</th>
                    <th>Days Present</th>
                    <th>Earned</th>
                    <th>Advances</th>
                    <th>Net Salary</th>
                    <th>Status</th>
                    <th style="width:130px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                @php $record = $salaryRecords[$emp->id] ?? null; @endphp
                <tr>
                    <td>
                        <div style="font-weight:600; font-size:13px;">{{ $emp->name }}</div>
                        <div style="font-size:11px; color:var(--text-muted);">{{ $emp->designation ?? $emp->role }}</div>
                    </td>
                    <td>
                        <span class="salary-type-badge {{ $emp->salary_type }}">
                            {{ $emp->salary_type === 'monthly' ? 'Monthly' : 'Daily' }}
                        </span>
                    </td>
                    <td style="font-size:13px; font-weight:600;">
                        Rs. {{ number_format($emp->salary_amount, 0) }}
                    </td>
                    <td style="font-size:13px; text-align:center;">
                        {{ $record ? $record->days_present : '—' }}
                    </td>
                    <td style="font-size:13px; font-weight:600;">
                        @if($record)
                            Rs. {{ number_format($record->earned_salary, 0) }}
                        @else
                            <span style="color:var(--text-muted);">—</span>
                        @endif
                    </td>
                    <td style="font-size:13px; font-weight:600; color:#e53e3e;">
                        @if($record && $record->total_advances > 0)
                            - Rs. {{ number_format($record->total_advances, 0) }}
                        @else
                            <span style="color:var(--text-muted);">—</span>
                        @endif
                    </td>
                    <td style="font-size:14px; font-weight:700; color:var(--navy);">
                        @if($record)
                            Rs. {{ number_format($record->net_salary, 0) }}
                        @else
                            <span style="color:var(--text-muted); font-size:12px;">Not generated</span>
                        @endif
                    </td>
                    <td>
                        @if($record)
                            <span class="salary-status-badge {{ $record->status }}">
                                {{ ucfirst($record->status) }}
                            </span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-secondary action-btn"
                                    wire:click="generateSalary({{ $emp->id }})"
                                    wire:loading.attr="disabled"
                                    title="{{ $record ? 'Recalculate' : 'Generate Salary' }}">
                                <span wire:loading wire:target="generateSalary({{ $emp->id }})">
                                    <span class="spinner-border spinner-border-sm"></span>
                                </span>
                                <span wire:loading.remove wire:target="generateSalary({{ $emp->id }})">
                                    <i class="bi bi-calculator" style="font-size:12px;"></i>
                                    {{ $record ? 'Recalc' : 'Generate' }}
                                </span>
                            </button>

                            @if($record && $record->status === 'draft')
                            <button class="btn btn-sm btn-outline-success action-btn"
                                    wire:click="markPaid({{ $record->id }})"
                                    title="Mark as Paid">
                                <i class="bi bi-check-lg" style="font-size:12px;"></i>
                                Paid
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center; padding:30px; color:var(--text-muted); font-size:13px;">
                        No employees found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>