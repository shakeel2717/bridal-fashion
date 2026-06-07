<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Rental;
use App\Models\RentalPayment;
use App\Models\Sale;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today    = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        $stats = [
            'total_customers'  => Customer::where('is_walkin', false)->count(),
            'total_products'   => Product::active()->count(),
            'active_rentals'   => Rental::whereNotIn('status', ['returned', 'cancelled', 'abandoned'])->count(),
            'overdue'          => Rental::whereRaw('DATE(return_date) < ?', [$today->toDateString()])
                                    ->whereNotIn('status', ['returned', 'cancelled', 'abandoned'])->count(),
            'pickup_today'     => Rental::whereRaw('DATE(pickup_date) = ?', [$today->toDateString()])
                                    ->whereNotIn('status', ['returned', 'cancelled', 'abandoned'])->count(),
            'pickup_tomorrow'  => Rental::whereRaw('DATE(pickup_date) = ?', [$tomorrow->toDateString()])
                                    ->whereNotIn('status', ['returned', 'cancelled', 'abandoned'])->count(),
            'return_tomorrow'  => Rental::whereRaw('DATE(return_date) = ?', [$tomorrow->toDateString()])
                                    ->whereNotIn('status', ['returned', 'cancelled', 'abandoned'])->count(),
            'monthly_revenue'  => RentalPayment::whereRaw('DATE(payment_date) >= ?', [$today->startOfMonth()->toDateString()])
                                    ->whereRaw('DATE(payment_date) <= ?', [$today->endOfMonth()->toDateString()])
                                    ->sum('amount')
                                + Sale::whereMonth('sale_date', $today->month)
                                    ->whereYear('sale_date', $today->year)
                                    ->whereNotIn('status', ['cancelled'])
                                    ->sum('advance_paid'),
            'pending_balance'  => Rental::whereNotIn('status', ['returned', 'cancelled', 'abandoned'])
                                    ->sum('remaining_balance'),
            'total_cash'       => Account::where('is_active', true)->sum('current_balance'),
            'total_expenses'   => Expense::whereMonth('expense_date', $today->month)
                                    ->whereYear('expense_date', $today->year)
                                    ->sum('amount'),
            'pending_po'       => PurchaseOrder::whereNotIn('status', ['received', 'cancelled'])
                                    ->sum('balance_due'),
            'total_sales'      => Sale::whereMonth('sale_date', $today->month)
                                    ->whereYear('sale_date', $today->year)
                                    ->whereNotIn('status', ['cancelled'])
                                    ->count(),
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

        // Account balances for dashboard
        $accounts = Account::where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'current_balance']);

        return view('dashboard', compact(
            'stats', 'overdue', 'pickupToday', 'returnTomorrow', 'accounts'
        ));
    }
}