<div>
    <div class="modal fade" id="customerModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered" style="max-width:540px;">
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
                        <div class="col-12">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text"
                                   wire:model="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   placeholder="Customer full name">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-6">
                            <label class="form-label">Phone 1 <span class="text-danger">*</span></label>
                            <input type="text"
                                   wire:model="phone1"
                                   class="form-control @error('phone1') is-invalid @enderror"
                                   placeholder="03XX-XXXXXXX">
                            @error('phone1')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-6">
                            <label class="form-label">Phone 2</label>
                            <input type="text"
                                   wire:model="phone2"
                                   class="form-control"
                                   placeholder="03XX-XXXXXXX">
                        </div>

                        <div class="col-6">
                            <label class="form-label">WhatsApp</label>
                            <input type="text"
                                   wire:model="whatsapp"
                                   class="form-control"
                                   placeholder="03XX-XXXXXXX">
                        </div>

                        <div class="col-6">
                            <label class="form-label">CNIC</label>
                            <input type="text"
                                   wire:model="cnic"
                                   class="form-control @error('cnic') is-invalid @enderror"
                                   placeholder="00000-0000000-0">
                            @error('cnic')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea wire:model="address"
                                      class="form-control"
                                      rows="2"
                                      placeholder="Customer address"></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea wire:model="notes"
                                      class="form-control"
                                      rows="2"
                                      placeholder="Any additional notes..."></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Photo <span style="color:var(--text-muted); font-weight:400;">(optional)</span></label>
                            @if($existingPhoto)
                                <div class="mb-2">
                                    <img src="{{ Storage::url($existingPhoto) }}"
                                         style="width:56px; height:56px; border-radius:50%; object-fit:cover;">
                                </div>
                            @endif
                            <input type="file"
                                   wire:model="photo"
                                   class="form-control @error('photo') is-invalid @enderror"
                                   accept="image/*">
                            @error('photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
        const modal   = new bootstrap.Modal(modalEl);

        Livewire.on('open-customer-modal', () => modal.show());
        Livewire.on('close-customer-modal', () => modal.hide());

        Livewire.on('open-create-customer', () => {
            @this.resetForm();
            modal.show();
        });

        Livewire.on('open-edit-customer', (data) => {
            @this.openEdit(data.id);
            modal.show();
        });
    });
</script>
@endpush