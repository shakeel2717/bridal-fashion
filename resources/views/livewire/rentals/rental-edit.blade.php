<div>
    @if (session('success'))
        <div class="alert alert-success py-2 mb-3" style="font-size:13px;">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <div class="page-title">
                <div class="page-title">Edit Rental — {{ $rental->bill_ref ?? '#' . $rental->id }}</div>
            </div>
            <div class="page-subtitle">Update rental details</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('rentals.show', $rental->id) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Detail
            </a>
        </div>
    </div>

    <div class="row g-3">

        {{-- LEFT: Main Form --}}
        <div class="col-8">

            {{-- Customer Info --}}
            <div class="table-card mb-3" style="padding:20px;">
                <div
                    style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:14px;">
                    <i class="bi bi-person me-1"></i> Customer Information
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" wire:model="customerName"
                            class="form-control @error('customerName') is-invalid @enderror"
                            placeholder="Customer name">
                        @error('customerName')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-3">
                        <div style="font-size:10px; color:var(--text-muted);">Phone 1</div>
                        <div style="font-weight:600;">
                            {{ $rental->customer_phone1 }}
                            <span
                                style="font-size:10px; padding:1px 6px; border-radius:3px; margin-left:4px;
              background:{{ ($rental->phone1_gender ?? 'male') === 'female' ? '#fff5f7' : '#ebf8ff' }};
              color:{{ ($rental->phone1_gender ?? 'male') === 'female' ? '#97266d' : '#2c5282' }};">
                                {{ ($rental->phone1_gender ?? 'male') === 'female' ? '♀ F' : '♂ M' }}
                            </span>
                        </div>
                    </div>

                    @if ($rental->customer_phone2)
                        <div class="col-3">
                            <div style="font-size:10px; color:var(--text-muted);">Phone 2</div>
                            <div style="font-weight:600;">
                                {{ $rental->customer_phone2 }}
                                <span
                                    style="font-size:10px; padding:1px 6px; border-radius:3px; margin-left:4px;
              background:{{ ($rental->phone2_gender ?? 'male') === 'female' ? '#fff5f7' : '#ebf8ff' }};
              color:{{ ($rental->phone2_gender ?? 'male') === 'female' ? '#97266d' : '#2c5282' }};">
                                    {{ ($rental->phone2_gender ?? 'male') === 'female' ? '♀ F' : '♂ M' }}
                                </span>
                            </div>
                        </div>
                    @endif

                    @if ($rental->customer_whatsapp)
                        <div class="col-3">
                            <div style="font-size:10px; color:var(--text-muted);">WhatsApp</div>
                            <div style="font-weight:600;">
                                {{ $rental->customer_whatsapp }}
                                <span
                                    style="font-size:10px; padding:1px 6px; border-radius:3px; margin-left:4px;
              background:{{ ($rental->whatsapp_gender ?? 'male') === 'female' ? '#fff5f7' : '#ebf8ff' }};
              color:{{ ($rental->whatsapp_gender ?? 'male') === 'female' ? '#97266d' : '#2c5282' }};">
                                    {{ ($rental->whatsapp_gender ?? 'male') === 'female' ? '♀ F' : '♂ M' }}
                                </span>
                            </div>
                        </div>
                    @endif
                    <div class="col-6">
                        <label class="form-label">CNIC</label>
                        <input type="text" wire:model="customerCnic" class="form-control"
                            placeholder="00000-0000000-0">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Delivery Address</label>
                        <input type="text" wire:model="deliveryAddress" class="form-control" placeholder="Address">
                    </div>
                </div>
            </div>

            {{-- Items --}}
            <div class="table-card mb-3" style="padding:20px;">
                <div
                    style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:14px;">
                    <i class="bi bi-box-seam me-1"></i> Rented Items
                </div>

                {{-- Product Search --}}
                <div style="position:relative; margin-bottom:16px;">
                    <input type="text" wire:model.live.debounce.300ms="productSearch" wire:keyup="searchProducts"
                        class="form-control form-control-sm" placeholder="Search product code or name to add...">
                    <i class="bi bi-search"
                        style="position:absolute; right:12px; top:8px; color:var(--text-muted); font-size:13px;"></i>

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
                </div>

                {{-- Items List --}}
                @forelse($items as $index => $item)
                    <div class="rental-item-row mb-3">

                        {{-- Remove confirm inline --}}
                        @if ($removeItemId === $index)
                            <div
                                style="background:#fff5f5; border-radius:6px; padding:10px 12px; margin-bottom:10px; font-size:12px; display:flex; align-items:center; justify-content:space-between;">
                                <span style="color:#c53030; font-weight:600;">Remove this item?</span>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-danger action-btn"
                                        wire:click="removeItem({{ $index }})">Yes, Remove</button>
                                    <button class="btn btn-sm btn-outline-secondary action-btn"
                                        wire:click="$set('removeItemId', null)">Cancel</button>
                                </div>
                            </div>
                        @else
                            <button class="item-remove-btn" wire:click="confirmRemoveItem({{ $index }})">
                                <i class="bi bi-x"></i>
                            </button>
                        @endif

                        {{-- Item Header --}}
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <span class="item-code">{{ $item['code'] }}</span>
                            <div>
                                <div class="item-name">{{ $item['name'] }}</div>
                            </div>
                        </div>

                        {{-- Price + Note --}}
                        <div class="row g-2 mb-2">
                            <div class="col-4">
                                <label
                                    style="font-size:10px; font-weight:600; color:var(--text-muted); text-transform:uppercase;">
                                    Rental Price (Rs.)
                                </label>
                                <input type="number" wire:model.lazy="items.{{ $index }}.rental_price"
                                    wire:change="recalcTotal" class="form-control form-control-sm" min="0">
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
                @empty
                    <div
                        style="text-align:center; padding:20px; color:var(--text-muted); font-size:13px; border:2px dashed var(--border); border-radius:8px;">
                        <i class="bi bi-box-seam" style="font-size:28px; display:block; margin-bottom:6px;"></i>
                        No items — search above to add
                    </div>
                @endforelse
            </div>

            {{-- Dates & Details --}}
            <div class="table-card mb-3" style="padding:20px;">
                <div
                    style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:14px;">
                    <i class="bi bi-calendar me-1"></i> Dates & Details
                </div>
                <div class="row g-3">
                    <div class="col-4">
                        <label class="form-label">Bill Book Ref</label>
                        <input type="text" wire:model="billRef" class="form-control" placeholder="e.g. B-1042">
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
                    <div class="col-4">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select wire:model="status" class="form-select @error('status') is-invalid @enderror">
                            <option value="booked">Booked</option>
                            <option value="ready">Ready</option>
                            <option value="picked_up">Picked Up</option>
                            <option value="partially_picked_up">Partially Picked Up</option>
                            <option value="returned">Returned</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="abandoned">Abandoned</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
                </div>
            </div>

        </div>

        {{-- RIGHT: Summary + Save --}}
        <div class="col-4">

            {{-- Financial --}}
            <div class="table-card mb-3" style="padding:16px;">
                <div
                    style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:12px;">
                    Payment Summary
                </div>

                <div class="mb-3">
                    <label class="form-label">Total Amount (Rs.)</label>
                    <input type="number" wire:model.lazy="totalAmount" wire:change="recalcTotal"
                        class="form-control" style="font-weight:700; color:var(--navy);">
                </div>
                <div class="mb-3">
                    <label class="form-label">Advance Paid (Rs.)</label>
                    <input type="number" wire:model.lazy="advancePaid" wire:change="recalcTotal"
                        class="form-control @error('advancePaid') is-invalid @enderror">
                    @error('advancePaid')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div style="background:#f7fafc; border-radius:8px; padding:12px; border:1px solid var(--border);">
                    <div style="font-size:11px; color:var(--text-muted);">Remaining Balance</div>
                    <div
                        style="font-size:20px; font-weight:800; color:{{ (float) $remainingBalance > 0 ? '#e53e3e' : '#38a169' }};">
                        Rs. {{ number_format(max(0, (float) $totalAmount - (float) $advancePaid), 0) }}
                    </div>
                </div>
            </div>

            {{-- Rental Summary --}}
            <div class="rental-summary-box mb-3">
                <div
                    style="font-size:11px; font-weight:700; text-transform:uppercase; color:rgba(255,255,255,0.5); margin-bottom:12px;">
                    Rental Summary
                </div>
                <div class="summary-row">
                    <span class="s-label">Customer</span>
                    <span class="s-value">{{ $customerName ?: '—' }}</span>
                </div>
                <div class="summary-row">
                    <span class="s-label">Items</span>
                    <span class="s-value">{{ count($items) }}</span>
                </div>
                <div class="summary-row">
                    <span class="s-label">Status</span>
                    <span class="s-value">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                </div>
                @if ($pickupDate)
                    <div class="summary-row">
                        <span class="s-label">Pickup</span>
                        <span class="s-value">{{ Carbon\Carbon::parse($pickupDate)->format('d/m/Y') }}</span>
                    </div>
                @endif
                @if ($returnDate)
                    <div class="summary-row">
                        <span class="s-label">Return</span>
                        <span class="s-value">{{ Carbon\Carbon::parse($returnDate)->format('d/m/Y') }}</span>
                    </div>
                @endif
                <div class="summary-row total-row">
                    <span class="s-label">Total</span>
                    <span class="s-value gold">Rs. {{ number_format((float) $totalAmount, 0) }}</span>
                </div>
            </div>

            {{-- Save Button --}}
            <button class="btn btn-primary w-100" style="height:46px; font-size:14px; font-weight:700;"
                wire:click="save" wire:loading.attr="disabled">
                <span wire:loading wire:target="save">
                    <span class="spinner-border spinner-border-sm me-2"></span>
                </span>
                <i class="bi bi-check-circle me-2"></i> Save Changes
            </button>

            <a href="{{ route('rentals.show', $rental->id) }}" class="btn btn-outline-secondary w-100 mt-2">
                Cancel Changes
            </a>
        </div>
    </div>
</div>
