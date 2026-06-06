<?php

namespace App\Http\Controllers;

class VendorsController extends Controller
{
    public function index()
    {
        return view('vendors.index');
    }
}
