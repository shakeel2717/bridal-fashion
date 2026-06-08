<div>
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <div class="page-title">New Purchase Order</div>
            <div class="page-subtitle">Create vendor purchase order</div>
        </div>
        <a href="{{ route('purchase-orders.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="row g-3">
        <div class="col-8">

            {{-- Vendor & Header --}}
            <div class="table-card mb-3" style="padding:20px;">
                <div
                    style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:14px;">
                    <i class="bi bi-shop me-1"></i> Vendor & Order Info
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label">Vendor <span class="text-danger">*</span></label>
                        @if (!$showVendorForm)
                            <div class="d-flex gap-2">
                                <select wire:model="vendorId"
                                    class="form-select @error('vendorId') is-invalid @enderror">
                                    <option value="">Select vendor...</option>
                                    @foreach ($vendors as $vendor)
                                        <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" wire:click="$set('showVendorForm', true)"
                                    class="btn btn-outline-secondary" style="padding:0 12px;" title="Add vendor">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                            @error('vendorId')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        @else
                            <div
                                style="background:#f0fff4; border:1.5px solid #9ae6b4; border-radius:8px; padding:10px;">
                                <div class="row g-2">
                                    <div class="col-7">
                                        <input type="text" wire:model="newVendorName"
                                            class="form-control form-control-sm" placeholder="Vendor name *">
                                    </div>
                                    <div class="col-5">
                                        <input type="text" wire:model="newVendorPhone"
                                            class="form-control form-control-sm" placeholder="Phone">
                                    </div>
                                    <div class="col-12 d-flex gap-2">
                                        <button wire:click="saveVendor"
                                            class="btn btn-sm btn-success flex-fill">Save</button>
                                        <button wire:click="$set('showVendorForm', false)"
                                            class="btn btn-sm btn-outline-secondary">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="col-6">
                        <label class="form-label">Vendor Bill Number</label>
                        <input type="text" wire:model="vendorBillNumber" class="form-control"
                            placeholder="e.g. VB-2024-001">
                    </div>

                    <div class="col-4">
                        <label class="form-label">Order Date <span class="text-danger">*</span></label>
                        <input type="date" wire:model="orderDate"
                            class="form-control @error('orderDate') is-invalid @enderror">
                        @error('orderDate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-4">
                        <label class="form-label">Expected Delivery</label>
                        <input type="date" wire:model="expectedDate" class="form-control">
                    </div>

                    <div class="col-4">
                        <label class="form-label">Discount (Rs.)</label>
                        <input type="number" wire:model.lazy="discount"
                            wire:change="recalcItems"
                            class="form-control" min="0" placeholder="0">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea wire:model="notes" class="form-control" rows="2" placeholder="Any notes..."></textarea>
                    </div>
                </div>
            </div>

            {{-- Items --}}
            <div class="table-card mb-3" style="padding:20px;">
                <div
                    style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:14px;">
                    <i class="bi bi-box me-1"></i> Items
                </div>

                {{-- Product Search --}}
                <div style="position:relative; margin-bottom:14px;">
                    <input type="text" wire:model.live.debounce.300ms="productSearch" wire:keyup="searchProducts"
                        class="form-control form-control-sm" placeholder="Search existing product to add...">

                    @if (count($searchResults) > 0)
                        <div class="product-search-dropdown">
                            @foreach ($searchResults as $result)
                                <div class="search-item" wire:click="addProduct({{ $result['id'] }})">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <span class="search-item-code">{{ $result['code'] }}</span>
                                            <div class="search-item-name">{{ $result['name'] }}</div>
                                            <div class="search-item-category">{{ $result['category'] }}</div>
                                        </div>
                                        <div class="search-item-price">
                                            Cost: Rs. {{ number_format($result['purchase_price'], 0) }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <button type="button" wire:click="addCustomItem"
                    style="background:none; border:1.5px dashed var(--border); border-radius:6px; padding:6px 14px; font-size:12px; font-weight:600; color:var(--text-muted); cursor:pointer; width:100%; margin-bottom:14px;">
                    <i class="bi bi-plus me-1"></i> Add Custom Item (not in system)
                </button>

                @error('items')
                    <div class="alert alert-danger py-2 mb-3" style="font-size:12px;">{{ $message }}</div>
                @enderror

                {{-- Items Grid --}}
                @if (count($items) > 0)
                    <div class="po-item-row header">
                        <div>Item</div>
                        <div style="text-align:center;">Qty</div>
                        <div style="text-align:right;">Unit Price</div>
                        <div style="text-align:right;">Total</div>
                        <div></div>
                    </div>

                    @foreach ($items as $index => $item)
                        <div style="background:#f7fafc; border-radius:8px; padding:12px; margin-bottom:8px;">
                            <div class="row g-2 align-items-center">
                                <div class="col-5">
                                    <input type="text" wire:model="items.{{ $index }}.item_name"
                                        class="form-control form-control-sm" placeholder="Item name"
                                        {{ $item['product_id'] ? 'readonly' : '' }}>
                                </div>
                                <div class="col-1">
                                    <input type="number" wire:model.lazy="items.{{ $index }}.qty"
                                        wire:change="recalcItems" class="form-control form-control-sm"
                                        min="1" style="text-align:center;">
                                </div>
                                <div class="col-2">
                                    <input type="number" wire:model.lazy="items.{{ $index }}.unit_price"
                                        wire:change="recalcItems" class="form-control form-control-sm"
                                        min="0" placeholder="0">
                                </div>
                                <div class="col-2"
                                    style="text-align:right; font-size:13px; font-weight:700; color:var(--navy);">
                                    Rs. {{ number_format((float) ($item['total_price'] ?? 0), 0) }}
                                </div>
                                <div class="col-2 d-flex gap-1">
                                    <input type="text" wire:model="items.{{ $index }}.item_code"
                                        class="form-control form-control-sm" placeholder="Code"
                                        {{ $item['product_id'] ? 'readonly' : '' }}>
                                    <button type="button" wire:click="removeItem({{ $index }})"
                                        style="background:none; border:none; color:#fc8181; font-size:18px; cursor:pointer; padding:0 6px; flex-shrink:0;">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            </div>
                            @if ($item['product_id'])
                                <div style="font-size:10px; color:var(--text-muted); margin-top:4px;">
                                    <span class="tbl-code-badge"
                                        style="font-size:10px;">{{ $item['item_code'] }}</span>
                                    Linked to product
                                </div>
                            @endif
                        </div>
                    @endforeach
                @else
                    <div
                        style="text-align:center; padding:20px; color:var(--text-muted); font-size:13px; border:2px dashed var(--border); border-radius:8px;">
                        Search products or add custom items above
                    </div>
                @endif
            </div>

            {{-- Payment --}}
            <div class="table-card" style="padding:20px;">
                <div
                    style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:14px;">
                    <i class="bi bi-cash me-1"></i> Initial Payment (optional)
                </div>
                <div class="row g-3">
                    <div class="col-4">
                        <label class="form-label">Amount Paid (Rs.)</label>
                        <input type="number" wire:model.lazy="initialPayment" class="form-control" min="0"
                            placeholder="0">
                    </div>
                    <div class="col-4">
                        <label class="form-label">Pay From Account</label>
                        <select wire:model="initialPaymentAccountId" class="form-select">
                            <option value="">Select account...</option>
                            @foreach ($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Summary --}}
        <div class="col-4">
            <div class="po-vendor-card mb-3">
                <div
                    style="font-size:10px; font-weight:700; text-transform:uppercase; color:var(--navy-muted); margin-bottom:8px;">
                    Order Summary
                </div>
                <div style="font-size:12px; margin-bottom:6px;">
                    @if ($vendorId)
                        @php $selectedVendor = $vendors->find($vendorId); @endphp
                        @if ($selectedVendor)
                            <div class="po-vendor-name">{{ $selectedVendor->name }}</div>
                            @if ($selectedVendor->phone)
                                <div class="po-vendor-phone">{{ $selectedVendor->phone }}</div>
                            @endif
                        @endif
                    @else
                        <div style="color:var(--navy-muted);">No vendor selected</div>
                    @endif
                </div>

                <div style="border-top:1px solid rgba(255,255,255,0.1); padding-top:12px; margin-top:8px;">
                    <div class="summary-row">
                        <span class="s-label">Items</span>
                        <span class="s-value">{{ count($items) }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="s-label">Subtotal</span>
                        <span class="s-value">Rs. {{ number_format($this->subtotal, 0) }}</span>
                    </div>
                    @if ((float) $discount > 0)
                        <div class="summary-row">
                            <span class="s-label">Discount</span>
                            <span class="s-value" style="color:#fc8181;">
                                - Rs. {{ number_format((float) $discount, 0) }}
                            </span>
                        </div>
                    @endif
                    <div class="summary-row total-row">
                        <span class="s-label">Total</span>
                        <span class="s-value gold">Rs. {{ number_format($this->total, 0) }}</span>
                    </div>
                    @if ((float) $initialPayment > 0)
                        <div class="summary-row">
                            <span class="s-label">Paid Now</span>
                            <span class="s-value">Rs. {{ number_format((float) $initialPayment, 0) }}</span>
                        </div>
                        <div class="summary-row">
                            <span class="s-label">Balance Due</span>
                            <span class="s-value" style="color:#fc8181;">
                                Rs. {{ number_format($this->balanceDue, 0) }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="d-flex flex-column gap-2">
                <button class="btn btn-primary w-100" style="height:44px; font-size:14px; font-weight:700;"
                    wire:click="save('ordered')" wire:loading.attr="disabled">
                    <span wire:loading wire:target="save">
                        <span class="spinner-border spinner-border-sm me-2"></span>
                    </span>
                    <i class="bi bi-check-circle me-2"></i> Place Order
                </button>
                <button class="btn btn-outline-secondary w-100" wire:click="save('draft')">
                    <i class="bi bi-save me-1"></i> Save as Draft
                </button>
            </div>
        </div>
    </div>
</div>
