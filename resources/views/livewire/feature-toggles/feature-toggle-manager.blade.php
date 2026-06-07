<div>
    @if (session('success'))
        <div class="alert alert-success py-2 mb-3" style="font-size:13px;">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">Feature Permissions</div>
            <div class="page-subtitle">Control which features each employee can access</div>
        </div>
    </div>

    <div class="row g-3">

        {{-- Left: Employee List --}}
        <div class="col-3">
            {{-- Global Defaults Card --}}
            <div class="table-card mb-3" style="padding:14px 16px;">
                <div
                    style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:10px;">
                    <i class="bi bi-globe me-1"></i> Global Defaults
                </div>
                <div style="font-size:12px; color:var(--text-muted); margin-bottom:10px; line-height:1.5;">
                    These apply to all employees unless overridden individually.
                </div>
                @foreach (\App\Livewire\FeatureToggles\FeatureToggleManager::FEATURES as $feature => $info)
                    @php
                        $global = $globalToggles[$feature] ?? null;
                        $enabled = $global?->is_enabled ?? true;
                    @endphp
                    <div
                        style="display:flex; align-items:center; justify-content:space-between; padding:5px 0; border-bottom:1px solid var(--border);">
                        <span style="font-size:12px; color:var(--text-primary);">{{ $info['label'] }}</span>
                        <div style="display:flex; align-items:center; gap:6px;">
                            <span
                                style="font-size:10px; color:{{ $enabled ? '#276749' : '#c53030' }}; font-weight:600;">
                                {{ $enabled ? 'On' : 'Off' }}
                            </span>
                            <button wire:click="updateGlobal('{{ $feature }}', {{ $enabled ? 'false' : 'true' }})"
                                style="width:36px; height:20px; border-radius:10px; border:none; cursor:pointer; transition:all 0.15s;
                                       background:{{ $enabled ? '#38a169' : '#cbd5e0' }}; position:relative;">
                                <div
                                    style="width:14px; height:14px; background:#fff; border-radius:50%; position:absolute; top:3px;
                                        {{ $enabled ? 'right:3px;' : 'left:3px;' }} transition:all 0.15s;">
                                </div>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Employee List --}}
            <div class="table-card" style="padding:14px 16px;">
                <div
                    style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:10px;">
                    <i class="bi bi-people me-1"></i> Employees
                </div>
                @forelse($employees as $emp)
                    <div wire:click="selectUser({{ $emp->id }})"
                        style="display:flex; align-items:center; gap:10px; padding:8px 10px; border-radius:8px; cursor:pointer; margin-bottom:4px;
                            background:{{ $selectedUserId === $emp->id ? 'var(--gold-light)' : 'transparent' }};
                            border:1.5px solid {{ $selectedUserId === $emp->id ? 'var(--gold)' : 'transparent' }};
                            transition:all 0.15s;">
                        <div
                            style="width:32px; height:32px; border-radius:50%; background:var(--navy); color:#fff; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; flex-shrink:0;">
                            {{ strtoupper(substr($emp->name, 0, 2)) }}
                        </div>
                        <div>
                            <div style="font-size:12px; font-weight:600; color:var(--text-primary);">
                                {{ $emp->name }}</div>
                            <div style="font-size:10px; color:var(--text-muted);">{{ $emp->designation ?? 'Employee' }}
                            </div>
                        </div>
                        @if ($selectedUserId === $emp->id)
                            <i class="bi bi-chevron-right ms-auto" style="color:var(--gold); font-size:12px;"></i>
                        @endif
                    </div>
                @empty
                    <div style="font-size:12px; color:var(--text-muted); text-align:center; padding:16px 0;">
                        No active employees
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Right: Feature Toggles --}}
        <div class="col-9">
            @if (!$selectedUserId)
                <div class="table-card" style="padding:60px 20px; text-align:center;">
                    <i class="bi bi-shield-lock"
                        style="font-size:48px; color:var(--gold); display:block; margin-bottom:16px;"></i>
                    <div style="font-size:14px; font-weight:600; color:var(--text-primary); margin-bottom:8px;">
                        Select an Employee
                    </div>
                    <div style="font-size:12px; color:var(--text-muted);">
                        Choose an employee from the left to manage their feature access.
                    </div>
                </div>
            @else
                @php $selectedEmployee = $employees->find($selectedUserId); @endphp

                {{-- Employee Header --}}
                <div class="table-card mb-3" style="padding:16px 20px;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div
                                style="width:44px; height:44px; border-radius:50%; background:var(--navy); color:#fff; display:flex; align-items:center; justify-content:center; font-size:14px; font-weight:700;">
                                {{ strtoupper(substr($selectedEmployee->name, 0, 2)) }}
                            </div>
                            <div>
                                <div style="font-size:14px; font-weight:700; color:var(--text-primary);">
                                    {{ $selectedEmployee->name }}
                                </div>
                                <div style="font-size:12px; color:var(--text-muted);">
                                    {{ $selectedEmployee->designation ?? 'Employee' }}
                                    · {{ $selectedEmployee->email }}
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-success action-btn" wire:click="enableAll">
                                <i class="bi bi-check-all me-1"></i> Enable All
                            </button>
                            <button class="btn btn-sm btn-outline-danger action-btn" wire:click="disableAll">
                                <i class="bi bi-x-circle me-1"></i> Disable All
                            </button>
                            <button class="btn btn-sm btn-outline-secondary action-btn" wire:click="resetAllToGlobal"
                                title="Reset all to global defaults">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset All
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Feature Grid --}}
                <div class="row g-3">
                    {{-- Feature Groups --}}
                    @php
                        $moduleFeatures = array_filter(
                            \App\Livewire\FeatureToggles\FeatureToggleManager::FEATURES,
                            fn($k) => !str_starts_with($k, 'stat_') && !str_starts_with($k, 'dash_'),
                            ARRAY_FILTER_USE_KEY,
                        );
                        $statFeatures = array_filter(
                            \App\Livewire\FeatureToggles\FeatureToggleManager::FEATURES,
                            fn($k) => str_starts_with($k, 'stat_'),
                            ARRAY_FILTER_USE_KEY,
                        );
                        $dashFeatures = array_filter(
                            \App\Livewire\FeatureToggles\FeatureToggleManager::FEATURES,
                            fn($k) => str_starts_with($k, 'dash_'),
                            ARRAY_FILTER_USE_KEY,
                        );
                    @endphp

                    {{-- Modules --}}
                    <div
                        style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:10px; margin-top:4px;">
                        <i class="bi bi-grid me-1"></i> Module Access
                    </div>
                    <div class="row g-3 mb-4">
                        @foreach ($moduleFeatures as $feature => $info)
                            @php
                                $state = $toggles[$feature] ?? ['enabled' => true, 'overridden' => false];
                                $enabled = $state['enabled'];
                                $overridden = $state['overridden'];
                            @endphp
                            <div class="col-4">
                                <div
                                    style="background:#fff; border-radius:10px; padding:14px; border:1.5px solid {{ $enabled ? '#9ae6b4' : '#fed7d7' }};">
                                    <div class="d-flex align-items-start justify-content-between mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <div
                                                style="width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:15px;
                                background:{{ $enabled ? '#f0fff4' : '#fff5f5' }};
                                color:{{ $enabled ? '#276749' : '#c53030' }};">
                                                <i class="bi {{ $info['icon'] }}"></i>
                                            </div>
                                            <div>
                                                <div
                                                    style="font-size:12px; font-weight:700; color:var(--text-primary);">
                                                    {{ $info['label'] }}</div>
                                                @if ($overridden)
                                                    <span
                                                        style="font-size:9px; font-weight:700; padding:1px 5px; border-radius:3px; background:#ebf8ff; color:#2c5282;">CUSTOM</span>
                                                @else
                                                    <span
                                                        style="font-size:9px; font-weight:700; padding:1px 5px; border-radius:3px; background:#f7fafc; color:#718096;">GLOBAL</span>
                                                @endif
                                            </div>
                                        </div>
                                        <button wire:click="toggle('{{ $feature }}')"
                                            style="width:44px; height:22px; border-radius:11px; border:none; cursor:pointer; transition:all 0.2s; flex-shrink:0;
                               background:{{ $enabled ? '#38a169' : '#cbd5e0' }}; position:relative;">
                                            <div
                                                style="width:16px; height:16px; background:#fff; border-radius:50%; position:absolute; top:3px;
                                {{ $enabled ? 'right:3px;' : 'left:3px;' }} transition:all 0.2s;">
                                            </div>
                                        </button>
                                    </div>
                                    <div style="font-size:10px; color:var(--text-muted); margin-bottom:8px;">
                                        {{ $info['desc'] }}</div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span
                                            style="font-size:11px; font-weight:700; color:{{ $enabled ? '#276749' : '#c53030' }};">
                                            {{ $enabled ? 'Enabled' : 'Disabled' }}
                                        </span>
                                        @if ($overridden)
                                            <button wire:click="resetToGlobal('{{ $feature }}')"
                                                style="font-size:10px; background:none; border:none; color:var(--text-muted); cursor:pointer; text-decoration:underline;">
                                                Reset
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Dashboard Stat Cards --}}
                    <div
                        style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:10px;">
                        <i class="bi bi-bar-chart me-1"></i> Dashboard Stat Cards
                        <span style="font-size:10px; font-weight:400; margin-left:6px; color:#c53030;">
                            ⚠ Confidential stats should be hidden from employees
                        </span>
                    </div>
                    <div class="row g-3 mb-4">
                        @foreach ($statFeatures as $feature => $info)
                            @php
                                $state = $toggles[$feature] ?? ['enabled' => true, 'overridden' => false];
                                $enabled = $state['enabled'];
                                $overridden = $state['overridden'];
                                $isConfidential = in_array($feature, [
                                    'stat_monthly_revenue',
                                    'stat_pending_balance',
                                    'stat_total_cash',
                                    'stat_expenses',
                                    'stat_pending_po',
                                ]);
                            @endphp
                            <div class="col-4">
                                <div
                                    style="background:#fff; border-radius:10px; padding:14px; border:1.5px solid {{ $enabled ? '#9ae6b4' : '#fed7d7' }};
                    {{ $isConfidential ? 'background: #fffff0;' : '' }}">
                                    <div class="d-flex align-items-start justify-content-between mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <div
                                                style="width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:15px;
                                background:{{ $enabled ? '#f0fff4' : '#fff5f5' }};
                                color:{{ $enabled ? '#276749' : '#c53030' }};">
                                                <i class="bi {{ $info['icon'] }}"></i>
                                            </div>
                                            <div>
                                                <div
                                                    style="font-size:12px; font-weight:700; color:var(--text-primary);">
                                                    {{ $info['label'] }}</div>
                                                <div class="d-flex gap-1">
                                                    @if ($isConfidential)
                                                        <span
                                                            style="font-size:9px; font-weight:700; padding:1px 5px; border-radius:3px; background:#fff5f5; color:#c53030;">
                                                            CONFIDENTIAL
                                                        </span>
                                                    @endif
                                                    @if ($overridden)
                                                        <span
                                                            style="font-size:9px; font-weight:700; padding:1px 5px; border-radius:3px; background:#ebf8ff; color:#2c5282;">CUSTOM</span>
                                                    @else
                                                        <span
                                                            style="font-size:9px; font-weight:700; padding:1px 5px; border-radius:3px; background:#f7fafc; color:#718096;">GLOBAL</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <button wire:click="toggle('{{ $feature }}')"
                                            style="width:44px; height:22px; border-radius:11px; border:none; cursor:pointer; transition:all 0.2s; flex-shrink:0;
                               background:{{ $enabled ? '#38a169' : '#cbd5e0' }}; position:relative;">
                                            <div
                                                style="width:16px; height:16px; background:#fff; border-radius:50%; position:absolute; top:3px;
                                {{ $enabled ? 'right:3px;' : 'left:3px;' }} transition:all 0.2s;">
                                            </div>
                                        </button>
                                    </div>
                                    <div style="font-size:10px; color:var(--text-muted); margin-bottom:8px;">
                                        {{ $info['desc'] }}</div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span
                                            style="font-size:11px; font-weight:700; color:{{ $enabled ? '#276749' : '#c53030' }};">
                                            {{ $enabled ? 'Visible' : 'Hidden' }}
                                        </span>
                                        @if ($overridden)
                                            <button wire:click="resetToGlobal('{{ $feature }}')"
                                                style="font-size:10px; background:none; border:none; color:var(--text-muted); cursor:pointer; text-decoration:underline;">
                                                Reset
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Dashboard Bottom Cards --}}
                    <div
                        style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:10px;">
                        <i class="bi bi-layout-text-sidebar me-1"></i> Dashboard Info Cards (Bottom)
                    </div>
                    <div class="row g-3">
                        @foreach ($dashFeatures as $feature => $info)
                            @php
                                $state = $toggles[$feature] ?? ['enabled' => true, 'overridden' => false];
                                $enabled = $state['enabled'];
                                $overridden = $state['overridden'];
                            @endphp
                            <div class="col-4">
                                <div
                                    style="background:#fff; border-radius:10px; padding:14px; border:1.5px solid {{ $enabled ? '#9ae6b4' : '#fed7d7' }};">
                                    <div class="d-flex align-items-start justify-content-between mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <div
                                                style="width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:15px;
                                background:{{ $enabled ? '#f0fff4' : '#fff5f5' }};
                                color:{{ $enabled ? '#276749' : '#c53030' }};">
                                                <i class="bi {{ $info['icon'] }}"></i>
                                            </div>
                                            <div>
                                                <div
                                                    style="font-size:12px; font-weight:700; color:var(--text-primary);">
                                                    {{ $info['label'] }}</div>
                                                @if ($overridden)
                                                    <span
                                                        style="font-size:9px; font-weight:700; padding:1px 5px; border-radius:3px; background:#ebf8ff; color:#2c5282;">CUSTOM</span>
                                                @else
                                                    <span
                                                        style="font-size:9px; font-weight:700; padding:1px 5px; border-radius:3px; background:#f7fafc; color:#718096;">GLOBAL</span>
                                                @endif
                                            </div>
                                        </div>
                                        <button wire:click="toggle('{{ $feature }}')"
                                            style="width:44px; height:22px; border-radius:11px; border:none; cursor:pointer; transition:all 0.2s; flex-shrink:0;
                               background:{{ $enabled ? '#38a169' : '#cbd5e0' }}; position:relative;">
                                            <div
                                                style="width:16px; height:16px; background:#fff; border-radius:50%; position:absolute; top:3px;
                                {{ $enabled ? 'right:3px;' : 'left:3px;' }} transition:all 0.2s;">
                                            </div>
                                        </button>
                                    </div>
                                    <div style="font-size:10px; color:var(--text-muted); margin-bottom:8px;">
                                        {{ $info['desc'] }}</div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span
                                            style="font-size:11px; font-weight:700; color:{{ $enabled ? '#276749' : '#c53030' }};">
                                            {{ $enabled ? 'Visible' : 'Hidden' }}
                                        </span>
                                        @if ($overridden)
                                            <button wire:click="resetToGlobal('{{ $feature }}')"
                                                style="font-size:10px; background:none; border:none; color:var(--text-muted); cursor:pointer; text-decoration:underline;">
                                                Reset
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
