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
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        $stats = [
            'total_customers' => Customer::where('is_walkin', false)->count(),
            'total_products' => Product::active()->count(),
            'active_rentals' => Rental::whereNotIn('status', ['returned', 'cancelled', 'abandoned'])->count(),
            'overdue' => Rental::whereRaw('DATE(return_date) < ?', [$today->toDateString()])
                ->whereNotIn('status', ['returned', 'cancelled', 'abandoned'])->count(),
            'pickup_today' => Rental::whereRaw('DATE(pickup_date) = ?', [$today->toDateString()])
                ->whereNotIn('status', ['returned', 'cancelled', 'abandoned'])->count(),
            'pickup_tomorrow' => Rental::whereRaw('DATE(pickup_date) = ?', [$tomorrow->toDateString()])
                ->whereNotIn('status', ['returned', 'cancelled', 'abandoned'])->count(),
            'return_tomorrow' => Rental::whereRaw('DATE(return_date) = ?', [$tomorrow->toDateString()])
                ->whereNotIn('status', ['returned', 'cancelled', 'abandoned'])->count(),
            'monthly_revenue' => Sale::whereMonth('sale_date', $today->month)
                ->whereYear('sale_date', $today->year)
                ->where('status', 'completed')->sum('total_amount')
                                + Rental::whereMonth('booking_date', $today->month)
                                    ->whereYear('booking_date', $today->year)
                                    ->whereNotIn('status', ['cancelled'])->sum('advance_paid'),
            'pending_balance' => Rental::whereNotIn('status', ['returned', 'cancelled', 'abandoned'])
                ->sum('remaining_balance'),
        ];

        $overdue = Rental::whereRaw('DATE(return_date) < ?', [$today->toDateString()])
            ->whereNotIn('status', ['returned', 'cancelled', 'abandoned'])
            ->with('items')->latest('return_date')->take(5)->get();

        $pickupToday = Rental::whereRaw('DATE(pickup_date) = ?', [$today->toDateString()])
            ->whereNotIn('status', ['returned', 'cancelled', 'abandoned'])
            ->latest()->take(5)->get();

        $returnTomorrow = Rental::whereRaw('DATE(return_date) = ?', [$tomorrow->toDateString()])
            ->whereNotIn('status', ['returned', 'cancelled', 'abandoned'])
            ->latest()->take(5)->get();

        return view('dashboard', compact('stats', 'overdue', 'pickupToday', 'returnTomorrow'));
    }
}
