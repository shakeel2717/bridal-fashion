<div>
    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- Day Navigation Header                                   --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">Daily Close Report</div>
            <div class="page-subtitle">روزانہ بند کاروبار کا خلاصہ</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button wire:click="prevDay" class="btn btn-outline-secondary btn-sm" style="padding:5px 14px;">
                <i class="bi bi-chevron-left"></i> Prev
            </button>
            <div style="min-width:200px; text-align:center; font-weight:700; font-size:14px; color:var(--navy);">
                {{ $dateLabel }}
                @if($isToday)
                    <span style="font-size:10px; background:#ebfbee; color:#276749; padding:2px 8px; border-radius:10px; margin-left:5px; font-weight:700; vertical-align:middle;">TODAY</span>
                @endif
            </div>
            <button wire:click="nextDay" class="btn btn-outline-secondary btn-sm" style="padding:5px 14px;"
                @if($isToday) disabled @endif>
                Next <i class="bi bi-chevron-right"></i>
            </button>
            @if(!$isToday)
                <button wire:click="goToday" class="btn btn-primary btn-sm" style="padding:5px 14px;">
                    <i class="bi bi-calendar-check"></i> Today
                </button>
            @endif
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- Summary Row                                            --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="row g-2 mb-3">
        <div class="col">
            <div style="background:#ebf8ff; border:1px solid #bee3f8; border-radius:10px; padding:14px 16px; text-align:center;">
                <div style="font-size:11px; color:#2c5282; font-weight:700; text-transform:uppercase; margin-bottom:4px;">
                    <i class="bi bi-box-seam-fill me-1"></i> Rentals Booked
                </div>
                <div style="font-size:24px; font-weight:800; color:#2c5282;">{{ $rentalsBooked->count() }}</div>
                <div style="font-size:11px; color:#4a90c4;">Rs. {{ number_format($totalRentalValue, 0) }}</div>
            </div>
        </div>
        <div class="col">
            <div style="background:#f0fff4; border:1px solid #9ae6b4; border-radius:10px; padding:14px 16px; text-align:center;">
                <div style="font-size:11px; color:#276749; font-weight:700; text-transform:uppercase; margin-bottom:4px;">
                    <i class="bi bi-cart-check-fill me-1"></i> Sales
                </div>
                <div style="font-size:24px; font-weight:800; color:#276749;">{{ $sales->count() }}</div>
                <div style="font-size:11px; color:#48bb78;">Rs. {{ number_format($totalSaleValue, 0) }}</div>
            </div>
        </div>
        <div class="col">
            <div style="background:#fffff0; border:1px solid #f6e05e; border-radius:10px; padding:14px 16px; text-align:center;">
                <div style="font-size:11px; color:#744210; font-weight:700; text-transform:uppercase; margin-bottom:4px;">
                    <i class="bi bi-cash-coin me-1"></i> Cash In
                </div>
                <div style="font-size:24px; font-weight:800; color:#744210;">Rs. {{ number_format($totalCashIn, 0) }}</div>
                <div style="font-size:11px; color:#975a16;">Payments + Advances</div>
            </div>
        </div>
        <div class="col">
            <div style="background:#fff5f5; border:1px solid #fed7d7; border-radius:10px; padding:14px 16px; text-align:center;">
                <div style="font-size:11px; color:#c53030; font-weight:700; text-transform:uppercase; margin-bottom:4px;">
                    <i class="bi bi-receipt-cutoff me-1"></i> Expenses
                </div>
                <div style="font-size:24px; font-weight:800; color:#c53030;">Rs. {{ number_format($totalExpenses, 0) }}</div>
                <div style="font-size:11px; color:#e53e3e;">{{ $expenses->count() }} entries</div>
            </div>
        </div>
        <div class="col">
            @php $pos = $netCash >= 0; @endphp
            <div style="background:{{ $pos ? '#f0fff4' : '#fff5f5' }}; border:1px solid {{ $pos ? '#9ae6b4' : '#fed7d7' }}; border-radius:10px; padding:14px 16px; text-align:center;">
                <div style="font-size:11px; color:{{ $pos ? '#276749' : '#c53030' }}; font-weight:700; text-transform:uppercase; margin-bottom:4px;">
                    <i class="bi bi-arrow-left-right me-1"></i> Net Cash
                </div>
                <div style="font-size:24px; font-weight:800; color:{{ $pos ? '#276749' : '#c53030' }};">
                    {{ $pos ? '+' : '' }}Rs. {{ number_format($netCash, 0) }}
                </div>
                <div style="font-size:11px; color:var(--text-muted);">In − Out</div>
            </div>
        </div>
        <div class="col">
            <div style="background:#f8f4ff; border:1px solid #d6bcfa; border-radius:10px; padding:14px 16px; text-align:center;">
                <div style="font-size:11px; color:#553c9a; font-weight:700; text-transform:uppercase; margin-bottom:4px;">
                    <i class="bi bi-safe2 me-1"></i> Cash in Hand
                </div>
                <div style="font-size:20px; font-weight:800; color:#553c9a;">Rs. {{ number_format($cashInHand, 0) }}</div>
                <div style="font-size:11px; color:#805ad5;">All: Rs. {{ number_format($totalAllAccounts, 0) }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3">

        {{-- LEFT COLUMN ──────────────────────────────────────── --}}
        <div class="col-8">

            {{-- Rentals Booked Today --}}
            <div class="table-card mb-3">
                <div class="table-card-header">
                    <span class="table-card-title">
                        <i class="bi bi-box-seam me-1"></i> Rentals Booked
                        <span style="background:#ebf8ff; color:#2c5282; padding:1px 8px; border-radius:8px; font-size:11px; margin-left:4px;">{{ $rentalsBooked->count() }}</span>
                    </span>
                    <span style="font-size:12px; color:var(--text-muted);">Total Value: <strong>Rs. {{ number_format($totalRentalValue, 0) }}</strong> &nbsp;|&nbsp; Advance: <strong>Rs. {{ number_format($totalRentalAdvance, 0) }}</strong></span>
                </div>
                @if($rentalsBooked->isEmpty())
                    <div class="text-center py-3" style="font-size:13px; color:var(--text-muted);">No rentals booked on this day.</div>
                @else
                <table class="table table-hover mb-0" style="font-size:12px;">
                    <thead>
                        <tr>
                            <th>Bill#</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Pickup</th>
                            <th>Return</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Advance</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rentalsBooked as $r)
                        <tr>
                            <td><a href="{{ route('rentals.show', $r->id) }}" style="font-weight:600; text-decoration:none; color:var(--navy);">{{ $r->bill_ref }}</a></td>
                            <td>
                                <div style="font-weight:600;">{{ $r->customer_name }}</div>
                                <div style="font-size:11px; color:var(--text-muted);">{{ $r->customer_phone1 }}</div>
                            </td>
                            <td>
                                @foreach($r->items->take(3) as $item)
                                    <span style="font-size:10px; background:#f7fafc; border:1px solid var(--border); padding:1px 5px; border-radius:3px; margin-right:2px;">{{ $item->product_code }}</span>
                                @endforeach
                                @if($r->items->count() > 3)
                                    <span style="font-size:10px; color:var(--text-muted);">+{{ $r->items->count()-3 }}</span>
                                @endif
                            </td>
                            <td style="white-space:nowrap;">{{ $r->pickup_date ? \Carbon\Carbon::parse($r->pickup_date)->format('d/m/Y') : '—' }}</td>
                            <td style="white-space:nowrap;">{{ $r->return_date ? \Carbon\Carbon::parse($r->return_date)->format('d/m/Y') : '—' }}</td>
                            <td class="text-end fw-700">Rs. {{ number_format($r->total_amount, 0) }}</td>
                            <td class="text-end">Rs. {{ number_format($r->advance_paid, 0) }}</td>
                            <td>
                                <span class="badge-status {{ $r->status }}">{{ ucfirst(str_replace('_',' ',$r->status)) }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot style="background:#f8fafc;">
                        <tr>
                            <td colspan="5" style="font-weight:700; font-size:12px; padding:8px 12px;">Totals</td>
                            <td class="text-end fw-700" style="padding:8px 12px;">Rs. {{ number_format($totalRentalValue, 0) }}</td>
                            <td class="text-end fw-700" style="padding:8px 12px;">Rs. {{ number_format($totalRentalAdvance, 0) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
                @endif
            </div>

            {{-- Sales Today --}}
            <div class="table-card mb-3">
                <div class="table-card-header">
                    <span class="table-card-title">
                        <i class="bi bi-cart-check me-1"></i> Sales
                        <span style="background:#f0fff4; color:#276749; padding:1px 8px; border-radius:8px; font-size:11px; margin-left:4px;">{{ $sales->count() }}</span>
                    </span>
                    <span style="font-size:12px; color:var(--text-muted);">Total: <strong>Rs. {{ number_format($totalSaleValue, 0) }}</strong> &nbsp;|&nbsp; Advance: <strong>Rs. {{ number_format($totalSaleAdvance, 0) }}</strong></span>
                </div>
                @if($sales->isEmpty())
                    <div class="text-center py-3" style="font-size:13px; color:var(--text-muted);">No sales on this day.</div>
                @else
                <table class="table table-hover mb-0" style="font-size:12px;">
                    <thead>
                        <tr>
                            <th>Bill#</th>
                            <th>Customer</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Advance</th>
                            <th class="text-end">Balance</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sales as $s)
                        <tr>
                            <td><a href="{{ route('sales.show', $s->id) }}" style="font-weight:600; text-decoration:none; color:var(--navy);">{{ $s->bill_ref }}</a></td>
                            <td>
                                <div style="font-weight:600;">{{ $s->customer_name }}</div>
                                <div style="font-size:11px; color:var(--text-muted);">{{ $s->customer_phone1 }}</div>
                            </td>
                            <td class="text-end fw-700">Rs. {{ number_format($s->total_amount, 0) }}</td>
                            <td class="text-end">Rs. {{ number_format($s->advance_paid, 0) }}</td>
                            <td class="text-end" style="color:{{ $s->remaining_balance > 0 ? '#c53030' : '#276749' }}; font-weight:600;">
                                Rs. {{ number_format($s->remaining_balance, 0) }}
                            </td>
                            <td><span class="badge-status {{ $s->status }}">{{ ucfirst($s->status) }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot style="background:#f8fafc;">
                        <tr>
                            <td colspan="2" style="font-weight:700; font-size:12px; padding:8px 12px;">Totals</td>
                            <td class="text-end fw-700" style="padding:8px 12px;">Rs. {{ number_format($totalSaleValue, 0) }}</td>
                            <td class="text-end fw-700" style="padding:8px 12px;">Rs. {{ number_format($totalSaleAdvance, 0) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
                @endif
            </div>

            {{-- Rental Payments Received --}}
            <div class="table-card mb-3">
                <div class="table-card-header">
                    <span class="table-card-title">
                        <i class="bi bi-cash-coin me-1"></i> Rental Payments Received
                        <span style="background:#fffff0; color:#744210; padding:1px 8px; border-radius:8px; font-size:11px; margin-left:4px;">{{ $rentalPayments->count() }}</span>
                    </span>
                    <span style="font-size:12px; color:var(--text-muted);">Total: <strong style="color:#276749;">Rs. {{ number_format($totalRentalPayments, 0) }}</strong></span>
                </div>
                @if($rentalPayments->isEmpty())
                    <div class="text-center py-3" style="font-size:13px; color:var(--text-muted);">No rental payments on this day.</div>
                @else
                <table class="table table-hover mb-0" style="font-size:12px;">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Bill#</th>
                            <th>Method</th>
                            <th>Note</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rentalPayments as $p)
                        <tr>
                            <td style="font-weight:600;">{{ $p->rental?->customer_name ?? '—' }}</td>
                            <td>{{ $p->rental?->bill_ref ?? '—' }}</td>
                            <td><span style="font-size:11px; background:#f7fafc; border:1px solid var(--border); padding:1px 6px; border-radius:4px;">{{ $p->payment_method ?? 'cash' }}</span></td>
                            <td style="color:var(--text-muted);">{{ $p->note ?? '—' }}</td>
                            <td class="text-end fw-700" style="color:#276749;">Rs. {{ number_format($p->amount, 0) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot style="background:#f8fafc;">
                        <tr>
                            <td colspan="4" style="font-weight:700; font-size:12px; padding:8px 12px;">Total Received</td>
                            <td class="text-end fw-700" style="color:#276749; padding:8px 12px;">Rs. {{ number_format($totalRentalPayments, 0) }}</td>
                        </tr>
                    </tfoot>
                </table>
                @endif
            </div>

        </div>

        {{-- RIGHT COLUMN ─────────────────────────────────────── --}}
        <div class="col-4">

            {{-- Expenses --}}
            <div class="table-card mb-3">
                <div class="table-card-header">
                    <span class="table-card-title">
                        <i class="bi bi-receipt-cutoff me-1"></i> Expenses
                    </span>
                    <span style="font-size:12px; font-weight:700; color:#c53030;">Rs. {{ number_format($totalExpenses, 0) }}</span>
                </div>
                @if($expenses->isEmpty())
                    <div class="text-center py-3" style="font-size:13px; color:var(--text-muted);">No expenses on this day.</div>
                @else
                <table class="table table-hover mb-0" style="font-size:12px;">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Description</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expenses as $e)
                        <tr>
                            <td style="font-weight:600;">{{ $e->category?->name ?? '—' }}</td>
                            <td style="color:var(--text-muted);">{{ $e->description ?? '—' }}</td>
                            <td class="text-end fw-700" style="color:#c53030;">Rs. {{ number_format($e->amount, 0) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot style="background:#fff5f5;">
                        <tr>
                            <td colspan="2" style="font-weight:700; font-size:12px; padding:8px 12px;">Total</td>
                            <td class="text-end fw-700" style="color:#c53030; padding:8px 12px;">Rs. {{ number_format($totalExpenses, 0) }}</td>
                        </tr>
                    </tfoot>
                </table>
                @endif
            </div>

            {{-- Cash Summary Box --}}
            <div class="table-card mb-3">
                <div class="table-card-header">
                    <span class="table-card-title"><i class="bi bi-calculator me-1"></i> Day Close Summary</span>
                </div>
                <div style="padding:14px 16px;">
                    <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom:1px solid var(--border);">
                        <span style="font-size:12px; color:var(--text-muted);">Rental Payments In</span>
                        <span style="font-weight:700; color:#276749;">Rs. {{ number_format($totalRentalPayments, 0) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom:1px solid var(--border);">
                        <span style="font-size:12px; color:var(--text-muted);">Sale Advance In</span>
                        <span style="font-weight:700; color:#276749;">Rs. {{ number_format($totalSaleAdvance, 0) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom:2px solid var(--navy);">
                        <span style="font-size:12px; font-weight:700;">Total Cash In</span>
                        <span style="font-weight:800; color:#276749; font-size:14px;">Rs. {{ number_format($totalCashIn, 0) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom:1px solid var(--border);">
                        <span style="font-size:12px; color:var(--text-muted);">Total Expenses Out</span>
                        <span style="font-weight:700; color:#c53030;">Rs. {{ number_format($totalExpenses, 0) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2" style="background:{{ $netCash >= 0 ? '#f0fff4' : '#fff5f5' }}; border-radius:6px; padding:10px 12px; margin-top:6px;">
                        <span style="font-size:13px; font-weight:700;">Net Cash for Day</span>
                        <span style="font-weight:800; color:{{ $netCash >= 0 ? '#276749' : '#c53030' }}; font-size:16px;">
                            {{ $netCash >= 0 ? '+' : '' }}Rs. {{ number_format($netCash, 0) }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Account Balances --}}
            <div class="table-card">
                <div class="table-card-header">
                    <span class="table-card-title"><i class="bi bi-bank2 me-1"></i> Account Balances</span>
                </div>
                <div style="padding:8px 16px;">
                    @foreach($accounts as $acc)
                    <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom:1px solid var(--border);">
                        <div>
                            <div style="font-size:12px; font-weight:600;">{{ $acc->name }}</div>
                            <div style="font-size:10px; color:var(--text-muted);">{{ ucfirst($acc->type) }}</div>
                        </div>
                        <span style="font-weight:700; font-size:13px; color:{{ $acc->current_balance >= 0 ? 'var(--navy)' : '#c53030' }};">
                            Rs. {{ number_format($acc->current_balance, 0) }}
                        </span>
                    </div>
                    @endforeach
                    <div class="d-flex justify-content-between align-items-center py-2 mt-1" style="background:#f8fafc; border-radius:6px; padding:10px 12px;">
                        <span style="font-size:12px; font-weight:700;">Total All Accounts</span>
                        <span style="font-weight:800; color:var(--navy);">Rs. {{ number_format($totalAllAccounts, 0) }}</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
