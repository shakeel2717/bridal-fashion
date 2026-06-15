<div>
    @if (session('success'))
        <div class="alert alert-success py-2 mb-3" style="font-size:13px;">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">
                Rental — {{ $rental->bill_ref ?? '#' . $rental->id }}
                <span class="rental-status-badge {{ $rental->status }} ms-2">
                    {{ ucfirst(str_replace('_', ' ', $rental->status)) }}
                </span>
            </div>
            <div class="page-subtitle">
                Booked on {{ \Carbon\Carbon::parse($rental->booking_date)->format('d/m/Y') }}
                @if ($rental->employee)
                    · Handled by {{ $rental->employee->name }}
                @endif
            </div>
        </div>
        <a href="{{ route('rentals.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="row g-3">

        {{-- LEFT COLUMN --}}
        <div class="col-8">

            {{-- Customer Info --}}
            <div class="table-card mb-3" style="padding:16px 20px;">
                <div
                    style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:12px;">
                    <i class="bi bi-person me-1"></i> Customer Information
                </div>
                <div class="row g-2" style="font-size:13px;">
                    <div class="col-3">
                        <div style="font-size:10px; color:var(--text-muted);">Name</div>
                        <div style="font-weight:600;">{{ $rental->customer_name }}</div>
                    </div>
                    <div class="col-3">
                        <div style="font-size:10px; color:var(--text-muted);">Phone 1</div>
                        <div style="font-weight:600; display:flex; align-items:center; gap:6px;">
                            {{ $rental->customer_phone1 }}
                            <span
                                style="font-size:10px; padding:1px 7px; border-radius:3px;
              background:{{ ($rental->phone1_gender ?? 'male') === 'female' ? '#fff5f7' : '#ebf8ff' }};
              color:{{ ($rental->phone1_gender ?? 'male') === 'female' ? '#97266d' : '#2c5282' }};">
                                {{ ($rental->phone1_gender ?? 'male') === 'female' ? '♀ F' : '♂ M' }}
                            </span>
                        </div>
                    </div>

                    @if ($rental->customer_phone2)
                        <div class="col-3">
                            <div style="font-size:10px; color:var(--text-muted);">Phone 2</div>
                            <div style="font-weight:600; display:flex; align-items:center; gap:6px;">
                                {{ $rental->customer_phone2 }}
                                <span
                                    style="font-size:10px; padding:1px 7px; border-radius:3px;
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
                            <div style="font-weight:600; display:flex; align-items:center; gap:6px;">
                                {{ $rental->customer_whatsapp }}
                                <span
                                    style="font-size:10px; padding:1px 7px; border-radius:3px;
              background:{{ ($rental->whatsapp_gender ?? 'male') === 'female' ? '#fff5f7' : '#ebf8ff' }};
              color:{{ ($rental->whatsapp_gender ?? 'male') === 'female' ? '#97266d' : '#2c5282' }};">
                                    {{ ($rental->whatsapp_gender ?? 'male') === 'female' ? '♀ F' : '♂ M' }}
                                </span>
                            </div>
                        </div>
                    @endif
                    @if ($rental->customer_cnic)
                        <div class="col-3">
                            <div style="font-size:10px; color:var(--text-muted);">CNIC</div>
                            <div style="font-weight:600; font-family:monospace; font-size:12px;">
                                {{ $rental->customer_cnic }}</div>
                        </div>
                    @endif
                    @if ($rental->delivery_address)
                        <div class="col-6">
                            <div style="font-size:10px; color:var(--text-muted);">Address</div>
                            <div style="font-weight:600;">{{ $rental->delivery_address }}</div>
                        </div>
                    @endif

                    {{-- Walk-in Documents --}}
                    @if ($rental->walkin_photo || $rental->walkin_cnic_front || $rental->walkin_cnic_back)
                        <div class="col-12"
                            style="border-top:1px solid var(--border); padding-top:12px; margin-top:4px;">
                            <div
                                style="font-size:10px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:10px;">
                                Walk-in Documents
                            </div>
                            <div class="d-flex gap-3 flex-wrap">
                                @if ($rental->walkin_photo)
                                    <div style="text-align:center;">
                                        <img src="{{ Storage::url($rental->walkin_photo) }}"
                                            style="width:60px; height:60px; object-fit:cover; border-radius:50%; border:2px solid var(--gold);">
                                        <div style="font-size:10px; color:var(--text-muted); margin-top:4px;">Photo
                                        </div>
                                    </div>
                                @endif

                                @if ($rental->walkin_cnic_front)
                                    <div style="text-align:center;">
                                        <a href="{{ Storage::url($rental->walkin_cnic_front) }}" target="_blank">
                                            <img src="{{ Storage::url($rental->walkin_cnic_front) }}"
                                                style="width:120px; height:70px; object-fit:cover; border-radius:6px; border:2px solid var(--border);">
                                        </a>
                                        <div style="font-size:10px; color:var(--text-muted); margin-top:4px;">CNIC Front
                                        </div>
                                    </div>
                                @endif

                                @if ($rental->walkin_cnic_back)
                                    <div style="text-align:center;">
                                        <a href="{{ Storage::url($rental->walkin_cnic_back) }}" target="_blank">
                                            <img src="{{ Storage::url($rental->walkin_cnic_back) }}"
                                                style="width:120px; height:70px; object-fit:cover; border-radius:6px; border:2px solid var(--border);">
                                        </a>
                                        <div style="font-size:10px; color:var(--text-muted); margin-top:4px;">CNIC Back
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Stitching Task --}}
            @if ($stitchingTask)
                <div class="table-card mb-3"
                    style="padding:16px 20px; border-left:3px solid {{ $stitchingTask->status === 'done' ? '#68d391' : ($stitchingTask->status === 'denied' ? '#fc8181' : '#f6e05e') }};">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <div
                                style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:6px;">
                                <i class="bi bi-scissors me-1"></i> Stitching Task
                                @if ($rental->stitching_date)
                                    <span style="font-weight:400; margin-left:8px;">
                                        Due: {{ \Carbon\Carbon::parse($rental->stitching_date)->format('d/m/Y') }}
                                    </span>
                                @endif
                            </div>
                            <div style="font-size:13px; font-weight:600; color:var(--text-primary);">
                                {{ $stitchingTask->title }}
                            </div>
                            @if ($stitchingTask->status !== 'pending')
                                <div style="font-size:11px; margin-top:6px; color:var(--text-muted);">
                                    {{ ucfirst($stitchingTask->status) }} by
                                    <strong>{{ $stitchingTask->actionedBy?->name ?? 'Unknown' }}</strong>
                                    on {{ $stitchingTask->actioned_at?->format('d/m/Y h:i A') }}
                                    @if ($stitchingTask->note)
                                        · "{{ $stitchingTask->note }}"
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="d-flex gap-2 align-items-center">
                            @if ($stitchingTask->status === 'pending')
                                @if ($taskActionId === $stitchingTask->id)
                                    <div style="display:flex; gap:6px; align-items:center;">
                                        <input type="text" wire:model="taskNote" class="form-control form-control-sm"
                                            style="width:180px;" placeholder="Note (optional)">
                                        <button class="btn btn-sm btn-success action-btn" wire:click="applyTaskAction">
                                            Confirm {{ ucfirst($taskActionType) }}
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary action-btn"
                                            wire:click="$set('taskActionId', null)">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                @else
                                    <button class="btn btn-sm btn-outline-success action-btn"
                                        wire:click="openTaskAction({{ $stitchingTask->id }}, 'done')">
                                        <i class="bi bi-check me-1"></i> Done
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger action-btn"
                                        wire:click="openTaskAction({{ $stitchingTask->id }}, 'denied')">
                                        <i class="bi bi-x me-1"></i> Denied
                                    </button>
                                @endif
                            @else
                                <span
                                    class="badge-status {{ $stitchingTask->status === 'done' ? 'ready' : 'overdue' }}">
                                    {{ ucfirst($stitchingTask->status) }}
                                </span>
                                <button class="btn btn-sm btn-outline-secondary action-btn"
                                    wire:click="undoTask({{ $stitchingTask->id }})" title="Reset to pending">
                                    <i class="bi bi-arrow-counterclockwise" style="font-size:11px;"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Rented Items --}}
            <div class="table-card mb-3" style="padding:16px 20px;">
                <div
                    style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:14px;">
                    <i class="bi bi-box-seam me-1"></i> Rented Items
                </div>

                <table class="table mb-0" style="font-size:12px;">
                    <thead>
                        <tr>
                            <th style="width:40px;"></th>
                            <th>Item</th>
                            <th style="text-align:right;">Rental Price</th>
                            <th style="text-align:right;">Add-ons</th>
                            <th style="text-align:right;">Total</th>
                            <th style="text-align:center; width:160px;">Status / Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rental->items as $item)
                            @php
                                $itemPendingTasks = $item->tasks->where('status', 'pending')->count();
                                $canPickup = $itemPendingTasks === 0 && $pendingTasksCount === 0;
                            @endphp
                            <tr style="{{ $item->pickup_status === 'returned' ? 'opacity:0.6;' : '' }}">
                                {{-- Photo --}}
                                <td style="vertical-align:middle;">
                                    @if ($item->product?->photo)
                                        <img src="{{ Storage::url($item->product->photo) }}"
                                            style="width:36px; height:36px; object-fit:cover; border-radius:6px; border:1px solid var(--border);">
                                    @else
                                        <div
                                            style="width:36px; height:36px; background:var(--gold-light); border-radius:6px; display:flex; align-items:center; justify-content:center;">
                                            <i class="bi bi-image" style="font-size:13px; color:var(--gold);"></i>
                                        </div>
                                    @endif
                                </td>

                                {{-- Name + tasks --}}
                                <td style="vertical-align:middle;">
                                    <span class="tbl-code-badge">{{ $item->product_code }}</span>
                                    <div style="font-weight:600; margin-top:2px;">{{ $item->product_name }}</div>

                                    {{-- Double-booked warning --}}
                                    @if ($item->double_booked ?? false)
                                        <div style="font-size:10px; color:#c53030; margin-top:2px;">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Double booked
                                        </div>
                                    @endif

                                    {{-- Addon tasks inline --}}
                                    @if ($item->tasks->count() > 0)
                                        <div style="margin-top:6px; display:flex; flex-direction:column; gap:3px;">
                                            @foreach ($item->tasks as $task)
                                                <div
                                                    style="display:flex; align-items:center; justify-content:space-between; padding:4px 8px; border-radius:5px; font-size:11px;
                                        background:{{ $task->status === 'done' ? '#f0fff4' : ($task->status === 'denied' ? '#fff5f5' : '#fffff0') }};
                                        border:1px solid {{ $task->status === 'done' ? '#9ae6b4' : ($task->status === 'denied' ? '#fc8181' : '#f6e05e') }};">
                                                    <div>
                                                        @if ($task->status === 'done')
                                                            <i class="bi bi-check-circle-fill text-success me-1"></i>
                                                        @elseif($task->status === 'denied')
                                                            <i class="bi bi-x-circle-fill text-danger me-1"></i>
                                                        @else
                                                            <i class="bi bi-circle me-1" style="color:#b7791f;"></i>
                                                        @endif
                                                        <span style="font-weight:600;">{{ $task->title }}</span>
                                                        @if ($task->cost > 0)
                                                            <span style="color:var(--gold-hover); margin-left:4px;">
                                                                Rs. {{ number_format($task->cost, 0) }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="d-flex gap-1 align-items-center ms-2">
                                                        @if ($task->status === 'pending')
                                                            @if ($taskActionId === $task->id)
                                                                <input type="text" wire:model="taskNote"
                                                                    class="form-control form-control-sm"
                                                                    style="width:130px; font-size:11px;"
                                                                    placeholder="Note (optional)">
                                                                <button class="btn btn-sm btn-success action-btn"
                                                                    wire:click="applyTaskAction">OK</button>
                                                                <button
                                                                    class="btn btn-sm btn-outline-secondary action-btn"
                                                                    wire:click="$set('taskActionId', null)">
                                                                    <i class="bi bi-x"></i>
                                                                </button>
                                                            @else
                                                                <button
                                                                    class="btn btn-sm btn-outline-success action-btn"
                                                                    wire:click="openTaskAction({{ $task->id }}, 'done')"
                                                                    title="Mark Done">
                                                                    <i class="bi bi-check"
                                                                        style="font-size:11px;"></i>
                                                                </button>
                                                                <button
                                                                    class="btn btn-sm btn-outline-danger action-btn"
                                                                    wire:click="openTaskAction({{ $task->id }}, 'denied')"
                                                                    title="Mark Denied">
                                                                    <i class="bi bi-x" style="font-size:11px;"></i>
                                                                </button>
                                                            @endif
                                                        @else
                                                            <button class="btn btn-sm btn-outline-secondary action-btn"
                                                                wire:click="undoTask({{ $task->id }})"
                                                                title="Reset to pending">
                                                                <i class="bi bi-arrow-counterclockwise"
                                                                    style="font-size:10px;"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>

                                {{-- Rental price --}}
                                <td style="text-align:right; vertical-align:middle; font-weight:600;">
                                    Rs. {{ number_format($item->rental_price, 0) }}
                                </td>

                                {{-- Add-ons price --}}
                                <td style="text-align:right; vertical-align:middle;">
                                    @if ($item->custom_option_price > 0)
                                        <span style="color:var(--gold-hover); font-weight:600;">
                                            Rs. {{ number_format($item->custom_option_price, 0) }}
                                        </span>
                                        @if ($item->custom_option_label)
                                            <div style="font-size:10px; color:var(--text-muted);">
                                                {{ $item->custom_option_label }}</div>
                                        @endif
                                    @else
                                        <span style="color:var(--text-muted);">—</span>
                                    @endif
                                </td>

                                {{-- Row total --}}
                                <td
                                    style="text-align:right; vertical-align:middle; font-weight:700; color:var(--navy);">
                                    Rs. {{ number_format($item->rental_price + $item->custom_option_price, 0) }}
                                </td>

                                {{-- Pickup / Return action --}}
                                <td style="text-align:center; vertical-align:middle;">
                                    @if ($item->pickup_status === 'pending')
                                        @if ($canPickup && !in_array($rental->status, ['cancelled', 'abandoned']))
                                            <button class="btn btn-sm btn-outline-success action-btn"
                                                wire:click="markItemPickedUp({{ $item->id }})">
                                                <i class="bi bi-box-arrow-up me-1"></i> Picked Up
                                            </button>
                                        @elseif(!$canPickup)
                                            <span style="font-size:10px; color:#e53e3e; font-weight:600;">
                                                <i class="bi bi-lock me-1"></i> Tasks pending
                                            </span>
                                        @else
                                            <span class="badge-status pending">Pending</span>
                                        @endif
                                    @elseif($item->pickup_status === 'picked_up')
                                        <div style="font-size:10px; color:var(--text-muted); margin-bottom:2px;">
                                            {{ \Carbon\Carbon::parse($item->picked_up_at)->format('d/m/Y') }}
                                        </div>
                                        @if ($item->pickedUpBy)
                                            <div style="font-size:10px; color:var(--text-muted); margin-bottom:4px;">
                                                by <strong>{{ $item->pickedUpBy->name }}</strong>
                                            </div>
                                        @endif
                                        <button class="btn btn-sm btn-outline-primary action-btn"
                                            wire:click="markItemReturned({{ $item->id }})">
                                            <i class="bi bi-box-arrow-in-down me-1"></i> Returned
                                        </button>
                                    @elseif($item->pickup_status === 'returned')
                                        <div
                                            style="font-size:10px; color:var(--text-muted); margin-bottom:4px; padding-bottom:4px; border-bottom:1px solid var(--border);">
                                            <i class="bi bi-box-arrow-up me-1" style="color:#276749;"></i>
                                            Picked: {{ \Carbon\Carbon::parse($item->picked_up_at)->format('d/m/Y') }}
                                            @if ($item->pickedUpBy)
                                                · <strong>{{ $item->pickedUpBy->name }}</strong>
                                            @endif
                                        </div>
                                        <div style="font-size:10px; color:#276749; font-weight:600;">
                                            <i class="bi bi-check-circle me-1"></i> Returned
                                        </div>
                                        <div style="font-size:10px; color:var(--text-muted);">
                                            {{ \Carbon\Carbon::parse($item->returned_at)->format('d/m/Y') }}
                                        </div>
                                        @if ($item->receivedBy)
                                            <div style="font-size:10px; color:var(--text-muted);">
                                                by <strong>{{ $item->receivedBy->name }}</strong>
                                            </div>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        @if (($rental->discount_amount ?? 0) > 0)
                            <tr>
                                <td colspan="4" style="text-align:right; font-size:12px; color:var(--text-muted);">
                                    Discount</td>
                                <td style="text-align:right; color:#e53e3e; font-weight:600;">
                                    − Rs. {{ number_format($rental->discount_amount, 0) }}
                                </td>
                                <td></td>
                            </tr>
                        @endif
                        <tr style="border-top:2px solid var(--navy);">
                            <td colspan="4"
                                style="text-align:right; font-weight:700; font-size:14px; padding-top:10px;">Total</td>
                            <td
                                style="text-align:right; font-weight:800; font-size:16px; color:var(--navy); padding-top:10px;">
                                Rs. {{ number_format($rental->total_amount, 0) }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Security Deposits --}}
            @if ($rental->securityDeposits->count() > 0)
                <div class="table-card mb-3" style="padding:16px 20px;">
                    <div
                        style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:12px;">
                        <i class="bi bi-shield-check me-1"></i>
                        Security / Refundable Deposits
                    </div>

                    @foreach ($rental->securityDeposits as $deposit)
                        <div
                            style="display:flex; align-items:center; justify-content:space-between; padding:10px 12px; border-radius:8px; margin-bottom:6px;
         background:{{ $deposit->is_refunded ? '#f0fff4' : ($deposit->is_paid ? '#fffff0' : '#fff5f5') }};
         border:1px solid {{ $deposit->is_refunded ? '#9ae6b4' : ($deposit->is_paid ? '#f6e05e' : '#fed7d7') }};">

                            <div>
                                <div style="font-size:13px; font-weight:600; color:var(--text-primary);">
                                    {{ $deposit->item_name }}
                                </div>
                                <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">
                                    @if ($deposit->is_refunded)
                                        <span style="color:#276749;">
                                            <i class="bi bi-check-circle me-1"></i>
                                            Refunded by {{ $deposit->refundedBy?->name }}
                                            on {{ $deposit->refunded_at?->format('d/m/Y') }}
                                        </span>
                                    @elseif($deposit->is_paid)
                                        <span style="color:#b7791f;">
                                            <i class="bi bi-cash me-1"></i> Paid — pending return
                                        </span>
                                    @else
                                        <span style="color:#c53030;">
                                            <i class="bi bi-x-circle me-1"></i> Not paid by customer
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-2">
                                <span style="font-size:14px; font-weight:700; color:var(--navy);">
                                    Rs. {{ number_format($deposit->amount, 0) }}
                                </span>

                                @if (!in_array($rental->status, ['cancelled', 'abandoned']))
                                    @if ($deposit->is_paid && !$deposit->is_refunded)
                                        <button class="btn btn-sm btn-outline-success action-btn"
                                            wire:click="refundDeposit({{ $deposit->id }})"
                                            title="Mark as refunded to customer">
                                            <i class="bi bi-arrow-return-left me-1"></i> Refund
                                        </button>
                                    @elseif($deposit->is_refunded)
                                        <button class="btn btn-sm btn-outline-secondary action-btn"
                                            wire:click="markDepositNotRefunded({{ $deposit->id }})"
                                            title="Undo refund">
                                            <i class="bi bi-arrow-counterclockwise" style="font-size:11px;"></i>
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endforeach

                    {{-- Total deposit summary --}}
                    @php
                        $totalDeposits = $rental->securityDeposits->sum('amount');
                        $paidDeposits = $rental->securityDeposits->where('is_paid', true)->sum('amount');
                        $refundedDeposits = $rental->securityDeposits->where('is_refunded', true)->sum('amount');
                    @endphp
                    <div
                        style="display:flex; justify-content:space-between; font-size:12px; padding-top:10px; border-top:1px solid var(--border); margin-top:8px;">
                        <div>
                            Total: <strong>Rs. {{ number_format($totalDeposits, 0) }}</strong>
                            · Paid: <strong style="color:#b7791f;">Rs. {{ number_format($paidDeposits, 0) }}</strong>
                            · Refunded: <strong style="color:#276749;">Rs.
                                {{ number_format($refundedDeposits, 0) }}</strong>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Notes --}}
            @if ($rental->notes)
                <div class="table-card" style="padding:14px 20px;">
                    <div
                        style="font-size:10px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:6px;">
                        Notes</div>
                    <div style="font-size:13px;">{{ $rental->notes }}</div>
                </div>
            @endif
        </div>

        {{-- RIGHT COLUMN --}}
        <div class="col-4">

            {{-- Financial Summary --}}
            <div class="rental-summary-box mb-3">
                <div
                    style="font-size:11px; font-weight:700; text-transform:uppercase; color:rgba(255,255,255,0.5); margin-bottom:12px;">
                    Financial Summary
                </div>
                <div class="summary-row">
                    <span class="s-label">Total Amount</span>
                    <span class="s-value">Rs. {{ number_format($rental->total_amount, 0) }}</span>
                </div>
                <div class="summary-row">
                    <span class="s-label">Total Paid</span>
                    <span class="s-value">Rs. {{ number_format($totalPaid, 0) }}</span>
                </div>
                <div class="summary-row">
                    <span class="s-label">Advance Paid</span>
                    <span class="s-value">Rs. {{ number_format($rental->advance_paid, 0) }}</span>
                </div>
                <div class="summary-row">
                    <span class="s-label">Payment Via</span>
                    <span class="s-value" style="font-size:11px;">
                        {{ ucfirst(str_replace('_', ' ', $rental->advance_payment_method ?? 'cash')) }}
                    </span>
                </div>
                <div class="summary-row total-row">
                    <span class="s-label">Remaining</span>
                    <span class="s-value {{ $remaining > 0 ? 'gold' : '' }}"
                        style="{{ $remaining <= 0 ? 'color:#68d391;' : '' }}">
                        Rs. {{ number_format($remaining, 0) }}
                        @if ($remaining <= 0)
                            <span style="font-size:10px; font-weight:400; margin-left:4px;">✓ Paid</span>
                        @endif
                    </span>
                </div>
                @if ($rental->refund_amount > 0)
                    <div class="summary-row" style="margin-top:6px;">
                        <span class="s-label" style="color:#fc8181;">Refunded</span>
                        <span class="s-value" style="color:#fc8181;">
                            Rs. {{ number_format($rental->refund_amount, 0) }}
                        </span>
                    </div>
                @endif
            </div>

            {{-- Cancellation Hold --}}
            @if ($cancellationHolds->count() > 0)
                <div class="table-card mb-3" style="padding:14px 16px; border-left:3px solid #d69e2e;">
                    <div
                        style="font-size:11px; font-weight:700; text-transform:uppercase; color:#b7791f; margin-bottom:8px;">
                        <i class="bi bi-cash-coin me-1"></i> Hold on Cancellation
                    </div>
                    @foreach ($cancellationHolds as $hold)
                        <div style="font-size:12px; padding:4px 0; border-bottom:1px solid var(--border);">
                            <div style="font-weight:600;">{{ $hold->title }}</div>
                            @if ($hold->note)
                                <div style="color:var(--text-muted); font-size:11px;">{{ $hold->note }}</div>
                            @endif
                            <div style="font-weight:700; color:#b7791f;">
                                Rs. {{ number_format($hold->cost, 0) }}
                            </div>
                        </div>
                    @endforeach
                    @if ($totalHeld > 0)
                        <div style="font-size:13px; font-weight:700; color:#b7791f; padding-top:8px;">
                            Total Held: Rs. {{ number_format($totalHeld, 0) }}
                        </div>
                    @endif
                </div>
            @endif

            {{-- Payment History --}}
            <div class="table-card mb-3" style="padding:16px;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted);">
                        <i class="bi bi-cash me-1"></i> Payments
                    </div>
                    @if (!in_array($rental->status, ['cancelled', 'abandoned']))
                        <button class="btn btn-sm btn-outline-success action-btn"
                            wire:click="$set('showPaymentForm', true)">
                            <i class="bi bi-plus me-1"></i> Add Payment
                        </button>
                    @endif
                </div>

                @if ($showPaymentForm)
                    <div
                        style="background:#f7fafc; border-radius:8px; padding:12px; margin-bottom:12px; border:1px solid var(--border);">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label">Amount (Rs.) <span class="text-danger">*</span></label>
                                <input type="number" wire:model="paymentAmount"
                                    class="form-control form-control-sm @error('paymentAmount') is-invalid @enderror"
                                    placeholder="{{ $remaining }}" min="1">
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
                                        <option value="{{ $acc->id }}">
                                            {{ $acc->name }}
                                            <span style="color:var(--text-muted);">
                                                ({{ ucfirst(str_replace('_', ' ', $acc->type)) }})
                                            </span>
                                        </option>
                                    @endforeach
                                </select>
                                @error('paymentMethod')
                                    <div style="color:#e53e3e; font-size:12px; margin-top:4px;">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Note</label>
                                <input type="text" wire:model="paymentNote" class="form-control form-control-sm"
                                    placeholder="e.g. remaining balance on pickup">
                            </div>
                            <div class="col-12 d-flex gap-2">
                                <button class="btn btn-sm btn-success flex-fill" wire:click="addPayment"
                                    wire:loading.attr="disabled">
                                    <span wire:loading wire:target="addPayment">
                                        <span class="spinner-border spinner-border-sm"></span>
                                    </span>
                                    Save Payment
                                </button>
                                <button class="btn btn-sm btn-outline-secondary"
                                    wire:click="$set('showPaymentForm', false)">Cancel</button>
                            </div>
                        </div>
                    </div>
                @endif

                @forelse($payments as $payment)
                    <div
                        style="display:flex; justify-content:space-between; align-items:flex-start; padding:8px 0; border-bottom:1px solid var(--border); font-size:12px;">
                        <div>
                            <div style="font-weight:600; color:var(--text-primary);">
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

            {{-- Dates --}}
            <div class="table-card mb-3" style="padding:14px 16px;">
                <div
                    style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:10px;">
                    Dates & Info
                </div>
                @foreach ([
        'Booking' => $rental->booking_date,
        'Pickup' => $rental->pickup_date,
        'Return' => $rental->return_date,
        'Stitching' => $rental->stitching_date,
    ] as $label => $date)
                    @if ($date)
                        <div
                            style="display:flex; justify-content:space-between; font-size:12px; padding:4px 0; border-bottom:1px solid var(--border);">
                            <span style="color:var(--text-muted);">{{ $label }}</span>
                            <span style="font-weight:600;">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</span>
                        </div>
                    @endif
                @endforeach
                @if ($rental->bill_ref)
                    <div style="display:flex; justify-content:space-between; font-size:12px; padding:4px 0;">
                        <span style="color:var(--text-muted);">Bill Ref</span>
                        <span style="font-weight:700; font-family:monospace;">{{ $rental->bill_ref }}</span>
                    </div>
                @endif
            </div>

            {{-- Actions --}}
            <div class="table-card" style="padding:14px 16px;">
                <div
                    style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:10px;">
                    Actions
                </div>
                <div class="d-flex flex-column gap-2">
                    @if ($rental->status === 'booked')
                        <button class="btn btn-sm btn-outline-warning w-100" wire:click="markReady">
                            <i class="bi bi-check-circle me-1"></i> Mark as Ready
                        </button>
                    @endif

                    @if (!in_array($rental->status, ['returned', 'cancelled', 'abandoned']))
                        <button class="btn btn-sm btn-outline-danger w-100" wire:click="cancelRental">
                            <i class="bi bi-x-circle me-1"></i> Cancel Rental
                        </button>
                        <button class="btn btn-sm btn-outline-secondary w-100" wire:click="markAbandoned">
                            <i class="bi bi-slash-circle me-1"></i> Mark Abandoned
                        </button>
                    @endif

                    <a href="{{ route('rentals.edit', $rental->id) }}"
                        class="btn btn-sm btn-outline-secondary w-100">
                        <i class="bi bi-pencil me-1"></i> Edit Rental
                    </a>
                </div>
            </div>

            {{-- Refund Form --}}
            @if ($showRefundForm)
                <div class="table-card mt-3" style="padding:16px; border:1.5px solid #fed7d7;">
                    <div
                        style="font-size:11px; font-weight:700; text-transform:uppercase; color:#c53030; margin-bottom:12px;">
                        Record Refund
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Refund Type</label>
                        <select wire:model.live="refundType" class="form-select form-select-sm">
                            <option value="none">No Refund</option>
                            <option value="full">Full Refund (Rs. {{ number_format($totalPaid, 0) }})</option>
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

            @if ($rental->refund_type && !$showRefundForm)
                <div class="table-card mt-3"
                    style="padding:14px 16px; border:1.5px solid #fed7d7; background:#fff5f5;">
                    <div style="font-size:11px; font-weight:700; color:#c53030; margin-bottom:6px;">
                        Refund Recorded
                    </div>
                    <div style="font-size:12px;">
                        Type: <strong>{{ ucfirst($rental->refund_type) }}</strong><br>
                        Amount: <strong>Rs. {{ number_format($rental->refund_amount, 0) }}</strong><br>
                        @if ($rental->refund_date)
                            Date: {{ \Carbon\Carbon::parse($rental->refund_date)->format('d/m/Y') }}<br>
                        @endif
                        @if ($rental->refund_note)
                            Note: {{ $rental->refund_note }}
                        @endif
                    </div>
                </div>
            @endif

        </div>
    </div>

    {{-- Pickup Modal --}}
    @if ($showPickupModal)
        <div class="confirm-modal-overlay">
            <div class="confirm-modal-box" style="max-width:420px;">
                <div class="confirm-title">
                    <i class="bi bi-box-arrow-up me-2" style="color:#276749;"></i>
                    Mark Item as Picked Up
                </div>
                <div class="confirm-subtitle">
                    Select the employee who gave this item to the customer.
                </div>
                <div class="mb-4">
                    <label class="form-label">Given By <span class="text-danger">*</span></label>
                    <select wire:model="pickupGivenBy"
                        class="form-select @error('pickupGivenBy') is-invalid @enderror">
                        <option value="">Select employee...</option>
                        @foreach (\App\Models\User::where('is_active', true)->orderBy('name')->get() as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                        @endforeach
                    </select>
                    @error('pickupGivenBy')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="confirm-actions">
                    <button class="btn btn-sm btn-outline-secondary"
                        wire:click="$set('showPickupModal', false)">Cancel</button>
                    <button class="btn btn-sm btn-success" wire:click="confirmItemPickedUp"
                        wire:loading.attr="disabled">
                        <span wire:loading wire:target="confirmItemPickedUp">
                            <span class="spinner-border spinner-border-sm me-1"></span>
                        </span>
                        Confirm Pickup
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Cancel with Hold Form --}}
    @if ($showCancelHoldForm)
        <div class="confirm-modal-overlay">
            <div class="confirm-modal-box" style="max-width:480px;">
                <div class="confirm-title">
                    <i class="bi bi-cash-coin me-2" style="color:#b7791f;"></i>
                    Cancellation — Hold Amount
                </div>
                <div class="confirm-subtitle">
                    Before cancelling, record any amount you are holding from this customer
                    (e.g. dry clean cost, stitching already done, preparation expenses).
                    This is optional — leave blank if nothing to hold.
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-5">
                        <label class="form-label">
                            Hold Amount (Rs.)
                            <span style="font-weight:400; color:var(--text-muted);">(optional)</span>
                        </label>
                        <input type="number" wire:model="holdAmount"
                            class="form-control @error('holdAmount') is-invalid @enderror" placeholder="e.g. 500"
                            min="0">
                        @error('holdAmount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-7">
                        <label class="form-label">
                            Description
                            <span style="font-weight:400; color:var(--text-muted);">(what is this for)</span>
                        </label>
                        <input type="text" wire:model="holdNote" class="form-control"
                            placeholder="e.g. Dry clean done, Stitching completed">
                    </div>
                    <div class="col-12">
                        <label class="form-label">
                            Customer Reason for Cancel
                            <span style="font-weight:400; color:var(--text-muted);">(optional)</span>
                        </label>
                        <input type="text" wire:model="holdReason" class="form-control"
                            placeholder="e.g. Changed mind, Found cheaper elsewhere">
                    </div>
                </div>

                {{-- Preview --}}
                @if (!empty($holdAmount) && (float) $holdAmount > 0)
                    <div
                        style="background:#fffff0; border:1px solid #f6e05e; border-radius:8px; padding:10px 14px; margin-bottom:16px; font-size:12px; color:#b7791f;">
                        <i class="bi bi-info-circle me-1"></i>
                        Rs. <strong>{{ number_format((float) $holdAmount, 0) }}</strong> will be recorded as company
                        hold
                        @if (!empty($holdNote))
                            for "{{ $holdNote }}"
                        @endif.
                        This will appear in the rental history.
                    </div>
                @endif

                <div class="confirm-actions">
                    <button class="btn btn-sm btn-outline-secondary" wire:click="skipHoldAndCancel">
                        Skip & Cancel
                    </button>
                    <button class="btn btn-sm btn-warning" wire:click="processCancelWithHold"
                        wire:loading.attr="disabled" style="color:#fff;">
                        <span wire:loading wire:target="processCancelWithHold">
                            <span class="spinner-border spinner-border-sm me-1"></span>
                        </span>
                        @if (!empty($holdAmount) && (float) $holdAmount > 0)
                            Hold Rs. {{ number_format((float) $holdAmount, 0) }} & Cancel
                        @else
                            Confirm Cancel
                        @endif
                    </button>
                </div>
            </div>
        </div>
    @endif
    {{-- Password Confirm Modal --}}
    @if ($showCancelConfirm)
        <div class="confirm-modal-overlay">
            <div class="confirm-modal-box">
                <div class="confirm-title">
                    <i class="bi bi-shield-lock me-2" style="color:#e53e3e;"></i>
                    @if ($pendingAction === 'cancel')
                        Cancel Rental
                    @else
                        Mark as Abandoned
                    @endif
                </div>
                <div class="confirm-subtitle">
                    This action cannot be undone. Please enter your password to confirm.
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
                        @if ($pendingAction === 'cancel')
                            Yes, Cancel Rental
                        @else
                            Yes, Mark Abandoned
                        @endif
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Return Item Modal --}}
    @if ($showReturnModal)
        <div class="confirm-modal-overlay">
            <div class="confirm-modal-box" style="max-width:420px;">
                <div class="confirm-title">
                    <i class="bi bi-box-arrow-in-down me-2" style="color:#3182ce;"></i>
                    Mark Item as Returned
                </div>
                <div class="confirm-subtitle">
                    Select the employee who received this item back in the shop.
                </div>

                <div class="mb-4">
                    <label class="form-label">Received By <span class="text-danger">*</span></label>
                    <select wire:model="returnReceivedBy"
                        class="form-select @error('returnReceivedBy') is-invalid @enderror">
                        <option value="">Select employee...</option>
                        @foreach (\App\Models\User::where('is_active', true)->orderBy('name')->get() as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                        @endforeach
                    </select>
                    @error('returnReceivedBy')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="confirm-actions">
                    <button class="btn btn-sm btn-outline-secondary" wire:click="$set('showReturnModal', false)">
                        Cancel
                    </button>
                    <button class="btn btn-sm btn-primary" wire:click="confirmItemReturned"
                        wire:loading.attr="disabled">
                        <span wire:loading wire:target="confirmItemReturned">
                            <span class="spinner-border spinner-border-sm me-1"></span>
                        </span>
                        Confirm Return
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
