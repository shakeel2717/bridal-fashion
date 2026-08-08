<?php

namespace App\Http\Controllers;

class DailySummaryController extends Controller
{
    public function index()
    {
        return view('daily-summary.index');
    }
}
