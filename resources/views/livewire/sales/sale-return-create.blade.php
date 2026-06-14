<div>
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <div class="page-title">Sale Return</div>
            <div class="page-subtitle">
                {{ $sale->bill_ref ?? '#' . $sale->id }} · {{ $sale->customer_name }}
            </div>
        </div>
        <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Sale
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success py-2 mb-3" style="font-size:13px;">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif

    @error('returnItems')
        <div class="alert alert-danger py-2 mb-3" style="font-size:13px;">{{ $message }}</div>
    @enderror

    <div class="row g-3">
        <div class="col-8">

            {{-- Items to Return --}}
            <div class="table-card mb-3" style="padding:20px;">
                <div
                    style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:14px;">
                    <i class="bi bi-arrow-return-left me-1"></i> Select Items Being Returned
                </div>

                @if (count($returnItems) === 0)
                    <div class="alert alert-warning py-2" style="font-size:13px;">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        No items found on this sale.
                    </div>
                @else
                    <table class="table mb-0" style="font-size:12px;">
                        <thead>
                            <tr>
                                <th style="width:36px;"></th>
                                <th>Item</th>
                                <th style="text-align:center; width:80px;">Sold Qty</th>
                                <th style="text-align:center; width:90px;">Return Qty</th>
                                <th style="text-align:right; width:110px;">Unit Price</th>
                                <th style="text-align:right; width:110px;">Total</th>
                                <th style="width:130px;">Reason</th>
                                <th style="width:120px;">Condition</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($returnItems as $index => $item)
                                @php $active = $item['selected']; @endphp
                                <tr style="{{ $active ? 'background:#fff5f5;' : '' }}">
                                    <td style="text-align:center;">
                                        <input type="checkbox"
                                            wire:model.live="returnItems.{{ $index }}.selected"
                                            wire:change="recalc" class="form-check-input"
                                            style="width:16px; height:16px; cursor:pointer;">
                                    </td>
                                    <td>
                                        <div style="font-weight:600;">{{ $item['item_name'] }}</div>
                                        @if ($item['item_code'])
                                            <span class="tbl-code-badge"
                                                style="font-size:10px;">{{ $item['item_code'] }}</span>
                                        @endif
                                    </td>
                                    <td style="text-align:center; font-weight:700; color:var(--navy);">
                                        {{ $item['max_qty'] }}
                                    </td>
                                    <td style="text-align:center;">
                                        <input type="number"
                                            wire:model.lazy="returnItems.{{ $index }}.qty_returned"
                                            wire:change="recalc" class="form-control form-control-sm" min="1"
                                            max="{{ $item['max_qty'] }}" style="text-align:center; width:70px;"
                                            {{ !$active ? 'disabled' : '' }}>
                                    </td>
                                    <td style="text-align:right;">
                                        Rs. {{ number_format((float) $item['unit_price'], 0) }}
                                    </td>
                                    <td style="text-align:right; font-weight:700; color:#e53e3e;">
                                        {{ $active ? 'Rs. ' . number_format((float) $item['total_price'], 0) : '—' }}
                                    </td>
                                    <td>
                                        <select wire:model="returnItems.{{ $index }}.reason"
                                            class="form-select form-select-sm" {{ !$active ? 'disabled' : '' }}>
                                            <option value="damage">Damage</option>
                                            <option value="wrong_item">Wrong Item</option>
                                            <option value="changed_mind">Changed Mind</option>
                                            <option value="quality">Quality Issue</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select wire:model="returnItems.{{ $index }}.condition"
                                            class="form-select form-select-sm" {{ !$active ? 'disabled' : '' }}>
                                            <option value="good">Good (restore stock)</option>
                                            <option value="damaged">Damaged (no stock)</option>
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        @if ($this->total > 0)
                            <tfoot>
                                <tr>
                                    <td colspan="5"
                                        style="text-align:right; font-weight:700; padding-top:10px; font-size:13px; color:var(--text-muted);">
                                        Return Total
                                    </td>
                                    <td
                                        style="text-align:right; font-weight:800; font-size:15px; color:#e53e3e; padding-top:10px;">
                                        Rs. {{ number_format($this->total, 0) }}
                                    </td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                @endif
            </div>

            {{-- Notes --}}
            <div class="table-card" style="padding:20px;">
                <label class="form-label">Notes</label>
                <textarea wire:model="notes" class="form-control" rows="2" placeholder="Optional notes about this return..."></textarea>
            </div>
        </div>

        {{-- Right Panel --}}
        <div class="col-4">

            {{-- Return Info --}}
            <div class="table-card mb-3" style="padding:20px;">
                <div
                    style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:14px;">
                    <i class="bi bi-calendar me-1"></i> Return Details
                </div>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Return Date <span class="text-danger">*</span></label>
                        <input type="date" wire:model="returnDate"
                            class="form-control @error('returnDate') is-invalid @enderror">
                        @error('returnDate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Resolution <span class="text-danger">*</span></label>
                        <select wire:model.live="resolution"
                            class="form-select @error('resolution') is-invalid @enderror">
                            <option value="pending">Pending (not decided yet)</option>
                            <option value="refund">Refund (give money back to customer)</option>
                            <option value="replacement">Replacement (send new item to customer)</option>
                        </select>
                        @error('resolution')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Refund Details --}}
            @if ($resolution === 'refund')
                <div class="table-card mb-3" style="padding:20px; border:1.5px solid #fed7d7;">
                    <div
                        style="font-size:11px; font-weight:700; text-transform:uppercase; color:#c53030; margin-bottom:14px;">
                        <i class="bi bi-cash me-1"></i> Refund to Customer
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Refund Amount (Rs.)</label>
                            <input type="number" wire:model.lazy="refundAmount"
                                class="form-control @error('refundAmount') is-invalid @enderror" min="0"
                                placeholder="{{ $this->total }}">
                            @error('refundAmount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Pay From Account <span class="text-danger">*</span></label>
                            <select wire:model="refundAccountId"
                                class="form-select @error('refundAccountId') is-invalid @enderror">
                                <option value="">Select account...</option>
                                @foreach ($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                                @endforeach
                            </select>
                            @error('refundAccountId')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Refund Date</label>
                            <input type="date" wire:model="refundDate" class="form-control">
                        </div>
                    </div>
                </div>
            @endif

            @if ($resolution === 'replacement')
                <div class="alert alert-info py-2 mb-3" style="font-size:12px;">
                    <i class="bi bi-info-circle me-1"></i>
                    <strong>Good condition:</strong> Stock unchanged — returned item restocked, replacement item sent
                    out.<br>
                    <strong>Damaged condition:</strong> Stock decremented — damaged item not restocked, but replacement
                    sent out.
                </div>
            @endif

            {{-- Summary --}}
            <div class="table-card mb-3" style="padding:0; overflow:hidden;">
                <div style="background:var(--navy); padding:12px 16px;">
                    <div style="font-size:14px; font-weight:700; color:#fff;">{{ $sale->customer_name }}</div>
                    <div style="font-size:11px; color:rgba(255,255,255,0.6);">
                        {{ $sale->bill_ref ?? 'Sale #' . $sale->id }}
                    </div>
                </div>
                <table class="table mb-0" style="font-size:13px;">
                    <tbody>
                        <tr>
                            <td style="color:var(--text-muted);">Items Selected</td>
                            <td style="text-align:right; font-weight:700;">{{ count($this->selectedItems) }}</td>
                        </tr>
                        <tr>
                            <td style="color:var(--text-muted);">Return Value</td>
                            <td style="text-align:right; font-weight:800; font-size:15px; color:#e53e3e;">
                                Rs. {{ number_format($this->total, 0) }}
                            </td>
                        </tr>
                        @if ($resolution === 'refund' && (float) $refundAmount > 0)
                            <tr style="background:#fff5f5;">
                                <td style="color:#c53030; font-weight:600;">Refund Amount</td>
                                <td style="text-align:right; font-weight:800; color:#c53030;">
                                    Rs. {{ number_format((float) $refundAmount, 0) }}
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <button class="btn btn-danger w-100" style="height:44px; font-size:14px; font-weight:700;"
                wire:click="save" wire:loading.attr="disabled" @if (count($this->selectedItems) === 0) disabled @endif>
                <span wire:loading wire:target="save">
                    <span class="spinner-border spinner-border-sm me-2"></span>
                </span>
                <i class="bi bi-arrow-return-left me-2"></i> Submit Return
            </button>
        </div>
    </div>
</div>
