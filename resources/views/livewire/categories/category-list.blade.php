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
            <div class="page-title">Categories</div>
            <div class="page-subtitle">Manage product categories</div>
        </div>
        <button class="btn btn-primary btn-sm d-flex align-items-center gap-2" wire:click="openCreate">
            <i class="bi bi-plus-lg"></i> Add Category
        </button>
    </div>

    <div class="row g-3">

        {{-- Form Panel --}}
        @if ($showForm)
            <div class="col-4">
                <div class="table-card">
                    <div class="table-card-header">
                        <span class="table-card-title">
                            {{ $editId ? 'Edit Category' : 'New Category' }}
                        </span>
                        <button class="btn btn-sm btn-outline-secondary action-btn" wire:click="resetForm">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <div style="padding:16px;">
                        <div class="mb-3">
                            <label class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" id="category_name_input" wire:model="name"
                                class="form-control @error('name') is-invalid @enderror"
                                placeholder="e.g. Bridal Lahnga">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Category Code <span class="text-danger">*</span></label>
                            <input type="text" wire:model="code"
                                class="form-control @error('code') is-invalid @enderror" placeholder="e.g. BL"
                                style="text-transform:uppercase;">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">
                                Short code used in product codes (e.g. BL-001)
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea wire:model="description" class="form-control" rows="2" placeholder="Optional description..."></textarea>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" wire:model="isActive" id="catActive">
                                <label class="form-check-label" for="catActive" style="font-size:13px;">Active</label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button class="btn btn-primary btn-sm flex-fill" wire:click="save"
                                wire:loading.attr="disabled">
                                <span wire:loading wire:target="save">
                                    <span class="spinner-border spinner-border-sm me-1"></span>
                                </span>
                                {{ $editId ? 'Update' : 'Save Category' }}
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" wire:click="resetForm">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Table --}}
        <div class="{{ $showForm ? 'col-8' : 'col-12' }}">
            <div class="table-card">
                <div class="table-card-header">
                    <span class="table-card-title">All Categories</span>
                    <div style="width:220px;">
                        <input type="text" wire:model.live.debounce.400ms="search"
                            class="form-control form-control-sm" placeholder="Search name or code...">
                    </div>
                </div>

                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width:50px;">#</th>
                            <th>Category Name</th>
                            <th>Code</th>
                            <th>Products</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th style="width:100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td style="color:var(--text-muted); font-size:11px;">{{ $category->id }}</td>
                                <td>
                                    <div style="font-weight:600; font-size:13px;">{{ $category->name }}</div>
                                    @if ($category->description)
                                        <div style="font-size:11px; color:var(--text-muted);">
                                            {{ Str::limit($category->description, 40) }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="tbl-code-badge">{{ $category->code }}</span>
                                </td>
                                <td style="font-size:13px; font-weight:600;">
                                    {{ $category->products_count }}
                                </td>
                                <td>
                                    <span class="status-dot {{ $category->is_active ? 'active' : 'inactive' }}"></span>
                                    <span style="font-size:12px;">
                                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td style="font-size:12px; color:var(--text-muted);">
                                    {{ $category->created_at->format('d/m/Y') }}
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-outline-secondary action-btn"
                                            wire:click="openEdit({{ $category->id }})" title="Edit">
                                            <i class="bi bi-pencil" style="font-size:12px;"></i>
                                        </button>
                                        <button
                                            class="btn btn-sm {{ $category->is_active ? 'btn-outline-warning' : 'btn-outline-success' }} action-btn"
                                            wire:click="toggleStatus({{ $category->id }})"
                                            title="{{ $category->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="bi bi-{{ $category->is_active ? 'pause' : 'play' }}"
                                                style="font-size:12px;"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger action-btn"
                                            wire:click="confirmDelete({{ $category->id }})" title="Delete">
                                            <i class="bi bi-trash" style="font-size:12px;"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7"
                                    style="text-align:center; padding:30px; color:var(--text-muted); font-size:13px;">
                                    <i class="bi bi-folder2-open"
                                        style="font-size:32px; display:block; margin-bottom:8px;"></i>
                                    No categories found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if ($categories->hasPages())
                    <div style="padding:12px 16px; border-top:1px solid var(--border);">
                        {{ $categories->links('vendor.pagination.simple-bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
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
                        Are you sure you want to delete this category?
                        This cannot be undone and will fail if products are assigned.
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
    @push('scripts')
        <script>
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('focus-first-input', ({
                    selector
                }) => {
                    setTimeout(() => {
                        const el = document.querySelector(selector);
                        if (el) {
                            el._skipAutoSelect = true;
                            el.focus();
                            el.select();
                        }
                    }, 150);
                });
            });
        </script>
    @endpush
</div>
