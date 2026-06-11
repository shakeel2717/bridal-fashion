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

                        {{-- Name --}}
                        <div class="col-6">
                            <label class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" wire:model="name" id="pf_name"
                                class="form-control @error('name') is-invalid @enderror"
                                placeholder="e.g. Red Bridal Lahnga">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Category --}}
                        <div class="col-6">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            @if (!$showCategoryForm)
                                <div class="d-flex gap-2">
                                    <select wire:model.live="categoryId" id="pf_category"
                                        class="form-select @error('categoryId') is-invalid @enderror">
                                        <option value="">Select category...</option>
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}
                                                ({{ $cat->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="button" wire:click="openCategoryForm"
                                        class="btn btn-outline-secondary" style="padding:0 12px;" title="Add category">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                                @error('categoryId')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            @else
                                <div
                                    style="background:#f0fff4; border:1.5px solid #9ae6b4; border-radius:8px; padding:10px;">
                                    <div style="font-size:11px; font-weight:700; color:#276749; margin-bottom:8px;">
                                        <i class="bi bi-folder-plus me-1"></i> New Category
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-7">
                                            <input type="text" wire:model="newCategoryName"
                                                class="form-control form-control-sm @error('newCategoryName') is-invalid @enderror"
                                                placeholder="Category name *">
                                            @error('newCategoryName')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-5">
                                            <input type="text" wire:model="newCategoryCode"
                                                class="form-control form-control-sm @error('newCategoryCode') is-invalid @enderror"
                                                placeholder="Code e.g. BL *" style="text-transform:uppercase;">
                                            @error('newCategoryCode')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-12 d-flex gap-2">
                                            <button wire:click="saveCategory" class="btn btn-sm btn-success flex-fill">
                                                <i class="bi bi-check me-1"></i> Save
                                            </button>
                                            <button wire:click="cancelCategoryForm"
                                                class="btn btn-sm btn-outline-secondary">Cancel</button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Product Group --}}
                        <div class="col-6">
                            <label class="form-label">
                                Product Group
                                <span style="font-size:10px; color:var(--text-muted); font-weight:400;">(link similar
                                    items)</span>
                            </label>
                            @if (!$showGroupForm)
                                <div class="d-flex gap-2">
                                    <select wire:model="groupId" class="form-select">
                                        <option value="">No group</option>
                                        @foreach ($groups as $group)
                                            <option value="{{ $group->id }}">
                                                {{ $group->name }}
                                                @if ($group->code)
                                                    ({{ $group->code }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="button" wire:click="openGroupForm" class="btn btn-outline-secondary"
                                        style="padding:0 12px;" title="New group">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                            @else
                                <div
                                    style="background:#ebf8ff; border:1.5px solid #bee3f8; border-radius:8px; padding:10px;">
                                    <div class="row g-2">
                                        <div class="col-8">
                                            <input type="text" wire:model="newGroupName"
                                                class="form-control form-control-sm @error('newGroupName') is-invalid @enderror"
                                                placeholder="Group name *">
                                            @error('newGroupName')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-4">
                                            <input type="text" wire:model="newGroupCode"
                                                class="form-control form-control-sm" placeholder="Code (opt.)"
                                                style="text-transform:uppercase;">
                                        </div>
                                        <div class="col-12 d-flex gap-2">
                                            <button wire:click="saveGroup"
                                                class="btn btn-sm btn-primary flex-fill">Save</button>
                                            <button wire:click="cancelGroupForm"
                                                class="btn btn-sm btn-outline-secondary">Cancel</button>
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

                        {{-- Qty — only for rental/both --}}
@if($type !== 'sale')
<div class="col-4">
    <label class="form-label">
        How Many Separate Records
        <span style="font-size:10px; color:var(--text-muted); font-weight:400;">(not stock)</span>
    </label>
    <input type="number" wire:model.live="stockQty"
        class="form-control @error('stockQty') is-invalid @enderror" min="1"
        @if($isEdit) readonly @endif>
    @error('stockQty') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
@endif

                        {{-- For SALE type: simple single code input --}}
                        @if (!$isEdit && $type === 'sale')
                            <div class="col-4">
                                <label class="form-label">Design # (Code) <span class="text-danger">*</span></label>
                                <input type="text" wire:model="itemVariants.0.code"
                                    class="form-control @error('itemVariants.0.code') is-invalid @enderror"
                                    placeholder="e.g. SH-001"
                                    style="text-transform:uppercase; font-family:monospace;">
                                @error('itemVariants.0.code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- For RENTAL/BOTH type: full per-item grid --}}
                        @elseif(!$isEdit && in_array($type, ['rental', 'both']) && count($itemVariants) > 0)
                            <div class="col-12">
                                <div
                                    style="background:#f7fafc; border:1px solid var(--border); border-radius:8px; padding:14px;">
                                    <div
                                        style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:12px;">
                                        <i class="bi bi-list-ul me-1"></i>
                                        Item Details
                                        <span style="font-weight:400; font-size:10px; margin-left:6px;">(code required
                                            for each item)</span>
                                    </div>
                                    <div
                                        style="display:grid; grid-template-columns: 50px 1fr 1fr 1fr; gap:8px; margin-bottom:6px;">
                                        <div style="font-size:10px; font-weight:700; color:var(--text-muted);">#</div>
                                        <div style="font-size:10px; font-weight:700; color:var(--text-muted);">CODE
                                            <span style="color:#e53e3e;">*</span></div>
                                        <div style="font-size:10px; font-weight:700; color:var(--text-muted);">COLOR
                                        </div>
                                        <div style="font-size:10px; font-weight:700; color:var(--text-muted);">SIZE
                                        </div>
                                    </div>
                                    @foreach ($itemVariants as $i => $variant)
                                        <div
                                            style="display:grid; grid-template-columns: 50px 1fr 1fr 1fr; gap:8px; margin-bottom:6px; align-items:start;">
                                            <div style="padding-top:6px;">
                                                <span class="tbl-code-badge"
                                                    style="font-size:10px;">{{ $i + 1 }}</span>
                                            </div>
                                            <div>
                                                <input type="text"
                                                    wire:model="itemVariants.{{ $i }}.code"
                                                    class="form-control form-control-sm @error('itemVariants.' . $i . '.code') is-invalid @enderror"
                                                    placeholder="e.g. BL-001"
                                                    style="text-transform:uppercase; font-family:monospace;">
                                                @error('itemVariants.' . $i . '.code')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div>
                                                <input type="text"
                                                    wire:model="itemVariants.{{ $i }}.color"
                                                    class="form-control form-control-sm" placeholder="e.g. Red">
                                            </div>
                                            <div>
                                                <input type="text"
                                                    wire:model="itemVariants.{{ $i }}.size"
                                                    class="form-control form-control-sm" placeholder="e.g. 36">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Edit: single code/color/size row --}}
                        @if ($isEdit && count($itemVariants) > 0)
                            <div class="col-4">
                                <label class="form-label">Item Code <span class="text-danger">*</span></label>
                                <input type="text" wire:model="itemVariants.0.code"
                                    class="form-control @error('itemVariants.0.code') is-invalid @enderror"
                                    placeholder="e.g. BL-001"
                                    style="text-transform:uppercase; font-family:monospace;">
                                @error('itemVariants.0.code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-4">
                                <label class="form-label">Color</label>
                                <input type="text" wire:model="itemVariants.0.color" class="form-control"
                                    placeholder="e.g. Red, Golden">
                            </div>
                            <div class="col-4">
                                <label class="form-label">Size / Waist</label>
                                <input type="text" wire:model="itemVariants.0.size" class="form-control"
                                    placeholder="e.g. 36, Free">
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
                                        style="width:60px; height:60px; object-fit:cover; border-radius:8px; border:1px solid var(--border);">
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
                                        style="width:60px; height:60px; object-fit:cover; border-radius:8px; border:2px solid var(--gold);">
                                </div>
                            @endif
                        </div>

                        {{-- Notes --}}
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea wire:model="notes" class="form-control" rows="2" placeholder="Any notes about this product..."></textarea>
                        </div>

                        {{-- Status --}}
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" wire:model="isActive"
                                    id="prodActive">
                                <label class="form-check-label" for="prodActive"
                                    style="font-size:13px;">Active</label>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" wire:model.live="isAbandoned"
                                    id="prodAbandoned">
                                <label class="form-check-label" for="prodAbandoned"
                                    style="font-size:13px; color:#e53e3e;">Mark as Abandoned</label>
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
