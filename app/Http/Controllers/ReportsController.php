<?php

namespace App\Http\Controllers;

class ReportsController extends Controller
{
    // Landing page with report cards
    public function index()
    {
        return view('reports.index');
    }

    // Sales report (previously lived at /reports)
    public function sales()
    {
        return view('reports.sales');
    }

    // Single Item booking search (search a product code -> all its bookings)
    public function items()
    {
        return view('reports.items');
    }

    // Rentals analytics report
    public function rentals()
    {
        return view('reports.rentals');
    }

    public function allItems()
    {
        return view('reports.stock');
    }

    public function topItems()
    {
        return view('reports.top-items');
    }

    public function purchaseSale()
    {
        return view('reports.purchase');
    }

    public function customerVendor()
    {
        return view('reports.customer-vendor');
    }

    public function employee()
    {
        return view('reports.employee');
    }
}
