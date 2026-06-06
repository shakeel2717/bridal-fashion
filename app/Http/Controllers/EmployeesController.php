<?php

namespace App\Http\Controllers;

class EmployeesController extends Controller
{
    public function index()
    {
        return view('employees.index');
    }
}
