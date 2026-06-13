<div>
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <div class="page-title">New Sale</div>
            <div class="page-subtitle">Record a product sale</div>
        </div>
        <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="row g-3">
        <div class="col-8">

            {{-- Sale Info & Customer --}}
            <div class="table-card mb-3" style="padding:20px;">
                <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:14px;">
                    <i class="bi bi-receipt me-1"></i> Sale Info
                </div>
                <div class="row g-3">

                    <div class="col-2">
                        <label class="form-label">Sale Date <span class="text-danger">*</span></label>
                        <input type="date" wire:model="saleDate"
                            class="form-control @error('saleDate') is-invalid @enderror" autofocus>
                        @error('saleDate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-5" style="position:relative;">
                        <label class="form-label">Customer</label>
                        <div class="d-flex gap-2">
                            <div style="flex:1; position:relative;">
                                <input type="text" id="sale_customer_search"
                                    wire:model.live.debounce.400ms="customerSearch"
                                    wire:keyup="searchCustomers"
                                    class="form-control @error('customerId') is-invalid @enderror"
                                    placeholder="Search name, phone, CNIC..."
                                    autocomplete="off">

                                @if ($foundCustomers !== null)
                                    <div class="product-search-dropdown" style="min-width:340px;">
                                        @forelse($foundCustomers as $c)
                                            <div class="search-item" wire:click="selectCustomer({{ $c['id'] }})">
                                                <div class="search-item-code">{{ $c['phone1'] }}</div>
                                                <div class="search-item-name">{{ $c['name'] }}</div>
                                                @if ($c['cnic'])
                                                    <div class="search-item-category">CNIC: {{ $c['cnic'] }}</div>
                                                @endif
                                            </div>
                                        @empty
                                            <div style="padding:14px; font-size:12px; color:var(--text-muted); text-align:center;">
                                                No customers found
                                            </div>
                                        @endforelse
                                    </div>
                                @endif
                            </div>
                            @if ($customerId && !app(App\Models\Customer::class)->where('id', $customerId)->where('is_walkin', true)->exists())
                                <button type="button" wire:click="clearCustomer"
                                    class="btn btn-outline-secondary" style="padding:0 12px;" title="Reset to Walk-in">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            @endif
                        </div>
                        @error('customerId') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-3">
                        <label class="form-label">Bill Ref</label>
                        <input type="text" wire:model="billRef" class="form-control" placeholder="e.g. S-1001">
                    </div>

                    <div class="col-2">
                        <label class="form-label">Handled By</label>
                        <select wire:model="employeeId" class="form-select">
                            <option value="">— none —</option>
                            @foreach ($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>
            </div>

            {{-- Items --}}
            <div class="table-card mb-3" style="padding:20px; overflow:visible;">
                <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:14px;">
                    <i class="bi bi-cart me-1"></i> Items
                </div>

                {{-- Product Search Row --}}
                <div style="background:#f0fff4; border:1.5px solid #9ae6b4; border-radius:8px; padding:10px 12px; margin-bottom:12px;">
                    <div style="display:grid; grid-template-columns: 1fr 80px 120px; gap:8px; align-items:end;">

                        {{-- Design # Search --}}
                        <div style="position:relative;">
                            <label style="font-size:10px; font-weight:700; text-transform:uppercase; color:#276749; margin-bottom:4px; display:block;">
                                Design # / Name
                            </label>
                            <input type="text" id="sale_product_search"
                                wire:model.live.debounce.300ms="productSearch"
                                wire:keyup="searchProducts"
                                class="form-control form-control-sm"
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
                                                        <span style="font-size:10px; background:#ebf8ff; color:#2c5282; padding:1px 6px; border-radius:3px; margin-left:4px;">
                                                            Group: {{ $result['group'] }}
                                                        </span>
                                                    @endif
                                                    <div class="search-item-name">{{ $result['name'] }}</div>
                                                    <div class="search-item-category">
                                                        {{ $result['category'] }}
                                                        · Stock: {{ $result['stock_qty'] }}
                                                    </div>
                                                </div>
                                                <div class="search-item-price">
                                                    Rs. {{ number_format($result['sale_price'], 0) }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Qty --}}
                        <div>
                            <label style="font-size:10px; font-weight:700; text-transform:uppercase; color:#276749; margin-bottom:4px; display:block;">
                                Qty
                            </label>
                            <input type="number" id="sale_new_qty" wire:model="newItemQty"
                                class="form-control form-control-sm" min="1"
                                style="text-align:center;" placeholder="1">
                        </div>

                        {{-- Price --}}
                        <div>
                            <label style="font-size:10px; font-weight:700; text-transform:uppercase; color:#276749; margin-bottom:4px; display:block;">
                                Sale Price (Rs.)
                            </label>
                            <input type="number" id="sale_new_price" wire:model="newItemPrice"
                                class="form-control form-control-sm" min="0"
                                style="text-align:right;" placeholder="0">
                        </div>
                    </div>
                </div>

                @error('items')
                    <div class="alert alert-danger py-2 mb-2" style="font-size:12px;">{{ $message }}</div>
                @enderror

                {{-- Items Table --}}
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
                                ->keys()->values()->toArray();

                            $colors = [
                                'rgba(255,255,0,0.15)',
                                'rgba(0,200,100,0.12)',
                                'rgba(0,150,255,0.12)',
                                'rgba(200,0,255,0.10)',
                                'rgba(255,100,0,0.12)',
                                'rgba(0,200,200,0.12)',
                                'rgba(255,0,100,0.10)',
                                'rgba(100,100,255,0.12)',
                            ];

                            $priceColorMap = [];
                            foreach ($priceGroups as $idx => $price) {
                                $priceColorMap[(string) $price] = $colors[$idx % count($colors)];
                            }
                        @endphp

                        @forelse($items as $index => $item)
                            @php
                                $bg      = $priceColorMap[(string) $item['unit_price']] ?? null;
                                $tdStyle = $bg ? "background-color:{$bg};" : '';
                            @endphp
                            <tr>
                                <td style="text-align:center; font-weight:700; color:var(--text-muted); {{ $tdStyle }}">
                                    {{ count($items) - $index }}
                                </td>
                                <td style="{{ $tdStyle }}">
                                    <input type="text" wire:model="items.{{ $index }}.item_code"
                                        class="form-control form-control-sm"
                                        style="font-family:monospace; text-transform:uppercase; background:transparent;"
                                        readonly>
                                </td>
                                <td style="{{ $tdStyle }}">
                                    <input type="text" wire:model="items.{{ $index }}.item_name"
                                        class="form-control form-control-sm"
                                        style="background:transparent;" readonly>
                                </td>
                                <td style="{{ $tdStyle }}">
                                    <input type="number" wire:model.lazy="items.{{ $index }}.qty"
                                        wire:change="recalcItems"
                                        class="form-control form-control-sm" min="1"
                                        style="text-align:center; background:transparent;">
                                </td>
                                <td style="{{ $tdStyle }}">
                                    <input type="number" wire:model.lazy="items.{{ $index }}.unit_price"
                                        wire:change="recalcItems"
                                        class="form-control form-control-sm" min="0"
                                        style="text-align:right; background:transparent;" placeholder="0">
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
                                    Search products above to add items
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if (count($items) > 0)
                        <tfoot>
                            <tr>
                                <td colspan="5" style="text-align:right; font-size:12px; color:var(--text-muted); padding-top:10px;">
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

            {{-- Customer Summary --}}
            <div class="table-card mb-3" style="padding:0; overflow:hidden;">
                <div style="background:var(--navy); padding:12px 16px;">
                    @if ($customerId)
                        @php $selectedCustomer = \App\Models\Customer::find($customerId); @endphp
                        @if ($selectedCustomer)
                            <div style="font-size:14px; font-weight:700; color:#fff;">
                                {{ $selectedCustomer->name }}
                                @if ($selectedCustomer->is_walkin)
                                    <span style="font-size:10px; background:rgba(255,255,255,0.15); color:rgba(255,255,255,0.8); padding:1px 7px; border-radius:10px; margin-left:6px;">Walk-in</span>
                                @endif
                            </div>
                            @if ($selectedCustomer->phone1 && !$selectedCustomer->is_walkin)
                                <div style="font-size:12px; color:rgba(255,255,255,0.6);">{{ $selectedCustomer->phone1 }}</div>
                            @endif
                        @endif
                    @else
                        <div style="font-size:13px; color:rgba(255,255,255,0.5);">No customer selected</div>
                    @endif
                </div>

                <table class="table mb-0" style="font-size:13px;">
                    <tbody>
                        <tr>
                            <td style="color:var(--text-muted);">Items</td>
                            <td style="text-align:right; font-weight:700;">{{ count($items) }}</td>
                        </tr>
                        <tr>
                            <td style="color:var(--text-muted);">Subtotal</td>
                            <td style="text-align:right; font-weight:700;">Rs. {{ number_format($this->subtotal, 0) }}</td>
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
                        @if ((float) $advancePaid > 0)
                            <tr>
                                <td style="color:var(--text-muted);">Received</td>
                                <td style="text-align:right; font-weight:700; color:#276749;">
                                    Rs. {{ number_format((float) $advancePaid, 0) }}
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

            {{-- Discount & Payment --}}
            <div class="table-card mb-3" style="padding:20px;">
                <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:14px;">
                    <i class="bi bi-cash me-1"></i> Discount & Payment
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label">Receive Into Account</label>
                        <select wire:model="advanceAccountId" class="form-select">
                            <option value="">Select account...</option>
                            @foreach ($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Amount Received (Rs.)</label>
                        <input type="number" wire:model.lazy="advancePaid"
                            class="form-control @error('advancePaid') is-invalid @enderror"
                            min="0" placeholder="0">
                        @error('advancePaid') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-6">
                        <label class="form-label">Payment Date</label>
                        <input type="date" wire:model="paymentDate" class="form-control">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Discount (Rs.)</label>
                        <input type="number" wire:model.lazy="discount" wire:change="recalcItems"
                            class="form-control" min="0" placeholder="0">
                    </div>
                </div>
            </div>

            <div class="d-flex flex-column gap-2">
                <button class="btn btn-primary w-100" style="height:44px; font-size:14px; font-weight:700;"
                    wire:click="save" wire:loading.attr="disabled">
                    <span wire:loading wire:target="save">
                        <span class="spinner-border spinner-border-sm me-2"></span>
                    </span>
                    <i class="bi bi-check-circle me-2"></i> Complete Sale
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('livewire:updated', setupSaleSearch);
            document.addEventListener('livewire:initialized', setupSaleSearch);

            document.addEventListener('livewire:initialized', () => {
                Livewire.on('focus-sale-qty', () => {
                    setTimeout(() => {
                        const qty = document.getElementById('sale_new_qty');
                        if (qty) { qty.focus(); qty.select(); }
                    }, 100);
                });
                Livewire.on('focus-sale-search', () => {
                    setTimeout(() => {
                        const s = document.getElementById('sale_product_search');
                        if (s) { s.focus(); s.select(); }
                    }, 100);
                });
            });

            function setupSaleSearch() {
                const searchInput = document.getElementById('sale_product_search');
                const qtyInput    = document.getElementById('sale_new_qty');
                const priceInput  = document.getElementById('sale_new_price');

                if (!searchInput || searchInput._saleSearchBound) return;
                searchInput._saleSearchBound = true;

                let highlightIndex = -1;

                searchInput.addEventListener('keydown', function(e) {
                    const dropdown = document.querySelector('.po-search-item')
                        ?.closest('.product-search-dropdown');

                    if (e.key === 'Enter') {
                        e.preventDefault(); e.stopImmediatePropagation();
                        const dropItems = dropdown
                            ? Array.from(dropdown.querySelectorAll('.po-search-item')) : [];
                        if (dropItems.length > 0) {
                            const target    = dropItems[highlightIndex] ?? dropItems[0];
                            const productId = target.dataset.productId;
                            if (productId) @this.call('selectProductForRow', parseInt(productId));
                            highlightIndex = -1;
                        }
                        return;
                    }
                    if (!dropdown) return;
                    const dropItems = Array.from(dropdown.querySelectorAll('.po-search-item'));
                    if (!dropItems.length) return;

                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        highlightIndex = Math.min(highlightIndex + 1, dropItems.length - 1);
                        dropItems.forEach((el, i) => el.style.background = i === highlightIndex ? '#ebf8ff' : '');
                    }
                    if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        highlightIndex = Math.max(highlightIndex - 1, 0);
                        dropItems.forEach((el, i) => el.style.background = i === highlightIndex ? '#ebf8ff' : '');
                    }
                    if (e.key === 'Escape') {
                        highlightIndex = -1;
                        @this.set('productSearch', '');
                        @this.set('searchResults', []);
                    }
                });

                if (qtyInput && !qtyInput._saleQtyBound) {
                    qtyInput._saleQtyBound = true;
                    qtyInput.addEventListener('keydown', function(e) {
                        if (e.key !== 'Enter') return;
                        e.preventDefault(); e.stopImmediatePropagation();
                        if (priceInput) { priceInput.focus(); priceInput.select(); }
                    });
                }

                if (priceInput && !priceInput._salePriceBound) {
                    priceInput._salePriceBound = true;
                    priceInput.addEventListener('keydown', function(e) {
                        if (e.key !== 'Enter') return;
                        e.preventDefault(); e.stopImmediatePropagation();
                        @this.call('addItemToTable');
                    });
                }
            }
        </script>
    @endpush
</div>
