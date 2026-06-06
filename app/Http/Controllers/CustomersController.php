<?php

namespace App\Http\Controllers;

class CustomersController extends Controller
{
    public function index()
    {
        return view('customers.index');
    }
}