<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Rental;
use App\Models\Sale;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today    = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        $stats = [
            'total_customers'  => Customer::count(),
            'total_products'   => Product::active()->count(),
            'active_rentals'   => Rental::whereNotIn('status', ['returned', 'cancelled', 'abandoned'])->count(),
            'overdue'          => Rental::overdue()->count(),
            'pickup_today'     => Rental::pickupToday()->count(),
            'return_tomorrow'  => Rental::where('return_date', $tomorrow->toDateString())
                                    ->whereNotIn('status', ['returned', 'cancelled', 'abandoned'])
                                    ->count(),
            'monthly_revenue'  => Sale::whereMonth('sale_date', $today->month)
                                    ->whereYear('sale_date', $today->year)
                                    ->where('status', 'completed')
                                    ->sum('total_amount')
                                + Rental::whereMonth('booking_date', $today->month)
                                    ->whereYear('booking_date', $today->year)
                                    ->whereNotIn('status', ['cancelled'])
                                    ->sum('advance_paid'),
            'pending_balance'  => Rental::whereNotIn('status', ['returned', 'cancelled', 'abandoned'])
                                    ->sum('remaining_balance'),
        ];

        $overdue       = Rental::overdue()->with('items')->latest('return_date')->take(5)->get();
        $pickupToday   = Rental::pickupToday()->latest()->take(5)->get();
        $returnTomorrow = Rental::where('return_date', $tomorrow->toDateString())
                            ->whereNotIn('status', ['returned', 'cancelled', 'abandoned'])
                            ->latest()->take(5)->get();

        return view('dashboard', compact('stats', 'overdue', 'pickupToday', 'returnTomorrow'));
    }
}