<?php

namespace App\Http\Controllers;

class ReportsController extends Controller
{
    public function index()
    {
        return view('reports.sales');
    }

    public function items()
    {
        return view('reports.rentals');
    }

    public function allItems()
    {
        return view('reports.all-items');
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
}
