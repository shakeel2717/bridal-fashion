<div>
    @if (session('success'))
        <div class="alert alert-success py-2 mb-3" style="font-size:13px;">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger py-2 mb-3" style="font-size:13px;">
            <i class="bi bi-exclamation-circle me-1"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">Employees</div>
            <div class="page-subtitle">Manage staff accounts and profiles</div>
        </div>
        <button class="btn btn-primary btn-sm d-flex align-items-center gap-2"
            wire:click="$dispatch('open-create-employee')">
            <i class="bi bi-plus-lg"></i> Add Employee
        </button>
    </div>

    {{-- Stat Pills --}}
    <div class="d-flex gap-2 mb-3">
        <div
            style="background:#fff; border-radius:7px; padding:8px 16px; font-size:12px; border:1px solid var(--border);">
            <span style="color:var(--text-muted);">Admins</span>
            <span class="ms-2 fw-700" style="color:#1a2340;">{{ $counts['admin'] }}</span>
        </div>
        <div
            style="background:#fff; border-radius:7px; padding:8px 16px; font-size:12px; border:1px solid var(--border);">
            <span style="color:var(--text-muted);">Employees</span>
            <span class="ms-2 fw-700" style="color:var(--gold);">{{ $counts['employee'] }}</span>
        </div>
        <div
            style="background:#fff; border-radius:7px; padding:8px 16px; font-size:12px; border:1px solid var(--border);">
            <span style="color:var(--text-muted);">Active</span>
            <span class="ms-2 fw-700" style="color:#38a169;">{{ $counts['active'] }}</span>
        </div>
        <div
            style="background:#fff; border-radius:7px; padding:8px 16px; font-size:12px; border:1px solid var(--border);">
            <span style="color:var(--text-muted);">Inactive</span>
            <span class="ms-2 fw-700" style="color:#718096;">{{ $counts['inactive'] }}</span>
        </div>
    </div>

    <div class="table-card">
        <div class="table-card-header" style="flex-wrap:wrap; gap:10px;">
            <div class="d-flex gap-2 align-items-center">
                {{-- Role Filter --}}
                <div class="tab-pills" style="margin-bottom:0;">
                    <button class="tab-pill {{ $filterRole === 'employee' ? 'active' : '' }}"
                        wire:click="$set('filterRole','employee')">Employees</button>
                    <button class="tab-pill {{ $filterRole === 'admin' ? 'active' : '' }}"
                        wire:click="$set('filterRole','admin')">Admins</button>
                    <button class="tab-pill {{ $filterRole === '' ? 'active' : '' }}"
                        wire:click="$set('filterRole','')">All</button>
                </div>

                {{-- Status Filter --}}
                <select wire:model.live="filterStatus" class="form-select form-select-sm" style="width:130px;">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="">All Status</option>
                </select>
            </div>

            <div style="width:240px;">
                <input type="text" wire:model.live.debounce.400ms="search" class="form-control form-control-sm"
                    placeholder="Search name, phone, CNIC...">
            </div>
        </div>

        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="width:42px;">#</th>
                    <th>Employee</th>
                    <th>Contact</th>
                    <th>CNIC</th>
                    <th>Role</th>
                    <th>Designation</th>
                    <th>Salary</th>
                    <th>Joining</th>
                    <th>Status</th>
                    <th style="width:90px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                    <tr>
                        <td style="color:var(--text-muted); font-size:11px;">{{ $emp->id }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if ($emp->photo)
                                    <img src="{{ Storage::url($emp->photo) }}" class="employee-photo" alt="">
                                @else
                                    <div class="employee-avatar">
                                        {{ strtoupper(substr($emp->name, 0, 2)) }}
                                    </div>
                                @endif
                                <div>
                                    <div style="font-weight:600; font-size:13px;">{{ $emp->name }}</div>
                                    <div style="font-size:11px; color:var(--text-muted);">{{ $emp->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="font-size:13px;">
                            {{ $emp->phone ?? '—' }}
                        </td>
                        <td style="font-size:12px; font-family:monospace;">
                            {{ $emp->cnic ?? '—' }}
                        </td>
                        <td>
                            <span class="role-badge {{ $emp->role }}">
                                {{ ucfirst($emp->role) }}
                            </span>
                        </td>
                        <td style="font-size:12px;">{{ $emp->designation ?? '—' }}</td>
                        <td>
                            <span class="salary-type-badge {{ $emp->salary_type }}">
                                {{ $emp->salary_type === 'monthly' ? 'Monthly' : 'Daily' }}
                            </span>
                            <div style="font-size:12px; font-weight:600; margin-top:2px;">
                                Rs. {{ number_format($emp->salary_amount, 0) }}
                            </div>
                        </td>
                        <td style="font-size:12px; color:var(--text-muted);">
                            <div>{{ $emp->joining_date?->format('d/m/Y') ?? '—' }}</div>
                            @if ($emp->resign_date)
                                <div style="font-size:11px; color:#e53e3e; margin-top:2px;">
                                    Resigned: {{ $emp->resign_date->format('d/m/Y') }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="status-dot {{ $emp->is_active ? 'active' : 'inactive' }}"></span>
                            <span style="font-size:12px;">
                                {{ $emp->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-outline-secondary action-btn"
                                    wire:click="$dispatch('open-edit-employee', { id: {{ $emp->id }} })"
                                    title="Edit">
                                    <i class="bi bi-pencil" style="font-size:12px;"></i>
                                </button>
                                @if ($emp->id !== auth()->id())
                                    <button class="btn btn-sm btn-outline-danger action-btn"
                                        wire:click="confirmDelete({{ $emp->id }})" title="Delete">
                                        <i class="bi bi-trash" style="font-size:12px;"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10"
                            style="text-align:center; padding:30px; color:var(--text-muted); font-size:13px;">
                            <i class="bi bi-person-badge" style="font-size:32px; display:block; margin-bottom:8px;"></i>
                            No employees found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($employees->hasPages())
            <div style="padding:12px 16px; border-top:1px solid var(--border);">
                {{ $employees->links('vendor.pagination.simple-bootstrap-5') }}
            </div>
        @endif
    </div>

    {{-- Delete Confirm --}}
    @if ($deleteId)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered" style="max-width:380px;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title">Confirm Delete</h6>
                    </div>
                    <div class="modal-body" style="font-size:13px;">
                        Are you sure you want to delete this employee? This cannot be undone.
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

    <livewire:employees.employee-form />
</div>
