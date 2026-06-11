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

            {{-- Customer --}}
            <div class="table-card mb-3" style="padding:20px;">
                <div
                    style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:14px;">
                    <i class="bi bi-person me-1"></i> Customer
                </div>

                <div class="mb-3">
                    <div class="walkin-toggle" style="max-width:300px;">
                        <button type="button" class="toggle-btn {{ $customerType === 'existing' ? 'active' : '' }}"
                            wire:click="setCustomerType('existing')">
                            <i class="bi bi-person-check me-1"></i> Registered
                        </button>
                        <button type="button" class="toggle-btn {{ $customerType === 'walkin' ? 'active' : '' }}"
                            wire:click="setCustomerType('walkin')">
                            <i class="bi bi-person me-1"></i> Walk-in
                        </button>
                    </div>
                </div>

                @if ($customerType === 'existing')
                    <div class="mb-3" style="position:relative;">
                        <input type="text" wire:model.live.debounce.400ms="customerSearch"
                            wire:keyup="searchCustomers" class="form-control"
                            placeholder="Search by name, phone or CNIC...">

                        @if ($foundCustomers !== null)
                            <div class="product-search-dropdown">
                                @forelse($foundCustomers as $c)
                                    <div class="search-item" wire:click="selectCustomer({{ $c['id'] }})">
                                        <div class="search-item-code">{{ $c['cnic'] ?? 'No CNIC' }}</div>
                                        <div class="search-item-name">{{ $c['name'] }}</div>
                                        <div class="search-item-category">{{ $c['phone1'] }}</div>
                                    </div>
                                @empty
                                    <div
                                        style="padding:14px; font-size:12px; color:var(--text-muted); text-align:center;">
                                        No customers found
                                    </div>
                                @endforelse
                            </div>
                        @endif
                    </div>
                @endif

                @if ($customerId || $customerType === 'walkin')
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" wire:model="customerName"
                                class="form-control @error('customerName') is-invalid @enderror"
                                placeholder="Customer name"
                                {{ $customerType === 'existing' && $customerId ? 'readonly' : '' }}>
                            @error('customerName')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label">Phone <span class="text-danger">*</span></label>
                            <div class="d-flex gap-2 align-items-center">
                                <input type="text" wire:model="customerPhone1"
                                    class="form-control @error('customerPhone1') is-invalid @enderror"
                                    placeholder="03XX-XXXXXXX"
                                    {{ $customerType === 'existing' && $customerId ? 'readonly' : '' }}>
                                <div class="gender-toggle">
                                    <button type="button"
                                        class="gt-btn male {{ $phone1Gender === 'male' ? 'active' : '' }}"
                                        wire:click="setGender('phone1Gender', 'male')">
                                        ♂ M
                                    </button>
                                    <button type="button"
                                        class="gt-btn female {{ $phone1Gender === 'female' ? 'active' : '' }}"
                                        wire:click="setGender('phone1Gender', 'female')">
                                        ♀ F
                                    </button>
                                </div>
                            </div>
                            @error('customerPhone1')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-6">
                            <label class="form-label">Phone 2</label>
                            <div class="d-flex gap-2 align-items-center">
                                <input type="text" wire:model="customerPhone2" class="form-control"
                                    placeholder="03XX-XXXXXXX">
                                <div class="gender-toggle">
                                    <button type="button"
                                        class="gt-btn male {{ $phone2Gender === 'male' ? 'active' : '' }}"
                                        wire:click="setGender('phone2Gender', 'male')">
                                        ♂ M
                                    </button>
                                    <button type="button"
                                        class="gt-btn female {{ $phone2Gender === 'female' ? 'active' : '' }}"
                                        wire:click="setGender('phone2Gender', 'female')">
                                        ♀ F
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label">CNIC</label>
                            <input type="text" wire:model="customerCnic" class="form-control"
                                placeholder="00000-0000000-0">
                        </div>
                    </div>
                @endif
            </div>

            {{-- Items --}}
            <div class="table-card mb-3" style="padding:20px;">
                <div
                    style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:14px;">
                    <i class="bi bi-cart me-1"></i> Sale Items
                </div>

                <div style="position:relative; margin-bottom:16px;">
                    <input type="text" wire:model.live.debounce.300ms="productSearch" wire:keyup="searchProducts"
                        class="form-control" placeholder="Search product code or name...">
                    <i class="bi bi-search"
                        style="position:absolute; right:12px; top:10px; color:var(--text-muted);"></i>

                    @if (count($searchResults) > 0)
                        <div class="product-search-dropdown">
                            @foreach ($searchResults as $result)
                                <div class="search-item" wire:click="addItem({{ $result['id'] }})">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <span class="search-item-code">{{ $result['code'] }}</span>
                                            <div class="search-item-name">{{ $result['name'] }}</div>
                                            <div class="search-item-category">
                                                {{ $result['category'] }}
                                                @if ($result['size'])
                                                    · Size: {{ $result['size'] }}
                                                @endif
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

                @error('items')
                    <div class="alert alert-danger py-2 mb-3" style="font-size:12px;">{{ $message }}</div>
                @enderror

                @forelse($items as $index => $item)
                    <div class="rental-item-row mb-2">
                        <button class="item-remove-btn" wire:click="removeItem({{ $index }})">
                            <i class="bi bi-x"></i>
                        </button>

                        <div class="d-flex align-items-start gap-3 mb-2">
                            <span class="item-code">{{ $item['code'] }}</span>
                            <div>
                                <div class="item-name">{{ $item['name'] }}</div>
                                <div style="font-size:11px; color:var(--text-muted);">
                                    {{ $item['category'] }}
                                    @if ($item['size'])
                                        · Size: {{ $item['size'] }}
                                    @endif
                                    · Stock: {{ $item['max_qty'] }}
                                </div>
                            </div>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-3">
                                <label
                                    style="font-size:10px; font-weight:600; color:var(--text-muted); text-transform:uppercase;">
                                    Sale Price (Rs.)
                                </label>
                                <input type="number" wire:model.lazy="items.{{ $index }}.sale_price"
                                    wire:change="recalcTotal" class="form-control form-control-sm" min="0">
                            </div>
                            <div class="col-3">
                                <label
                                    style="font-size:10px; font-weight:600; color:var(--text-muted); text-transform:uppercase;">
                                    Qty (max {{ $item['max_qty'] }})
                                </label>
                                <input type="number" wire:model.lazy="items.{{ $index }}.qty"
                                    wire:change="recalcTotal" class="form-control form-control-sm" min="1"
                                    max="{{ $item['max_qty'] }}">
                            </div>
                            <div class="col-3" style="padding-top:18px;">
                                <div style="font-size:12px; font-weight:700; color:var(--navy);">
                                    = Rs.
                                    {{ number_format((float) ($item['sale_price'] ?? 0) * (int) ($item['qty'] ?? 1), 0) }}
                                </div>
                            </div>
                        </div>

                        {{-- Addons --}}
                        @if (count($item['addons']) > 0)
                            <div class="mb-2">
                                <div
                                    style="font-size:10px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:6px;">
                                    Custom Add-ons
                                </div>
                                @foreach ($item['addons'] as $addonIndex => $addon)
                                    <div class="d-flex gap-2 align-items-center mb-2">
                                        <input type="text"
                                            wire:model="items.{{ $index }}.addons.{{ $addonIndex }}.label"
                                            class="form-control form-control-sm" placeholder="e.g. Gift wrapping"
                                            style="flex:1;">
                                        <input type="number"
                                            wire:model.lazy="items.{{ $index }}.addons.{{ $addonIndex }}.price"
                                            wire:change="recalcTotal" class="form-control form-control-sm"
                                            placeholder="Price" style="width:100px;" min="0">
                                        <button type="button"
                                            wire:click="removeAddon({{ $index }}, {{ $addonIndex }})"
                                            style="background:none; border:none; color:#fc8181; font-size:18px; cursor:pointer; padding:0 4px;">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <button type="button" wire:click="addAddon({{ $index }})"
                            style="background:none; border:1.5px dashed var(--border); border-radius:6px; padding:4px 12px; font-size:11px; font-weight:600; color:var(--text-muted); cursor:pointer; width:100%;">
                            <i class="bi bi-plus me-1"></i> Add Custom Option
                        </button>
                    </div>
                @empty
                    <div
                        style="text-align:center; padding:30px; color:var(--text-muted); font-size:13px; border:2px dashed var(--border); border-radius:8px;">
                        <i class="bi bi-cart" style="font-size:32px; display:block; margin-bottom:8px;"></i>
                        Search and add products above
                    </div>
                @endforelse
            </div>

            {{-- Sale Details --}}
            <div class="table-card" style="padding:20px;">
                <div
                    style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:14px;">
                    <i class="bi bi-info-circle me-1"></i> Sale Details
                </div>
                <div class="row g-3">
                    <div class="col-4">
                        <label class="form-label">Sale Date <span class="text-danger">*</span></label>
                        <input type="date" wire:model="saleDate"
                            class="form-control @error('saleDate') is-invalid @enderror">
                        @error('saleDate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-4">
                        <label class="form-label">Bill Ref</label>
                        <input type="text" wire:model="billRef" class="form-control" placeholder="e.g. S-1001">
                    </div>
                    <div class="col-4">
                        <label class="form-label">Handled By</label>
                        <select wire:model="employeeId" class="form-select">
                            <option value="">Select employee...</option>
                            @foreach ($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea wire:model="notes" class="form-control" rows="2" placeholder="Any notes..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT: Summary --}}
        <div class="col-4">
            <div class="rental-summary-box mb-3">
                <div
                    style="font-size:11px; font-weight:700; text-transform:uppercase; color:rgba(255,255,255,0.5); margin-bottom:12px;">
                    Sale Summary
                </div>
                <div class="summary-row">
                    <span class="s-label">Customer</span>
                    <span class="s-value">{{ $customerName ?: '—' }}</span>
                </div>
                <div class="summary-row">
                    <span class="s-label">Items</span>
                    <span class="s-value">{{ count($items) }}</span>
                </div>
                @foreach ($items as $item)
                    <div class="summary-row" style="font-size:11px;">
                        <span class="s-label">{{ $item['code'] }} × {{ $item['qty'] ?? 1 }}</span>
                        <span class="s-value">
                            Rs. {{ number_format((float) ($item['sale_price'] ?? 0) * (int) ($item['qty'] ?? 1), 0) }}
                        </span>
                    </div>
                @endforeach
                <div class="summary-row total-row">
                    <span class="s-label">Total</span>
                    <span class="s-value gold">Rs. {{ number_format((float) $totalAmount, 0) }}</span>
                </div>
            </div>

            <div class="table-card mb-3" style="padding:16px;">
                <div
                    style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:12px;">
                    Payment
                </div>
                <div class="mb-3">
                    <label class="form-label">Total Amount (Rs.)</label>
                    <input type="number" wire:model.lazy="totalAmount" wire:change="recalcTotal"
                        class="form-control" style="font-weight:700; color:var(--navy);">
                </div>
                <div class="mb-3">
                    <label class="form-label">Amount Received (Rs.)</label>
                    <input type="number" wire:model.lazy="advancePaid"
                        class="form-control @error('advancePaid') is-invalid @enderror" placeholder="0"
                        min="0">
                    @error('advancePaid')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Payment Date</label>
                    <input type="date" wire:model="paymentDate" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Receive Into Account</label>
                    <select wire:model="advanceAccountId" class="form-select">
                        <option value="">Select account...</option>
                        @foreach ($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="background:#f7fafc; border-radius:8px; padding:10px 12px; border:1px solid var(--border);">
                    <div style="font-size:11px; color:var(--text-muted);">Remaining Balance</div>
                    <div style="font-size:18px; font-weight:800; color:#e53e3e;">
                        Rs. {{ number_format(max(0, (float) $totalAmount - (float) $advancePaid), 0) }}
                    </div>
                </div>
            </div>

            <button class="btn btn-primary w-100" style="height:46px; font-size:14px; font-weight:700;"
                wire:click="save" wire:loading.attr="disabled">
                <span wire:loading wire:target="save">
                    <span class="spinner-border spinner-border-sm me-2"></span>
                </span>
                <i class="bi bi-check-circle me-2"></i> Complete Sale
            </button>
        </div>
    </div>
</div>
