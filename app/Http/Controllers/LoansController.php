<?php

namespace App\Http\Controllers;

use App\Models\Lender;

class LoansController extends Controller
{
    public function index()
    {
        return view('loans.index');
    }

    public function show(Lender $lender)
    {
        return view('loans.show', compact('lender'));
    }
}
