<div>
    @if (session('success'))
        <div class="alert alert-success py-2 mb-3" style="font-size:13px;">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">
                {{ $po->po_number }}
                <span class="po-status-badge {{ $po->status }} ms-2">
                    {{ ucfirst($po->status) }}
                </span>
            </div>
            <div class="page-subtitle">
                {{ \Carbon\Carbon::parse($po->order_date)->format('d/m/Y') }}
                · {{ $po->vendor->name }}
                @if ($po->vendor_bill_number)
                    · Bill: <span style="font-family:monospace;">{{ $po->vendor_bill_number }}</span>
                @endif
            </div>
        </div>
        <a href="{{ route('purchase-orders.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="row g-3">
        <div class="col-8">

            {{-- Items --}}
            <div class="table-card mb-3" style="padding:16px 20px;">
                <div class="d-flex justify-content-between align-items-center mb-12">
                    <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted);">
                        <i class="bi bi-box me-1"></i> Items
                    </div>
                    @if (!in_array($po->status, ['received', 'cancelled']))
                        <button class="btn btn-sm btn-outline-success action-btn" wire:click="markReceived">
                            <i class="bi bi-check-all me-1"></i> Mark All Received
                        </button>
                    @endif
                </div>

                <table class="table mb-0" style="font-size:12px; margin-top:10px;">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th style="text-align:center;">Ordered</th>
                            <th style="text-align:center;">Received</th>
                            <th style="text-align:right;">Unit Price</th>
                            <th style="text-align:right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($po->items as $item)
                            <tr>
                                <td>
                                    <div style="font-weight:600;">{{ $item->item_name }}</div>
                                    @if ($item->item_code)
                                        <span class="tbl-code-badge"
                                            style="font-size:10px;">{{ $item->item_code }}</span>
                                    @endif
                                    @if ($item->product)
                                        <div style="font-size:10px; color:var(--text-muted);">Linked to product</div>
                                    @endif
                                </td>
                                <td style="text-align:center; font-weight:600;">{{ $item->qty }}</td>
                                <td style="text-align:center;">
                                    <span
                                        style="font-weight:700; color:{{ $item->received_qty >= $item->qty ? '#276749' : '#b7791f' }};">
                                        {{ $item->received_qty }}
                                    </span>
                                    @if ($item->received_qty < $item->qty && !in_array($po->status, ['cancelled']))
                                        <button class="btn btn-sm btn-outline-success action-btn ms-1"
                                            wire:click="markItemReceived({{ $item->id }}, {{ $item->qty }})"
                                            title="Mark received">
                                            <i class="bi bi-check" style="font-size:11px;"></i>
                                        </button>
                                    @endif
                                </td>
                                <td style="text-align:right;">Rs. {{ number_format($item->unit_price, 0) }}</td>
                                <td style="text-align:right; font-weight:700;">
                                    Rs. {{ number_format($item->total_price, 0) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        @if ($po->discount > 0)
                            <tr>
                                <td colspan="4" style="text-align:right; font-size:12px; color:var(--text-muted);">
                                    Discount</td>
                                <td style="text-align:right; color:#e53e3e; font-weight:600;">
                                    - Rs. {{ number_format($po->discount, 0) }}
                                </td>
                            </tr>
                        @endif
                        <tr style="border-top:2px solid var(--navy);">
                            <td colspan="4"
                                style="text-align:right; font-weight:700; font-size:14px; padding-top:10px;">
                                Total
                            </td>
                            <td
                                style="text-align:right; font-weight:800; font-size:16px; color:var(--navy); padding-top:10px;">
                                Rs. {{ number_format($po->total_amount, 0) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Notes --}}
            @if ($po->notes)
                <div class="table-card" style="padding:14px 20px;">
                    <div
                        style="font-size:10px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:6px;">
                        Notes</div>
                    <div style="font-size:13px;">{{ $po->notes }}</div>
                </div>
            @endif
        </div>

        {{-- Right --}}
        <div class="col-4">

            {{-- Financial --}}
            <div class="po-vendor-card mb-3">
                <div
                    style="font-size:10px; font-weight:700; text-transform:uppercase; color:var(--navy-muted); margin-bottom:8px;">
                    Vendor
                </div>
                <div class="po-vendor-name">{{ $po->vendor->name }}</div>
                @if ($po->vendor->phone)
                    <div class="po-vendor-phone">{{ $po->vendor->phone }}</div>
                @endif

                <div style="border-top:1px solid rgba(255,255,255,0.1); padding-top:12px; margin-top:12px;">
                    <div class="summary-row">
                        <span class="s-label">Total Amount</span>
                        <span class="s-value">Rs. {{ number_format($po->total_amount, 0) }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="s-label">Amount Paid</span>
                        <span class="s-value">Rs. {{ number_format($totalPaid, 0) }}</span>
                    </div>
                    <div class="summary-row total-row">
                        <span class="s-label">Balance Due</span>
                        <span class="s-value {{ $balanceDue > 0 ? '' : '' }}"
                            style="color:{{ $balanceDue > 0 ? '#fc8181' : '#68d391' }};">
                            Rs. {{ number_format($balanceDue, 0) }}
                            @if ($balanceDue <= 0)
                                ✓
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            {{-- Payments --}}
            <div class="table-card mb-3" style="padding:16px;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted);">
                        <i class="bi bi-cash me-1"></i> Payments to Vendor
                    </div>
                    @if (!in_array($po->status, ['cancelled']) && $balanceDue > 0)
                        <button class="btn btn-sm btn-outline-success action-btn"
                            wire:click="$set('showPaymentForm', true)">
                            <i class="bi bi-plus me-1"></i> Pay
                        </button>
                    @endif
                </div>

                @if ($showPaymentForm)
                    <div
                        style="background:#f7fafc; border-radius:8px; padding:12px; border:1px solid var(--border); margin-bottom:12px;">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label">Amount (Rs.) <span class="text-danger">*</span></label>
                                <input type="number" wire:model="paymentAmount" class="form-control form-control-sm"
                                    min="1" placeholder="{{ $balanceDue }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Date</label>
                                <input type="date" wire:model="paymentDate" class="form-control form-control-sm">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Pay From Account</label>
                                <select wire:model="paymentAccountId"
                                    class="form-select form-select-sm @error('paymentAccountId') is-invalid @enderror">
                                    <option value="">Select account...</option>
                                    @foreach ($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                                    @endforeach
                                </select>
                                @error('paymentAccountId')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <input type="text" wire:model="paymentNote" class="form-control form-control-sm"
                                    placeholder="Note (optional)">
                            </div>
                            <div class="col-12 d-flex gap-2">
                                <button class="btn btn-sm btn-success flex-fill" wire:click="addPayment"
                                    wire:loading.attr="disabled">
                                    Save Payment
                                </button>
                                <button class="btn btn-sm btn-outline-secondary"
                                    wire:click="$set('showPaymentForm', false)">Cancel</button>
                            </div>
                        </div>
                    </div>
                @endif

                @forelse($po->payments as $payment)
                    <div class="po-payment-row">
                        <div>
                            <div style="font-weight:600;">
                                Rs. {{ number_format($payment->amount, 0) }}
                                <span
                                    style="font-size:10px; background:#f0fff4; color:#276749; padding:1px 6px; border-radius:3px; margin-left:4px;">
                                    {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                </span>
                            </div>
                            <div style="font-size:10px; color:var(--text-muted);">
                                {{ $payment->payment_date->format('d/m/Y') }}
                                · {{ $payment->createdBy?->name ?? 'System' }}
                                @if ($payment->note)
                                    · {{ $payment->note }}
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="font-size:12px; color:var(--text-muted); text-align:center; padding:10px 0;">
                        No payments recorded
                    </div>
                @endforelse
            </div>

            {{-- Actions --}}
            <div class="table-card" style="padding:14px 16px;">
                <div
                    style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:10px;">
                    Actions
                </div>
                <div class="d-flex flex-column gap-2">
                    @if ($po->status === 'draft')
                        <button class="btn btn-sm btn-outline-primary w-100"
                            wire:click="$set('po.status', 'ordered')">
                            <i class="bi bi-send me-1"></i> Confirm Order
                        </button>
                    @endif

                    @if (in_array($po->status, ['received', 'partial']))
                        <a href="{{ route('purchase-orders.return', $po->id) }}"
                            class="btn btn-sm btn-outline-warning w-100">
                            <i class="bi bi-box-arrow-left me-1"></i> Return to Vendor
                        </a>
                    @endif

                    <a href="{{ route('purchase-orders.edit', $po->id) }}"
                        class="btn btn-sm btn-outline-secondary w-100">
                        <i class="bi bi-pencil me-1"></i> Edit Order
                    </a>

                    @if (!in_array($po->status, ['received', 'cancelled']))
                        <button class="btn btn-sm btn-outline-danger w-100" wire:click="cancelOrder">
                            <i class="bi bi-x-circle me-1"></i> Cancel Order
                        </button>
                    @endif

                    @if ($po->received_date)
                        <div style="font-size:11px; color:var(--text-muted); text-align:center; padding:4px 0;">
                            Received: {{ \Carbon\Carbon::parse($po->received_date)->format('d/m/Y') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Returns History --}}
    @if ($po->returns->count() > 0)
        <div class="table-card mt-3" style="padding:16px 20px;">
            <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:12px;">
                <i class="bi bi-box-arrow-left me-1"></i> Returns History
            </div>
            <table class="table mb-0" style="font-size:12px;">
                <thead>
                    <tr>
                        <th>Return #</th>
                        <th>Date</th>
                        <th style="text-align:center;">Items</th>
                        <th style="text-align:right;">Value</th>
                        <th>Resolution</th>
                        <th>Status</th>
                        <th style="text-align:right;">Refund</th>
                        <th style="width:80px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($po->returns as $ret)
                        <tr>
                            <td style="font-family:monospace; font-weight:700; color:var(--navy);">
                                {{ $ret->return_number }}
                            </td>
                            <td>{{ $ret->return_date->format('d/m/Y') }}</td>
                            <td style="text-align:center;">{{ $ret->items->count() }}</td>
                            <td style="text-align:right; font-weight:700; color:#e53e3e;">
                                Rs. {{ number_format($ret->total_amount, 0) }}
                            </td>
                            <td>
                                @php
                                    $resColor = match($ret->resolution) {
                                        'refund'      => ['bg' => '#fff5f5', 'text' => '#c53030'],
                                        'replacement' => ['bg' => '#ebf8ff', 'text' => '#2c5282'],
                                        default       => ['bg' => '#fffbeb', 'text' => '#b7791f'],
                                    };
                                @endphp
                                <span style="font-size:11px; background:{{ $resColor['bg'] }}; color:{{ $resColor['text'] }}; padding:2px 8px; border-radius:4px; font-weight:600;">
                                    {{ ucfirst($ret->resolution) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $stColor = match($ret->status) {
                                        'resolved' => ['bg' => '#f0fff4', 'text' => '#276749'],
                                        'sent'     => ['bg' => '#ebf8ff', 'text' => '#2c5282'],
                                        default    => ['bg' => '#fffbeb', 'text' => '#b7791f'],
                                    };
                                @endphp
                                <span style="font-size:11px; background:{{ $stColor['bg'] }}; color:{{ $stColor['text'] }}; padding:2px 8px; border-radius:4px; font-weight:600;">
                                    {{ ucfirst($ret->status) }}
                                </span>
                            </td>
                            <td style="text-align:right; color:#276749; font-weight:700;">
                                {{ $ret->refund_amount ? 'Rs. '.number_format($ret->refund_amount, 0) : '—' }}
                            </td>
                            <td style="text-align:center;">
                                @if ($ret->resolution === 'pending' || $ret->status !== 'resolved')
                                    <button class="btn btn-sm btn-outline-warning action-btn"
                                        wire:click="openResolveReturn({{ $ret->id }})"
                                        title="Resolve this return">
                                        <i class="bi bi-pencil" style="font-size:11px;"></i> Resolve
                                    </button>
                                @else
                                    <span style="font-size:11px; color:#276749;">
                                        <i class="bi bi-check-circle-fill"></i>
                                    </span>
                                @endif
                            </td>
                        </tr>

                        {{-- Inline Resolve Form --}}
                        @if ($resolvingReturnId === $ret->id)
                            <tr>
                                <td colspan="8" style="padding:0;">
                                    <div style="background:#fffbeb; border:1.5px solid #f6e05e; border-radius:8px; padding:16px; margin:4px 8px 8px;">
                                        <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:#b7791f; margin-bottom:12px;">
                                            <i class="bi bi-patch-check me-1"></i> Resolve Return {{ $ret->return_number }}
                                            <span style="font-size:10px; font-weight:400; color:#718096; margin-left:8px;">
                                                Returned items value: Rs. {{ number_format($ret->total_amount, 0) }}
                                            </span>
                                        </div>

                                        <div class="row g-3 align-items-end">
                                            <div class="col-auto">
                                                <label class="form-label">Resolution <span class="text-danger">*</span></label>
                                                <select wire:model.live="resolveResolution" class="form-select form-select-sm" style="width:220px;">
                                                    <option value="refund">Refund — vendor sends money back</option>
                                                    <option value="replacement">Replacement — vendor sends new items</option>
                                                </select>
                                            </div>
                                            <div class="col-auto">
                                                <label class="form-label">Update Status</label>
                                                <select wire:model="resolveStatus" class="form-select form-select-sm" style="width:150px;">
                                                    <option value="sent">Sent (items dispatched)</option>
                                                    <option value="resolved">Resolved (fully done)</option>
                                                </select>
                                            </div>

                                            @if ($resolveResolution === 'refund')
                                                <div class="col-auto">
                                                    <label class="form-label">Refund Amount (Rs.)</label>
                                                    <input type="number" wire:model="resolveRefundAmount"
                                                        class="form-control form-control-sm @error('resolveRefundAmount') is-invalid @enderror"
                                                        style="width:130px;" min="0">
                                                    @error('resolveRefundAmount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="col-auto">
                                                    <label class="form-label">Receive Into Account <span class="text-danger">*</span></label>
                                                    <select wire:model="resolveRefundAccountId"
                                                        class="form-select form-select-sm @error('resolveRefundAccountId') is-invalid @enderror"
                                                        style="width:180px;">
                                                        <option value="">Select account...</option>
                                                        @foreach ($accounts as $acc)
                                                            <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('resolveRefundAccountId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="col-auto">
                                                    <label class="form-label">Refund Date</label>
                                                    <input type="date" wire:model="resolveRefundDate"
                                                        class="form-control form-control-sm" style="width:150px;">
                                                </div>
                                            @endif

                                            @if ($resolveResolution === 'replacement')
                                                <div class="col-auto">
                                                    <div class="alert alert-info py-1 mb-0" style="font-size:11px;">
                                                        <i class="bi bi-info-circle me-1"></i>
                                                        When replacement items arrive, receive them via a new PO or manually update stock.
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="col-auto ms-auto d-flex gap-2 align-items-end">
                                                <button class="btn btn-sm btn-outline-secondary"
                                                    wire:click="cancelResolve">Cancel</button>
                                                <button class="btn btn-sm btn-warning fw-700"
                                                    wire:click="saveReturnResolution"
                                                    wire:loading.attr="disabled">
                                                    <span wire:loading wire:target="saveReturnResolution">
                                                        <span class="spinner-border spinner-border-sm me-1"></span>
                                                    </span>
                                                    <i class="bi bi-check-lg me-1"></i> Save Resolution
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
