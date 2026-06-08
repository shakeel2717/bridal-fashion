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

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">Vendors</div>
            <div class="page-subtitle">Manage product suppliers</div>
        </div>
        <button class="btn btn-primary btn-sm d-flex align-items-center gap-2" wire:click="openCreate">
            <i class="bi bi-plus-lg"></i> Add Vendor
        </button>
    </div>

    <div class="row g-3">

        @if ($showForm)
            <div class="col-4">
                <div class="table-card">
                    <div class="table-card-header">
                        <span class="table-card-title">
                            {{ $editId ? 'Edit Vendor' : 'New Vendor' }}
                        </span>
                        <button class="btn btn-sm btn-outline-secondary action-btn" wire:click="resetForm">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <div style="padding:16px;">
                        <div class="mb-3">
                            <label class="form-label">Vendor Name <span class="text-danger">*</span></label>
                            <input type="text" wire:model="name"
                                class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Ali Fabrics">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" wire:model="phone" class="form-control" placeholder="03XX-XXXXXXX">
                        </div>

                        <div class="col-12">
                            <label class="form-label">
                                Vendor Photo
                                <span
                                    style="color:var(--text-muted); font-weight:400; font-size:11px;">(optional)</span>
                            </label>
                            @if ($existingVendorPhoto)
                                <div class="mb-2">
                                    <img src="{{ Storage::url($existingVendorPhoto) }}"
                                        style="width:50px; height:50px; object-fit:cover; border-radius:50%; border:2px solid var(--gold);">
                                </div>
                            @endif
                            <input type="file" wire:model="vendorPhoto" class="form-control form-control-sm"
                                accept="image/*">
                            @if ($vendorPhoto)
                                <div class="mt-2">
                                    <img src="{{ $vendorPhoto->temporaryUrl() }}"
                                        style="width:50px; height:50px; object-fit:cover; border-radius:50%; border:2px solid var(--gold);">
                                </div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea wire:model="address" class="form-control" rows="2" placeholder="Vendor address..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea wire:model="notes" class="form-control" rows="2" placeholder="Any notes..."></textarea>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" wire:model="isActive" id="vendorActive">
                                <label class="form-check-label" for="vendorActive"
                                    style="font-size:13px;">Active</label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button class="btn btn-primary btn-sm flex-fill" wire:click="save"
                                wire:loading.attr="disabled">
                                <span wire:loading wire:target="save">
                                    <span class="spinner-border spinner-border-sm me-1"></span>
                                </span>
                                {{ $editId ? 'Update' : 'Save Vendor' }}
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" wire:click="resetForm">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="{{ $showForm ? 'col-8' : 'col-12' }}">
            <div class="table-card">
                <div class="table-card-header">
                    <span class="table-card-title">All Vendors</span>
                    <div style="width:220px;">
                        <input type="text" wire:model.live.debounce.400ms="search"
                            class="form-control form-control-sm" placeholder="Search name or phone...">
                    </div>
                </div>

                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width:50px;">#</th>
                            <th>Vendor Name</th>
                            <th>Phone</th>
                            <th>Products</th>
                            <th>Status</th>
                            <th>Added</th>
                            <th style="width:100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vendors as $vendor)
                            <tr>
                                <td style="color:var(--text-muted); font-size:11px;">{{ $vendor->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($vendor->photo)
    <img src="{{ Storage::url($vendor->photo) }}"
         style="width:32px; height:32px; object-fit:cover; border-radius:50%; border:1px solid var(--border); flex-shrink:0;">
                                        @else
                                            <div
                                                style="width:32px; height:32px; background:var(--navy); border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                                <span style="font-size:11px; font-weight:700; color:#fff;">
                                                    {{ strtoupper(substr($vendor->name, 0, 2)) }}
                                                </span>
                                            </div>
                                        @endif
                                        <div>
                                            <div style="font-weight:600; font-size:13px;">{{ $vendor->name }}</div>
                                            @if ($vendor->phone)
                                                <div style="font-size:11px; color:var(--text-muted);">
                                                    {{ $vendor->phone }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td style="font-size:13px;">{{ $vendor->phone ?? '—' }}</td>
                                <td style="font-size:13px; font-weight:600;">
                                    {{ $vendor->products_count }}
                                </td>
                                <td>
                                    <span class="status-dot {{ $vendor->is_active ? 'active' : 'inactive' }}"></span>
                                    <span style="font-size:12px;">
                                        {{ $vendor->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td style="font-size:12px; color:var(--text-muted);">
                                    {{ $vendor->created_at->format('d/m/Y') }}
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-outline-secondary action-btn"
                                            wire:click="openEdit({{ $vendor->id }})" title="Edit">
                                            <i class="bi bi-pencil" style="font-size:12px;"></i>
                                        </button>
                                        <button
                                            class="btn btn-sm {{ $vendor->is_active ? 'btn-outline-warning' : 'btn-outline-success' }} action-btn"
                                            wire:click="toggleStatus({{ $vendor->id }})"
                                            title="{{ $vendor->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="bi bi-{{ $vendor->is_active ? 'pause' : 'play' }}"
                                                style="font-size:12px;"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger action-btn"
                                            wire:click="confirmDelete({{ $vendor->id }})" title="Delete">
                                            <i class="bi bi-trash" style="font-size:12px;"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7"
                                    style="text-align:center; padding:30px; color:var(--text-muted); font-size:13px;">
                                    <i class="bi bi-shop"
                                        style="font-size:32px; display:block; margin-bottom:8px;"></i>
                                    No vendors found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if ($vendors->hasPages())
                    <div style="padding:12px 16px; border-top:1px solid var(--border);">
                        {{ $vendors->links('vendor.pagination.simple-bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if ($deleteId)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered" style="max-width:380px;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title">Confirm Delete</h6>
                    </div>
                    <div class="modal-body" style="font-size:13px;">
                        Are you sure you want to delete this vendor?
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
</div>
