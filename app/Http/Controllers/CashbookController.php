<?php

namespace App\Http\Controllers;

class CashbookController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        return view('cashbook.index');
    }
}
