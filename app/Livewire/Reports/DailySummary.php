<?php

namespace App\Livewire\Reports;

use App\Models\Expense;
use App\Models\Rental;
use App\Models\RentalPayment;
use App\Models\Sale;
use App\Models\Account;
use Carbon\Carbon;
use Livewire\Component;

class DailySummary extends Component
{
    public string $currentDate = '';

    public function mount(): void
    {
        $this->currentDate = now()->toDateString();
    }

    public function prevDay(): void
    {
        $this->currentDate = Carbon::parse($this->currentDate)->subDay()->toDateString();
    }

    public function nextDay(): void
    {
        $next = Carbon::parse($this->currentDate)->addDay();
        if ($next->isAfter(now())) return;
        $this->currentDate = $next->toDateString();
    }

    public function goToday(): void
    {
        $this->currentDate = now()->toDateString();
    }

    public function render()
    {
        $date = $this->currentDate;

        // ── Rentals booked on this day ──
        $rentalsBooked = Rental::with(['items', 'payments'])
            ->whereDate('booking_date', $date)
            ->whereNotIn('status', ['cancelled', 'abandoned'])
            ->get();

        $totalRentalValue   = $rentalsBooked->sum('total_amount');
        $totalRentalAdvance = $rentalsBooked->sum('advance_paid');

        // ── Sales on this day ──
        $sales = Sale::with('items')
            ->whereDate('sale_date', $date)
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->get();

        $totalSaleValue   = $sales->sum('total_amount');
        $totalSaleAdvance = $sales->sum('advance_paid');

        // ── Rental payments received on this day ──
        $rentalPayments = RentalPayment::with('rental:id,customer_name,bill_ref')
            ->whereDate('payment_date', $date)
            ->orderBy('created_at')
            ->get();

        $totalRentalPayments = $rentalPayments->sum('amount');

        // ── Expenses on this day ──
        $expenses = Expense::with('category')
            ->whereDate('expense_date', $date)
            ->orderBy('created_at')
            ->get();

        $totalExpenses = $expenses->sum('amount');

        // ── Summary maths ──
        $totalCashIn  = $totalRentalPayments + $totalSaleAdvance;
        $totalCashOut = $totalExpenses;
        $netCash      = $totalCashIn - $totalCashOut;

        // ── Current account balances ──
        $accounts        = Account::where('is_active', true)->orderBy('name')->get();
        $cashInHand      = $accounts->where('type', 'cash')->sum('current_balance');
        $totalAllAccounts= $accounts->sum('current_balance');

        $isToday   = $date === now()->toDateString();
        $dateLabel = Carbon::parse($date)->format('l, d M Y');

        return view('livewire.reports.daily-summary', compact(
            'rentalsBooked', 'totalRentalValue', 'totalRentalAdvance',
            'sales', 'totalSaleValue', 'totalSaleAdvance',
            'rentalPayments', 'totalRentalPayments',
            'expenses', 'totalExpenses',
            'totalCashIn', 'totalCashOut', 'netCash',
            'accounts', 'cashInHand', 'totalAllAccounts',
            'isToday', 'dateLabel'
        ));
    }
}
