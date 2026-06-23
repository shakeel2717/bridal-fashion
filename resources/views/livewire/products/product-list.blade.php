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
    <div class="page-header-sticky">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <div class="page-title">Stock</div>
                <div class="page-subtitle">Manage inventory items</div>
            </div>
            <button class="btn btn-primary btn-sm d-flex align-items-center gap-2"
                wire:click="$dispatch('open-create-product')">
                <i class="bi bi-plus-lg"></i> Add Stock
            </button>
        </div>
    </div>

    {{-- Stat pills --}}
    <div class="d-flex gap-2 mb-3">
        <div
            style="background:#fff; border-radius:7px; padding:8px 16px; font-size:12px; border:1px solid var(--border);">
            <span style="color:var(--text-muted);">Active</span>
            <span class="ms-2 fw-700" style="color:#38a169;">{{ $counts['active'] }}</span>
        </div>
        <div
            style="background:#fff; border-radius:7px; padding:8px 16px; font-size:12px; border:1px solid var(--border);">
            <span style="color:var(--text-muted);">Abandoned</span>
            <span class="ms-2 fw-700" style="color:#e53e3e;">{{ $counts['abandoned'] }}</span>
        </div>
        <div
            style="background:#fff; border-radius:7px; padding:8px 16px; font-size:12px; border:1px solid var(--border);">
            <span style="color:var(--text-muted);">Inactive</span>
            <span class="ms-2 fw-700" style="color:#718096;">{{ $counts['inactive'] }}</span>
        </div>
        <div
            style="background:#fff; border-radius:7px; padding:8px 16px; font-size:12px; border:1px solid var(--border);">
            <span style="color:var(--text-muted);">Total</span>
            <span class="ms-2 fw-700">{{ $counts['total'] }}</span>
        </div>
    </div>

    <div class="table-card">
        {{-- Filters --}}
        <div class="table-card-header" style="flex-wrap:wrap; gap:10px;">
            <div class="d-flex gap-2 align-items-center flex-wrap">
                {{-- Status filter --}}
                <div class="tab-pills" style="margin-bottom:0;">
                    <button class="tab-pill {{ $filterStatus === 'active' && $filterStock === '' ? 'active' : '' }}"
                        wire:click="$set('filterStatus','active'); $set('filterStock','')">Active</button>
                    <button class="tab-pill {{ $filterStatus === 'abandoned' ? 'active' : '' }}"
                        wire:click="$set('filterStatus','abandoned'); $set('filterStock','')">Abandoned</button>
                    <button class="tab-pill {{ $filterStatus === 'inactive' ? 'active' : '' }}"
                        wire:click="$set('filterStatus','inactive'); $set('filterStock','')">Inactive</button>
                    <button class="tab-pill {{ $filterStock === 'zero' ? 'active' : '' }}"
                        wire:click="$set('filterStock','zero'); $set('filterStatus','active')">
                        Zero Stock
                        <span
                            style="font-size:10px; background:#e53e3e; color:#fff; padding:0 5px; border-radius:3px; margin-left:4px;">
                            {{ $counts['zero_stock'] }}
                        </span>
                    </button>
                    <button
                        class="tab-pill {{ $filterStock === '' && $filterStatus === 'active' ? '' : ($filterStock === '' && $filterStatus !== 'active' ? '' : '') }} {{ $filterStock === '' && $filterStatus === 'active' && request()->has('page') ? 'active' : '' }}"
                        wire:click="$set('filterStock',''); $set('filterStatus','active')">All</button>
                </div>

                {{-- Type filter --}}
                <select wire:model.live="filterType" class="form-select form-select-sm" style="width:120px;">
                    <option value="">All Types</option>
                    <option value="rental">Rental</option>
                    <option value="sale">Sale</option>
                    <option value="both">Both</option>
                </select>

                {{-- Category filter --}}
                <select wire:model.live="filterCategory" class="form-select form-select-sm" style="width:150px;">
                    <option value="">All Categories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Search --}}
            <div style="width:240px;">
                <input type="text" wire:model.live.debounce.400ms="search" class="form-control form-control-sm"
                    placeholder="Search code, name, size...">
            </div>
        </div>

        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="width:40px; text-align:center;">Sr</th>
                    <th>Design#</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Size</th>
                    <th>Type</th>
                    <th>Prices</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th style="width:120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr class="{{ $product->is_abandoned ? 'abandoned-row' : '' }}">
                        <td style="text-align:center; font-size:12px; color:var(--text-muted); font-weight:600;">
                            {{ $products->firstItem() + $loop->index }}
                        </td>
                        <td>
                            <span class="product-code-badge">{{ $product->code }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if ($product->photo)
                                    <img src="{{ Storage::url($product->photo) }}"
                                        style="width:36px; height:36px; object-fit:cover; border-radius:6px; border:1px solid var(--border); flex-shrink:0;">
                                @else
                                    <div
                                        style="width:36px; height:36px; background:var(--gold-light); border-radius:6px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                        <i class="bi bi-image" style="font-size:16px; color:var(--gold);"></i>
                                    </div>
                                @endif
                                <div>
                                    <div style="font-weight:600; font-size:13px;">{{ $product->name }}</div>
                                    @if ($product->vendor)
                                        <div style="font-size:11px; color:var(--text-muted);">
                                            {{ $product->vendor->name }}</div>
                                    @endif
                                    @if ($product->is_abandoned)
                                        <div style="font-size:10px; color:#e53e3e; font-weight:600;">
                                            ABANDONED — Rs. {{ number_format($product->abandoned_price, 0) }}
                                        </div>
                                    @endif
                                    @if ($product->group)
                                        <div style="font-size:10px; margin-top:2px;">
                                            <span
                                                style="background:#ebf8ff; color:#2c5282; padding:1px 7px; border-radius:4px; font-weight:600;">
                                                <i class="bi bi-collection" style="font-size:9px;"></i>
                                                {{ $product->group->name }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="tbl-code-badge">{{ $product->category->code }}</span>
                            <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">
                                {{ $product->category->name }}
                            </div>
                        </td>
                        <td style="font-size:13px;">
                            @if ($product->size)
                                {{ $product->size }}
                            @endif
                            @if ($product->color)
                                <span
                                    style="display:inline-block; font-size:10px; background:#f0f2f5; padding:1px 7px; border-radius:4px; margin-top:2px;">
                                    {{ $product->color }}
                                </span>
                            @endif
                            @if (!$product->size && !$product->color)
                                —
                            @endif
                        </td>
                        <td>
                            <span class="product-type-badge {{ $product->type }}">
                                {{ ucfirst($product->type) }}
                            </span>
                        </td>
                        <td class="product-price-col">
                            @if ($product->type !== 'sale')
                                <div>
                                    <span class="price-label">Rent</span>
                                    <span class="price-value ms-1">Rs.
                                        {{ number_format($product->rental_price, 0) }}</span>
                                </div>
                            @endif
                            @if ($product->type !== 'rental')
                                <div>
                                    <span class="price-label">Sale</span>
                                    <span class="price-value ms-1">Rs.
                                        {{ number_format($product->sale_price, 0) }}</span>
                                </div>
                            @endif
                            <div>
                                <span class="price-label">Cost</span>
                                <span class="price-value ms-1">Rs.
                                    {{ number_format($product->purchase_price, 0) }}</span>
                            </div>
                        </td>
                        <td style="font-size:13px; font-weight:600; text-align:center;">
                            @if ($product->type === 'fabric')
                                {{ number_format((float) $product->stock_decimal, 2) }}
                                <div style="font-size:10px; color:var(--text-muted); font-weight:400;">
                                    {{ $product->fabric_unit }}</div>
                            @elseif ($product->type === 'service')
                                <span style="font-size:11px; color:var(--text-muted);">—</span>
                            @else
                                {{ $product->stock_qty }}
                            @endif
                        </td>
                        <td>
                            @if ($product->is_abandoned)
                                <span class="badge-status overdue">Abandoned</span>
                            @elseif($product->is_active)
                                <span class="badge-status ready">Active</span>
                            @else
                                <span class="badge-status pending">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                <button class="btn btn-sm btn-outline-secondary action-btn"
                                    wire:click="openEditModal({{ $product->id }})" title="Edit">
                                    <i class="bi bi-pencil" style="font-size:12px;"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning action-btn"
                                    wire:click="openExpenses({{ $product->id }})" title="Expenses">
                                    <i class="bi bi-receipt" style="font-size:12px;"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger action-btn"
                                    wire:click="confirmDelete({{ $product->id }})" title="Delete">
                                    <i class="bi bi-trash" style="font-size:12px;"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9"
                            style="text-align:center; padding:30px; color:var(--text-muted); font-size:13px;">
                            <i class="bi bi-tags" style="font-size:32px; display:block; margin-bottom:8px;"></i>
                            No products found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($products->hasPages())
            <div style="padding:12px 16px; border-top:1px solid var(--border);">
                {{ $products->links('vendor.pagination.simple-bootstrap-5') }}
            </div>
        @endif
    </div>

    {{-- Delete Modal --}}
    @if ($deleteId)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered" style="max-width:380px;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title">Confirm Delete</h6>
                    </div>
                    <div class="modal-body" style="font-size:13px;">
                        Are you sure you want to delete this product?
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

    {{-- Edit Product Modal --}}
    @if ($editId)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title">
                            <i class="bi bi-pencil me-2"></i> Edit Stock
                            <span class="tbl-code-badge ms-2">{{ $editCode }}</span>
                            <span class="product-type-badge {{ $editType }} ms-2">{{ ucfirst($editType) }}</span>
                        </h6>
                        <button type="button" class="btn-close" wire:click="closeEditModal"></button>
                    </div>
                    <div class="modal-body" style="padding:20px 24px;">
                        <div class="row g-3">

                            {{-- Name --}}
                            <div class="col-6">
                                <label class="form-label">Stock Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="editName"
                                    class="form-control @error('editName') is-invalid @enderror"
                                    placeholder="e.g. Red Bridal Lahnga">
                                @error('editName')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Category --}}
                            <div class="col-6">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select wire:model="editCategoryId"
                                    class="form-select @error('editCategoryId') is-invalid @enderror">
                                    <option value="">Select category...</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}
                                            ({{ $cat->code }})</option>
                                    @endforeach
                                </select>
                                @error('editCategoryId')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Group --}}
                            <div class="col-6">
                                <label class="form-label">Stock Group</label>
                                <select wire:model="editGroupId" class="form-select">
                                    <option value="">No group</option>
                                    @foreach ($groups as $group)
                                        <option value="{{ $group->id }}">
                                            {{ $group->name }}{{ $group->code ? ' (' . $group->code . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Code --}}
                            <div class="col-4">
                                <label class="form-label">Item Code <span class="text-danger">*</span></label>
                                <input type="text" wire:model="editCode"
                                    class="form-control @error('editCode') is-invalid @enderror"
                                    style="text-transform:uppercase; font-family:monospace;">
                                @error('editCode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Color --}}
                            <div class="col-4">
                                <label class="form-label">Color</label>
                                <input type="text" wire:model="editColor" class="form-control"
                                    placeholder="e.g. Red, Golden">
                            </div>

                            {{-- Size --}}
                            <div class="col-4">
                                <label class="form-label">Size / Waist</label>
                                <input type="text" wire:model="editSize" class="form-control"
                                    placeholder="e.g. 36, Free">
                            </div>

                            {{-- Prices --}}
                            @if (in_array($editType, ['rental', 'both']))
                                <div class="col-4">
                                    <label class="form-label">Rental Price (Rs.)</label>
                                    <input type="number" wire:model="editRentalPrice" class="form-control"
                                        min="0" placeholder="0">
                                </div>
                            @endif
                            @if (in_array($editType, ['sale', 'both', 'service', 'fabric']))
                                <div class="col-4">
                                    <label class="form-label">
                                        {{ $editType === 'fabric' ? 'Price per ' . ($editFabricUnit === 'gaz' ? 'Gaz' : 'Meter') : 'Sale Price' }}
                                        (Rs.)
                                    </label>
                                    <input type="number" wire:model="editSalePrice"
                                        class="form-control @error('editSalePrice') is-invalid @enderror"
                                        min="0" placeholder="0">
                                    @error('editSalePrice')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif

                            {{-- Fabric unit --}}
                            @if ($editType === 'fabric')
                                <div class="col-4">
                                    <label class="form-label">Fabric Unit</label>
                                    <select wire:model="editFabricUnit" class="form-select">
                                        <option value="meter">Meter</option>
                                        <option value="gaz">Gaz (Yard)</option>
                                    </select>
                                </div>
                            @endif

                            {{-- Photo --}}
                            <div class="col-12">
                                <label class="form-label">Photo
                                    <span style="font-size:11px; color:var(--text-muted); font-weight:400;">(optional,
                                        max 3MB)</span>
                                </label>
                                @if ($editExistingPhoto)
                                    <div class="mb-2 d-flex align-items-center gap-3">
                                        <img src="{{ Storage::url($editExistingPhoto) }}"
                                            style="width:60px; height:60px; object-fit:cover; border-radius:8px; border:1px solid var(--border);">
                                        <span style="font-size:12px; color:var(--text-muted);">Current photo</span>
                                    </div>
                                @endif
                                <input type="file" wire:model="editPhoto"
                                    class="form-control @error('editPhoto') is-invalid @enderror" accept="image/*">
                                @error('editPhoto')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if ($editPhoto)
                                    <div class="mt-2">
                                        <img src="{{ $editPhoto->temporaryUrl() }}"
                                            style="width:60px; height:60px; object-fit:cover; border-radius:8px; border:2px solid var(--gold);">
                                    </div>
                                @endif
                            </div>

                            {{-- Notes --}}
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea wire:model="editNotes" class="form-control" rows="2" placeholder="Any notes..."></textarea>
                            </div>

                            {{-- Status --}}
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model="editIsActive"
                                        id="editActive">
                                    <label class="form-check-label" for="editActive"
                                        style="font-size:13px;">Active</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model.live="editIsAbandoned"
                                        id="editAbandoned">
                                    <label class="form-check-label" for="editAbandoned"
                                        style="font-size:13px; color:#e53e3e;">Mark as Abandoned</label>
                                </div>
                            </div>

                            @if ($editIsAbandoned)
                                <div class="col-12">
                                    <div
                                        style="background:#fff5f5; border:1px solid #fed7d7; border-radius:8px; padding:14px;">
                                        <div
                                            style="font-size:11px; font-weight:700; color:#c53030; margin-bottom:10px; text-transform:uppercase;">
                                            Abandoned Details
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-4">
                                                <label class="form-label">Written-off Value (Rs.)</label>
                                                <input type="number" wire:model="editAbandonedPrice"
                                                    class="form-control form-control-sm" placeholder="0"
                                                    min="0">
                                            </div>
                                            <div class="col-4">
                                                <label class="form-label">Date</label>
                                                <input type="date" wire:model="editAbandonedDate"
                                                    class="form-control form-control-sm">
                                            </div>
                                            <div class="col-4">
                                                <label class="form-label">Reason</label>
                                                <input type="text" wire:model="editAbandonedNote"
                                                    class="form-control form-control-sm"
                                                    placeholder="e.g. torn, lost">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>
                    <div class="modal-footer gap-2">
                        <button class="btn btn-sm btn-outline-secondary" wire:click="closeEditModal">Cancel</button>
                        <button class="btn btn-sm btn-primary" wire:click="saveEdit" wire:loading.attr="disabled">
                            <span wire:loading wire:target="saveEdit">
                                <span class="spinner-border spinner-border-sm me-1"></span>
                            </span>
                            Update Stock
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Sub-components --}}
    <livewire:products.product-form />
    {{-- Expenses Modal --}}
    <div class="modal fade" id="expensesModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">
                        <i class="bi bi-receipt me-2"></i> Product Expenses
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        wire:click="closeExpenses"></button>
                </div>

                <div class="modal-body" style="padding:0;">
                    @if ($expenseProductName)
                        <div
                            style="padding:12px 20px; background:#f7fafc; border-bottom:1px solid var(--border); font-size:13px; font-weight:600;">
                            {{ $expenseProductName }}
                        </div>
                    @endif

                    {{-- Add Form --}}
                    <div style="padding:16px 20px; border-bottom:1px solid var(--border);">
                        <div
                            style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:10px;">
                            Add Expense
                        </div>
                        <div class="row g-2">
                            <div class="col-4">
                                <label class="form-label">Amount (Rs.) <span class="text-danger">*</span></label>
                                <input type="number" wire:model="expenseAmount"
                                    class="form-control form-control-sm @error('expenseAmount') is-invalid @enderror"
                                    placeholder="500" min="1">
                                @error('expenseAmount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-4">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" wire:model="expenseDate"
                                    class="form-control form-control-sm @error('expenseDate') is-invalid @enderror">
                                @error('expenseDate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-4">
                                <label class="form-label">Note</label>
                                <input type="text" wire:model="expenseNote" class="form-control form-control-sm"
                                    placeholder="e.g. repair">
                            </div>
                            <div class="col-12">
                                <button class="btn btn-sm btn-primary" wire:click="saveExpense"
                                    wire:loading.attr="disabled">
                                    <span wire:loading wire:target="saveExpense">
                                        <span class="spinner-border spinner-border-sm me-1"></span>
                                    </span>
                                    Add Expense
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- List --}}
                    <div style="padding:16px 20px; max-height:280px; overflow-y:auto;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div
                                style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted);">
                                Expense History
                            </div>
                            @if ($totalExpenses > 0)
                                <div style="font-size:12px; font-weight:700; color:#e53e3e;">
                                    Total: Rs. {{ number_format($totalExpenses, 0) }}
                                </div>
                            @endif
                        </div>

                        @forelse($expenses as $expense)
                            <div class="expense-item">
                                <div>
                                    <div style="font-weight:600; color:var(--text-primary);">
                                        {{ $expense->note ?? 'Expense' }}
                                    </div>
                                    <div class="expense-date">
                                        {{ $expense->expense_date->format('d/m/Y') }}
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="expense-amount">
                                        Rs. {{ number_format($expense->amount, 0) }}
                                    </span>
                                    @if ($expenseDeleteId === $expense->id)
                                        <button class="btn btn-sm btn-danger action-btn"
                                            wire:click="deleteExpense()">Confirm</button>
                                        <button class="btn btn-sm btn-outline-secondary action-btn"
                                            wire:click="$set('expenseDeleteId', null)">Cancel</button>
                                    @else
                                        <button class="btn btn-sm btn-outline-danger action-btn"
                                            wire:click="confirmDeleteExpense({{ $expense->id }})">
                                            <i class="bi bi-trash" style="font-size:11px;"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div style="text-align:center; padding:20px; color:var(--text-muted); font-size:12px;">
                                <i class="bi bi-receipt"
                                    style="font-size:24px; display:block; margin-bottom:6px;"></i>
                                No expenses recorded
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal"
                        wire:click="closeExpenses">Close</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('livewire:initialized', () => {
                const expModal = new bootstrap.Modal(document.getElementById('expensesModal'));
                Livewire.on('show-expenses-modal', () => expModal.show());
            });
        </script>
    @endpush
</div>
