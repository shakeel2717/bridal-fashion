<div>
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <div class="page-title">{{ $isEditMode ? 'Edit Purchase Order' : 'New Purchase Order' }}</div>
            <div class="page-subtitle">{{ $isEditMode ? 'Update vendor purchase order' : 'Create vendor purchase order' }}</div>
        </div>
        <a href="{{ $isEditMode ? route('purchase-orders.show', $purchaseOrderId) : route('purchase-orders.index') }}"
            class="btn btn-sm btn-outline-secondary">
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

                    <div class="col-2">
                        <label class="form-label">Order Date <span class="text-danger">*</span></label>
                        <input type="date" wire:model="orderDate"
                            class="form-control @error('orderDate') is-invalid @enderror" autofocus>
                        @error('orderDate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-5">
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

                    <div class="col-5">
                        <label class="form-label">Vendor Bill Number <span class="text-danger">*</span></label>
                        <input type="text" wire:model="vendorBillNumber"
                            class="form-control @error('vendorBillNumber') is-invalid @enderror"
                            placeholder="e.g. VB-2024-001">
                        @error('vendorBillNumber')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>

            {{-- Items --}}
            <div class="table-card mb-3" style="padding:20px; overflow:visible;">
                <div
                    style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:14px;">
                    <i class="bi bi-box me-1"></i> Items
                </div>

                {{-- Product Search --}}
                {{-- Product Search Row --}}
                <div
                    style="background:#f0fff4; border:1.5px solid #9ae6b4; border-radius:8px; padding:10px 12px; margin-bottom:12px;">
                    <div style="display:grid; grid-template-columns: 1fr 80px 120px; gap:8px; align-items:end;">

                        {{-- Design # Search --}}
                        <div style="position:relative;">
                            <label
                                style="font-size:10px; font-weight:700; text-transform:uppercase; color:#276749; margin-bottom:4px; display:block;">
                                Design # / Name
                            </label>
                            <input type="text" id="po_product_search" wire:model.live.debounce.300ms="productSearch"
                                wire:keyup="searchProducts" class="form-control form-control-sm"
                                placeholder="Search code or name..." autocomplete="off">

                            @if (count($searchResults) > 0)
                                <div class="product-search-dropdown" style="min-width:500px;">
                                    @foreach ($searchResults as $result)
                                        <div class="search-item po-search-item"
                                            wire:click="addProduct({{ $result['id'] }})"
                                            data-product-id="{{ $result['id'] }}"
                                            style="{{ !$result['is_direct'] ? 'background:#f7fafc;' : '' }}">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <span class="search-item-code">{{ $result['code'] }}</span>
                                                    @if (!$result['is_direct'] && $result['group'])
                                                        <span
                                                            style="font-size:10px; background:#ebf8ff; color:#2c5282; padding:1px 6px; border-radius:3px; margin-left:4px;">
                                                            Group: {{ $result['group'] }}
                                                        </span>
                                                    @endif
                                                    <div class="search-item-name">{{ $result['name'] }}</div>
                                                    <div class="search-item-category">{{ $result['category'] }}</div>
                                                </div>
                                                <div class="search-item-price">
                                                    Rs. {{ number_format($result['purchase_price'], 0) }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Qty --}}
                        <div>
                            <label
                                style="font-size:10px; font-weight:700; text-transform:uppercase; color:#276749; margin-bottom:4px; display:block;">
                                Qty
                            </label>
                            <input type="number" id="po_new_qty" wire:model="newItemQty"
                                class="form-control form-control-sm" min="1" style="text-align:center;"
                                placeholder="1">
                        </div>

                        {{-- Price --}}
                        <div>
                            <label
                                style="font-size:10px; font-weight:700; text-transform:uppercase; color:#276749; margin-bottom:4px; display:block;">
                                Unit Price (Rs.)
                            </label>
                            <input type="number" id="po_new_price" wire:model="newItemPrice"
                                class="form-control form-control-sm" min="0" style="text-align:right;"
                                placeholder="0">
                        </div>

                    </div>
                </div>

                @error('items')
                    <div class="alert alert-danger py-2 mb-2" style="font-size:12px;">{{ $message }}</div>
                @enderror

                {{-- Excel-style Table --}}
                <table class="table mb-0" style="font-size:12px;">
                    <thead>
                        <tr>
                            <th style="width:40px; text-align:center;">Sr</th>
                            <th style="width:100px;">Design #</th>
                            <th>Item Name</th>
                            <th style="width:80px; text-align:center;">Qty</th>
                            <th style="width:110px; text-align:right;">Unit Price</th>
                            <th style="width:110px; text-align:right;">Total</th>
                            <th style="width:40px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $priceGroups = collect($items)
                                ->groupBy('unit_price')
                                ->filter(fn($g) => $g->count() > 1)
                                ->keys()
                                ->values()
                                ->toArray();

                            $colors = [
                                'rgba(255, 255, 0, 0.15)',
                                'rgba(0, 200, 100, 0.12)',
                                'rgba(0, 150, 255, 0.12)',
                                'rgba(200, 0, 255, 0.10)',
                                'rgba(255, 100, 0, 0.12)',
                                'rgba(0, 200, 200, 0.12)',
                                'rgba(255, 0, 100, 0.10)',
                                'rgba(100, 100, 255, 0.12)',
                            ];

                            $priceColorMap = [];
                            foreach ($priceGroups as $idx => $price) {
                                $priceColorMap[(string) $price] = $colors[$idx % count($colors)];
                            }
                        @endphp
                        @forelse($items as $index => $item)
                            @php
                                $bg = $priceColorMap[(string) $item['unit_price']] ?? null;
                                $tdStyle = $bg ? "background-color:{$bg};" : '';
                            @endphp
                            <tr>
                                <td
                                    style="text-align:center; font-weight:700; color:var(--text-muted); {{ $tdStyle }}">
                                    {{ count($items) - $index }}
                                </td>
                                <td style="{{ $tdStyle }}">
                                    <input type="text" wire:model="items.{{ $index }}.item_code"
                                        class="form-control form-control-sm"
                                        style="font-family:monospace; text-transform:uppercase; background:transparent;"
                                        {{ $item['product_id'] ? 'readonly' : '' }}>
                                </td>
                                <td style="{{ $tdStyle }}">
                                    <input type="text" wire:model="items.{{ $index }}.item_name"
                                        class="form-control form-control-sm" style="background:transparent;"
                                        {{ $item['product_id'] ? 'readonly' : '' }}>
                                </td>
                                <td style="{{ $tdStyle }}">
                                    <input type="number" wire:model.lazy="items.{{ $index }}.qty"
                                        wire:change="recalcItems" class="form-control form-control-sm" min="1"
                                        style="text-align:center; background:transparent;" data-po-field="qty"
                                        data-po-index="{{ $index }}">
                                </td>
                                <td style="{{ $tdStyle }}">
                                    <input type="number" wire:model.lazy="items.{{ $index }}.unit_price"
                                        wire:change="recalcItems" class="form-control form-control-sm" min="0"
                                        style="text-align:right; background:transparent;" placeholder="0"
                                        data-po-field="price" data-po-index="{{ $index }}">
                                </td>
                                <td style="text-align:right; font-weight:700; color:var(--navy); {{ $tdStyle }}">
                                    Rs. {{ number_format((float) ($item['total_price'] ?? 0), 0) }}
                                </td>
                                <td style="text-align:center; {{ $tdStyle }}">
                                    <button type="button" wire:click="removeItem({{ $index }})"
                                        style="background:none; border:none; color:#fc8181; font-size:18px; cursor:pointer; padding:0;">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align:center; padding:20px; color:var(--text-muted);">
                                    Search products or add custom items above
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if (count($items) > 0)
                        <tfoot>
                            <tr>
                                <td colspan="5"
                                    style="text-align:right; font-size:12px; color:var(--text-muted); padding-top:10px;">
                                    Subtotal
                                </td>
                                <td style="text-align:right; font-weight:700; padding-top:10px;">
                                    Rs. {{ number_format($this->subtotal, 0) }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        {{-- Right Summary --}}
        <div class="col-4">
            {{-- Discount + Payment --}}
            <div class="table-card mb-3" style="padding:20px;">
                <div
                    style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:14px;">
                    <i class="bi bi-cash me-1"></i> Discount{{ $isEditMode ? '' : ' & Payment' }}
                </div>
                <div class="row g-3">
                    @if (!$isEditMode)
                        <div class="col-4">
                            <label class="form-label">Pay From Account</label>
                            <select wire:model="initialPaymentAccountId" class="form-select">
                                <option value="">Select account...</option>
                                @foreach ($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-4">
                            <label class="form-label">Amount Paid (Rs.)</label>
                            <input type="number" wire:model.lazy="initialPayment" class="form-control" min="0"
                                placeholder="0">
                        </div>
                        <div class="col-4">
                            <label class="form-label">Payment Date</label>
                            <input type="date" wire:model="initialPaymentDate" class="form-control">
                        </div>
                    @endif
                    <div class="{{ $isEditMode ? 'col-12' : 'col-12' }}">
                        <label class="form-label">Discount (Rs.)</label>
                        <input type="number" wire:model.lazy="discount" wire:change="recalcItems"
                            class="form-control" min="0" placeholder="0">
                    </div>
                    @if ($isEditMode)
                        <div class="col-12">
                            <div class="alert alert-info py-2 mb-0" style="font-size:12px;">
                                <i class="bi bi-info-circle me-1"></i>
                                Payments are managed on the <a href="{{ route('purchase-orders.show', $purchaseOrderId) }}">order detail page</a>.
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="table-card mb-3" style="padding:0; overflow:hidden;">
                {{-- Vendor Header --}}
                <div style="background:var(--navy); padding:12px 16px;">
                    @if ($vendorId)
                        @php $selectedVendor = $vendors->find($vendorId); @endphp
                        @if ($selectedVendor)
                            <div style="font-size:14px; font-weight:700; color:#fff;">{{ $selectedVendor->name }}
                            </div>
                            @if ($selectedVendor->phone)
                                <div style="font-size:12px; color:rgba(255,255,255,0.6);">{{ $selectedVendor->phone }}
                                </div>
                            @endif
                        @endif
                    @else
                        <div style="font-size:13px; color:rgba(255,255,255,0.5);">No vendor selected</div>
                    @endif
                </div>

                {{-- Summary Table --}}
                <table class="table mb-0" style="font-size:13px;">
                    <tbody>
                        <tr>
                            <td style="color:var(--text-muted);">Items</td>
                            <td style="text-align:right; font-weight:700;">{{ count($items) }}</td>
                        </tr>
                        <tr>
                            <td style="color:var(--text-muted);">Subtotal</td>
                            <td style="text-align:right; font-weight:700;">Rs. {{ number_format($this->subtotal, 0) }}
                            </td>
                        </tr>
                        @if ((float) $discount > 0)
                            <tr>
                                <td style="color:var(--text-muted);">Discount</td>
                                <td style="text-align:right; font-weight:700; color:#e53e3e;">
                                    - Rs. {{ number_format((float) $discount, 0) }}
                                </td>
                            </tr>
                        @endif
                        <tr style="background:#f7fafc;">
                            <td style="font-weight:700; color:var(--navy);">Total</td>
                            <td style="text-align:right; font-size:16px; font-weight:800; color:var(--navy);">
                                Rs. {{ number_format($this->total, 0) }}
                            </td>
                        </tr>
                        @if ((float) $initialPayment > 0)
                            <tr>
                                <td style="color:var(--text-muted);">Paid Now</td>
                                <td style="text-align:right; font-weight:700; color:#276749;">
                                    Rs. {{ number_format((float) $initialPayment, 0) }}
                                </td>
                            </tr>
                            <tr>
                                <td style="font-weight:700; color:#e53e3e;">Balance Due</td>
                                <td style="text-align:right; font-weight:800; color:#e53e3e; font-size:15px;">
                                    Rs. {{ number_format($this->balanceDue, 0) }}
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="d-flex flex-column gap-2">
                <button class="btn btn-primary w-100" style="height:44px; font-size:14px; font-weight:700;"
                    wire:click="save('ordered')" wire:loading.attr="disabled">
                    <span wire:loading wire:target="save">
                        <span class="spinner-border spinner-border-sm me-2"></span>
                    </span>
                    @if ($isEditMode)
                        <i class="bi bi-check-circle me-2"></i> Update Order
                    @else
                        <i class="bi bi-check-circle me-2"></i> Place Order
                    @endif
                </button>
                <button class="btn btn-outline-secondary w-100" wire:click="save('draft')">
                    <i class="bi bi-save me-1"></i> {{ $isEditMode ? 'Update as Draft' : 'Save as Draft' }}
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('livewire:updated', setupPOSearch);
            document.addEventListener('livewire:initialized', setupPOSearch);

            // Livewire events
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('focus-po-qty', () => {
                    setTimeout(() => {
                        const qty = document.getElementById('po_new_qty');
                        if (qty) {
                            qty._skipAutoSelect = true;
                            qty.focus();
                            qty.select();
                        }
                    }, 100);
                });

                Livewire.on('focus-po-search', () => {
                    setTimeout(() => {
                        const search = document.getElementById('po_product_search');
                        if (search) {
                            search._skipAutoSelect = true;
                            search.focus();
                            search.select();
                        }
                    }, 100);
                });
            });

            function setupPOSearch() {
                const searchInput = document.getElementById('po_product_search');
                const qtyInput = document.getElementById('po_new_qty');
                const priceInput = document.getElementById('po_new_price');

                if (!searchInput || searchInput._poSearchBound) return;
                searchInput._poSearchBound = true;

                let highlightIndex = -1;

                // ── Search: Enter selects product, moves to qty ───
                searchInput.addEventListener('keydown', function(e) {
                    const dropdown = document.querySelector('.po-search-item')
                        ?.closest('.product-search-dropdown');

                    if (e.key === 'Enter') {
                        e.preventDefault();
                        e.stopImmediatePropagation();

                        const dropItems = dropdown ?
                            Array.from(dropdown.querySelectorAll('.po-search-item')) : [];

                        if (dropItems.length > 0) {
                            const target = dropItems[highlightIndex] ?? dropItems[0];
                            const productId = target.dataset.productId;
                            if (productId) {
                                // Select product (fills name/price, moves to qty via Livewire event)
                                @this.call('selectProductForRow', parseInt(productId));
                            }
                            highlightIndex = -1;
                        }
                        return;
                    }

                    if (!dropdown) return;
                    const dropItems = Array.from(dropdown.querySelectorAll('.po-search-item'));
                    if (dropItems.length === 0) return;

                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        highlightIndex = Math.min(highlightIndex + 1, dropItems.length - 1);
                        dropItems.forEach((el, i) => {
                            el.style.background = i === highlightIndex ? '#ebf8ff' : '';
                        });
                    }

                    if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        highlightIndex = Math.max(highlightIndex - 1, 0);
                        dropItems.forEach((el, i) => {
                            el.style.background = i === highlightIndex ? '#ebf8ff' : '';
                        });
                    }

                    if (e.key === 'Escape') {
                        highlightIndex = -1;
                        @this.set('productSearch', '');
                        @this.set('searchResults', []);
                    }
                });

                // ── Qty: Enter → Price ────────────────────────────
                if (qtyInput && !qtyInput._poQtyBound) {
                    qtyInput._poQtyBound = true;
                    qtyInput.addEventListener('keydown', function(e) {
                        if (e.key !== 'Enter') return;
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        if (priceInput) {
                            priceInput.focus();
                            priceInput.select();
                        }
                    });
                }

                // ── Price: Enter → Add item → back to Search ──────
                if (priceInput && !priceInput._poPriceBound) {
                    priceInput._poPriceBound = true;
                    priceInput.addEventListener('keydown', function(e) {
                        if (e.key !== 'Enter') return;
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        // Add item — focus-po-search dispatched by Livewire after add
                        @this.call('addItemToTable');
                    });
                }
            }
        </script>
    @endpush
</div>
