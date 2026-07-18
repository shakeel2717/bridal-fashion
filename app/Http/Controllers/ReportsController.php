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

    // Legacy /reports/item URL -> rental reports page (item search now lives inside it)
    public function items()
    {
        return view('reports.rentals');
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
