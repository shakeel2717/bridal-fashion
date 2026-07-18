@extends('layouts.app')
@section('title', 'Reports')
@section('content')

<div class="page-title mb-1">Reports</div>
<div class="page-subtitle mb-4">Choose a report to view</div>

@php
    $cards = [
        ['route' => 'reports.sales',           'name' => 'Sales Report',            'desc' => 'Sales revenue, collection & due',        'icon' => 'bi-cart-check-fill',   'wrap' => 'green',  'color' => '#38a169'],
        ['route' => 'reports.purchase-sale',   'name' => 'Purchase & Sale Report',  'desc' => 'Purchase orders vs sales',               'icon' => 'bi-arrow-left-right',  'wrap' => 'gold',   'color' => '#c9963a'],
        ['route' => 'reports.rentals',         'name' => 'Rentals Report',          'desc' => 'Rental activity & summaries',             'icon' => 'bi-box-seam-fill',     'wrap' => 'purple', 'color' => '#553c9a'],
        ['route' => 'reports.all-items',       'name' => 'Stock / All Items',       'desc' => 'Category-wise stock & bookings',          'icon' => 'bi-grid-fill',         'wrap' => 'teal',   'color' => '#319795'],
        ['route' => 'reports.top-items',       'name' => 'Top & Lowest Items',      'desc' => 'Best and worst performing items',         'icon' => 'bi-trophy-fill',       'wrap' => 'gold',   'color' => '#c9963a'],
        ['route' => 'reports.customer-vendor', 'name' => 'Customers & Vendors',     'desc' => 'Activity per customer and vendor',        'icon' => 'bi-people-fill',       'wrap' => 'purple', 'color' => '#805ad5'],
        ['route' => 'reports.employee',        'name' => 'Employees Report',        'desc' => 'Attendance, business & financials',       'icon' => 'bi-person-badge-fill', 'wrap' => 'red',    'color' => '#d53f8c'],
    ];
@endphp

<div class="row g-3">
    @foreach ($cards as $card)
        <div class="col-4">
            <a href="{{ route($card['route']) }}" class="module-card" style="display:block; text-decoration:none;">
                <div class="mod-icon-wrap {{ $card['wrap'] }}">
                    <i class="bi {{ $card['icon'] }}" style="font-size:22px; color:{{ $card['color'] }};"></i>
                </div>
                <div class="mod-name">{{ $card['name'] }}</div>
                <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">
                    {{ $card['desc'] }}
                </div>
            </a>
        </div>
    @endforeach
</div>

@endsection
