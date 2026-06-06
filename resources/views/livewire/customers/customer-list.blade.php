<div>
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success py-2 mb-3" style="font-size:13px;">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger py-2 mb-3" style="font-size:13px;">
            <i class="bi bi-exclamation-circle me-1"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="page-title">Customers</div>
            <div class="page-subtitle">Manage registered customers</div>
        </div>
        <button class="btn btn-primary btn-sm d-flex align-items-center gap-2"
                wire:click="$dispatch('open-customer-modal')"
                onclick="Livewire.dispatch('open-create-customer')">
            <i class="bi bi-plus-lg"></i> Add Customer
        </button>
    </div>

    {{-- Filter Tabs + Search --}}
    <div class="table-card mb-0">
        <div class="table-card-header">
            <div class="d-flex align-items-center gap-3">
                <button wire:click="$set('filter','regular')"
                        class="btn btn-sm {{ $filter === 'regular' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    Regular
                    <span class="ms-1" style="font-size:10px;">({{ $counts['regular'] }})</span>
                </button>
                <button wire:click="$set('filter','walkin')"
                        class="btn btn-sm {{ $filter === 'walkin' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    Walk-in
                    <span class="ms-1" style="font-size:10px;">({{ $counts['walkin'] }})</span>
                </button>
                <button wire:click="$set('filter','all')"
                        class="btn btn-sm {{ $filter === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    All
                    <span class="ms-1" style="font-size:10px;">({{ $counts['all'] }})</span>
                </button>
            </div>
            <div style="width:260px;">
                <input type="text"
                       wire:model.live.debounce.400ms="search"
                       class="form-control form-control-sm"
                       placeholder="Search name, phone, CNIC...">
            </div>
        </div>

        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="width:42px;">#</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>WhatsApp</th>
                    <th>CNIC</th>
                    <th>Type</th>
                    <th>Rentals</th>
                    <th>Sales</th>
                    <th style="width:100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                <tr>
                    <td style="color:var(--text-muted); font-size:11px;">{{ $customer->id }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if($customer->photo)
                                <img src="{{ Storage::url($customer->photo) }}"
                                     class="customer-photo" alt="">
                            @else
                                <div class="customer-avatar">
                                    {{ strtoupper(substr($customer->name, 0, 2)) }}
                                </div>
                            @endif
                            <div>
                                <div style="font-weight:600; font-size:13px;">{{ $customer->name }}</div>
                                @if($customer->address)
                                    <div style="font-size:11px; color:var(--text-muted);">
                                        {{ Str::limit($customer->address, 30) }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td style="font-size:13px;">
                        {{ $customer->phone1 }}
                        @if($customer->phone2)
                            <div style="font-size:11px; color:var(--text-muted);">{{ $customer->phone2 }}</div>
                        @endif
                    </td>
                    <td style="font-size:13px;">{{ $customer->whatsapp ?? '—' }}</td>
                    <td style="font-size:13px; font-family:monospace;">{{ $customer->cnic ?? '—' }}</td>
                    <td>
                        @if($customer->is_walkin)
                            <span class="customer-badge-walkin">Walk-in</span>
                        @else
                            <span class="customer-badge-regular">Regular</span>
                        @endif
                    </td>
                    <td style="font-size:13px; font-weight:600;">
                        {{ $customer->rentals_count }}
                    </td>
                    <td style="font-size:13px; font-weight:600;">
                        {{ $customer->sales_count }}
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            @if(!$customer->is_walkin)
                            <button class="btn btn-sm btn-outline-secondary"
                                    style="padding:3px 8px;"
                                    wire:click="$dispatch('open-edit-customer', { id: {{ $customer->id }} })"
                                    title="Edit">
                                <i class="bi bi-pencil" style="font-size:12px;"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger"
                                    style="padding:3px 8px;"
                                    wire:click="confirmDelete({{ $customer->id }})"
                                    title="Delete">
                                <i class="bi bi-trash" style="font-size:12px;"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center; padding:30px; color:var(--text-muted); font-size:13px;">
                        <i class="bi bi-people" style="font-size:32px; display:block; margin-bottom:8px;"></i>
                        No customers found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($customers->hasPages())
        <div style="padding:12px 16px; border-top:1px solid var(--border);">
            {{ $customers->links('vendor.pagination.simple-bootstrap-5') }}
        </div>
        @endif
    </div>

    {{-- Delete Confirm Modal --}}
    @if($deleteId)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered" style="max-width:380px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Confirm Delete</h6>
                </div>
                <div class="modal-body" style="font-size:13px;">
                    Are you sure you want to delete this customer? This action cannot be undone.
                </div>
                <div class="modal-footer gap-2">
                    <button class="btn btn-sm btn-outline-secondary"
                            wire:click="$set('deleteId', null)">Cancel</button>
                    <button class="btn btn-sm btn-danger"
                            wire:click="delete()">Yes, Delete</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Customer Form Modal --}}
    <livewire:customers.customer-form />
</div>