<?php
// app/Http/Controllers/RentalsController.php

namespace App\Http\Controllers;

use App\Models\Rental;

class RentalsController extends Controller
{
    public function index()
    {
        return view('rentals.index');
    }

    public function create()
    {
        return view('rentals.create');
    }

    public function show(Rental $rental)
    {
        return view('rentals.show', compact('rental'));
    }

    public function edit(Rental $rental)
    {
        return view('rentals.edit', compact('rental'));
    }
}