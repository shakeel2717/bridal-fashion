<div>
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <div class="page-title">New Rental Booking</div>
            <div class="page-subtitle">Fill in details step by step</div>
        </div>
        <a href="{{ route('rentals.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
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
            Dates
        </div>
        <div class="step-line {{ $step > 2 ? 'done' : '' }}"></div>
        <div class="step {{ $step >= 3 ? ($step > 3 ? 'done' : 'active') : '' }}" wire:click="goToStep(3)"
            style="cursor:pointer;">
            <div class="step-num">{{ $step > 3 ? '✓' : '3' }}</div>
            Items
        </div>
        <div class="step-line {{ $step > 3 ? 'done' : '' }}"></div>
        <div class="step {{ $step >= 4 ? 'active' : '' }}">
            <div class="step-num">4</div>
            Payment
        </div>
    </div>

    {{-- ── STEP 1: Customer ── --}}
    @if ($step === 1)
        <div class="row g-3">
            <div class="col-8">
                <div class="table-card mb-3" style="padding:16px 20px; overflow:visible;">
                    <div class="mb-4">
                        <label class="form-label">Customer Type</label>
                        <div class="walkin-toggle" style="max-width:300px;">
                            <button type="button"
                                class="toggle-btn {{ $customerType === 'existing' ? 'active' : '' }}"
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
                            <label class="form-label">Search Customer</label>
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
                                <div class="d-flex gap-2">
                                    <input type="text" wire:model="customerPhone1"
                                        class="form-control @error('customerPhone1') is-invalid @enderror"
                                        placeholder="03XX-XXXXXXX"
                                        {{ $customerType === 'existing' && $customerId ? 'readonly' : '' }}>
                                    <div class="gender-toggle">
                                        <button type="button"
                                            class="gt-btn male {{ $phone1Gender === 'male' ? 'active' : '' }}"
                                            wire:click="setGender('phone1Gender','male')">♂ M</button>
                                        <button type="button"
                                            class="gt-btn female {{ $phone1Gender === 'female' ? 'active' : '' }}"
                                            wire:click="setGender('phone1Gender','female')">♀ F</button>
                                    </div>
                                </div>
                                @error('customerPhone1')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-6">
                                <label class="form-label">Phone 2</label>
                                <div class="d-flex gap-2">
                                    <input type="text" wire:model="customerPhone2" class="form-control"
                                        placeholder="03XX-XXXXXXX">
                                    <div class="gender-toggle">
                                        <button type="button"
                                            class="gt-btn male {{ $phone2Gender === 'male' ? 'active' : '' }}"
                                            wire:click="setGender('phone2Gender','male')">♂ M</button>
                                        <button type="button"
                                            class="gt-btn female {{ $phone2Gender === 'female' ? 'active' : '' }}"
                                            wire:click="setGender('phone2Gender','female')">♀ F</button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6">
                                <label class="form-label">WhatsApp</label>
                                <div class="d-flex gap-2">
                                    <input type="text" wire:model="customerWhatsapp" class="form-control"
                                        placeholder="03XX-XXXXXXX">
                                    <div class="gender-toggle">
                                        <button type="button"
                                            class="gt-btn male {{ $whatsappGender === 'male' ? 'active' : '' }}"
                                            wire:click="setGender('whatsappGender','male')">♂ M</button>
                                        <button type="button"
                                            class="gt-btn female {{ $whatsappGender === 'female' ? 'active' : '' }}"
                                            wire:click="setGender('whatsappGender','female')">♀ F</button>
                                    </div>
                                </div>
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

                            @if ($customerType === 'walkin')
                                <div class="col-12">
                                    <div
                                        style="background:#f7fafc; border:1px solid var(--border); border-radius:8px; padding:16px;">
                                        <div
                                            style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:14px;">
                                            <i class="bi bi-camera me-1"></i>
                                            Walk-in Customer Documents
                                            <span style="font-weight:400; font-size:10px; margin-left:4px;">(all
                                                optional)</span>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-4">
                                                <label class="form-label" style="font-size:12px;">Profile
                                                    Photo</label>
                                                <input type="file" wire:model="walkinPhoto"
                                                    class="form-control form-control-sm @error('walkinPhoto') is-invalid @enderror"
                                                    accept="image/*">
                                                @error('walkinPhoto')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                @if ($walkinPhoto)
                                                    <div class="mt-2">
                                                        <img src="{{ $walkinPhoto->temporaryUrl() }}"
                                                            style="width:60px; height:60px; object-fit:cover; border-radius:50%; border:2px solid var(--gold);">
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="col-4">
                                                <label class="form-label" style="font-size:12px;">CNIC Front</label>
                                                <input type="file" wire:model="walkinCnicFront"
                                                    class="form-control form-control-sm @error('walkinCnicFront') is-invalid @enderror"
                                                    accept="image/*">
                                                @error('walkinCnicFront')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                @if ($walkinCnicFront)
                                                    <div class="mt-2">
                                                        <img src="{{ $walkinCnicFront->temporaryUrl() }}"
                                                            style="width:100%; height:55px; object-fit:cover; border-radius:6px; border:2px solid var(--gold);">
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="col-4">
                                                <label class="form-label" style="font-size:12px;">CNIC Back</label>
                                                <input type="file" wire:model="walkinCnicBack"
                                                    class="form-control form-control-sm @error('walkinCnicBack') is-invalid @enderror"
                                                    accept="image/*">
                                                @error('walkinCnicBack')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                @if ($walkinCnicBack)
                                                    <div class="mt-2">
                                                        <img src="{{ $walkinCnicBack->temporaryUrl() }}"
                                                            style="width:100%; height:55px; object-fit:cover; border-radius:6px; border:2px solid var(--gold);">
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-4">
                <div class="table-card" style="padding:20px;">
                    <div
                        style="font-size:12px; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:12px;">
                        Info
                    </div>
                    <div style="font-size:12px; color:var(--text-muted); line-height:1.8;">
                        <i class="bi bi-info-circle text-primary me-1"></i>
                        CNIC required for registered customers.<br><br>
                        <i class="bi bi-person me-1"></i>
                        Walk-in customers — name & phone only.<br><br>
                        <i class="bi bi-calendar me-1"></i>
                        You'll set dates on the next step, then we'll check item availability.
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-3">
            <button class="btn btn-primary" wire:click="nextStep">
                Next: Set Dates <i class="bi bi-arrow-right ms-1"></i>
            </button>
        </div>
    @endif

    {{-- ── STEP 2: Dates & Details ── --}}
    @if ($step === 2)
        <div class="row g-3">
            <div class="col-8">
                <div class="table-card" style="padding:20px;">
                    <div
                        style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:16px;">
                        <i class="bi bi-calendar me-1"></i> Rental Dates
                    </div>
                    <div class="row g-3">
                        <div class="col-4">
                            <label class="form-label">Booking Date <span class="text-danger">*</span></label>
                            <input type="date" wire:model="bookingDate"
                                class="form-control @error('bookingDate') is-invalid @enderror">
                            @error('bookingDate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-4">
                            <label class="form-label">
                                Pickup Date <span class="text-danger">*</span>
                                <span style="font-size:10px; color:var(--gold-hover); font-weight:400;">
                                    (used for availability)
                                </span>
                            </label>
                            <input type="date" wire:model="pickupDate"
                                class="form-control @error('pickupDate') is-invalid @enderror">
                            @error('pickupDate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-4">
                            <label class="form-label">
                                Return Date <span class="text-danger">*</span>
                                <span style="font-size:10px; color:var(--gold-hover); font-weight:400;">
                                    (used for availability)
                                </span>
                            </label>
                            <input type="date" wire:model="returnDate"
                                class="form-control @error('returnDate') is-invalid @enderror">
                            @error('returnDate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Replace the stitching date + instructions inputs (col-4 and col-8) with: --}}
                        <div class="col-12">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="stitchingToggle"
                                    wire:model.live="showStitching">
                                <label class="form-check-label" for="stitchingToggle"
                                    style="font-size:12px; font-weight:600; color:var(--text-muted);">
                                    <i class="bi bi-scissors me-1"></i> Stitching Required
                                </label>
                            </div>
                            @if ($showStitching)
                                <div class="row g-3">
                                    <div class="col-4">
                                        <label class="form-label">Stitching Date</label>
                                        <input type="date" wire:model="stitchingDate" class="form-control">
                                    </div>
                                    <div class="col-8">
                                        <label class="form-label">Stitching Instructions</label>
                                        <input type="text" wire:model="stitchingInstructions" class="form-control"
                                            placeholder="e.g. Waist 28, shorten sleeves 2 inches">
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="col-6">
                            <label class="form-label">Bill Book Ref</label>
                            <input type="text" wire:model="billRef" class="form-control"
                                placeholder="e.g. B-1042">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Handled By</label>
                            <select wire:model="employeeId" class="form-select">
                                <option value="">Select employee...</option>
                                @foreach ($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-4">
                <div class="table-card" style="padding:20px;">
                    <div
                        style="font-size:12px; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:12px;">
                        Customer
                    </div>
                    <div style="font-size:13px; font-weight:600; color:var(--text-primary); margin-bottom:4px;">
                        {{ $customerName }}
                    </div>
                    <div style="font-size:12px; color:var(--text-muted); margin-bottom:2px;">
                        {{ $customerPhone1 }}
                        @if ($customerPhone2)
                            · {{ $customerPhone2 }}
                        @endif
                    </div>
                    @if ($customerCnic)
                        <div style="font-size:11px; color:var(--text-muted); font-family:monospace;">
                            {{ $customerCnic }}
                        </div>
                    @endif

                    @if ($pickupDate && $returnDate)
                        <div
                            style="margin-top:16px; background:#f0fff4; border:1px solid #9ae6b4; border-radius:8px; padding:12px;">
                            <div style="font-size:11px; font-weight:700; color:#276749; margin-bottom:6px;">
                                <i class="bi bi-calendar-check me-1"></i> Date Range
                            </div>
                            <div style="font-size:12px; color:#276749;">
                                Pickup: <strong>{{ \Carbon\Carbon::parse($pickupDate)->format('d/m/Y') }}</strong><br>
                                Return: <strong>{{ \Carbon\Carbon::parse($returnDate)->format('d/m/Y') }}</strong>
                            </div>
                            <div style="font-size:11px; color:#276749; margin-top:4px;">
                                Items will be checked for availability in next step.
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between mt-3">
            <button class="btn btn-outline-secondary" wire:click="prevStep">
                <i class="bi bi-arrow-left me-1"></i> Back
            </button>
            <button class="btn btn-primary" wire:click="nextStep">
                Next: Select Items <i class="bi bi-arrow-right ms-1"></i>
            </button>
        </div>
    @endif

    {{-- ── STEP 3: Items ── --}}
    @if ($step === 3)
        <div class="row g-3">
            <div class="col-8">
                <div class="table-card" style="padding:20px;">

                    {{-- Availability Notice --}}
                    @if ($pickupDate && $returnDate)
                        <div
                            style="background:#fffff0; border:1px solid #f6e05e; border-radius:8px; padding:10px 14px; margin-bottom:16px; font-size:12px; color:#b7791f;">
                            <i class="bi bi-calendar-check me-1"></i>
                            Showing availability for
                            <strong>{{ \Carbon\Carbon::parse($pickupDate)->format('d/m/Y') }}</strong>
                            to
                            <strong>{{ \Carbon\Carbon::parse($returnDate)->format('d/m/Y') }}</strong>.
                            Items marked <span style="color:#e53e3e; font-weight:700;">Booked</span> are already
                            reserved.
                        </div>
                    @endif

                    {{-- Search --}}
                    <div style="position:relative; margin-bottom:16px;">
                        <input type="text" wire:model.live.debounce.300ms="productSearch"
                            wire:keyup="searchProducts" class="form-control"
                            placeholder="Search by code or name (e.g. BL-001)...">
                        <i class="bi bi-search"
                            style="position:absolute; right:12px; top:10px; color:var(--text-muted);"></i>

                        @if (count($searchResults) > 0)
                            <div class="product-search-dropdown">
                                @foreach ($searchResults as $result)
                                    <div class="search-item" wire:click="addItem({{ $result['id'] }})"
                                        style="{{ !$result['available'] ? 'opacity:0.85;' : '' }} cursor:pointer;">
                                        <div class="d-flex align-items-center gap-3">
                                            @if ($result['photo'])
                                                <img src="{{ Storage::url($result['photo']) }}"
                                                    style="width:36px; height:36px; object-fit:cover; border-radius:6px; flex-shrink:0;">
                                            @else
                                                <div
                                                    style="width:36px; height:36px; background:var(--gold-light); border-radius:6px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                                    <i class="bi bi-image"
                                                        style="font-size:14px; color:var(--gold);"></i>
                                                </div>
                                            @endif
                                            <div class="flex-fill">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <span class="search-item-code">{{ $result['code'] }}</span>
                                                        @if (!$result['available'])
                                                            <span
                                                                style="font-size:10px; background:#fff5f5; color:#c53030; padding:1px 6px; border-radius:3px; margin-left:4px; font-weight:700;">
                                                                Booked
                                                            </span>
                                                        @else
                                                            <span
                                                                style="font-size:10px; background:#f0fff4; color:#276749; padding:1px 6px; border-radius:3px; margin-left:4px; font-weight:700;">
                                                                Available
                                                            </span>
                                                        @endif
                                                        <div class="search-item-name">{{ $result['name'] }}</div>
                                                        <div class="search-item-category">
                                                            {{ $result['category'] }}
                                                            @if ($result['size'])
                                                                · Size: {{ $result['size'] }}
                                                            @endif
                                                            @if ($result['color'])
                                                                · {{ $result['color'] }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="search-item-price">
                                                        Rs. {{ number_format($result['rental_price'], 0) }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if (strlen($productSearch) >= 2 && count($searchResults) === 0)
                            <div class="product-search-dropdown">
                                <div style="padding:14px; font-size:12px; color:var(--text-muted); text-align:center;">
                                    No products found
                                </div>
                            </div>
                        @endif
                    </div>

                    @error('items')
                        <div class="alert alert-danger py-2 mb-3" style="font-size:12px;">{{ $message }}</div>
                    @enderror

                    {{-- Added Items --}}
                    @if (count($items) > 0)
                        <div
                            style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:10px;">
                            Added Items ({{ count($items) }})
                        </div>

                        @foreach ($items as $index => $item)
                            <div class="rental-item-row"
                                style="{{ $item['double_booked'] ?? false ? 'border-left: 3px solid #e53e3e;' : '' }}">

                                @if (isset($item['double_booked']) && $item['double_booked'] === true)
                                    <div
                                        style="background:#fff5f5; border:1px solid #fed7d7; border-radius:6px; padding:6px 10px; margin-bottom:8px; font-size:11px; color:#c53030; font-weight:600;">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                        Warning: This item is already booked for the selected date range.
                                        You can still add it — admin will manage the conflict.
                                    </div>
                                @endif
                                <button class="item-remove-btn" wire:click="removeItem({{ $index }})">
                                    <i class="bi bi-x"></i>
                                </button>

                                <div class="d-flex align-items-start gap-3 mb-2">
                                    @if ($item['photo'])
                                        <img src="{{ Storage::url($item['photo']) }}"
                                            style="width:40px; height:40px; object-fit:cover; border-radius:6px; flex-shrink:0;">
                                    @endif
                                    <span class="item-code">{{ $item['code'] }}</span>
                                    <div>
                                        <div class="item-name">{{ $item['name'] }}</div>
                                        <div style="font-size:11px; color:var(--text-muted);">
                                            {{ $item['category'] }}
                                            @if ($item['size'])
                                                · Size: {{ $item['size'] }}
                                            @endif
                                            @if ($item['color'])
                                                · {{ $item['color'] }}
                                            @endif
                                        </div>
                                    </div>
                                </div>

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
                                            class="form-control form-control-sm" placeholder="e.g. needs alteration">
                                    </div>
                                </div>

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
                                                    placeholder="e.g. Name written on dupatta" style="flex:1;">
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
                        @endforeach

                        {{-- Security Deposits --}}
                        <div style="border-top:1px solid var(--border); margin-top:20px; padding-top:16px;">
                            <div class="d-flex align-items-center justify-content-between mb-10">
                                <div
                                    style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted);">
                                    <i class="bi bi-shield-check me-1"></i>
                                    Security / Refundable Deposits
                                    <span style="font-size:10px; font-weight:400; margin-left:4px;">(optional)</span>
                                </div>
                                <button type="button" wire:click="addSecurityDeposit"
                                    class="btn btn-sm btn-outline-secondary action-btn">
                                    <i class="bi bi-plus me-1"></i> Add Deposit Item
                                </button>
                            </div>

                            @if (count($securityDeposits) > 0)
                                <div style="margin-top:10px;">
                                    @foreach ($securityDeposits as $dIndex => $deposit)
                                        <div
                                            style="background:#fffff0; border:1px solid #f6e05e; border-radius:8px; padding:12px; margin-bottom:8px; position:relative;">
                                            <button type="button"
                                                wire:click="removeSecurityDeposit({{ $dIndex }})"
                                                style="position:absolute; top:8px; right:8px; background:none; border:none; color:#fc8181; font-size:16px; cursor:pointer; padding:0 4px;">
                                                <i class="bi bi-x"></i>
                                            </button>

                                            <div class="row g-2">
                                                <div class="col-5">
                                                    <label
                                                        style="font-size:10px; font-weight:600; color:var(--text-muted); text-transform:uppercase;">
                                                        Item Name
                                                    </label>
                                                    <input type="text"
                                                        wire:model="securityDeposits.{{ $dIndex }}.item_name"
                                                        class="form-control form-control-sm"
                                                        placeholder="e.g. Jewelry Box, Packing">
                                                </div>
                                                <div class="col-3">
                                                    <label
                                                        style="font-size:10px; font-weight:600; color:var(--text-muted); text-transform:uppercase;">
                                                        Amount (Rs.)
                                                    </label>
                                                    <input type="number"
                                                        wire:model="securityDeposits.{{ $dIndex }}.amount"
                                                        class="form-control form-control-sm" placeholder="300"
                                                        min="0">
                                                </div>
                                                <div class="col-4" style="padding-top:18px;">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            wire:model="securityDeposits.{{ $dIndex }}.is_paid"
                                                            id="deposit_paid_{{ $dIndex }}">
                                                        <label class="form-check-label"
                                                            for="deposit_paid_{{ $dIndex }}"
                                                            style="font-size:12px; color:#b7791f; font-weight:600;">
                                                            Customer Paid
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div
                                    style="font-size:12px; color:var(--text-muted); text-align:center; padding:12px 0;">
                                    No security deposits — click "Add Deposit Item" to add one
                                </div>
                            @endif
                        </div>
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
                        <span class="s-value">{{ $customerName }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="s-label">Pickup</span>
                        <span
                            class="s-value">{{ $pickupDate ? \Carbon\Carbon::parse($pickupDate)->format('d/m/Y') : '—' }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="s-label">Return</span>
                        <span
                            class="s-value">{{ $returnDate ? \Carbon\Carbon::parse($returnDate)->format('d/m/Y') : '—' }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="s-label">Items</span>
                        <span class="s-value">{{ count($items) }}</span>
                    </div>
                    @foreach ($items as $item)
                        <div class="summary-row" style="font-size:11px;">
                            <span class="s-label">{{ $item['code'] }}</span>
                            <span class="s-value">
                                Rs.
                                {{ number_format((float) $item['rental_price'] + collect($item['addons'])->sum(fn($a) => (float) ($a['price'] ?? 0)), 0) }}
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
                Next: Payment <i class="bi bi-arrow-right ms-1"></i>
            </button>
        </div>
    @endif

    {{-- ── STEP 4: Payment ── --}}
    @if ($step === 4)
        <div class="row g-3">
            <div class="col-8">
                <div class="table-card" style="padding:20px;">
                    <div
                        style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:16px;">
                        <i class="bi bi-cash me-1"></i> Payment Details
                    </div>
                    {{-- Replace the row g-3 inside the payment card with: --}}
                    <div class="row g-3">
                        <div class="col-4">
                            <label class="form-label">Advance Paid (Rs.)</label>
                            <input type="number" wire:model.lazy="advancePaid"
                                class="form-control @error('advancePaid') is-invalid @enderror" min="0">
                            @error('advancePaid')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-4">
                            <label class="form-label">Discount</label>
                            <div class="input-group">
                                <select wire:model.live="discountType" wire:change="recalcTotal" class="form-select"
                                    style="max-width:80px;">
                                    <option value="fixed">Rs.</option>
                                    <option value="percent">%</option>
                                </select>
                                <input type="number" wire:model.lazy="discountValue" wire:change="recalcTotal"
                                    class="form-control" placeholder="0" min="0"
                                    max="{{ $discountType === 'percent' ? 100 : '' }}">
                            </div>
                            @if ((float) $discountAmount > 0)
                                <div style="font-size:11px; color:#38a169; margin-top:4px;">
                                    − Rs. {{ number_format((float) $discountAmount, 0) }} discount applied
                                </div>
                            @endif
                        </div>
                        <div class="col-4">
                            <label class="form-label">Receive Into Account</label>
                            <select wire:model="advanceAccountId" class="form-select">
                                <option value="">Select account...</option>
                                @foreach ($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <div
                                style="background:#f7fafc; border:1px solid var(--border); border-radius:8px; padding:14px;">
                                <div class="row g-2 text-center">
                                    <div class="col-4" style="border-right:1px solid var(--border);">
                                        <div
                                            style="font-size:10px; color:var(--text-muted); text-transform:uppercase; font-weight:600;">
                                            Subtotal</div>
                                        <div style="font-size:16px; font-weight:700; color:var(--navy);">
                                            Rs. {{ number_format((float) $totalAmount + (float) $discountAmount, 0) }}
                                        </div>
                                    </div>
                                    <div class="col-4" style="border-right:1px solid var(--border);">
                                        <div
                                            style="font-size:10px; color:var(--text-muted); text-transform:uppercase; font-weight:600;">
                                            Discount</div>
                                        <div style="font-size:16px; font-weight:700; color:#e53e3e;">
                                            − Rs. {{ number_format((float) $discountAmount, 0) }}
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div
                                            style="font-size:10px; color:var(--text-muted); text-transform:uppercase; font-weight:600;">
                                            Remaining</div>
                                        <div
                                            style="font-size:16px; font-weight:800; color:{{ (float) $advancePaid >= (float) $totalAmount ? '#38a169' : '#e53e3e' }};">
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
                <div class="rental-summary-box mb-3">
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
                    <div class="summary-row">
                        <span class="s-label">Items</span>
                        <span class="s-value">{{ count($items) }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="s-label">Pickup</span>
                        <span
                            class="s-value">{{ $pickupDate ? \Carbon\Carbon::parse($pickupDate)->format('d/m/Y') : '—' }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="s-label">Return</span>
                        <span
                            class="s-value">{{ $returnDate ? \Carbon\Carbon::parse($returnDate)->format('d/m/Y') : '—' }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="s-label">Total</span>
                        <span class="s-value">Rs. {{ number_format((float) $totalAmount, 0) }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="s-label">Discount</span>
                        <span class="s-value" style="color:#e53e3e;">− Rs.
                            {{ number_format((float) $discountAmount, 0) }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="s-label">Advance</span>
                        <span class="s-value">Rs. {{ number_format((float) $advancePaid, 0) }}</span>
                    </div>
                    <div class="summary-row total-row">
                        <span class="s-label">Remaining</span>
                        <span class="s-value gold">
                            Rs. {{ number_format(max(0, (float) $totalAmount - (float) $advancePaid), 0) }}
                        </span>
                    </div>
                </div>

                <button class="btn btn-primary w-100" style="height:46px; font-size:14px; font-weight:700;"
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
