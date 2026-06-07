<div>
    <div class="modal fade" id="productModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">
                        <i class="bi bi-tags me-2"></i>
                        {{ $isEdit ? 'Edit Product' : 'Add New Product' }}
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="resetForm"></button>
                </div>

                <div class="modal-body" style="padding:20px 24px;">
                    <div class="row g-3">

                        {{-- Code + Auto --}}
                        <div class="col-5">
                            <label class="form-label">Product Code <span class="text-danger">*</span></label>
                            <input type="text" wire:model="code"
                                class="form-control @error('code') is-invalid @enderror" placeholder="e.g. BL-001"
                                style="text-transform:uppercase; font-family:monospace;"
                                @if ($autoCode && !$isEdit) readonly @endif>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-4" style="padding-top:28px;">
                            @if (!$isEdit)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model.live="autoCode"
                                        id="autoCode">
                                    <label class="form-check-label" for="autoCode" style="font-size:12px;">
                                        Auto-generate code
                                    </label>
                                </div>
                            @endif
                        </div>

                        {{-- Name --}}
                        <div class="col-12">
                            <label class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" wire:model="name"
                                class="form-control @error('name') is-invalid @enderror"
                                placeholder="e.g. Red Bridal Lahnga">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Category --}}
                        <div class="col-6">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select wire:model.live="categoryId"
                                class="form-select @error('categoryId') is-invalid @enderror">
                                <option value="">Select category...</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $cat->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('categoryId')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Vendor --}}
                        <div class="col-6">
                            <label class="form-label">Vendor</label>

                            @if (!$showVendorForm)
                                <div class="d-flex gap-2">
                                    <select wire:model="vendorId" class="form-select">
                                        <option value="">No vendor</option>
                                        @foreach ($vendors as $vendor)
                                            <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" wire:click="openVendorForm" class="btn btn-outline-secondary"
                                        style="white-space:nowrap; padding:0 12px;" title="Add new vendor">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                            @else
                                <div
                                    style="background:#f0fff4; border:1.5px solid #9ae6b4; border-radius:8px; padding:12px;">
                                    <div
                                        style="font-size:11px; font-weight:700; text-transform:uppercase; color:#276749; margin-bottom:10px;">
                                        <i class="bi bi-shop me-1"></i> New Vendor
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-12">
                                            <input type="text" wire:model="newVendorName"
                                                class="form-control form-control-sm @error('newVendorName') is-invalid @enderror"
                                                placeholder="Vendor name *">
                                            @error('newVendorName')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-12">
                                            <input type="text" wire:model="newVendorPhone"
                                                class="form-control form-control-sm" placeholder="Phone (optional)">
                                        </div>
                                        <div class="col-12">
                                            <input type="text" wire:model="newVendorAddress"
                                                class="form-control form-control-sm" placeholder="Address (optional)">
                                        </div>
                                        <div class="col-12 d-flex gap-2 mt-1">
                                            <button type="button" wire:click="saveVendor" wire:loading.attr="disabled"
                                                class="btn btn-sm btn-success flex-fill">
                                                <span wire:loading wire:target="saveVendor">
                                                    <span class="spinner-border spinner-border-sm me-1"></span>
                                                </span>
                                                <i class="bi bi-check me-1"></i> Save Vendor
                                            </button>
                                            <button type="button" wire:click="cancelVendorForm"
                                                class="btn btn-sm btn-outline-secondary">
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Type --}}
                        <div class="col-4">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <select wire:model.live="type" class="form-select">
                                <option value="rental">Rental Only</option>
                                <option value="sale">Sale Only</option>
                                <option value="both">Both</option>
                            </select>
                        </div>

                        {{-- Size --}}
                        <div class="col-4">
                            <label class="form-label">Size / Waist</label>
                            <input type="text" wire:model="size" class="form-control"
                                placeholder="e.g. 28, 30, Free">
                        </div>

                        <div class="col-4">
                            <label class="form-label">Color</label>
                            <input type="text" wire:model="color" class="form-control"
                                placeholder="e.g. Red, Golden, White">
                        </div>

                        {{-- Qty --}}
                        <div class="col-4">
                            <label class="form-label">
                                Quantity
                                @if (!$isEdit)
                                    <span style="font-size:10px; color:var(--text-muted); font-weight:400;">
                                        @if ($type === 'sale')
                                            (stock qty)
                                        @else
                                            (creates N separate items)
                                        @endif
                                    </span>
                                @endif
                            </label>
                            <input type="number" wire:model.live="stockQty"
                                class="form-control @error('stockQty') is-invalid @enderror" min="1"
                                @if ($isEdit) readonly @endif>
                            @error('stockQty')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if (!$isEdit && in_array($type, ['rental', 'both']))
                            <div class="col-12">
                                <div
                                    style="background:#f7fafc; border:1px solid var(--border); border-radius:8px; padding:14px;">
                                    <div
                                        style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:12px;">
                                        <i class="bi bi-palette me-1"></i>
                                        Per Item Color & Size
                                    </div>
                                    <div
                                        style="display:grid; grid-template-columns: 50px 1fr 1fr; gap:8px; margin-bottom:6px;">
                                        <div style="font-size:10px; font-weight:700; color:var(--text-muted);">#</div>
                                        <div style="font-size:10px; font-weight:700; color:var(--text-muted);">COLOR
                                        </div>
                                        <div style="font-size:10px; font-weight:700; color:var(--text-muted);">SIZE /
                                            WAIST</div>
                                    </div>
                                    @foreach ($itemVariants as $i => $variant)
                                        <div
                                            style="display:grid; grid-template-columns: 50px 1fr 1fr; gap:8px; margin-bottom:6px; align-items:center;">
                                            <div>
                                                <span class="tbl-code-badge"
                                                    style="font-size:10px;">{{ $i + 1 }}</span>
                                            </div>
                                            <div>
                                                <input type="text"
                                                    wire:model="itemVariants.{{ $i }}.color"
                                                    class="form-control form-control-sm"
                                                    placeholder="e.g. Red, Golden">
                                            </div>
                                            <div>
                                                <input type="text"
                                                    wire:model="itemVariants.{{ $i }}.size"
                                                    class="form-control form-control-sm" placeholder="e.g. 36, Free">
                                            </div>
                                        </div>
                                    @endforeach

                                    @if (empty($itemVariants))
                                        <div
                                            style="font-size:12px; color:var(--text-muted); text-align:center; padding:8px 0;">
                                            Enter quantity above to set per-item colors & sizes
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Purchase Price always shown --}}
                        <div class="col-4">
                            <label class="form-label">Purchase / Cost Price (Rs.)</label>
                            <input type="number" wire:model="purchasePrice"
                                class="form-control @error('purchasePrice') is-invalid @enderror" placeholder="0"
                                min="0" step="0.01">
                            @error('purchasePrice')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Rental Price --}}
                        @if (in_array($type, ['rental', 'both']))
                            <div class="col-4">
                                <label class="form-label">Rental Price (Rs.) <span
                                        class="text-danger">*</span></label>
                                <input type="number" wire:model="rentalPrice"
                                    class="form-control @error('rentalPrice') is-invalid @enderror" placeholder="0"
                                    min="0" step="0.01">
                                @error('rentalPrice')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        {{-- Sale Price --}}
                        @if (in_array($type, ['sale', 'both']))
                            <div class="col-4">
                                <label class="form-label">Sale Price (Rs.) <span class="text-danger">*</span></label>
                                <input type="number" wire:model="salePrice"
                                    class="form-control @error('salePrice') is-invalid @enderror" placeholder="0"
                                    min="0" step="0.01">
                                @error('salePrice')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        {{-- Notes --}}
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea wire:model="notes" class="form-control" rows="2" placeholder="Any notes about this product..."></textarea>
                        </div>

                        {{-- Photo --}}
                        <div class="col-12">
                            <label class="form-label">
                                Product Photo
                                <span style="color:var(--text-muted); font-weight:400; font-size:11px;">(optional, max
                                    3MB)</span>
                            </label>

                            @if ($existingPhoto)
                                <div class="mb-2 d-flex align-items-center gap-3">
                                    <img src="{{ Storage::url($existingPhoto) }}"
                                        style="width:80px; height:80px; object-fit:cover; border-radius:8px; border:1px solid var(--border);">
                                    <div style="font-size:12px; color:var(--text-muted);">Current photo</div>
                                </div>
                            @endif

                            <input type="file" wire:model="photo"
                                class="form-control @error('photo') is-invalid @enderror" accept="image/*">
                            @error('photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            @if ($photo)
                                <div class="mt-2">
                                    <img src="{{ $photo->temporaryUrl() }}"
                                        style="width:80px; height:80px; object-fit:cover; border-radius:8px; border:2px solid var(--gold);">
                                    <div style="font-size:10px; color:var(--text-muted); margin-top:4px;">Preview</div>
                                </div>
                            @endif
                        </div>

                        {{-- Status + Abandoned --}}
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" wire:model="isActive"
                                    id="prodActive">
                                <label class="form-check-label" for="prodActive" style="font-size:13px;">
                                    Active
                                </label>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" wire:model.live="isAbandoned"
                                    id="prodAbandoned">
                                <label class="form-check-label" for="prodAbandoned"
                                    style="font-size:13px; color:#e53e3e;">
                                    Mark as Abandoned
                                </label>
                            </div>
                        </div>

                        {{-- Abandoned Fields --}}
                        @if ($isAbandoned)
                            <div class="col-12">
                                <div
                                    style="background:#fff5f5; border:1px solid #fed7d7; border-radius:8px; padding:14px;">
                                    <div
                                        style="font-size:11px; font-weight:700; color:#c53030; margin-bottom:10px; text-transform:uppercase;">
                                        Abandoned Details
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-4">
                                            <label class="form-label">Written-off Value (Rs.) <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" wire:model="abandonedPrice"
                                                class="form-control form-control-sm @error('abandonedPrice') is-invalid @enderror"
                                                placeholder="0" min="0">
                                            @error('abandonedPrice')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label">Date <span class="text-danger">*</span></label>
                                            <input type="date" wire:model="abandonedDate"
                                                class="form-control form-control-sm @error('abandonedDate') is-invalid @enderror">
                                            @error('abandonedDate')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label">Reason</label>
                                            <input type="text" wire:model="abandonedNote"
                                                class="form-control form-control-sm" placeholder="e.g. torn, lost">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

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
                        {{ $isEdit ? 'Update Product' : 'Save Product' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            const modalEl = document.getElementById('productModal');
            const modal = new bootstrap.Modal(modalEl);

            Livewire.on('open-product-modal', () => modal.show());
            Livewire.on('close-product-modal', () => modal.hide());
        });
    </script>
@endpush
