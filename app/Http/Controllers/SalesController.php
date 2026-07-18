<?php
// app/Http/Controllers/SalesController.php

namespace App\Http\Controllers;

use App\Models\Sale;

class SalesController extends Controller
{
    public function index()  { return view('sales.index'); }
    public function create() { return view('sales.create'); }
    public function show(Sale $sale) { return view('sales.show', compact('sale')); }

    public function return(Sale $sale)
    {
        return view('sales.return', compact('sale'));
    }

    public function receipt(Sale $sale)
    {
        $sale->load(['items', 'employee']);

        return view('sales.receipt', compact('sale'));
    }
}