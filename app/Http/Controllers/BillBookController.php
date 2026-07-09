<?php

namespace App\Http\Controllers;

use App\Models\BillBook;

class BillBookController extends Controller
{
    public function index()
    {
        return view('bill-books.index');
    }

    public function phonebook()
    {
        return view('phonebook.index');
    }

    public function show(BillBook $billBook)
    {
        return view('bill-books.show', compact('billBook'));
    }
}