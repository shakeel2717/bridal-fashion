<div>
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">Attendance</div>
            <div class="page-subtitle">Monthly attendance tracking</div>
        </div>
        <select wire:model.live="filterRole"
                class="form-select form-select-sm" style="width:130px;">
            <option value="employee">Employees</option>
            <option value="admin">Admins</option>
            <option value="">All Staff</option>
        </select>
    </div>

    {{-- Month Navigator --}}
    <div class="table-card mb-3" style="padding:14px 16px;">
        <div class="d-flex align-items-center justify-content-between">
            <button class="month-nav-btn" wire:click="previousMonth">
                <i class="bi bi-chevron-left"></i> Prev
            </button>
            <div style="font-size:16px; font-weight:700; color:var(--text-primary);">
                {{ $currentMonth->format('F Y') }}
            </div>
            <button class="month-nav-btn"
                    wire:click="nextMonth"
                    @if(!$canGoNext) disabled style="opacity:0.4; cursor:not-allowed;" @endif>
                Next <i class="bi bi-chevron-right"></i>
            </button>
        </div>
    </div>

    {{-- Legend --}}
    <div class="att-legend">
        <div class="legend-item">
            <div class="legend-dot" style="background:#68d391;"></div> Present
        </div>
        <div class="legend-item">
            <div class="legend-dot" style="background:#fc8181;"></div> Absent
        </div>
        <div class="legend-item">
            <div class="legend-dot" style="background:#f6e05e;"></div> Half Day
        </div>
        <div class="legend-item">
            <div class="legend-dot" style="background:#63b3ed;"></div> Leave
        </div>
        <div class="legend-item">
            <div class="legend-dot" style="background:#e2e8f0;"></div> Not Marked
        </div>
    </div>

    {{-- Attendance Grid --}}
    <div class="table-card" style="overflow-x:auto; padding:16px;">
        @if($employees->isEmpty())
            <div style="text-align:center; padding:30px; color:var(--text-muted); font-size:13px;">
                <i class="bi bi-people" style="font-size:32px; display:block; margin-bottom:8px;"></i>
                No active employees found
            </div>
        @else

        {{-- Day headers --}}
        <div style="display:grid; grid-template-columns: 200px repeat({{ $daysInMonth }}, 36px) 170px; gap:2px; margin-bottom:2px; min-width:max-content;">
            <div class="att-header-cell" style="justify-content:flex-start; padding-left:10px;">Employee</div>
            @for($d = 1; $d <= $daysInMonth; $d++)
                @php
                    $ds  = \Carbon\Carbon::createFromDate($year, $month, $d)->toDateString();
                    $dow = \Carbon\Carbon::createFromDate($year, $month, $d)->format('D');
                    $sun = \Carbon\Carbon::createFromDate($year, $month, $d)->isSunday();
                @endphp
                <div class="att-header-cell"
                     style="{{ $sun ? 'color:#e53e3e;' : '' }}"
                     title="{{ $dow }} — {{ $ds }}">
                    {{ $d }}
                </div>
            @endfor
            <div class="att-header-cell">Summary</div>
        </div>

        {{-- Employee rows --}}
        @foreach($employees as $emp)
        <div style="display:grid; grid-template-columns: 200px repeat({{ $daysInMonth }}, 36px) 170px; gap:2px; margin-bottom:2px; min-width:max-content;">

            <div class="att-name-cell">
                @if($emp->photo)
                    <img src="{{ Storage::url($emp->photo) }}"
                         style="width:24px; height:24px; border-radius:50%; object-fit:cover; flex-shrink:0;">
                @else
                    <div style="width:24px; height:24px; border-radius:50%; background:var(--navy); color:#fff; display:flex; align-items:center; justify-content:center; font-size:9px; font-weight:700; flex-shrink:0;">
                        {{ strtoupper(substr($emp->name, 0, 2)) }}
                    </div>
                @endif
                <span style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                    {{ $emp->name }}
                </span>
            </div>

            @for($d = 1; $d <= $daysInMonth; $d++)
                @php
                    $ds       = \Carbon\Carbon::createFromDate($year, $month, $d)->toDateString();
                    $isFuture = $ds > $today;
                    $isToday  = $ds === $today;
                    $key      = $emp->id . '_' . $ds;
                    $att      = $attendances[$key] ?? null;
                    $status   = $att ? $att->first()->status : null;

                    $cls  = 'att-cell';
                    $cls .= $isFuture ? ' future'  : '';
                    $cls .= $isToday  ? ' today'   : '';
                    $cls .= $status   ? " {$status}" : '';

                    $lbl = match($status) {
                        'present'  => 'P',
                        'absent'   => 'A',
                        'half_day' => 'H',
                        'leave'    => 'L',
                        default    => $isFuture ? '' : '·',
                    };
                @endphp
                <div class="{{ $cls }}"
                     @if(!$isFuture)
                         wire:click="openMarkModal({{ $emp->id }}, '{{ $ds }}')"
                         title="{{ $ds }}: {{ $status ? ucfirst(str_replace('_',' ',$status)) : 'Not marked' }}"
                     @endif>
                    {{ $lbl }}
                </div>
            @endfor

            @php $s = $summary[$emp->id]; @endphp
            <div style="display:flex; align-items:center; gap:4px; padding:0 8px; flex-wrap:wrap;">
                <span class="att-summary-pill present">P:{{ $s['present'] }}</span>
                <span class="att-summary-pill absent">A:{{ $s['absent'] }}</span>
                <span class="att-summary-pill half_day">H:{{ $s['half_day'] }}</span>
                <span class="att-summary-pill leave">L:{{ $s['leave'] }}</span>
            </div>
        </div>
        @endforeach

        {{-- Mark All Present row --}}
        <div style="display:grid; grid-template-columns: 200px repeat({{ $daysInMonth }}, 36px) 170px; gap:2px; margin-top:6px; min-width:max-content;">
            <div class="att-header-cell" style="justify-content:flex-start; padding-left:10px; font-size:10px;">
                Mark All Present
            </div>
            @for($d = 1; $d <= $daysInMonth; $d++)
                @php
                    $ds       = \Carbon\Carbon::createFromDate($year, $month, $d)->toDateString();
                    $isFuture = $ds > $today;
                @endphp
                <div>
                    @if(!$isFuture)
                    <button style="width:36px; height:36px; background:var(--gold-light); border:1px solid var(--gold); border-radius:4px; font-size:9px; font-weight:700; color:var(--gold-hover); cursor:pointer; line-height:1;"
                            wire:click="markAllPresent('{{ $ds }}')"
                            title="Mark all present — {{ $ds }}">
                        All
                    </button>
                    @endif
                </div>
            @endfor
            <div></div>
        </div>

        @endif
    </div>

    {{-- Mark Modal --}}
    <div class="modal fade" id="markModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">
                        <i class="bi bi-calendar-check me-2"></i> Mark Attendance
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            wire:click="closeMarkModal"></button>
                </div>

                <div class="modal-body" style="padding:20px;">
                    @if($markUserName)
                    <div style="font-size:13px; font-weight:600; color:var(--text-primary); margin-bottom:2px;">
                        {{ $markUserName }}
                    </div>
                    <div style="font-size:12px; color:var(--text-muted); margin-bottom:16px;">
                        {{ $markDate ? \Carbon\Carbon::parse($markDate)->format('d/m/Y') : '' }}
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <div class="d-flex gap-2 flex-wrap">
                            @foreach(['present' => 'Present', 'absent' => 'Absent', 'half_day' => 'Half Day', 'leave' => 'Leave'] as $val => $lbl)
                            <button type="button"
                                    wire:click="setMarkStatus('{{ $val }}')"
                                    style="padding:6px 14px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; transition:all 0.1s;
                                           border: 2px solid {{ $markStatus === $val ? 'var(--gold)' : 'var(--border)' }};
                                           background: {{ $markStatus === $val ? 'var(--gold-light)' : '#fff' }};
                                           color: {{ $markStatus === $val ? 'var(--gold-hover)' : 'var(--text-muted)' }};">
                                {{ $lbl }}
                            </button>
                            @endforeach
                        </div>
                        @error('markStatus') <div class="text-danger" style="font-size:12px; margin-top:4px;">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-2">
                        <label class="form-label">
                            Note
                            <span style="font-weight:400; color:var(--text-muted);">(optional)</span>
                        </label>
                        <input type="text"
                               wire:model="markNote"
                               class="form-control"
                               placeholder="e.g. sick leave, emergency">
                    </div>
                </div>

                <div class="modal-footer d-flex justify-content-between align-items-center">
                    <button class="btn btn-sm btn-outline-danger"
                            wire:click="clearAttendance"
                            wire:loading.attr="disabled"
                            wire:target="clearAttendance">
                        <span wire:loading wire:target="clearAttendance">
                            <span class="spinner-border spinner-border-sm"></span>
                        </span>
                        <span wire:loading.remove wire:target="clearAttendance">
                            <i class="bi bi-trash me-1"></i> Remove
                        </span>
                    </button>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-secondary"
                                data-bs-dismiss="modal"
                                wire:click="closeMarkModal">Cancel</button>
                        <button class="btn btn-sm btn-primary"
                                wire:click="markAttendance"
                                wire:loading.attr="disabled"
                                wire:target="markAttendance">
                            <span wire:loading wire:target="markAttendance">
                                <span class="spinner-border spinner-border-sm me-1"></span>
                            </span>
                            Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        const markModalEl = document.getElementById('markModal');
        const markModal   = new bootstrap.Modal(markModalEl);

        Livewire.on('open-mark-modal',  () => markModal.show());
        Livewire.on('close-mark-modal', () => markModal.hide());
    });
</script>
@endpush