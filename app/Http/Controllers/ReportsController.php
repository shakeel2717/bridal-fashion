<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;

class ReportsController extends Controller
{
    public function index()    { return view('reports.index'); }
    public function items()    { return view('reports.items'); }
    public function allItems() { return view('reports.all-items'); }
    public function topItems() { return view('reports.top-items'); }
    public function purchaseSale() { return view('reports.purchase-sale'); }
    public function customerVendor() { return view('reports.customer-vendor'); }
}