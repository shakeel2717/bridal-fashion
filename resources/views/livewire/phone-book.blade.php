<div>

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">Phone Book</div>
            <div class="page-subtitle">Contacts & service providers</div>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="openCatManager" class="btn btn-outline-secondary btn-sm">
                Manage Categories
            </button>
            <button wire:click="openForm()" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Add Contact
            </button>
        </div>
    </div>

    <div class="row g-3">

        {{-- ── Side Panel Form ────────────────────────────────────────────── --}}
        @if($showForm)
            <div class="col-4">
                <div class="table-card">
                    <div class="table-card-header">
                        <span class="table-card-title">{{ $editId ? 'Edit Contact' : 'New Contact' }}</span>
                        <button class="btn btn-sm btn-outline-secondary action-btn" wire:click="resetForm">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <div style="padding:16px;">

                        <div class="mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" wire:model="contactName"
                                class="form-control @error('contactName') is-invalid @enderror"
                                placeholder="e.g. Rashid Electrician">
                            @error('contactName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select wire:model="contactCategoryId"
                                class="form-select @error('contactCategoryId') is-invalid @enderror">
                                <option value="">— No Category —</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('contactCategoryId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phone Numbers <span class="text-danger">*</span></label>
                            @foreach($contactPhones as $i => $phone)
                                <div class="d-flex gap-2 mb-2">
                                    <input type="text" wire:model="contactPhones.{{ $i }}"
                                        class="form-control @error('contactPhones.' . $i) is-invalid @enderror"
                                        placeholder="03XX-XXXXXXX">
                                    @if(count($contactPhones) > 1)
                                        <button type="button" wire:click="removePhone({{ $i }})"
                                            class="btn btn-outline-danger btn-sm action-btn flex-shrink-0">
                                            <i class="bi bi-dash"></i>
                                        </button>
                                    @endif
                                    @error('contactPhones.' . $i)
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endforeach
                            <button type="button" wire:click="addPhone"
                                class="btn btn-outline-secondary btn-sm" style="font-size:12px;">
                                + Add Number
                            </button>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Notes <small class="text-muted">(optional)</small></label>
                            <textarea wire:model="contactNotes" class="form-control" rows="2"
                                placeholder="e.g. Available weekdays only..."></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button class="btn btn-primary btn-sm flex-fill" wire:click="save"
                                wire:loading.attr="disabled" wire:target="save">
                                <span wire:loading wire:target="save">
                                    <span class="spinner-border spinner-border-sm me-1"></span>
                                </span>
                                {{ $editId ? 'Update' : 'Save Contact' }}
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" wire:click="resetForm">Cancel</button>
                        </div>

                    </div>
                </div>
            </div>
        @endif

        {{-- ── Contacts Table ──────────────────────────────────────────────── --}}
        <div class="{{ $showForm ? 'col-8' : 'col-12' }}">
            <div class="table-card">
                <div class="table-card-header">
                    <span class="table-card-title">
                        All Contacts
                        <span style="font-size:11px; font-weight:400; color:var(--text-muted); margin-left:6px;">
                            {{ $contacts->count() }} found
                        </span>
                    </span>
                    <div class="d-flex gap-2">
                        <input type="text" wire:model.live.debounce.300ms="search"
                            class="form-control form-control-sm" placeholder="Search name, phone, notes..."
                            style="width:200px;">
                        <select wire:model.live="filterCategory" class="form-select form-select-sm" style="width:150px;">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $cat->contacts_count }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Phone Numbers</th>
                            <th>Notes</th>
                            <th style="width:90px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contacts as $contact)
                            <tr>
                                <td style="color:var(--text-muted); font-size:11px;">{{ $contact->id }}</td>
                                <td>
                                    <div style="font-weight:600; font-size:13px;">{{ $contact->name }}</div>
                                </td>
                                <td>
                                    @if($contact->category)
                                        <span class="tbl-code-badge">{{ $contact->category->name }}</span>
                                    @else
                                        <span style="color:var(--text-muted); font-size:12px;">—</span>
                                    @endif
                                </td>
                                <td>
                                    @foreach($contact->phone_numbers as $phone)
                                        <div style="font-size:13px; font-weight:500;">{{ $phone }}</div>
                                    @endforeach
                                </td>
                                <td style="font-size:12px; color:var(--text-muted);">
                                    {{ $contact->notes ? Str::limit($contact->notes, 40) : '—' }}
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-outline-secondary action-btn"
                                            wire:click="openForm({{ $contact->id }})" title="Edit">
                                            <i class="bi bi-pencil" style="font-size:12px;"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger action-btn"
                                            wire:click="confirmDelete({{ $contact->id }})" title="Delete">
                                            <i class="bi bi-trash" style="font-size:12px;"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align:center; padding:40px; color:var(--text-muted); font-size:13px;">
                                    @if($search || $filterCategory !== null)
                                        No contacts match your search.
                                    @else
                                        No contacts yet. Click <strong>Add Contact</strong> to get started.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>
        </div>

    </div>


    {{-- ── Category Manager Modal ───────────────────────────────────────────── --}}
    @if($showCatManager)
        <div class="modal fade show" tabindex="-1"
            style="display:block; background:rgba(0,0,0,0.5);"
            wire:click.self="closeCatManager">
            <div class="modal-dialog modal-dialog-centered" style="max-width:440px;">
                <div class="modal-content">

                    <div class="modal-header" style="background:var(--navy); color:#fff; padding:12px 20px;">
                        <div style="font-size:14px; font-weight:700;">Manage Categories</div>
                        <button wire:click="closeCatManager" class="btn-close btn-close-white ms-auto" style="opacity:.8;"></button>
                    </div>

                    <div class="modal-body" style="padding:16px 20px;">

                        @if(session('cat_error'))
                            <div class="alert alert-danger py-2 mb-3" style="font-size:12px;">
                                {{ session('cat_error') }}
                            </div>
                        @endif

                        {{-- Add / Edit Form --}}
                        @if($showCatForm)
                            <div style="background:#f8f9fa; border-radius:8px; padding:14px; margin-bottom:16px;">
                                <div style="font-size:12px; font-weight:700; color:var(--navy); margin-bottom:10px; text-transform:uppercase;">
                                    {{ $editCatId ? 'Edit Category' : 'New Category' }}
                                </div>
                                <div class="mb-3">
                                    <input type="text" wire:model="catName"
                                        class="form-control form-control-sm @error('catName') is-invalid @enderror"
                                        placeholder="Category name (e.g. Electrician, Plumber)">
                                    @error('catName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="d-flex gap-2">
                                    <button wire:click="saveCategory" class="btn btn-primary btn-sm"
                                        wire:loading.attr="disabled" wire:target="saveCategory">
                                        <span wire:loading wire:target="saveCategory">
                                            <span class="spinner-border spinner-border-sm"></span>
                                        </span>
                                        {{ $editCatId ? 'Update' : 'Add' }}
                                    </button>
                                    <button wire:click="$set('showCatForm', false)" class="btn btn-outline-secondary btn-sm">Cancel</button>
                                </div>
                            </div>
                        @else
                            <button wire:click="openCatForm()" class="btn btn-outline-primary btn-sm mb-3" style="font-size:12px;">
                                <i class="bi bi-plus me-1"></i> New Category
                            </button>
                        @endif

                        {{-- Categories List --}}
                        @forelse($categories as $cat)
                            <div class="d-flex align-items-center justify-content-between py-2"
                                style="border-bottom:1px solid var(--border);">
                                <div>
                                    <span style="font-size:13px; font-weight:600;">{{ $cat->name }}</span>
                                    <span style="font-size:11px; color:var(--text-muted); margin-left:6px;">
                                        {{ $cat->contacts_count }} contact(s)
                                    </span>
                                </div>
                                <div class="d-flex gap-1 align-items-center">
                                    @if($deleteCatId === $cat->id)
                                        <span style="font-size:12px; color:#c53030; margin-right:4px;">Sure?</span>
                                        <button wire:click="deleteCategory" class="btn btn-danger btn-sm py-0 px-2" style="font-size:11px;">Yes</button>
                                        <button wire:click="$set('deleteCatId', null)" class="btn btn-outline-secondary btn-sm py-0 px-2" style="font-size:11px;">No</button>
                                    @else
                                        <button wire:click="openCatForm({{ $cat->id }})"
                                            class="btn btn-sm btn-outline-secondary action-btn" title="Edit">
                                            <i class="bi bi-pencil" style="font-size:11px;"></i>
                                        </button>
                                        <button wire:click="confirmDeleteCat({{ $cat->id }})"
                                            class="btn btn-sm btn-outline-danger action-btn" title="Delete">
                                            <i class="bi bi-trash" style="font-size:11px;"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div style="text-align:center; padding:20px; color:var(--text-muted); font-size:13px;">
                                No categories yet.
                            </div>
                        @endforelse

                    </div>

                    <div class="modal-footer" style="padding:10px 20px; background:#f0f4f8;">
                        <button wire:click="closeCatManager" class="btn btn-sm btn-outline-secondary">Close</button>
                    </div>

                </div>
            </div>
        </div>
    @endif


    {{-- ── Delete Contact Confirm ───────────────────────────────────────────── --}}
    @if($deleteId)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered" style="max-width:380px;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title">Confirm Delete</h6>
                    </div>
                    <div class="modal-body" style="font-size:13px;">
                        Are you sure you want to delete this contact? This cannot be undone.
                    </div>
                    <div class="modal-footer gap-2">
                        <button class="btn btn-sm btn-outline-secondary" wire:click="$set('deleteId', null)">Cancel</button>
                        <button class="btn btn-sm btn-danger" wire:click="delete()">Yes, Delete</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
