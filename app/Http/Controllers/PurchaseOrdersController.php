<?php
// app/Http/Controllers/PurchaseOrdersController.php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;

class PurchaseOrdersController extends Controller
{
    public function index()  { return view('purchase-orders.index'); }
    public function create() { return view('purchase-orders.create'); }
    public function show(PurchaseOrder $purchaseOrder)
    {
        return view('purchase-orders.show', ['po' => $purchaseOrder]);
    }
}