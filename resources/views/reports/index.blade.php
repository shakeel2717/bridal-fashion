@extends('layouts.app')
@section('title', 'Reports')
@section('content')

<div class="page-title mb-1">Reports</div>
<div class="page-subtitle mb-4">Financial and business insights</div>

<div class="row g-3">
    <div class="col-4">
        <a href="{{ route('reports.item') }}" class="module-card" style="display:block; text-decoration:none;">
            <div class="mod-icon-wrap blue">
                <i class="bi bi-box-seam-fill" style="font-size:22px; color:#3182ce;"></i>
            </div>
            <div class="mod-name">Single Item Report</div>
            <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">
                Full booking history for one product
            </div>
        </a>
    </div>
    <div class="col-4">
        <a href="{{ route('reports.all-items') }}" class="module-card" style="display:block; text-decoration:none;">
            <div class="mod-icon-wrap teal">
                <i class="bi bi-grid-fill" style="font-size:22px; color:#319795;"></i>
            </div>
            <div class="mod-name">All Items Summary</div>
            <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">
                Category-wise booking summary
            </div>
        </a>
    </div>
    <div class="col-4">
        <a href="{{ route('reports.top-items') }}" class="module-card" style="display:block; text-decoration:none;">
            <div class="mod-icon-wrap gold">
                <i class="bi bi-trophy-fill" style="font-size:22px; color:#c9963a;"></i>
            </div>
            <div class="mod-name">Top & Lowest Items</div>
            <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">
                Best and worst performing items
            </div>
        </a>
    </div>
    <div class="col-4">
        <a href="{{ route('reports.purchase-sale') }}" class="module-card" style="display:block; text-decoration:none;">
            <div class="mod-icon-wrap green">
                <i class="bi bi-arrow-left-right" style="font-size:22px; color:#38a169;"></i>
            </div>
            <div class="mod-name">Purchase & Sale Report</div>
            <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">
                PO vs sales with date filter
            </div>
        </a>
    </div>
    <div class="col-4">
        <a href="{{ route('reports.customer-vendor') }}" class="module-card" style="display:block; text-decoration:none;">
            <div class="mod-icon-wrap purple">
                <i class="bi bi-people-fill" style="font-size:22px; color:#805ad5;"></i>
            </div>
            <div class="mod-name">Customer & Vendor Report</div>
            <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">
                Activity per customer and vendor
            </div>
        </a>
    </div>
    <div class="col-4">
        <a href="{{ route('reports.index') }}#overview" class="module-card" style="display:block; text-decoration:none;">
            <div class="mod-icon-wrap red">
                <i class="bi bi-bar-chart-fill" style="font-size:22px; color:#e53e3e;"></i>
            </div>
            <div class="mod-name">Monthly Overview</div>
            <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">
                Income, expenses, profit summary
            </div>
        </a>
    </div>
</div>

@endsection