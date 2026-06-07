<?php

namespace App\Livewire\Reports;

use App\Models\Account;
use App\Models\Advance;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Rental;
use App\Models\RentalPayment;
use App\Models\Sale;
use App\Models\SalaryRecord;
use App\Models\Transaction;
use Carbon\Carbon;
use Livewire\Component;

class ReportsDashboard extends Component
{
    public string $activeTab  = 'overview';
    public int    $month;
    public int    $year;
    public string $dateFrom   = '';
    public string $dateTo     = '';

    public function mount(): void
    {
        $this->month   = now()->month;
        $this->year    = now()->year;
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo   = now()->endOfMonth()->format('Y-m-d');
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function previousMonth(): void
    {
        $date        = Carbon::createFromDate($this->year, $this->month, 1)->subMonth();
        $this->month = $date->month;
        $this->year  = $date->year;
        $this->dateFrom = $date->startOfMonth()->format('Y-m-d');
        $this->dateTo   = $date->endOfMonth()->format('Y-m-d');
    }

    public function nextMonth(): void
    {
        $nowMonth = (int) now()->format('m');
        $nowYear  = (int) now()->format('Y');
        if ($this->year === $nowYear && $this->month === $nowMonth) return;

        if ($this->month === 12) {
            $this->month = 1;
            $this->year += 1;
        } else {
            $this->month += 1;
        }
        $date = Carbon::createFromDate($this->year, $this->month, 1);
        $this->dateFrom = $date->startOfMonth()->format('Y-m-d');
        $this->dateTo   = $date->copy()->endOfMonth()->format('Y-m-d');
    }

    public function render()
    {
        $from = $this->dateFrom;
        $to   = $this->dateTo;

        // ── Overview Stats ────────────────────────────────
        $rentalIncome = RentalPayment::whereRaw('DATE(payment_date) >= ?', [$from])
            ->whereRaw('DATE(payment_date) <= ?', [$to])
            ->sum('amount');

        $saleIncome = Sale::whereRaw('DATE(sale_date) >= ?', [$from])
            ->whereRaw('DATE(sale_date) <= ?', [$to])
            ->whereNotIn('status', ['cancelled'])
            ->sum('advance_paid');

        $totalIncome = $rentalIncome + $saleIncome;

        $totalExpenses = Expense::whereRaw('DATE(expense_date) >= ?', [$from])
            ->whereRaw('DATE(expense_date) <= ?', [$to])
            ->sum('amount');

        $totalSalaries = SalaryRecord::where('month', $this->month)
            ->where('year', $this->year)
            ->where('status', 'paid')
            ->sum('net_salary');

        $pendingRentalBalance = Rental::whereNotIn('status', ['returned', 'cancelled', 'abandoned'])
            ->sum('remaining_balance');

        $netProfit = $totalIncome - $totalExpenses - $totalSalaries;

        // ── Account Balances ──────────────────────────────
        $accounts = Account::where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        $totalAccountBalance = $accounts->sum('current_balance');

        // ── Rental Stats ──────────────────────────────────
        $rentalsThisMonth = Rental::whereRaw('DATE(booking_date) >= ?', [$from])
            ->whereRaw('DATE(booking_date) <= ?', [$to])
            ->count();

        $rentalsReturned = Rental::whereRaw('DATE(booking_date) >= ?', [$from])
            ->whereRaw('DATE(booking_date) <= ?', [$to])
            ->where('status', 'returned')
            ->count();

        $rentalsActive = Rental::whereNotIn('status', ['returned', 'cancelled', 'abandoned'])
            ->count();

        $rentalsOverdue = Rental::whereRaw('DATE(return_date) < ?', [now()->toDateString()])
            ->whereNotIn('status', ['returned', 'cancelled', 'abandoned'])
            ->count();

        // ── Sale Stats ────────────────────────────────────
        $salesThisMonth = Sale::whereRaw('DATE(sale_date) >= ?', [$from])
            ->whereRaw('DATE(sale_date) <= ?', [$to])
            ->whereNotIn('status', ['cancelled'])
            ->count();

        $salesRevenue = Sale::whereRaw('DATE(sale_date) >= ?', [$from])
            ->whereRaw('DATE(sale_date) <= ?', [$to])
            ->whereNotIn('status', ['cancelled'])
            ->sum('total_amount');

        // ── Expense Breakdown ─────────────────────────────
        $expenseBreakdown = Expense::with('category')
            ->whereRaw('DATE(expense_date) >= ?', [$from])
            ->whereRaw('DATE(expense_date) <= ?', [$to])
            ->get()
            ->groupBy('expense_category_id')
            ->map(fn($group) => [
                'name'   => $group->first()->category->name,
                'color'  => $group->first()->category->color,
                'total'  => $group->sum('amount'),
                'count'  => $group->count(),
            ])
            ->sortByDesc('total')
            ->values();

        // ── Recent Transactions ───────────────────────────
        $recentTransactions = Transaction::with(['account', 'createdBy'])
            ->whereRaw('DATE(transaction_date) >= ?', [$from])
            ->whereRaw('DATE(transaction_date) <= ?', [$to])
            ->latest('transaction_date')
            ->take(20)
            ->get();

        // ── Salary Summary ────────────────────────────────
        $salaryRecords = SalaryRecord::with('user')
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->get();

        $pendingAdvances = Advance::where('is_deducted', false)->sum('amount');

        $canGoNext = !($this->year === (int)now()->format('Y')
                    && $this->month === (int)now()->format('m'));

        $currentMonth = Carbon::createFromDate($this->year, $this->month, 1);

        return view('livewire.reports.reports-dashboard', compact(
            'rentalIncome', 'saleIncome', 'totalIncome',
            'totalExpenses', 'totalSalaries', 'netProfit',
            'pendingRentalBalance', 'accounts', 'totalAccountBalance',
            'rentalsThisMonth', 'rentalsReturned', 'rentalsActive', 'rentalsOverdue',
            'salesThisMonth', 'salesRevenue',
            'expenseBreakdown', 'recentTransactions',
            'salaryRecords', 'pendingAdvances',
            'canGoNext', 'currentMonth'
        ));
    }
}