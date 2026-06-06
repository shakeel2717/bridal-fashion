<div>
    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <div class="page-title">New Rental Booking</div>
            <div class="page-subtitle">Fill in details step by step</div>
        </div>
        <a href="{{ route('rentals.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Rentals
        </a>
    </div>

    {{-- Step Indicator --}}
    <div class="step-indicator">
        <div class="step {{ $step >= 1 ? ($step > 1 ? 'done' : 'active') : '' }}" wire:click="goToStep(1)"
            style="cursor:pointer;">
            <div class="step-num">{{ $step > 1 ? '✓' : '1' }}</div>
            Customer
        </div>
        <div class="step-line {{ $step > 1 ? 'done' : '' }}"></div>
        <div class="step {{ $step >= 2 ? ($step > 2 ? 'done' : 'active') : '' }}" wire:click="goToStep(2)"
            style="cursor:pointer;">
            <div class="step-num">{{ $step > 2 ? '✓' : '2' }}</div>
            Items
        </div>
        <div class="step-line {{ $step > 2 ? 'done' : '' }}"></div>
        <div class="step {{ $step >= 3 ? 'active' : '' }}">
            <div class="step-num">3</div>
            Dates & Payment
        </div>
    </div>

    {{-- ── STEP 1: Customer ── --}}
    @if ($step === 1)
        <div class="row g-3">
            <div class="col-8">
                <div class="table-card" style="padding:20px; height:450px;">
                    <div class="mb-4">
                        <label class="form-label">Customer Type</label>
                        <div class="walkin-toggle" style="max-width:300px;">
                            <button type="button" class="toggle-btn {{ $customerType === 'existing' ? 'active' : '' }}"
                                wire:click="setCustomerType('existing')">
                                <i class="bi bi-person-check me-1"></i> Registered Customer
                            </button>
                            <button type="button" class="toggle-btn {{ $customerType === 'walkin' ? 'active' : '' }}"
                                wire:click="setCustomerType('walkin')">
                                <i class="bi bi-person me-1"></i> Walk-in
                            </button>
                        </div>
                    </div>

                    @if ($customerType === 'existing')
                        {{-- Customer Search --}}
                        <div class="mb-3" style="position:relative;">
                            <label class="form-label">Search Customer <span class="text-danger">*</span></label>
                            <input type="text" wire:model.live.debounce.400ms="customerSearch"
                                wire:keyup="searchCustomers" class="form-control"
                                placeholder="Type name, phone or CNIC...">

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
                                <label class="form-label">Phone 1 <span class="text-danger">*</span></label>
                                <input type="text" wire:model="customerPhone1"
                                    class="form-control @error('customerPhone1') is-invalid @enderror"
                                    placeholder="03XX-XXXXXXX"
                                    {{ $customerType === 'existing' && $customerId ? 'readonly' : '' }}>
                                @error('customerPhone1')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6">
                                <label class="form-label">Phone 2</label>
                                <input type="text" wire:model="customerPhone2" class="form-control"
                                    placeholder="03XX-XXXXXXX">
                            </div>
                            <div class="col-6">
                                <label class="form-label">WhatsApp</label>
                                <input type="text" wire:model="customerWhatsapp" class="form-control"
                                    placeholder="03XX-XXXXXXX">
                            </div>
                            @if ($customerType === 'existing')
                                <div class="col-6">
                                    <label class="form-label">CNIC <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="customerCnic"
                                        class="form-control @error('customerCnic') is-invalid @enderror"
                                        placeholder="00000-0000000-0">
                                    @error('customerCnic')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif
                            <div class="col-{{ $customerType === 'existing' ? '6' : '12' }}">
                                <label class="form-label">Delivery Address</label>
                                <input type="text" wire:model="deliveryAddress" class="form-control"
                                    placeholder="Customer address">
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-4">
                <div class="table-card" style="padding:20px;">
                    <div
                        style="font-size:12px; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:12px;">
                        Tips
                    </div>
                    <div style="font-size:12px; color:var(--text-muted); line-height:1.8;">
                        <i class="bi bi-info-circle text-primary me-1"></i>
                        For rental items, CNIC is required for registered customers.<br><br>
                        <i class="bi bi-person me-1"></i>
                        Walk-in customers don't need CNIC.<br><br>
                        <i class="bi bi-search me-1"></i>
                        Search by customer name, phone, or CNIC number.
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-3">
            <button class="btn btn-primary" wire:click="nextStep" wire:loading.attr="disabled">
                Next: Add Items <i class="bi bi-arrow-right ms-1"></i>
            </button>
        </div>
    @endif

    {{-- ── STEP 2: Items ── --}}
    @if ($step === 2)
        <div class="row g-3">
            <div class="col-8">
                <div class="table-card" style="padding:20px;">
                    <div style="font-size:13px; font-weight:700; color:var(--text-primary); margin-bottom:14px;">
                        Search & Add Products
                    </div>

                    {{-- Product Search --}}
                    <div style="position:relative; margin-bottom:20px;">
                        <input type="text" wire:model.live.debounce.300ms="productSearch"
                            wire:keyup="searchProducts" class="form-control"
                            placeholder="Type product code or name (e.g. BL-001)...">
                        <i class="bi bi-search"
                            style="position:absolute; right:12px; top:10px; color:var(--text-muted);"></i>

                        @if (count($searchResults) > 0)
                            <div class="product-search-dropdown">
                                @foreach ($searchResults as $result)
                                    <div class="search-item" wire:click="addItem({{ $result['id'] }})">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <span class="search-item-code">{{ $result['code'] }}</span>
                                                <div class="search-item-name">{{ $result['name'] }}</div>
                                                <div class="search-item-category">
                                                    {{ $result['category'] }}
                                                    @if ($result['size'])
                                                        · Size: {{ $result['size'] }}
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="search-item-price">
                                                Rs. {{ number_format($result['rental_price'], 0) }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if (strlen($productSearch) >= 2 && count($searchResults) === 0)
                            <div class="product-search-dropdown">
                                <div style="padding:14px; font-size:12px; color:var(--text-muted); text-align:center;">
                                    No products found for "{{ $productSearch }}"
                                </div>
                            </div>
                        @endif
                    </div>

                    @error('items')
                        <div class="alert alert-danger py-2 mb-3" style="font-size:12px;">
                            {{ $message }}
                        </div>
                    @enderror

                    {{-- Added Items --}}
                    @if (count($items) > 0)
                        <div
                            style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:10px;">
                            Added Items ({{ count($items) }})
                        </div>

                        @foreach ($items as $index => $item)
                            <div class="rental-item-row">
                                <button class="item-remove-btn" wire:click="removeItem({{ $index }})">
                                    <i class="bi bi-x"></i>
                                </button>

                                {{-- Item Header --}}
                                <div class="d-flex align-items-start gap-3 mb-3">
                                    <span class="item-code">{{ $item['code'] }}</span>
                                    <div>
                                        <div class="item-name">{{ $item['name'] }}</div>
                                        <div style="font-size:11px; color:var(--text-muted);">
                                            {{ $item['category'] }}
                                            @if ($item['size'])
                                                · Size: {{ $item['size'] }}
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Rental Price + Note --}}
                                <div class="row g-2 mb-2">
                                    <div class="col-4">
                                        <label
                                            style="font-size:10px; font-weight:600; color:var(--text-muted); text-transform:uppercase;">
                                            Rental Price (Rs.)
                                        </label>
                                        <input type="number"
                                            wire:model.lazy="items.{{ $index }}.rental_price"
                                            wire:change="recalcTotal" class="form-control form-control-sm"
                                            min="0">
                                    </div>
                                    <div class="col-8">
                                        <label
                                            style="font-size:10px; font-weight:600; color:var(--text-muted); text-transform:uppercase;">
                                            Note (optional)
                                        </label>
                                        <input type="text" wire:model="items.{{ $index }}.note"
                                            class="form-control form-control-sm"
                                            placeholder="e.g. waist needs adjustment">
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
                                                    class="form-control form-control-sm"
                                                    placeholder="e.g. Adil ki dulhan written on dupatta"
                                                    style="flex:1;">
                                                <input type="number"
                                                    wire:model.lazy="items.{{ $index }}.addons.{{ $addonIndex }}.price"
                                                    wire:change="recalcTotal" class="form-control form-control-sm"
                                                    placeholder="Price" style="width:100px;" min="0">
                                                <button type="button"
                                                    wire:click="removeAddon({{ $index }}, {{ $addonIndex }})"
                                                    style="background:none; border:none; color:#fc8181; font-size:18px; cursor:pointer; padding:0 4px; line-height:1;">
                                                    <i class="bi bi-x"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Add Addon Button --}}
                                <button type="button" wire:click="addAddon({{ $index }})"
                                    style="background:none; border:1.5px dashed var(--border); border-radius:6px; padding:4px 12px; font-size:11px; font-weight:600; color:var(--text-muted); cursor:pointer; transition:all 0.15s; width:100%;"
                                    onmouseover="this.style.borderColor='var(--gold)'; this.style.color='var(--gold-hover)'"
                                    onmouseout="this.style.borderColor='var(--border)'; this.style.color='var(--text-muted)'">
                                    <i class="bi bi-plus me-1"></i> Add Custom Option
                                </button>
                            </div>
                        @endforeach
                    @else
                        <div
                            style="text-align:center; padding:30px; color:var(--text-muted); font-size:13px; border:2px dashed var(--border); border-radius:8px;">
                            <i class="bi bi-box-seam" style="font-size:32px; display:block; margin-bottom:8px;"></i>
                            Search and add products above
                        </div>
                    @endif
                </div>
            </div>

            {{-- Summary --}}
            <div class="col-4">
                <div class="rental-summary-box">
                    <div
                        style="font-size:11px; font-weight:700; text-transform:uppercase; color:rgba(255,255,255,0.5); margin-bottom:12px;">
                        Booking Summary
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
                            <span class="s-label">{{ $item['code'] }}</span>
                            <span class="s-value">
                                Rs. {{ number_format((float) $item['rental_price'], 0) }}
                            </span>
                        </div>
                    @endforeach
                    <div class="summary-row total-row">
                        <span class="s-label">Total</span>
                        <span class="s-value gold">Rs. {{ number_format((float) $totalAmount, 0) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between mt-3">
            <button class="btn btn-outline-secondary" wire:click="prevStep">
                <i class="bi bi-arrow-left me-1"></i> Back
            </button>
            <button class="btn btn-primary" wire:click="nextStep">
                Next: Dates & Payment <i class="bi bi-arrow-right ms-1"></i>
            </button>
        </div>
    @endif

    {{-- ── STEP 3: Dates & Payment ── --}}
    @if ($step === 3)
        <div class="row g-3">
            <div class="col-8">
                <div class="table-card" style="padding:20px;">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Bill Book Ref #</label>
                            <input type="text" wire:model="billRef" class="form-control"
                                placeholder="e.g. B-1042">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Handled By (Employee)</label>
                            <select wire:model="employeeId" class="form-select">
                                <option value="">Select employee...</option>
                                @foreach ($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-4">
                            <label class="form-label">Booking Date <span class="text-danger">*</span></label>
                            <input type="date" wire:model="bookingDate"
                                class="form-control @error('bookingDate') is-invalid @enderror">
                            @error('bookingDate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-4">
                            <label class="form-label">Pickup Date</label>
                            <input type="date" wire:model="pickupDate" class="form-control">
                        </div>
                        <div class="col-4">
                            <label class="form-label">Return Date</label>
                            <input type="date" wire:model="returnDate" class="form-control">
                        </div>

                        <div class="col-4">
                            <label class="form-label">Stitching Date</label>
                            <input type="date" wire:model="stitchingDate" class="form-control">
                        </div>
                        <div class="col-8">
                            <label class="form-label">Stitching Instructions</label>
                            <input type="text" wire:model="stitchingInstructions" class="form-control"
                                placeholder="e.g. Waist 28, shorten sleeves by 2 inches">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea wire:model="notes" class="form-control" rows="2" placeholder="Any additional notes..."></textarea>
                        </div>

                        <div class="col-12">
                            <div style="border-top:1px solid var(--border); padding-top:16px; margin-top:4px;">
                                <div
                                    style="font-size:12px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:12px;">
                                    Payment
                                </div>
                                <div class="row g-3">
                                    <div class="col-4">
                                        <label class="form-label">Total Amount (Rs.)</label>
                                        <input type="number" wire:model.lazy="totalAmount" wire:change="recalcTotal"
                                            class="form-control" style="font-weight:700; color:var(--navy);">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label">Advance Paid (Rs.)</label>
                                        <input type="number" wire:model.lazy="advancePaid"
                                            class="form-control @error('advancePaid') is-invalid @enderror"
                                            min="0">
                                        @error('advancePaid')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label">Remaining Balance</label>
                                        <div
                                            style="height:38px; background:#f7fafc; border:1px solid var(--border); border-radius:6px; display:flex; align-items:center; padding:0 12px; font-weight:700; color:#e53e3e; font-size:14px;">
                                            Rs.
                                            {{ number_format(max(0, (float) $totalAmount - (float) $advancePaid), 0) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Final Summary --}}
            <div class="col-4">
                <div class="rental-summary-box">
                    <div
                        style="font-size:11px; font-weight:700; text-transform:uppercase; color:rgba(255,255,255,0.5); margin-bottom:12px;">
                        Final Summary
                    </div>
                    <div class="summary-row">
                        <span class="s-label">Customer</span>
                        <span class="s-value">{{ $customerName }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="s-label">Phone</span>
                        <span class="s-value">{{ $customerPhone1 }}</span>
                    </div>
                    @if ($customerCnic)
                        <div class="summary-row">
                            <span class="s-label">CNIC</span>
                            <span class="s-value"
                                style="font-size:11px; font-family:monospace;">{{ $customerCnic }}</span>
                        </div>
                    @endif
                    <div class="summary-row">
                        <span class="s-label">Items</span>
                        <span class="s-value">{{ count($items) }} item(s)</span>
                    </div>
                    @if ($pickupDate)
                        <div class="summary-row">
                            <span class="s-label">Pickup</span>
                            <span class="s-value">{{ \Carbon\Carbon::parse($pickupDate)->format('d/m/Y') }}</span>
                        </div>
                    @endif
                    @if ($returnDate)
                        <div class="summary-row">
                            <span class="s-label">Return</span>
                            <span class="s-value">{{ \Carbon\Carbon::parse($returnDate)->format('d/m/Y') }}</span>
                        </div>
                    @endif
                    <div class="summary-row">
                        <span class="s-label">Total</span>
                        <span class="s-value">Rs. {{ number_format((float) $totalAmount, 0) }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="s-label">Advance Paid</span>
                        <span class="s-value">Rs. {{ number_format((float) $advancePaid, 0) }}</span>
                    </div>
                    <div class="summary-row total-row">
                        <span class="s-label">Remaining</span>
                        <span class="s-value gold">
                            Rs. {{ number_format(max(0, (float) $totalAmount - (float) $advancePaid), 0) }}
                        </span>
                    </div>
                </div>

                <button class="btn btn-primary w-100 mt-3" style="height:46px; font-size:14px; font-weight:700;"
                    wire:click="save" wire:loading.attr="disabled">
                    <span wire:loading wire:target="save">
                        <span class="spinner-border spinner-border-sm me-2"></span>
                    </span>
                    <i class="bi bi-check-circle me-2"></i> Confirm Booking
                </button>
            </div>
        </div>

        <div class="d-flex justify-content-start mt-3">
            <button class="btn btn-outline-secondary" wire:click="prevStep">
                <i class="bi bi-arrow-left me-1"></i> Back
            </button>
        </div>
    @endif
</div>
