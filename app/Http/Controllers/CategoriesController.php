<?php

namespace App\Http\Controllers;

class CategoriesController extends Controller
{
    public function index()
    {
        return view('categories.index');
    }
}
