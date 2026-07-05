<div>
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">Bill Books</div>
            <div class="page-subtitle">Track manual receipt book usage</div>
        </div>
        <button wire:click="openForm()" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Add Bill Book
        </button>
    </div>

    {{-- Stats --}}
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:16px;">
        @foreach([
            ['Total Books',  $stats['total'],  '#3182ce','#ebf8ff','bi-book'],
            ['Active',       $stats['active'], '#38a169','#f0fff4','bi-check-circle'],
            ['Rental Books', $stats['rental'], '#805ad5','#faf5ff','bi-box-seam'],
            ['Sale Books',   $stats['sale'],   '#d69e2e','#fffff0','bi-bag'],
        ] as [$lbl,$val,$col,$bg,$icon])
            <div style="background:{{ $bg }}; border:1.5px solid {{ $col }}22; border-radius:10px; padding:14px 16px;">
                <div style="font-size:10px; font-weight:700; color:{{ $col }}; text-transform:uppercase; margin-bottom:4px;">
                    <i class="bi {{ $icon }} me-1"></i>{{ $lbl }}
                </div>
                <div style="font-size:24px; font-weight:800; color:{{ $col }};">{{ $val }}</div>
            </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="table-card mb-3" style="padding:10px 14px;">
        <div class="d-flex gap-2 align-items-center">
            <input type="text" wire:model.live.debounce.300ms="search"
                class="form-control form-control-sm" placeholder="Search bill book..." style="width:220px;">
            <select wire:model.live="filterType" class="form-select form-select-sm" style="width:130px;">
                <option value="">All Types</option>
                <option value="rental">Rental</option>
                <option value="sale">Sale</option>
                <option value="both">Both</option>
            </select>
        </div>
    </div>

    {{-- Bill Books Grid --}}
    @if($books->isEmpty())
        <div class="table-card" style="padding:50px; text-align:center; color:var(--text-muted);">
            <i class="bi bi-book" style="font-size:40px; display:block; margin-bottom:10px; color:#e2e8f0;"></i>
            <div style="font-size:14px; font-weight:600; color:#a0aec0;">No bill books added yet</div>
        </div>
    @else
        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:14px;">
            @foreach($books as $book)
                <div class="table-card" style="padding:16px;">
                    {{-- Top row --}}
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <div>
                            <div style="font-size:14px; font-weight:700; color:var(--navy);">{{ $book['name'] }}</div>
                            <div style="font-size:11px; color:var(--text-muted);">
                                @if($book['prefix'])
                                    Prefix: <strong>{{ $book['prefix'] }}</strong> ·
                                @endif
                                Range: <strong>{{ $book['from'] }}</strong> – <strong>{{ $book['to'] }}</strong>
                            </div>
                        </div>
                        <div class="d-flex gap-1 align-items-center">
                            @php $typeColors = ['rental'=>['#faf5ff','#805ad5'],'sale'=>['#fffff0','#d69e2e'],'both'=>['#f0fff4','#276749']]; $tc = $typeColors[$book['type']]; @endphp
                            <span style="background:{{ $tc[0] }}; color:{{ $tc[1] }}; border-radius:20px; padding:2px 9px; font-size:10px; font-weight:700;">
                                {{ ucfirst($book['type']) }}
                            </span>
                            @if(!$book['is_active'])
                                <span style="background:#f7f7f7; color:#718096; border-radius:20px; padding:2px 9px; font-size:10px; font-weight:700;">Inactive</span>
                            @endif
                        </div>
                    </div>

                    {{-- Progress bar --}}
                    <div style="margin-bottom:10px;">
                        <div style="display:flex; justify-content:space-between; font-size:11px; color:var(--text-muted); margin-bottom:3px;">
                            <span><span style="color:#38a169; font-weight:700;">{{ $book['used'] }}</span> used</span>
                            <span><span style="color:#c53030; font-weight:700;">{{ $book['missing'] }}</span> missing</span>
                            <span style="font-weight:700; color:var(--navy);">{{ $book['total'] }} total</span>
                        </div>
                        <div style="background:#e2e8f0; border-radius:99px; height:8px; overflow:hidden;">
                            <div style="width:{{ $book['pct'] }}%; height:100%; background:linear-gradient(90deg,#38a169,#68d391); border-radius:99px;"></div>
                        </div>
                        <div style="font-size:10px; color:var(--text-muted); text-align:right; margin-top:2px;">{{ $book['pct'] }}% used</div>
                    </div>

                    @if($book['notes'])
                        <div style="font-size:11px; color:var(--text-muted); margin-bottom:10px; font-style:italic;">{{ $book['notes'] }}</div>
                    @endif

                    {{-- Actions --}}
                    <div class="d-flex gap-2" style="border-top:1px solid var(--border); padding-top:10px; margin-top:4px;">
                        <a href="{{ route('bill-books.show', $book['id']) }}"
                            class="btn btn-sm btn-outline-primary" style="font-size:11px; flex:1; text-align:center;">
                            <i class="bi bi-table me-1"></i> View Detail
                        </a>
                        <button wire:click="openForm({{ $book['id'] }})" class="btn btn-sm btn-outline-secondary" title="Edit">
                            <i class="bi bi-pencil" style="font-size:11px;"></i>
                        </button>
                        <button wire:click="toggleActive({{ $book['id'] }})"
                            class="btn btn-sm {{ $book['is_active'] ? 'btn-outline-warning' : 'btn-outline-success' }}" title="{{ $book['is_active'] ? 'Deactivate' : 'Activate' }}">
                            <i class="bi {{ $book['is_active'] ? 'bi-pause-circle' : 'bi-play-circle' }}" style="font-size:11px;"></i>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Add/Edit Modal --}}
    @if($showForm)
        <div class="modal fade show" tabindex="-1" style="display:block; background:rgba(0,0,0,0.5);" wire:click.self="$set('showForm',false)">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header" style="background:var(--navy); color:#fff; padding:12px 20px;">
                        <div style="font-size:14px; font-weight:700;">
                            <i class="bi bi-book me-2" style="color:var(--gold);"></i>
                            {{ $editId ? 'Edit Bill Book' : 'Add New Bill Book' }}
                        </div>
                        <button wire:click="$set('showForm',false)" class="btn-close btn-close-white ms-auto" style="opacity:.8;"></button>
                    </div>
                    <div class="modal-body" style="padding:20px;">
                        <div class="mb-3">
                            <label class="form-label">Book Name <span class="text-danger">*</span></label>
                            <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror"
                                placeholder="e.g. Book 1, Jan-2025 Book">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-4">
                                <label class="form-label">Prefix <small class="text-muted">(optional)</small></label>
                                <input type="text" wire:model="prefix" class="form-control"
                                    placeholder="e.g. R, S, 24-">
                            </div>
                            <div class="col-4">
                                <label class="form-label">From <span class="text-danger">*</span></label>
                                <input type="number" wire:model="rangeFrom" class="form-control @error('rangeFrom') is-invalid @enderror"
                                    placeholder="1" min="1">
                                @error('rangeFrom') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-4">
                                <label class="form-label">To <span class="text-danger">*</span></label>
                                <input type="number" wire:model="rangeTo" class="form-control @error('rangeTo') is-invalid @enderror"
                                    placeholder="100" min="1">
                                @error('rangeTo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select wire:model="type" class="form-select">
                                <option value="both">Both (Rental + Sale)</option>
                                <option value="rental">Rental Only</option>
                                <option value="sale">Sale Only</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes <small class="text-muted">(optional)</small></label>
                            <input type="text" wire:model="notes" class="form-control" placeholder="Any notes...">
                        </div>
                        @if($rangeFrom && $rangeTo && (int)$rangeTo >= (int)$rangeFrom)
                            <div style="background:#f0f4ff; border-radius:8px; padding:10px 14px; font-size:12px; color:#3182ce;">
                                <i class="bi bi-info-circle me-1"></i>
                                This book covers <strong>{{ (int)$rangeTo - (int)$rangeFrom + 1 }}</strong> bill numbers
                                ({{ $prefix }}{{ $rangeFrom }} — {{ $prefix }}{{ $rangeTo }})
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer" style="padding:10px 20px; background:#f0f4f8;">
                        <button wire:click="$set('showForm',false)" class="btn btn-sm btn-outline-secondary">Cancel</button>
                        <button wire:click="save" class="btn btn-sm btn-primary">
                            <i class="bi bi-check-lg me-1"></i> {{ $editId ? 'Update' : 'Save' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>