<div>
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">Code Registry</div>
            <div class="page-subtitle">All product design codes — numeric gaps and prefixed codes</div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="d-flex gap-2 mb-3">
        <div wire:click="$set('activeTab','all')"
            style="padding:7px 20px; border-radius:7px; font-size:13px; font-weight:600; cursor:pointer;
                   background:{{ $activeTab === 'all' ? 'var(--navy)' : '#fff' }};
                   color:{{ $activeTab === 'all' ? '#fff' : 'var(--text-muted)' }};
                   border:1px solid {{ $activeTab === 'all' ? 'var(--navy)' : 'var(--border)' }};">
            <i class="bi bi-grid me-1"></i> All Codes
        </div>
        <div wire:click="$set('activeTab','favourites')"
            style="padding:7px 20px; border-radius:7px; font-size:13px; font-weight:600; cursor:pointer;
                   background:{{ $activeTab === 'favourites' ? '#c9963a' : '#fff' }};
                   color:{{ $activeTab === 'favourites' ? '#fff' : 'var(--text-muted)' }};
                   border:1px solid {{ $activeTab === 'favourites' ? '#c9963a' : 'var(--border)' }};">
            <i class="bi bi-star-fill me-1"></i> Favourites
            @if ($totalFavourites > 0)
                <span
                    style="background:{{ $activeTab === 'favourites' ? 'rgba(255,255,255,0.25)' : '#fff3cd' }};
                             color:{{ $activeTab === 'favourites' ? '#fff' : '#856404' }};
                             border-radius:20px; padding:1px 8px; font-size:11px; margin-left:4px;">
                    {{ $totalFavourites }}
                </span>
            @endif
        </div>
    </div>

    {{-- Search + Filter bar --}}
    <div class="table-card mb-3">
        <div class="table-card-header" style="flex-wrap:wrap; gap:10px;">
            @if ($activeTab === 'all')
                <div class="d-flex gap-2 align-items-center">
                    <span style="font-size:11px; color:var(--text-muted); font-weight:600;">Numeric Filter:</span>
                    @foreach (['' => 'All', 'used' => 'Used Only', 'missing' => 'Missing Only'] as $val => $label)
                        <div wire:click="$set('filterStatus','{{ $val }}')"
                            style="background:{{ $filterStatus === $val ? 'var(--navy)' : '#fff' }};
                                   color:{{ $filterStatus === $val ? '#fff' : 'var(--text-muted)' }};
                                   border:1px solid {{ $filterStatus === $val ? 'var(--navy)' : 'var(--border)' }};
                                   border-radius:6px; padding:3px 12px; font-size:12px; cursor:pointer;">
                            {{ $label }}
                        </div>
                    @endforeach
                </div>
            @else
                <div
                    style="font-size:12px; color:#856404; background:#fffbeb; border:1px solid #f6e05e; border-radius:6px; padding:5px 14px;">
                    <i class="bi bi-star-fill me-1" style="color:#c9963a;"></i>
                    Showing {{ $totalFavourites }} favourite code(s)
                </div>
            @endif
            <div style="width:230px;">
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm"
                    placeholder="Search code or name...">
            </div>
        </div>
    </div>

    {{-- ── SECTION 1: Numeric codes ── --}}
    <div class="table-card mb-4">
        <div
            style="padding:12px 16px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:12px; background:#f8f9fa;">
            <span style="font-size:15px; font-weight:800; color:var(--navy); font-family:monospace;">Numeric
                Codes</span>
            @if ($activeTab === 'all')
                <span style="font-size:12px; color:var(--text-muted);">Range: 1 →
                    {{ number_format($maxNumeric) }}</span>
                <span
                    style="font-size:11px; background:#f0fff4; color:#276749; border:1px solid #9ae6b4; border-radius:20px; padding:2px 10px; font-weight:700;">
                    ✓ {{ $numericUsed }} used
                </span>
                @if ($numericMissing > 0)
                    <span
                        style="font-size:11px; background:#fff5f5; color:#c53030; border:1px solid #fc8181; border-radius:20px; padding:2px 10px; font-weight:700;">
                        ✗ {{ $numericMissing }} missing
                    </span>
                @endif
            @else
                <span
                    style="font-size:11px; background:#fff3cd; color:#856404; border-radius:20px; padding:2px 10px; font-weight:700;">
                    ★ {{ $numericUsed }} favourited
                </span>
            @endif
        </div>

        @if ($maxNumeric === 0 || count($numericRange) === 0)
            <div style="padding:30px; text-align:center; color:var(--text-muted); font-size:13px;">
                <i class="bi bi-upc" style="font-size:28px; display:block; margin-bottom:8px;"></i>
                {{ $activeTab === 'favourites' ? 'No numeric favourites yet' : 'No numeric codes found' }}
            </div>
        @else
            <table class="table table-sm mb-0" style="font-size:12px;">
                <thead>
                    <tr>
                        <th style="width:100px;">Code</th>
                        <th>Name</th>
                        <th style="width:90px;">Type</th>
                        <th style="width:90px;">Color</th>
                        <th style="width:80px;">Status</th>
                        <th style="width:50px;"></th>
                        <th style="width:120px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($numericRange as $number => $product)
                        @php
                            $isMissing = $product === null;
                            $codeStr = (string) $number;
                            $paddedCode = str_pad($number, 4, '0', STR_PAD_LEFT);
                            $isFav = isset($favouriteCodes[$codeStr]) || isset($favouriteCodes[$paddedCode]);
                        @endphp
                        <tr style="{{ $isMissing ? 'background:#fffaf0;' : '' }}">
                            <td
                                style="font-family:monospace; font-weight:{{ $isMissing ? '400' : '700' }};
                                       color:{{ $isMissing ? '#b7791f' : '#1a202c' }};">
                                {{ $paddedCode }}
                            </td>
                            <td>
                                @if ($isMissing)
                                    <span style="color:#b7791f; font-style:italic; font-size:11px;">— missing —</span>
                                @else
                                    <span style="font-weight:600;">{{ $product->name }}</span>
                                @endif
                            </td>
                            <td>
                                @if (!$isMissing)
                                    @php
                                        $tc = match ($product->type) {
                                            'rental' => ['bg' => '#ebf4ff', 'color' => '#2c5282', 'label' => 'Rental'],
                                            'sale' => ['bg' => '#f0fff4', 'color' => '#276749', 'label' => 'Sale'],
                                            'both' => ['bg' => '#f5f0ff', 'color' => '#553c9a', 'label' => 'Both'],
                                            'service' => [
                                                'bg' => '#fffbeb',
                                                'color' => '#744210',
                                                'label' => 'Service',
                                            ],
                                            'fabric' => ['bg' => '#e6fffa', 'color' => '#234e52', 'label' => 'Fabric'],
                                            default => [
                                                'bg' => '#f7fafc',
                                                'color' => '#718096',
                                                'label' => ucfirst($product->type),
                                            ],
                                        };
                                    @endphp
                                    <span
                                        style="background:{{ $tc['bg'] }}; color:{{ $tc['color'] }}; border-radius:4px; padding:1px 7px; font-size:10px; font-weight:700;">
                                        {{ $tc['label'] }}
                                    </span>
                                @endif
                            </td>
                            <td style="color:var(--text-muted);">{{ $product?->color ?? '' }}</td>
                            <td>
                                @if ($isMissing)
                                    <span style="font-size:10px; color:#b7791f; font-weight:600;">MISSING</span>
                                @elseif($product->is_abandoned)
                                    <span style="font-size:10px; color:#c53030; font-weight:600;">Abandoned</span>
                                @elseif(!$product->is_active)
                                    <span style="font-size:10px; color:#718096; font-weight:600;">Inactive</span>
                                @else
                                    <span style="font-size:10px; color:#276749; font-weight:600;">Active</span>
                                @endif
                            </td>
                            <td>
                                @if (!$isMissing)
                                    <a href="{{ route('products.index') }}?search={{ $product->code }}"
                                        style="font-size:11px; color:var(--navy);" title="View in Products">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                @endif
                            </td>
                            <td>
                                @if (!$isMissing)
                                    <button wire:click="toggleFavourite('{{ $codeStr }}')"
                                        style="border:1px solid {{ $isFav ? '#c9963a' : 'var(--border)' }};
                                               background:{{ $isFav ? '#fffbeb' : '#fff' }};
                                               color:{{ $isFav ? '#c9963a' : 'var(--text-muted)' }};
                                               border-radius:5px; padding:2px 10px; font-size:11px;
                                               font-weight:600; cursor:pointer; white-space:nowrap;">
                                        <i class="bi bi-star{{ $isFav ? '-fill' : '' }} me-1"></i>
                                        {{ $isFav ? 'Unfavourite' : 'Favourite' }}
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- ── SECTION 2: Prefixed codes ── --}}
    @if (count($prefixedRows) > 0)
        @foreach ($prefixedRows as $prefix => $products)
            <div class="table-card mb-3">
                <div
                    style="padding:12px 16px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:12px; background:#f8f9fa;">
                    <span
                        style="font-family:monospace; font-size:15px; font-weight:800; color:var(--navy);">{{ $prefix }}-*</span>
                    <span
                        style="font-size:11px; background:#f0fff4; color:#276749; border:1px solid #9ae6b4; border-radius:20px; padding:2px 10px; font-weight:700;">
                        {{ count($products) }} registered
                    </span>
                    @if ($activeTab === 'all')
                        <span style="font-size:11px; color:var(--text-muted);">Gap detection not available for prefixed
                            codes</span>
                    @endif
                </div>

                <table class="table table-sm mb-0" style="font-size:12px;">
                    <thead>
                        <tr>
                            <th style="width:110px;">Code</th>
                            <th>Name</th>
                            <th style="width:90px;">Type</th>
                            <th style="width:90px;">Color</th>
                            <th style="width:80px;">Status</th>
                            <th style="width:50px;"></th>
                            <th style="width:120px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            @php
                                $codeKey = strtoupper($product->code);
                                $isFav = isset($favouriteCodes[$codeKey]);
                                $tc = match ($product->type) {
                                    'rental' => ['bg' => '#ebf4ff', 'color' => '#2c5282', 'label' => 'Rental'],
                                    'sale' => ['bg' => '#f0fff4', 'color' => '#276749', 'label' => 'Sale'],
                                    'both' => ['bg' => '#f5f0ff', 'color' => '#553c9a', 'label' => 'Both'],
                                    'service' => ['bg' => '#fffbeb', 'color' => '#744210', 'label' => 'Service'],
                                    'fabric' => ['bg' => '#e6fffa', 'color' => '#234e52', 'label' => 'Fabric'],
                                    default => [
                                        'bg' => '#f7fafc',
                                        'color' => '#718096',
                                        'label' => ucfirst($product->type),
                                    ],
                                };
                            @endphp
                            <tr>
                                <td style="font-family:monospace; font-weight:700; color:#1a202c;">
                                    {{ strtoupper($product->code) }}
                                </td>
                                <td style="font-weight:600;">{{ $product->name }}</td>
                                <td>
                                    <span
                                        style="background:{{ $tc['bg'] }}; color:{{ $tc['color'] }}; border-radius:4px; padding:1px 7px; font-size:10px; font-weight:700;">
                                        {{ $tc['label'] }}
                                    </span>
                                </td>
                                <td style="color:var(--text-muted);">{{ $product->color ?? '—' }}</td>
                                <td>
                                    @if ($product->is_abandoned)
                                        <span style="font-size:10px; color:#c53030; font-weight:600;">Abandoned</span>
                                    @elseif(!$product->is_active)
                                        <span style="font-size:10px; color:#718096; font-weight:600;">Inactive</span>
                                    @else
                                        <span style="font-size:10px; color:#276749; font-weight:600;">Active</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('products.index') }}?search={{ $product->code }}"
                                        style="font-size:11px; color:var(--navy);" title="View in Products">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                </td>
                                <td>
                                    <button wire:click="toggleFavourite('{{ $codeKey }}')"
                                        style="border:1px solid {{ $isFav ? '#c9963a' : 'var(--border)' }};
                                               background:{{ $isFav ? '#fffbeb' : '#fff' }};
                                               color:{{ $isFav ? '#c9963a' : 'var(--text-muted)' }};
                                               border-radius:5px; padding:2px 10px; font-size:11px;
                                               font-weight:600; cursor:pointer; white-space:nowrap;">
                                        <i class="bi bi-star{{ $isFav ? '-fill' : '' }} me-1"></i>
                                        {{ $isFav ? 'Unfavourite' : 'Favourite' }}
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @elseif($activeTab === 'favourites')
        <div class="table-card" style="padding:30px; text-align:center; color:var(--text-muted); font-size:13px;">
            <i class="bi bi-star" style="font-size:28px; display:block; margin-bottom:8px;"></i>
            No prefixed code favourites yet
        </div>
    @endif
</div>
