<div>
    @if (session('success'))
        <div class="alert alert-success py-2 mb-3" style="font-size:13px;">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">
                Sale — {{ $sale->bill_ref ?? '#' . $sale->id }}
                <span class="sale-status-badge {{ $sale->status }} ms-2">
                    {{ ucfirst($sale->status) }}
                </span>
            </div>
            <div class="page-subtitle">
                {{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') }}
                @if ($sale->employee)
                    · {{ $sale->employee->name }}
                @endif
            </div>
        </div>
        <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="row g-3">
        <div class="col-8">

            {{-- Customer --}}
            <div class="table-card mb-3" style="padding:16px 20px;">
                <div
                    style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:12px;">
                    Customer
                </div>
                <div class="row g-2" style="font-size:13px;">
                    <div class="col-4">
                        <div style="font-size:10px; color:var(--text-muted);">Name</div>
                        <div style="font-weight:600;">{{ $sale->customer_name }}</div>
                    </div>
                    <div class="col-4">
                        <div style="font-size:10px; color:var(--text-muted);">Phone</div>
                        <div style="font-weight:600;">{{ $sale->customer_phone1 }}</div>
                    </div>
                    @if ($sale->customer_phone2)
                        <div class="col-4">
                            <div style="font-size:10px; color:var(--text-muted);">Phone 2</div>
                            <div style="font-weight:600;">{{ $sale->customer_phone2 }}</div>
                        </div>
                    @endif
                    @if ($sale->customer_cnic)
                        <div class="col-4">
                            <div style="font-size:10px; color:var(--text-muted);">CNIC</div>
                            <div style="font-weight:600; font-family:monospace; font-size:12px;">
                                {{ $sale->customer_cnic }}</div>
                        </div>
                    @endif
                    @if ($sale->delivery_address)
                        <div class="col-8">
                            <div style="font-size:10px; color:var(--text-muted);">Address</div>
                            <div style="font-weight:600;">{{ $sale->delivery_address }}</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Items --}}
            <div class="table-card mb-3" style="padding:16px 20px;">
                <div
                    style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:12px;">
                    Sold Items
                </div>
                <table class="table mb-0" style="font-size:13px;">
                    <thead>
                        <tr>
                            <th style="font-size:10px;">Code</th>
                            <th style="font-size:10px;">Product</th>
                            <th style="font-size:10px; text-align:center;">Qty</th>
                            <th style="font-size:10px; text-align:right;">Price</th>
                            <th style="font-size:10px; text-align:right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sale->items as $item)
                            <tr>
                                <td>
                                    <span class="product-code-badge" style="font-size:10px;">
                                        {{ $item->product_code }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight:600;">{{ $item->product_name }}</div>
                                    @if ($item->custom_option_label)
                                        <div style="font-size:11px; color:var(--gold-hover);">
                                            <i class="bi bi-plus-circle me-1"></i>
                                            {{ $item->custom_option_label }}
                                            @if ($item->custom_option_price > 0)
                                                + Rs. {{ number_format($item->custom_option_price, 0) }}
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td style="text-align:center; font-weight:600;">{{ $item->qty }}</td>
                                <td style="text-align:right;">Rs. {{ number_format($item->sale_price, 0) }}</td>
                                <td style="text-align:right; font-weight:700; color:var(--navy);">
                                    Rs.
                                    {{ number_format($item->sale_price * $item->qty + $item->custom_option_price, 0) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="border-top:2px solid var(--navy);">
                            <td colspan="4"
                                style="text-align:right; font-weight:700; font-size:14px; padding-top:10px;">
                                Total
                            </td>
                            <td
                                style="text-align:right; font-weight:800; font-size:16px; color:var(--navy); padding-top:10px;">
                                Rs. {{ number_format($sale->total_amount, 0) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Notes --}}
            @if ($sale->notes)
                <div class="table-card" style="padding:14px 20px;">
                    <div
                        style="font-size:10px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:6px;">
                        Notes / Payment History
                    </div>
                    <div style="font-size:12px; white-space:pre-line; color:var(--text-primary);">{{ $sale->notes }}
                    </div>
                </div>
            @endif

        </div>

        {{-- RIGHT --}}
        <div class="col-4">

            {{-- Financial --}}
            <div class="rental-summary-box mb-3">
                <div
                    style="font-size:11px; font-weight:700; text-transform:uppercase; color:rgba(255,255,255,0.5); margin-bottom:12px;">
                    Financial
                </div>
                <div class="summary-row">
                    <span class="s-label">Total Amount</span>
                    <span class="s-value">Rs. {{ number_format($sale->total_amount, 0) }}</span>
                </div>
                <div class="summary-row">
                    <span class="s-label">Received</span>
                    <span class="s-value">Rs. {{ number_format($sale->advance_paid, 0) }}</span>
                </div>
                <div class="summary-row">
                    <span class="s-label">Payment Via</span>
                    <span class="s-value" style="font-size:11px;">
                        {{ ucfirst(str_replace('_', ' ', $sale->advance_payment_method ?? 'cash')) }}
                    </span>
                </div>

                <div class="summary-row total-row">
                    <span class="s-label">Remaining</span>
                    <span class="s-value {{ $remaining > 0 ? 'gold' : '' }}"
                        style="{{ $remaining <= 0 ? 'color:#68d391;' : '' }}">
                        Rs. {{ number_format($remaining, 0) }}
                        @if ($remaining <= 0)
                            ✓
                        @endif
                    </span>
                </div>
                @if ($sale->refund_amount > 0)
                    <div class="summary-row" style="margin-top:6px;">
                        <span class="s-label" style="color:#fc8181;">Refunded</span>
                        <span class="s-value" style="color:#fc8181;">
                            Rs. {{ number_format($sale->refund_amount, 0) }}
                        </span>
                    </div>
                @endif
            </div>

            {{-- Payment --}}
            <div class="table-card mb-3" style="padding:16px;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted);">
                        <i class="bi bi-cash me-1"></i> Add Payment
                    </div>
                    @if (!in_array($sale->status, ['cancelled', 'refunded']) && $remaining > 0)
                        <button class="btn btn-sm btn-outline-success action-btn"
                            wire:click="$set('showPaymentForm', true)">
                            <i class="bi bi-plus me-1"></i> Payment
                        </button>
                    @endif
                </div>

                @if ($showPaymentForm)
                    <div style="background:#f7fafc; border-radius:8px; padding:12px; border:1px solid var(--border);">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label">Amount (Rs.) <span class="text-danger">*</span></label>
                                <input type="number" wire:model="paymentAmount"
                                    class="form-control form-control-sm @error('paymentAmount') is-invalid @enderror"
                                    min="1" placeholder="{{ $remaining }}">
                                @error('paymentAmount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6">
                                <label class="form-label">Date</label>
                                <input type="date" wire:model="paymentDate" class="form-control form-control-sm">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Receive Into Account</label>
                                <select wire:model="paymentMethod" class="form-select form-select-sm">
                                    <option value="">Select account...</option>
                                    @foreach ($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Note</label>
                                <input type="text" wire:model="paymentNote" class="form-control form-control-sm"
                                    placeholder="Optional note">
                            </div>
                            <div class="col-12 d-flex gap-2">
                                <button class="btn btn-sm btn-success flex-fill" wire:click="addPayment"
                                    wire:loading.attr="disabled">
                                    Save
                                </button>
                                <button class="btn btn-sm btn-outline-secondary"
                                    wire:click="$set('showPaymentForm', false)">Cancel</button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Actions --}}
            <div class="table-card mb-3" style="padding:14px 16px;">
                <div
                    style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:10px;">
                    Actions
                </div>
                <div class="d-flex flex-column gap-2">
                    @if ($sale->status === 'completed' || $sale->status === 'pending')
                        <button class="btn btn-sm btn-outline-danger w-100" wire:click="cancelSale">
                            <i class="bi bi-x-circle me-1"></i> Cancel Sale
                        </button>
                    @endif
                </div>
            </div>

            {{-- Refund --}}
            @if ($showRefundForm)
                <div class="table-card mb-3" style="padding:16px; border:1.5px solid #fed7d7;">
                    <div
                        style="font-size:11px; font-weight:700; color:#c53030; text-transform:uppercase; margin-bottom:12px;">
                        Record Refund
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Refund Type</label>
                        <select wire:model.live="refundType" class="form-select form-select-sm">
                            <option value="none">No Refund</option>
                            <option value="full">Full Refund (Rs. {{ number_format($sale->advance_paid, 0) }})
                            </option>
                            <option value="partial">Partial Refund</option>
                        </select>
                    </div>
                    @if ($refundType === 'partial')
                        <div class="mb-2">
                            <label class="form-label">Amount (Rs.)</label>
                            <input type="number" wire:model="refundAmount" class="form-control form-control-sm"
                                min="0">
                        </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <textarea wire:model="refundNote" class="form-control form-control-sm" rows="2" placeholder="Reason..."></textarea>
                    </div>
                    <button class="btn btn-sm btn-danger w-100" wire:click="saveRefund">Save Refund</button>
                </div>
            @endif

            @if ($sale->refund_amount > 0 && !$showRefundForm)
                <div class="table-card" style="padding:14px 16px; border:1.5px solid #fed7d7; background:#fff5f5;">
                    <div style="font-size:11px; font-weight:700; color:#c53030; margin-bottom:6px;">Refund Recorded
                    </div>
                    <div style="font-size:12px;">
                        Amount: <strong>Rs. {{ number_format($sale->refund_amount, 0) }}</strong><br>
                        @if ($sale->refund_date)
                            Date: {{ \Carbon\Carbon::parse($sale->refund_date)->format('d/m/Y') }}<br>
                        @endif
                        @if ($sale->refund_note)
                            Note: {{ $sale->refund_note }}
                        @endif
                    </div>
                </div>
            @endif

        </div>
    </div>
    {{-- Password Confirm Modal --}}
    @if ($showCancelConfirm)
        <div class="confirm-modal-overlay">
            <div class="confirm-modal-box">
                <div class="confirm-title">
                    <i class="bi bi-shield-lock me-2" style="color:#e53e3e;"></i>
                    Cancel Sale
                </div>
                <div class="confirm-subtitle">
                    This will cancel the sale and restore stock. Enter your password to confirm.
                </div>

                <div class="mb-3">
                    <label class="form-label">Your Password <span class="text-danger">*</span></label>
                    <input type="password" wire:model="cancelPassword" wire:keydown.enter="confirmWithPassword"
                        class="form-control" placeholder="Enter your password">
                    @if ($cancelPasswordError)
                        <div style="color:#e53e3e; font-size:12px; margin-top:5px;">
                            <i class="bi bi-exclamation-circle me-1"></i>
                            {{ $cancelPasswordError }}
                        </div>
                    @endif
                </div>

                <div class="confirm-actions">
                    <button class="btn btn-sm btn-outline-secondary" wire:click="$set('showCancelConfirm', false)">
                        Cancel
                    </button>
                    <button class="btn btn-sm btn-danger" wire:click="confirmWithPassword"
                        wire:loading.attr="disabled">
                        <span wire:loading wire:target="confirmWithPassword">
                            <span class="spinner-border spinner-border-sm me-1"></span>
                        </span>
                        Yes, Cancel Sale
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
