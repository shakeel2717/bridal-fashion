<div wire:init="$dispatch('focus-step-1')">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <div class="page-title">{{ $isEditMode ? 'Edit Rental — #' . $rentalId : 'New Rental Booking' }}</div>
            <div class="page-subtitle">
                {{ $isEditMode ? 'Update rental details step by step' : 'Fill in details step by step' }}</div>
        </div>
        {{-- Back button --}}
        <a href="{{ $isEditMode ? route('rentals.show', $rentalId) : route('rentals.index') }}"
            class="btn btn-sm btn-outline-secondary">
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
                                <input autofocus type="text" wire:model.live="customerName"
                                    class="form-control @error('customerName') is-invalid @enderror"
                                    placeholder="Customer name"
                                    {{ $customerType === 'existing' && $customerId ? 'readonly' : '' }}>
                                @error('customerName')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-4">
                                <label class="form-label">Delivery Address</label>
                                <input type="text" wire:model="deliveryAddress" class="form-control"
                                    placeholder="Customer address">
                            </div>

                            <div class="col-2">
                                <label class="form-label">City</label>
                                <input type="text" wire:model="customerCity" class="form-control"
                                    placeholder="e.g. Lahore">
                            </div>

                            <div class="col-6">
                                <label class="form-label">Phone 1 <span class="text-danger">*</span></label>
                                <div class="d-flex gap-2">
                                    <input type="text" wire:model.live="customerPhone1"
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
                                    <label class="form-label">CNIC </label>
                                    <input type="text" wire:model="customerCnic"
                                        class="form-control @error('customerCnic') is-invalid @enderror"
                                        placeholder="00000-0000000-0">
                                    @error('customerCnic')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif

                            @if ($customerType === 'walkin')
                                @if ($isEditMode)
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
                                                    <label class="form-label" style="font-size:12px;">CNIC
                                                        Front</label>
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
                                                    <label class="form-label" style="font-size:12px;">CNIC
                                                        Back</label>
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
                            <input type="text" wire:model="billRef"
                                class="form-control @error('billRef') is-invalid @enderror" placeholder="e.g. B-1042">
                            @error('billRef')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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

                    {{-- Search Row: Code + Price --}}
                    <div style="margin-bottom:16px;">
                        <div style="display:grid; grid-template-columns:1fr 140px; gap:8px; align-items:end;">

                            {{-- Code Search --}}
                            <div style="position:relative;">
                                <label
                                    style="font-size:10px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:4px; display:block;">
                                    Product Code
                                </label>
                                <input type="text" id="rental_product_search" data-rental-input="1"
                                    class="form-control" placeholder="Type exact code e.g. 101..."
                                    autocomplete="off">

                                @if (count($searchResults) > 0)
                                    <div class="product-search-dropdown">
                                        @foreach ($searchResults as $result)
                                            <div class="search-item po-search-item"
                                                wire:click="selectProductForPrice({{ $result['id'] }})"
                                                data-product-id="{{ $result['id'] }}"
                                                style="{{ !$result['available'] ? 'opacity:0.8;' : '' }} cursor:pointer;">
                                                <div class="d-flex align-items-center gap-3">
                                                    @if ($result['photo'])
                                                        <img src="{{ Storage::url($result['photo']) }}"
                                                            style="width:32px; height:32px; object-fit:cover; border-radius:5px; flex-shrink:0;">
                                                    @else
                                                        <div
                                                            style="width:32px; height:32px; background:var(--gold-light); border-radius:5px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                                            <i class="bi bi-image"
                                                                style="font-size:12px; color:var(--gold);"></i>
                                                        </div>
                                                    @endif
                                                    <div class="flex-fill">
                                                        <span class="search-item-code">{{ $result['code'] }}</span>
                                                        @if (!$result['available'])
                                                            <span
                                                                style="font-size:10px; background:#fff5f5; color:#c53030; padding:1px 5px; border-radius:3px; margin-left:4px; font-weight:700;">Booked</span>
                                                        @else
                                                            <span
                                                                style="font-size:10px; background:#f0fff4; color:#276749; padding:1px 5px; border-radius:3px; margin-left:4px; font-weight:700;">Available</span>
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
                                                    <div class="search-item-price">Rs.
                                                        {{ number_format($result['rental_price'], 0) }}</div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if (strlen($productSearch) >= 1 && count($searchResults) === 0 && !$showPriceInput)
                                    <div class="product-search-dropdown">
                                        <div
                                            style="padding:12px; font-size:12px; color:var(--text-muted); text-align:center;">
                                            No product with code "{{ $productSearch }}"
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Price Input --}}
                            <div>
                                <label
                                    style="font-size:10px; font-weight:700; text-transform:uppercase; color:{{ $showPriceInput ? '#276749' : 'var(--text-muted)' }}; margin-bottom:4px; display:block;">
                                    Price (Rs.)
                                </label>
                                <input type="number" id="rental_price_input" data-rental-input="1"
                                    wire:model.lazy="pendingPrice" class="form-control" placeholder="0"
                                    min="0"
                                    style="text-align:right; {{ !$showPriceInput ? 'opacity:0.4; pointer-events:none;' : 'border-color:#9ae6b4; background:#f0fff4;' }}"
                                    {{ !$showPriceInput ? 'disabled' : '' }}>
                            </div>
                        </div>

                        {{-- Selected product hint --}}
                        @if ($showPriceInput && $pendingProductCode)
                            <div style="margin-top:6px; font-size:11px; color:#276749; font-weight:600;">
                                <i class="bi bi-check-circle me-1"></i>
                                {{ $pendingProductCode }} — {{ $pendingProductName }}
                                · Enter price and press Enter to add
                            </div>
                        @endif
                    </div>

                    @error('items')
                        <div class="alert alert-danger py-2 mb-3" style="font-size:12px;">{{ $message }}</div>
                    @enderror

                    {{-- Items Table --}}
                    @if (count($items) > 0)
                        <table class="table mb-0" style="font-size:12px;">
                            <thead>
                                <tr>
                                    <th style="width:36px; text-align:center;">#</th>
                                    <th style="width:40px;"></th>
                                    <th style="width:70px;">Code</th>
                                    <th>Item</th>
                                    <th style="width:120px; text-align:right;">Price (Rs.)</th>
                                    <th style="width:36px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $index => $item)
                                    @php
                                        $isDoubleBooked = $item['double_booked'] ?? false;
                                        $addonCount = count($item['addons']);
                                        $totalRows = 1 + $addonCount + 1; // item row + addon rows + "add button" row
                                    @endphp
                                    <tr style="{{ $isDoubleBooked ? 'border-left:3px solid #e53e3e;' : '' }}">

                                        {{-- # --}}
                                        <td rowspan="{{ $totalRows }}"
                                            style="text-align:center; font-weight:700; color:var(--text-muted); vertical-align:middle;">
                                            {{ count($items) - $index }}
                                        </td>

                                        {{-- Photo --}}
                                        <td rowspan="{{ $totalRows }}"
                                            style="vertical-align:middle; padding:6px 4px;">
                                            @if ($item['photo'])
                                                <img src="{{ Storage::url($item['photo']) }}"
                                                    style="width:34px; height:34px; object-fit:cover; border-radius:5px; display:block;">
                                            @else
                                                <div
                                                    style="width:34px; height:34px; background:var(--gold-light); border-radius:5px; display:flex; align-items:center; justify-content:center;">
                                                    <i class="bi bi-image"
                                                        style="font-size:12px; color:var(--gold);"></i>
                                                </div>
                                            @endif
                                        </td>

                                        {{-- Code --}}
                                        <td rowspan="{{ $totalRows }}" style="vertical-align:middle;">
                                            <span
                                                style="font-family:monospace; font-weight:700; font-size:12px;">{{ $item['code'] }}</span>
                                            @if ($isDoubleBooked)
                                                <div
                                                    style="font-size:10px; color:#c53030; font-weight:600; margin-top:2px;">
                                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>Conflict
                                                </div>
                                            @endif
                                        </td>

                                        {{-- Item name (first inner row) --}}
                                        <td style="border-bottom:1px dashed var(--border); padding:8px 8px;">
                                            <div style="font-weight:600; font-size:12px;">{{ $item['name'] }}</div>
                                            <div style="font-size:11px; color:var(--text-muted);">
                                                {{ $item['category'] }}
                                                @if ($item['size'])
                                                    · {{ $item['size'] }}
                                                @endif
                                                @if ($item['color'])
                                                    · {{ $item['color'] }}
                                                @endif
                                            </div>
                                        </td>

                                        {{-- Price (item) --}}
                                        <td
                                            style="border-bottom:1px dashed var(--border); padding:8px 6px; vertical-align:middle;">
                                            <input type="number"
                                                wire:model.lazy="items.{{ $index }}.rental_price"
                                                wire:change="recalcTotal" class="form-control form-control-sm"
                                                style="text-align:right;" min="0">
                                        </td>

                                        {{-- Remove --}}
                                        <td rowspan="{{ $totalRows }}"
                                            style="text-align:center; vertical-align:middle;">
                                            <button type="button" wire:click="removeItem({{ $index }})"
                                                style="background:none; border:none; color:#fc8181; font-size:18px; cursor:pointer; padding:0;">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    {{-- Addon rows --}}
                                    @foreach ($item['addons'] as $addonIndex => $addon)
                                        <tr style="{{ $isDoubleBooked ? 'border-left:3px solid #e53e3e;' : '' }}">
                                            <td style="padding:4px 8px; border-bottom:1px dashed var(--border);">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="bi bi-arrow-return-right"
                                                        style="font-size:11px; color:var(--text-muted); flex-shrink:0;"></i>
                                                    <input type="text"
                                                        wire:model="items.{{ $index }}.addons.{{ $addonIndex }}.label"
                                                        class="form-control form-control-sm"
                                                        placeholder="e.g. Name written on dupatta" style="flex:1;">
                                                </div>
                                            </td>
                                            <td
                                                style="padding:4px 6px; border-bottom:1px dashed var(--border); vertical-align:middle;">
                                                <input type="number"
                                                    wire:model.lazy="items.{{ $index }}.addons.{{ $addonIndex }}.price"
                                                    wire:change="recalcTotal" class="form-control form-control-sm"
                                                    placeholder="0" style="text-align:right;" min="0">
                                            </td>
                                        </tr>
                                    @endforeach

                                    {{-- Add addon button row --}}
                                    <tr style="{{ $isDoubleBooked ? 'border-left:3px solid #e53e3e;' : '' }}">
                                        <td colspan="2" style="padding:5px 8px;">
                                            <button type="button" wire:click="addAddon({{ $index }})"
                                                class="btn btn-primary btn-sm">
                                                <i class="bi bi-plus me-1"></i> Add Custom Option
                                            </button>
                                            @if ($addonCount > 0)
                                                <span
                                                    style="font-size:11px; color:#38a169; font-weight:600; margin-left:10px;">
                                                    + Rs.
                                                    {{ number_format(collect($item['addons'])->sum(fn($a) => (float) ($a['price'] ?? 0)), 0) }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4"
                                        style="text-align:right; font-size:12px; color:var(--text-muted); padding-top:10px;">
                                        Subtotal
                                    </td>
                                    <td
                                        style="text-align:right; font-weight:700; padding-top:10px; color:var(--navy);">
                                        Rs. {{ number_format((float) $totalAmount + (float) $discountAmount, 0) }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    @else
                        <div
                            style="text-align:center; padding:30px; color:var(--text-muted); font-size:13px; border:2px dashed var(--border); border-radius:8px;">
                            <i class="bi bi-box-seam" style="font-size:32px; display:block; margin-bottom:8px;"></i>
                            Search and add products above
                        </div>
                    @endif
                </div>

                {{-- ── Linked Sale Items (optional) ── --}}
                <div style="border-top:2px solid var(--border); margin-top:24px; padding-top:20px;">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <div style="font-size:12px; font-weight:700; color:var(--navy); text-transform:uppercase;">
                                <i class="bi bi-cart-plus me-1"></i> Also Selling Items?
                                <span
                                    style="font-size:10px; font-weight:400; color:var(--text-muted); text-transform:none; margin-left:6px;">(optional
                                    — creates a linked sale automatically)</span>
                            </div>
                        </div>
                    </div>

                    {{-- Sale Product Search Row --}}
                    <div
                        style="background:#f0f4ff; border:1.5px solid #a3bffa; border-radius:8px; padding:10px 12px; margin-bottom:12px;">
                        <div style="display:grid; grid-template-columns:1fr 80px 120px; gap:8px; align-items:end;">

                            <div style="position:relative;">
                                <label
                                    style="font-size:10px; font-weight:700; text-transform:uppercase; color:#3c4f9e; margin-bottom:4px; display:block;">
                                    Product Code / Name
                                </label>
                                <input type="text" id="sale_product_search"
                                    wire:model.live.debounce.300ms="saleProductSearch" wire:keyup="searchSaleProducts"
                                    class="form-control form-control-sm" placeholder="Search sale product..."
                                    autocomplete="off">

                                @if (count($saleSearchResults) > 0)
                                    <div class="product-search-dropdown" style="min-width:420px;">
                                        @foreach ($saleSearchResults as $result)
                                            <div class="search-item sale-search-item"
                                                wire:click="selectSaleProduct({{ $result['id'] }})"
                                                data-product-id="{{ $result['id'] }}" style="cursor:pointer;">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <span class="search-item-code">{{ $result['code'] }}</span>
                                                        <div class="search-item-name">{{ $result['name'] }}</div>
                                                        <div class="search-item-category">
                                                            {{ $result['category'] }} · Stock:
                                                            {{ $result['stock_qty'] }}
                                                        </div>
                                                    </div>
                                                    <div class="search-item-price">Rs.
                                                        {{ number_format($result['sale_price'], 0) }}</div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div>
                                <label
                                    style="font-size:10px; font-weight:700; text-transform:uppercase; color:#3c4f9e; margin-bottom:4px; display:block;">Qty</label>
                                <input type="number" id="sale_new_qty" wire:model="saleNewItemQty"
                                    class="form-control form-control-sm" min="1" style="text-align:center;"
                                    placeholder="1">
                            </div>

                            <div>
                                <label
                                    style="font-size:10px; font-weight:700; text-transform:uppercase; color:#3c4f9e; margin-bottom:4px; display:block;">Price
                                    (Rs.)</label>
                                <input type="number" id="sale_new_price" wire:model="saleNewItemPrice"
                                    class="form-control form-control-sm" min="0" style="text-align:right;"
                                    placeholder="0">
                            </div>
                        </div>
                    </div>

                    {{-- Sale Items Table --}}
                    @if (count($saleItems) > 0)
                        <table class="table mb-0" style="font-size:12px;">
                            <thead>
                                <tr>
                                    <th style="width:36px; text-align:center;">#</th>
                                    <th style="width:90px;">Code</th>
                                    <th>Item</th>
                                    <th style="width:70px; text-align:center;">Qty</th>
                                    <th style="width:110px; text-align:right;">Price (Rs.)</th>
                                    <th style="width:110px; text-align:right;">Total</th>
                                    <th style="width:36px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($saleItems as $index => $item)
                                    <tr>
                                        <td style="text-align:center; font-weight:700; color:var(--text-muted);">
                                            {{ count($saleItems) - $index }}</td>
                                        <td><span
                                                style="font-family:monospace; font-weight:700;">{{ $item['item_code'] }}</span>
                                        </td>
                                        <td>{{ $item['item_name'] }}</td>
                                        <td>
                                            <input type="number"
                                                wire:model.lazy="saleItems.{{ $index }}.qty"
                                                wire:change="recalcSaleItems" class="form-control form-control-sm"
                                                style="text-align:center;" min="1">
                                        </td>
                                        <td>
                                            <input type="number"
                                                wire:model.lazy="saleItems.{{ $index }}.unit_price"
                                                wire:change="recalcSaleItems" class="form-control form-control-sm"
                                                style="text-align:right;" min="0">
                                        </td>
                                        <td style="text-align:right; font-weight:700; color:var(--navy);">
                                            Rs. {{ number_format((float) ($item['total_price'] ?? 0), 0) }}
                                        </td>
                                        <td style="text-align:center;">
                                            <button type="button" wire:click="removeSaleItem({{ $index }})"
                                                style="background:none; border:none; color:#fc8181; font-size:18px; cursor:pointer; padding:0;">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5"
                                        style="text-align:right; font-size:12px; color:var(--text-muted); padding-top:8px;">
                                        Subtotal
                                    </td>
                                    <td style="text-align:right; font-weight:700; padding-top:8px; color:var(--navy);">
                                        Rs. {{ number_format($this->getSaleSubtotalProperty(), 0) }}
                                    </td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="4"
                                        style="text-align:right; font-size:12px; color:var(--text-muted);">
                                        Discount (Rs.)
                                    </td>
                                    <td style="padding:4px 6px;">
                                        <input type="number" wire:model.lazy="saleDiscount"
                                            wire:change="recalcSaleItems" class="form-control form-control-sm"
                                            min="0" style="text-align:right;" placeholder="0">
                                    </td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="5"
                                        style="text-align:right; font-size:12px; font-weight:700; color:var(--navy); padding-top:4px;">
                                        Sale Total
                                    </td>
                                    <td
                                        style="text-align:right; font-weight:800; font-size:13px; color:var(--navy); padding-top:4px;">
                                        Rs. {{ number_format($this->getSaleTotalProperty(), 0) }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    @else
                        <div style="font-size:12px; color:var(--text-muted); padding:8px 0;">
                            No sale items added — search above to add products for sale.
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

                    {{-- Rental items --}}
                    <div
                        style="font-size:10px; font-weight:700; text-transform:uppercase; color:rgba(255,255,255,0.4); margin:10px 0 6px;">
                        Rental Items ({{ count($items) }})
                    </div>
                    @foreach ($items as $item)
                        <div class="summary-row" style="font-size:11px;">
                            <span class="s-label">{{ $item['code'] }}</span>
                            <span class="s-value">Rs.
                                {{ number_format((float) $item['rental_price'] + collect($item['addons'])->sum(fn($a) => (float) ($a['price'] ?? 0)), 0) }}</span>
                        </div>
                    @endforeach
                    <div class="summary-row"
                        style="border-top:1px solid rgba(255,255,255,0.1); padding-top:6px; margin-top:4px;">
                        <span class="s-label">Rental Total</span>
                        <span class="s-value">Rs. {{ number_format((float) $totalAmount, 0) }}</span>
                    </div>

                    {{-- Sale items (if any) --}}
                    @if (count($saleItems) > 0)
                        <div
                            style="font-size:10px; font-weight:700; text-transform:uppercase; color:rgba(255,255,255,0.4); margin:10px 0 6px;">
                            Sale Items ({{ count($saleItems) }})
                        </div>
                        @foreach ($saleItems as $item)
                            <div class="summary-row" style="font-size:11px;">
                                <span class="s-label">{{ $item['item_code'] }}</span>
                                <span class="s-value">{{ $item['qty'] }}× Rs.
                                    {{ number_format((float) $item['unit_price'], 0) }}</span>
                            </div>
                        @endforeach
                        @if ((float) $saleDiscount > 0)
                            <div class="summary-row" style="font-size:11px;">
                                <span class="s-label">Sale Discount</span>
                                <span class="s-value" style="color:#fc8181;">− Rs.
                                    {{ number_format((float) $saleDiscount, 0) }}</span>
                            </div>
                        @endif
                        <div class="summary-row"
                            style="border-top:1px solid rgba(255,255,255,0.1); padding-top:6px; margin-top:4px;">
                            <span class="s-label">Sale Total</span>
                            <span class="s-value">Rs. {{ number_format($this->getSaleTotalProperty(), 0) }}</span>
                        </div>
                    @endif

                    {{-- Grand Total --}}
                    <div class="summary-row total-row" style="margin-top:8px;">
                        <span class="s-label">Grand Total</span>
                        <span class="s-value gold">
                            Rs. {{ number_format((float) $totalAmount + $this->getSaleTotalProperty(), 0) }}
                        </span>
                    </div>

                    @if (count($saleItems) > 0)
                        <div style="font-size:10px; color:rgba(255,255,255,0.4); margin-top:8px;">
                            <i class="bi bi-info-circle me-1"></i> Sale will be recorded separately.
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
                            @php
                                $linkedSaleTotal = $isEditMode
                                    ? \App\Models\Sale::where('rental_id', $rentalId)->value('total_amount') ?? 0
                                    : $this->getSaleTotalProperty();
                                $saleItemsExist = $isEditMode
                                    ? \App\Models\Sale::where('rental_id', $rentalId)->exists()
                                    : count($saleItems) > 0;
                            @endphp
                            <div
                                style="background:#f7fafc; border:1px solid var(--border); border-radius:8px; padding:14px;">
                                <div class="row g-2 text-center">
                                    <div class="col-3" style="border-right:1px solid var(--border);">
                                        <div
                                            style="font-size:10px; color:var(--text-muted); text-transform:uppercase; font-weight:600;">
                                            Rental</div>
                                        <div style="font-size:15px; font-weight:700; color:var(--navy);">
                                            Rs. {{ number_format((float) $totalAmount, 0) }}
                                        </div>
                                    </div>
                                    <div class="col-3" style="border-right:1px solid var(--border);">
                                        <div
                                            style="font-size:10px; color:var(--text-muted); text-transform:uppercase; font-weight:600;">
                                            Sale</div>
                                        <div style="font-size:15px; font-weight:700; color:var(--navy);">
                                            Rs. {{ number_format((float) $linkedSaleTotal, 0) }}
                                        </div>
                                    </div>
                                    <div class="col-3" style="border-right:1px solid var(--border);">
                                        <div
                                            style="font-size:10px; color:var(--text-muted); text-transform:uppercase; font-weight:600;">
                                            Advance</div>
                                        <div style="font-size:15px; font-weight:700; color:#276749;">
                                            Rs. {{ number_format((float) $advancePaid, 0) }}
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div
                                            style="font-size:10px; color:var(--text-muted); text-transform:uppercase; font-weight:600;">
                                            Remaining</div>
                                        <div
                                            style="font-size:15px; font-weight:800; color:{{ (float) $advancePaid >= (float) $totalAmount ? '#38a169' : '#e53e3e' }};">
                                            Rs.
                                            {{ number_format(max(0, (float) $totalAmount - (float) $advancePaid), 0) }}
                                        </div>
                                    </div>
                                </div>
                                @if ($saleItemsExist)
                                    <div
                                        style="border-top:1px solid var(--border); margin-top:10px; padding-top:10px; text-align:center; font-size:12px; color:var(--text-muted);">
                                        Grand Total (Rental + Sale): <strong
                                            style="color:var(--navy); font-size:14px;">
                                            Rs.
                                            {{ number_format((float) $totalAmount + (float) $linkedSaleTotal, 0) }}
                                        </strong>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Final Summary --}}
            <div class="col-4">
                {{-- Final Summary --}}
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
                        <span class="s-label">Pickup</span>
                        <span
                            class="s-value">{{ $pickupDate ? \Carbon\Carbon::parse($pickupDate)->format('d/m/Y') : '—' }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="s-label">Return</span>
                        <span
                            class="s-value">{{ $returnDate ? \Carbon\Carbon::parse($returnDate)->format('d/m/Y') : '—' }}</span>
                    </div>

                    {{-- Rental --}}
                    <div
                        style="font-size:10px; font-weight:700; text-transform:uppercase; color:rgba(255,255,255,0.4); margin:10px 0 6px;">
                        Rental ({{ count($items) }} items)
                    </div>
                    <div class="summary-row">
                        <span class="s-label">Subtotal</span>
                        <span class="s-value">Rs.
                            {{ number_format((float) $totalAmount + (float) $discountAmount, 0) }}</span>
                    </div>
                    @if ((float) $discountAmount > 0)
                        <div class="summary-row">
                            <span class="s-label">Discount</span>
                            <span class="s-value" style="color:#fc8181;">− Rs.
                                {{ number_format((float) $discountAmount, 0) }}</span>
                        </div>
                    @endif
                    <div class="summary-row">
                        <span class="s-label">Rental Total</span>
                        <span class="s-value">Rs. {{ number_format((float) $totalAmount, 0) }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="s-label">Advance</span>
                        <span class="s-value">Rs. {{ number_format((float) $advancePaid, 0) }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="s-label">Remaining</span>
                        <span class="s-value"
                            style="color:{{ (float) $advancePaid >= (float) $totalAmount ? '#68d391' : '#fc8181' }};">
                            Rs. {{ number_format(max(0, (float) $totalAmount - (float) $advancePaid), 0) }}
                        </span>
                    </div>

                    {{-- Sale (if any) --}}
                    @if (count($saleItems) > 0)
                        <div
                            style="font-size:10px; font-weight:700; text-transform:uppercase; color:rgba(255,255,255,0.4); margin:10px 0 6px;">
                            Linked Sale ({{ count($saleItems) }} items)
                        </div>
                        @foreach ($saleItems as $item)
                            <div class="summary-row" style="font-size:11px;">
                                <span class="s-label">{{ $item['item_code'] }}</span>
                                <span class="s-value">{{ $item['qty'] }}× Rs.
                                    {{ number_format((float) $item['unit_price'], 0) }}</span>
                            </div>
                        @endforeach
                        @if ((float) $saleDiscount > 0)
                            <div class="summary-row" style="font-size:11px;">
                                <span class="s-label">Sale Discount</span>
                                <span class="s-value" style="color:#fc8181;">− Rs.
                                    {{ number_format((float) $saleDiscount, 0) }}</span>
                            </div>
                        @endif
                        <div class="summary-row">
                            <span class="s-label">Sale Total</span>
                            <span class="s-value">Rs. {{ number_format($this->getSaleTotalProperty(), 0) }}</span>
                        </div>
                    @endif

                    {{-- Grand Total --}}
                    <div class="summary-row total-row" style="margin-top:8px;">
                        <span class="s-label">Grand Total</span>
                        <span class="s-value gold">
                            Rs. {{ number_format((float) $totalAmount + $this->getSaleTotalProperty(), 0) }}
                        </span>
                    </div>

                    @if (count($saleItems) > 0)
                        <div style="font-size:10px; color:rgba(255,255,255,0.4); margin-top:8px;">
                            <i class="bi bi-info-circle me-1"></i> Sale recorded separately — payment managed in Sales
                            module.
                        </div>
                    @endif
                </div>

                <button class="btn btn-primary w-100" style="height:46px; font-size:14px; font-weight:700;"
                    wire:click="save" wire:loading.attr="disabled">
                    <span wire:loading wire:target="save">
                        <span class="spinner-border spinner-border-sm me-2"></span>
                    </span>
                    <i class="bi bi-check-circle me-2"></i> {{ $isEditMode ? 'Save Changes' : 'Confirm Booking' }}
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
@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', function() {

            // Step 1 — focus Full Name on load
            Livewire.on('focus-step-1', () => {
                setTimeout(() => {
                    const el = document.querySelector(
                        'input[wire\\:model\\.live="customerName"], input[wire\\:model="customerName"]'
                    );
                    if (el) el.focus();
                }, 150);
            });

            // Step 2 — focus Booking Date
            Livewire.on('focus-step-2', () => {
                setTimeout(() => {
                    const el = document.querySelector('input[wire\\:model="bookingDate"]');
                    if (el) el.focus();
                }, 150);
            });

            // Step 3 — focus Product Code search
            Livewire.on('step-changed-to-3', () => {
                setTimeout(() => {
                    setupRentalSearch();
                    const el = document.getElementById('rental_product_search');
                    if (el) el.focus();
                }, 150);
            });

            // Step 4 — focus Advance Paid
            Livewire.on('focus-step-4', () => {
                setTimeout(() => {
                    const el = document.querySelector('input[wire\\:model\\.lazy="advancePaid"]');
                    if (el) el.focus();
                }, 150);
            });

            Livewire.on('focus-rental-price', () => {
                setTimeout(() => {
                    const el = document.getElementById('rental_price_input');
                    if (el) {
                        el.removeAttribute('disabled');
                        el.focus();
                        el.select();
                    }
                }, 100);
            });

            Livewire.on('focus-rental-search', () => {
                setTimeout(() => {
                    const el = document.getElementById('rental_product_search');
                    if (el) {
                        el.focus();
                        el.select();
                    }
                }, 100);
            });

            Livewire.on('focus-sale-search', () => {
                setTimeout(() => {
                    const el = document.getElementById('sale_product_search');
                    if (el) {
                        el.focus();
                        el.select();
                    }
                }, 100);
            });

            Livewire.on('focus-sale-qty', () => {
                setTimeout(() => {
                    const el = document.getElementById('sale_new_qty');
                    if (el) {
                        el.focus();
                        el.select();
                    }
                }, 100);
            });
        });

        document.addEventListener('livewire:updated', () => {
            if (document.getElementById('rental_product_search')) {
                setupRentalSearch();
            }
        });

        let _saleHighlight = -1;

        function saleSearchKeydown(e) {
            const dropdown = document.querySelector('.sale-search-item')?.closest('.product-search-dropdown');
            const dropItems = dropdown ? Array.from(dropdown.querySelectorAll('.sale-search-item')) : [];

            if (e.key === 'Enter') {
                e.preventDefault();
                e.stopImmediatePropagation();
                e.stopPropagation();
                if (dropItems.length > 0) {
                    const idx = _saleHighlight >= 0 ? _saleHighlight : 0;
                    const target = dropItems[idx] ?? dropItems[0];
                    const id = target?.dataset.productId;
                    if (id) @this.call('selectSaleProduct', parseInt(id));
                    _saleHighlight = -1;
                }
                return;
            }

            if (!dropItems.length) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                _saleHighlight = Math.min(_saleHighlight + 1, dropItems.length - 1);
                dropItems.forEach((el, i) => el.style.background = i === _saleHighlight ? '#ebf8ff' : '');
            }
            if (e.key === 'ArrowUp') {
                e.preventDefault();
                _saleHighlight = Math.max(_saleHighlight - 1, 0);
                dropItems.forEach((el, i) => el.style.background = i === _saleHighlight ? '#ebf8ff' : '');
            }
            if (e.key === 'Escape') {
                _saleHighlight = -1;
                @this.set('saleProductSearch', '');
                @this.set('saleSearchResults', []);
            }
        }

        function rentalSearchKeydown(e) {
            const dropdown = document.querySelector('.po-search-item')?.closest('.product-search-dropdown');
            const dropItems = dropdown ? Array.from(dropdown.querySelectorAll('.po-search-item')) : [];

            if (e.key === 'Enter') {
                e.preventDefault();
                e.stopImmediatePropagation();
                e.stopPropagation();

                const searchEl = document.getElementById('rental_product_search');
                const currentVal = searchEl?.value;
                clearTimeout(rentalSearchTimer);

                const trySelect = (attempts) => {
                    const dd = document.querySelector('.po-search-item')?.closest('.product-search-dropdown');
                    const items = dd ? Array.from(dd.querySelectorAll('.po-search-item')) : [];
                    if (items.length > 0) {
                        const idx = (window._rentalHighlight ?? -1) < 0 ? 0 : window._rentalHighlight;
                        const target = items[idx] ?? items[0];
                        const productId = target.dataset.productId;
                        if (productId) {
                            if (searchEl) searchEl.value = '';
                            @this.call('selectProductForPrice', parseInt(productId));
                        }
                        window._rentalHighlight = -1;
                    } else if (attempts > 0) {
                        if (searchEl && document.activeElement !== searchEl) searchEl.focus();
                        setTimeout(() => trySelect(attempts - 1), 80);
                    }
                };

                @this.set('productSearch', currentVal).then(() => {
                    @this.call('searchProducts').then(() => {
                        trySelect(5);
                    });
                });
                return;
            }

            if (!dropdown || !dropItems.length) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                window._rentalHighlight = Math.min((window._rentalHighlight ?? -1) + 1, dropItems.length - 1);
                dropItems.forEach((el, i) => el.style.background = i === window._rentalHighlight ? '#ebf8ff' : '');
            }
            if (e.key === 'ArrowUp') {
                e.preventDefault();
                window._rentalHighlight = Math.max((window._rentalHighlight ?? 0) - 1, 0);
                dropItems.forEach((el, i) => el.style.background = i === window._rentalHighlight ? '#ebf8ff' : '');
            }
            if (e.key === 'Escape') {
                window._rentalHighlight = -1;
                @this.set('productSearch', '');
                @this.set('searchResults', []);
            }
        }

        function rentalPriceKeydown(e) {
            if (e.key !== 'Enter') return;
            e.preventDefault();
            e.stopImmediatePropagation();
            e.stopPropagation();
            @this.call('confirmAddItem');
        }

        let rentalSearchTimer = null;

        function rentalSearchInput(e) {
            clearTimeout(rentalSearchTimer);
            const val = e.target.value;
            rentalSearchTimer = setTimeout(() => {
                @this.set('productSearch', val, false);
                @this.call('searchProducts');
            }, 200);
        }

        function setupRentalSearch() {
            const searchInput = document.getElementById('rental_product_search');
            const priceInput = document.getElementById('rental_price_input');

            if (searchInput) {
                searchInput.removeEventListener('keydown', rentalSearchKeydown, true);
                searchInput.addEventListener('keydown', rentalSearchKeydown, true);
                searchInput.removeEventListener('input', rentalSearchInput);
                searchInput.addEventListener('input', rentalSearchInput);
            }

            if (priceInput) {
                priceInput.removeEventListener('keydown', rentalPriceKeydown, true);
                priceInput.addEventListener('keydown', rentalPriceKeydown, true);
            }

            // ── Sale product search ────────────────────────────────
            const saleSearchInput = document.getElementById('sale_product_search');
            const saleQtyInput = document.getElementById('sale_new_qty');
            const salePriceInput = document.getElementById('sale_new_price');

            if (saleSearchInput) {
                saleSearchInput.removeEventListener('keydown', saleSearchKeydown, true);
                saleSearchInput.addEventListener('keydown', saleSearchKeydown, true);
            }

            if (saleQtyInput && !saleQtyInput._saleQtyBound) {
                saleQtyInput._saleQtyBound = true;
                saleQtyInput.addEventListener('keydown', function(e) {
                    if (e.key !== 'Enter') return;
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    if (salePriceInput) {
                        salePriceInput.focus();
                        salePriceInput.select();
                    }
                });
            }

            if (salePriceInput && !salePriceInput._salePriceBound) {
                salePriceInput._salePriceBound = true;
                salePriceInput.addEventListener('keydown', function(e) {
                    if (e.key !== 'Enter') return;
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    @this.call('addSaleItem');
                });
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'Enter') {
                e.preventDefault();
                e.stopPropagation();
                const step = @this.get('step');
                if (step === 4) {
                    @this.call('save');
                } else {
                    @this.call('nextStep');
                }
            }
        }, true);
    </script>
@endpush
