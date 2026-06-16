<div>
    <div class="modal fade" id="customerModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered" style="max-width:600px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">
                        <i class="bi bi-person-plus me-2"></i>
                        {{ $isEdit ? 'Edit Customer' : 'Add New Customer' }}
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body" style="padding:20px 24px;">
                    <div class="row g-3">

                        {{-- Name --}}
                        <div class="col-12">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text"
                                   wire:model="name"
                                   id="customer_name_input"
                                   class="form-control @error('name') is-invalid @enderror"
                                   placeholder="Customer full name">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Phone 1 + Phone 2 --}}
                        <div class="col-6">
                            <label class="form-label">Phone 1 <span class="text-danger">*</span></label>
                            <input type="text"
                                   wire:model="phone1"
                                   class="form-control @error('phone1') is-invalid @enderror"
                                   placeholder="03XX-XXXXXXX">
                            @error('phone1') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label">Phone 2</label>
                            <input type="text" wire:model="phone2" class="form-control" placeholder="03XX-XXXXXXX">
                        </div>

                        {{-- WhatsApp + CNIC --}}
                        <div class="col-6">
                            <label class="form-label">WhatsApp</label>
                            <input type="text" wire:model="whatsapp" class="form-control" placeholder="03XX-XXXXXXX">
                        </div>
                        <div class="col-6">
                            <label class="form-label">CNIC</label>
                            <input type="text"
                                   wire:model="cnic"
                                   class="form-control @error('cnic') is-invalid @enderror"
                                   placeholder="00000-0000000-0">
                            @error('cnic') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- City + Address --}}
                        <div class="col-6">
                            <label class="form-label">City</label>
                            <input type="text"
                                   wire:model="city"
                                   class="form-control @error('city') is-invalid @enderror"
                                   placeholder="e.g. Lahore">
                            @error('city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label">Address</label>
                            <input type="text" wire:model="address" class="form-control" placeholder="Street / area">
                        </div>

                        {{-- Notes --}}
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea wire:model="notes"
                                      class="form-control"
                                      rows="2"
                                      placeholder="Any additional notes..."></textarea>
                        </div>

                        {{-- Documents section --}}
                        <div class="col-12">
                            <div style="background:#f7fafc; border:1px solid var(--border); border-radius:8px; padding:16px;">
                                <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:14px;">
                                    <i class="bi bi-camera me-1"></i> Documents
                                    <span style="font-weight:400; font-size:10px; margin-left:4px;">(all optional)</span>
                                </div>
                                <div class="row g-3">

                                    {{-- Profile Photo --}}
                                    <div class="col-4">
                                        <label class="form-label" style="font-size:12px;">Profile Photo</label>
                                        @if($existingPhoto && !$photo)
                                            <div class="mb-2">
                                                <img src="{{ Storage::url($existingPhoto) }}"
                                                     style="width:52px; height:52px; border-radius:50%; object-fit:cover; border:2px solid var(--gold);">
                                            </div>
                                        @endif
                                        <input type="file"
                                               wire:model="photo"
                                               class="form-control form-control-sm @error('photo') is-invalid @enderror"
                                               accept="image/*">
                                        @error('photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        @if($photo)
                                            <div class="mt-2">
                                                <img src="{{ $photo->temporaryUrl() }}"
                                                     style="width:52px; height:52px; border-radius:50%; object-fit:cover; border:2px solid var(--gold);">
                                            </div>
                                        @endif
                                    </div>

                                    {{-- CNIC Front --}}
                                    <div class="col-4">
                                        <label class="form-label" style="font-size:12px;">CNIC Front</label>
                                        @if($existingCnicFront && !$cnicFront)
                                            <div class="mb-2">
                                                <img src="{{ Storage::url($existingCnicFront) }}"
                                                     style="width:100%; height:50px; object-fit:cover; border-radius:6px; border:2px solid var(--gold);">
                                            </div>
                                        @endif
                                        <input type="file"
                                               wire:model="cnicFront"
                                               class="form-control form-control-sm @error('cnicFront') is-invalid @enderror"
                                               accept="image/*">
                                        @error('cnicFront') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        @if($cnicFront)
                                            <div class="mt-2">
                                                <img src="{{ $cnicFront->temporaryUrl() }}"
                                                     style="width:100%; height:50px; object-fit:cover; border-radius:6px; border:2px solid var(--gold);">
                                            </div>
                                        @endif
                                    </div>

                                    {{-- CNIC Back --}}
                                    <div class="col-4">
                                        <label class="form-label" style="font-size:12px;">CNIC Back</label>
                                        @if($existingCnicBack && !$cnicBack)
                                            <div class="mb-2">
                                                <img src="{{ Storage::url($existingCnicBack) }}"
                                                     style="width:100%; height:50px; object-fit:cover; border-radius:6px; border:2px solid var(--gold);">
                                            </div>
                                        @endif
                                        <input type="file"
                                               wire:model="cnicBack"
                                               class="form-control form-control-sm @error('cnicBack') is-invalid @enderror"
                                               accept="image/*">
                                        @error('cnicBack') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        @if($cnicBack)
                                            <div class="mt-2">
                                                <img src="{{ $cnicBack->temporaryUrl() }}"
                                                     style="width:100%; height:50px; object-fit:cover; border-radius:6px; border:2px solid var(--gold);">
                                            </div>
                                        @endif
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-sm btn-outline-secondary"
                            data-bs-dismiss="modal"
                            wire:click="resetForm">Cancel</button>
                    <button type="button"
                            class="btn btn-sm btn-primary"
                            wire:click="save"
                            wire:loading.attr="disabled">
                        <span wire:loading wire:target="save">
                            <span class="spinner-border spinner-border-sm me-1"></span>
                        </span>
                        {{ $isEdit ? 'Update Customer' : 'Save Customer' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        const modalEl = document.getElementById('customerModal');
        const modal   = bootstrap.Modal.getOrCreateInstance(modalEl);

        Livewire.on('open-customer-modal', () => {
            modal.show();
            setTimeout(() => {
                document.getElementById('customer_name_input')?.focus();
            }, 300);
        });

        Livewire.on('close-customer-modal', () => modal.hide());

        Livewire.on('open-create-customer', () => {
            @this.resetForm();
            modal.show();
            setTimeout(() => {
                document.getElementById('customer_name_input')?.focus();
            }, 300);
        });

        Livewire.on('open-edit-customer', (data) => {
            @this.openEdit(data.id);
            modal.show();
            setTimeout(() => {
                document.getElementById('customer_name_input')?.focus();
            }, 300);
        });

        // Ctrl+Enter to save from within the modal
        modalEl.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key === 'Enter') {
                e.preventDefault();
                e.stopPropagation();
                @this.call('save');
            }
        });
    });
</script>
@endpush