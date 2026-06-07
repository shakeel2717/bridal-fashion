<?php

namespace App\Http\Controllers;

class FeatureTogglesController extends Controller
{
    public function index()
    {
        return view('feature-toggles.index');
    }
}
