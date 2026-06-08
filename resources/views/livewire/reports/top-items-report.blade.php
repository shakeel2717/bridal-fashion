<div>
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
        <div>
            <div class="page-title">Top & Lowest Items</div>
            <div class="page-subtitle">Best and worst performing products</div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="table-card mb-3" style="padding:14px 20px;">
        <div class="d-flex gap-3 align-items-end flex-wrap">
            <div>
                <label class="form-label">From Date</label>
                <input type="date" wire:model.live="dateFrom" class="form-control form-control-sm" style="width:150px;">
            </div>
            <div>
                <label class="form-label">To Date</label>
                <input type="date" wire:model.live="dateTo" class="form-control form-control-sm" style="width:150px;">
            </div>
            <div>
                <label class="form-label">Type</label>
                <div class="walkin-toggle" style="max-width:200px;">
                    <button type="button"
                            class="toggle-btn {{ $filterType === 'rental' ? 'active' : '' }}"
                            wire:click="$set('filterType','rental')">Rental</button>
                    <button type="button"
                            class="toggle-btn {{ $filterType === 'sale' ? 'active' : '' }}"
                            wire:click="$set('filterType','sale')">Sale</button>
                </div>
            </div>
            <div>
                <label class="form-label">Show Top</label>
                <select wire:model.live="limit" class="form-select form-select-sm" style="width:100px;">
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Top Items --}}
        <div class="col-6">
            <div class="table-card">
                <div class="table-card-header">
                    <span class="table-card-title" style="color:#276749;">
                        <i class="bi bi-trophy-fill me-1" style="color:var(--gold);"></i>
                        Top {{ $limit }} Items
                    </span>
                </div>
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width:30px;">#</th>
                            <th>Product</th>
                            <th style="text-align:center;">Count</th>
                            <th style="text-align:right;">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topItems as $i => $item)
                        <tr>
                            <td>
                                <span style="font-size:12px; font-weight:800;
                                    color:{{ $i === 0 ? '#c9963a' : ($i === 1 ? '#718096' : ($i === 2 ? '#c05621' : 'var(--text-muted)')) }};">
                                    {{ $i + 1 }}
                                </span>
                            </td>
                            <td>
                                <span class="tbl-code-badge" style="font-size:10px;">{{ $item['code'] }}</span>
                                <div style="font-size:12px; font-weight:600;">{{ $item['name'] }}</div>
                                <div style="font-size:10px; color:var(--text-muted);">{{ $item['category'] }}</div>
                            </td>
                            <td style="text-align:center; font-size:16px; font-weight:800; color:#276749;">
                                {{ $item['booking_count'] }}
                            </td>
                            <td style="text-align:right; font-size:12px; font-weight:700;">
                                Rs. {{ number_format($item['revenue'], 0) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" style="text-align:center; padding:20px; color:var(--text-muted);">
                                No data
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Lowest Items --}}
        <div class="col-6">
            <div class="table-card">
                <div class="table-card-header">
                    <span class="table-card-title" style="color:#c53030;">
                        <i class="bi bi-arrow-down-circle-fill me-1" style="color:#fc8181;"></i>
                        Lowest {{ $limit }} Items
                    </span>
                </div>
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width:30px;">#</th>
                            <th>Product</th>
                            <th style="text-align:center;">Count</th>
                            <th style="text-align:right;">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lowestItems as $i => $item)
                        <tr>
                            <td style="font-size:12px; color:var(--text-muted);">{{ $i + 1 }}</td>
                            <td>
                                <span class="tbl-code-badge" style="font-size:10px;">{{ $item['code'] }}</span>
                                <div style="font-size:12px; font-weight:600;">{{ $item['name'] }}</div>
                                <div style="font-size:10px; color:var(--text-muted);">{{ $item['category'] }}</div>
                            </td>
                            <td style="text-align:center; font-size:16px; font-weight:800; color:#c53030;">
                                {{ $item['booking_count'] }}
                            </td>
                            <td style="text-align:right; font-size:12px; font-weight:700;">
                                Rs. {{ number_format($item['revenue'], 0) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" style="text-align:center; padding:20px; color:var(--text-muted);">
                                No data
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>