<?php

namespace App\Livewire\Salary;

use App\Models\Account;
use App\Models\Advance;
use App\Models\Attendance;
use App\Models\SalaryRecord;
use App\Models\User;
use App\Services\AccountService;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class SalaryList extends Component
{
    use WithPagination;

    public int $month;

    public string $salaryAccountId = '';

    public int $year;

    public string $filterRole = 'employee';

    public function mount(): void
    {
        $this->month = now()->month;
        $this->year = now()->year;
    }

    public function previousMonth(): void
    {
        $date = Carbon::createFromDate($this->year, $this->month, 1)->subMonth();
        $this->month = $date->month;
        $this->year = $date->year;
    }

    public function nextMonth(): void
    {
        $nowMonth = (int) now()->format('m');
        $nowYear = (int) now()->format('Y');

        if ($this->year === $nowYear && $this->month === $nowMonth) {
            return;
        }

        if ($this->month === 12) {
            $this->month = 1;
            $this->year += 1;
        } else {
            $this->month += 1;
        }
    }

    public function generateSalary(int $userId): void
    {
        $user = User::findOrFail($userId);

        $attendance = Attendance::where('user_id', $userId)
            ->whereYear('date', $this->year)
            ->whereMonth('date', $this->month)
            ->get();

        $daysPresent = $attendance->whereIn('status', ['present'])->count();
        $halfDays = $attendance->where('status', 'half_day')->count();
        $effectiveDays = $daysPresent + ($halfDays * 0.5);

        $baseSalary = $user->salary_amount;
        $earnedSalary = 0;

        if ($user->salary_type === 'monthly') {
            $daysInMonth = Carbon::createFromDate($this->year, $this->month, 1)->daysInMonth;
            $earnedSalary = round(($baseSalary / $daysInMonth) * $effectiveDays, 2);
        } else {
            $earnedSalary = round($baseSalary * $effectiveDays, 2);
        }

        // Get pending advances for this user
        $advances = Advance::where('user_id', $userId)
            ->where('is_deducted', false)
            ->where('advance_date', '<=', Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth())
            ->get();

        $totalAdvances = $advances->sum('amount');
        $netSalary = max(0, $earnedSalary - $totalAdvances);

        $record = SalaryRecord::updateOrCreate(
            [
                'user_id' => $userId,
                'month' => $this->month,
                'year' => $this->year,
            ],
            [
                'base_salary' => $baseSalary,
                'days_present' => $effectiveDays,
                'earned_salary' => $earnedSalary,
                'total_advances' => $totalAdvances,
                'total_bonus' => 0,
                'net_salary' => $netSalary,
                'status' => 'draft',
                'updated_by' => auth()->id(),
                'created_by' => auth()->id(),
            ]
        );

        // Mark advances as deducted
        foreach ($advances as $advance) {
            $advance->update([
                'is_deducted' => true,
                'salary_record_id' => $record->id,
            ]);
        }

        session()->flash('success', "Salary generated for {$user->name}.");
    }

    public function markPaid(int $recordId): void
    {
        $record = SalaryRecord::findOrFail($recordId);
        $user = $record->user;

        $this->validate([
            'salaryAccountId' => 'required|exists:accounts,id',
        ]);

        $record->update([
            'status' => 'paid',
            'paid_date' => now()->toDateString(),
            'updated_by' => auth()->id(),
        ]);

        AccountService::debit(
            (int) $this->salaryAccountId,
            (float) $record->net_salary,
            'salary',
            "Salary paid — {$user->name} ({$record->month}/{$record->year})",
            now()->toDateString(),
            $record,
        );

        $this->salaryAccountId = '';
        session()->flash('success', 'Salary marked as paid.');
    }

    public function render()
    {
        $employees = User::where('is_active', true)
            ->when($this->filterRole, fn ($q) => $q->where('role', $this->filterRole))
            ->orderBy('name')
            ->get();

        $salaryRecords = SalaryRecord::where('month', $this->month)
            ->where('year', $this->year)
            ->get()
            ->keyBy('user_id');

        $nowMonth = (int) now()->format('m');
        $nowYear = (int) now()->format('Y');
        $accounts = Account::where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get(['id', 'name']);

        $canGoNext = ! ($this->year === $nowYear && $this->month === $nowMonth);
        $currentMonth = Carbon::createFromDate($this->year, $this->month, 1);

        return view('livewire.salary.salary-list',
            compact('employees', 'salaryRecords', 'canGoNext', 'currentMonth', 'accounts'));
    }
}
