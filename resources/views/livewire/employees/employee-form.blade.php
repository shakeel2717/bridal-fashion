<div>
    <div class="modal fade" id="employeeModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">
                        <i class="bi bi-person-plus me-2"></i>
                        {{ $isEdit ? 'Edit Employee' : 'Add New Employee' }}
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="resetForm"></button>
                </div>

                <div class="modal-body" style="padding:20px 24px;">
                    <div class="row g-3">

                        {{-- Name --}}
                        <div class="col-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" wire:model="name"
                                class="form-control @error('name') is-invalid @enderror" placeholder="Muhammad Ali">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="col-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" wire:model="email"
                                class="form-control @error('email') is-invalid @enderror" placeholder="ali@example.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Password --}}
                        <div class="col-6">
                            <label class="form-label">
                                Password
                                @if ($isEdit)
                                    <span style="font-size:10px; color:var(--text-muted); font-weight:400;">
                                        (leave blank to keep current)
                                    </span>
                                @else
                                    <span class="text-danger">*</span>
                                @endif
                            </label>
                            <input type="password" wire:model="password"
                                class="form-control @error('password') is-invalid @enderror" placeholder="••••••••">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Role --}}
                        <div class="col-6">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select wire:model="role" class="form-select @error('role') is-invalid @enderror">
                                <option value="employee">Employee</option>
                                <option value="admin">Admin</option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Phone --}}
                        <div class="col-6">
                            <label class="form-label">Phone</label>
                            <input type="text" wire:model="phone" class="form-control" placeholder="03XX-XXXXXXX">
                        </div>

                        {{-- CNIC --}}
                        <div class="col-6">
                            <label class="form-label">CNIC</label>
                            <input type="text" wire:model="cnic"
                                class="form-control @error('cnic') is-invalid @enderror" placeholder="00000-0000000-0">
                            @error('cnic')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Designation --}}
                        <div class="col-6">
                            <label class="form-label">Designation</label>
                            <input type="text" wire:model="designation" class="form-control"
                                placeholder="e.g. Sales Staff">
                        </div>

                        {{-- Joining Date --}}
                        <div class="col-4">
                            <label class="form-label">Joining Date</label>
                            <input type="date" wire:model="joiningDate" class="form-control">
                        </div>

                        {{-- Resign Date --}}
                        <div class="col-4">
                            <label class="form-label">
                                Resign Date
                                <span style="font-size:10px; color:var(--text-muted); font-weight:400;">(if
                                    resigned)</span>
                            </label>
                            <input type="date" wire:model="resign_date" class="form-control">
                        </div>

                        {{-- Salary Type --}}
                        <div class="col-4">
                            <label class="form-label">Salary Type <span class="text-danger">*</span></label>
                            <select wire:model.live="salaryType" class="form-select">
                                <option value="monthly">Monthly</option>
                                <option value="daily">Per Day</option>
                            </select>
                        </div>

                        {{-- Salary Amount --}}
                        <div class="col-4">
                            <label class="form-label">
                                {{ $salaryType === 'monthly' ? 'Monthly Salary' : 'Daily Rate' }}
                                (Rs.) <span class="text-danger">*</span>
                            </label>
                            <input type="number" wire:model="salaryAmount"
                                class="form-control @error('salaryAmount') is-invalid @enderror" placeholder="0"
                                min="0">
                            @error('salaryAmount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Active --}}
                        <div class="col-4" style="padding-top:28px;">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" wire:model="isActive" id="empActive">
                                <label class="form-check-label" for="empActive" style="font-size:13px;">
                                    Active
                                </label>
                            </div>
                        </div>

                        {{-- Address --}}
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea wire:model="address" class="form-control" rows="2" placeholder="Employee address..."></textarea>
                        </div>

                        {{-- Photo --}}
                        <div class="col-12">
                            <label class="form-label">
                                Photo
                                <span style="color:var(--text-muted); font-weight:400;">(optional)</span>
                            </label>
                            @if ($existingPhoto)
                                <div class="mb-2">
                                    <img src="{{ Storage::url($existingPhoto) }}"
                                        style="width:56px; height:56px; border-radius:50%; object-fit:cover;">
                                </div>
                            @endif
                            <input type="file" wire:model="photo"
                                class="form-control @error('photo') is-invalid @enderror" accept="image/*">
                            @error('photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal"
                        wire:click="resetForm">Cancel</button>
                    <button type="button" class="btn btn-sm btn-primary" wire:click="save"
                        wire:loading.attr="disabled">
                        <span wire:loading wire:target="save">
                            <span class="spinner-border spinner-border-sm me-1"></span>
                        </span>
                        {{ $isEdit ? 'Update Employee' : 'Save Employee' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            const getModal = () => bootstrap.Modal.getOrCreateInstance(
                document.getElementById('employeeModal')
            );
            Livewire.on('open-employee-modal', () => getModal().show());
            Livewire.on('close-employee-modal', () => getModal().hide());
        });
    </script>
@endpush
