<?php

namespace App\Livewire\Reports;

use App\Models\Advance;
use App\Models\Attendance;
use App\Models\Rental;
use App\Models\Sale;
use App\Models\SalaryRecord;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;

class EmployeeReport extends Component
{
    public string $activeFilter     = 'this_month';
    public string $dateFrom         = '';
    public string $dateTo           = '';
    public string $activeReport     = '';
    public string $search           = '';
    public ?int   $selectedEmployee = null;
    public string $lateThreshold    = '09:00';

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo   = now()->format('Y-m-d');
    }

    public function setFilter(string $filter): void
    {
        $this->activeFilter = $filter;
        match ($filter) {
            'today'         => [$this->dateFrom, $this->dateTo] = [now()->format('Y-m-d'), now()->format('Y-m-d')],
            'this_week'     => [$this->dateFrom, $this->dateTo] = [now()->startOfWeek(0)->format('Y-m-d'), now()->endOfWeek(6)->format('Y-m-d')],
            'this_month'    => [$this->dateFrom, $this->dateTo] = [now()->startOfMonth()->format('Y-m-d'), now()->format('Y-m-d')],
            'last_month'    => [$this->dateFrom, $this->dateTo] = [now()->subMonthNoOverflow()->startOfMonth()->format('Y-m-d'), now()->subMonthNoOverflow()->endOfMonth()->format('Y-m-d')],
            'last_3_months' => [$this->dateFrom, $this->dateTo] = [now()->subMonths(3)->startOfMonth()->format('Y-m-d'), now()->format('Y-m-d')],
            'this_year'     => [$this->dateFrom, $this->dateTo] = [now()->startOfYear()->format('Y-m-d'), now()->format('Y-m-d')],
            'last_year'     => [$this->dateFrom, $this->dateTo] = [now()->subYear()->startOfYear()->format('Y-m-d'), now()->subYear()->endOfYear()->format('Y-m-d')],
            'all_time'      => [$this->dateFrom, $this->dateTo] = ['2000-01-01', now()->format('Y-m-d')],
            default         => null,
        };
    }

    public function updatedDateFrom(): void { $this->activeFilter = 'custom'; }
    public function updatedDateTo(): void   { $this->activeFilter = 'custom'; }

    public function selectReport(string $key): void
    {
        $this->activeReport     = $key;
        $this->search           = '';
        $this->selectedEmployee = null;
    }

    public function getReportMenu(): array
    {
        return [
            'attendance' => [
                'label' => 'Attendance',
                'icon'  => 'bi-calendar-check',
                'items' => [
                    'attendance_summary' => ['label' => 'All Employees Summary',  'icon' => 'bi-list-check'],
                    'attendance_detail'  => ['label' => 'Single Employee Detail', 'icon' => 'bi-person-lines-fill'],
                    'attendance_late'    => ['label' => 'Late Arrivals',          'icon' => 'bi-clock-history'],
                ],
            ],
            'business' => [
                'label' => 'Business Activity',
                'icon'  => 'bi-graph-up',
                'items' => [
                    'business_overview' => ['label' => 'All Employees Overview', 'icon' => 'bi-bar-chart'],
                    'business_rentals'  => ['label' => 'Rentals by Employee',    'icon' => 'bi-journal-bookmark'],
                    'business_sales'    => ['label' => 'Sales by Employee',      'icon' => 'bi-bag'],
                ],
            ],
            'hr' => [
                'label' => 'HR',
                'icon'  => 'bi-person-badge',
                'items' => [
                    'hr_advances' => ['label' => 'Advance Records', 'icon' => 'bi-cash'],
                    'hr_salary'   => ['label' => 'Salary Summary',  'icon' => 'bi-wallet2'],
                ],
            ],
        ];
    }

    private function from(): string { return $this->dateFrom ?: '2000-01-01'; }
    private function to(): string   { return $this->dateTo   ?: now()->format('Y-m-d'); }

    // ─────────────────────────────────────────────────────────────────────────
    //  ATTENDANCE REPORTS
    // ─────────────────────────────────────────────────────────────────────────

    private function reportAttendanceSummary(): array
    {
        $from = $this->from();
        $to   = $this->to();
        $late = $this->lateThreshold . ':00';

        $rows = User::where('role', 'employee')
            ->get()
            ->map(function ($emp) use ($from, $to, $late) {
                $records = Attendance::where('user_id', $emp->id)
                    ->whereBetween('date', [$from, $to])
                    ->get();

                $present   = $records->where('status', 'present')->count();
                $absent    = $records->where('status', 'absent')->count();
                $halfDay   = $records->where('status', 'half_day')->count();
                $leave     = $records->where('status', 'leave')->count();
                $lateCount = $records->filter(fn ($r) => $r->time_in && $r->time_in > $late)->count();

                $timesIn = $records->filter(fn ($r) => $r->time_in)->map(function ($r) {
                    [$h, $m] = explode(':', substr($r->time_in, 0, 5));
                    return (int) $h * 60 + (int) $m;
                });

                $avgMin    = $timesIn->count() ? (int) $timesIn->average() : null;
                $avgTimeIn = $avgMin !== null
                    ? sprintf('%02d:%02d', intdiv($avgMin, 60), $avgMin % 60)
                    : '—';

                $total = $present + $absent + $halfDay + $leave;

                return [
                    'name'        => $emp->name,
                    'total'       => $total,
                    'present'     => $present,
                    'absent'      => $absent,
                    'half_day'    => $halfDay,
                    'leave'       => $leave,
                    'late'        => $lateCount,
                    'on_time'     => max(0, $present - $lateCount),
                    'avg_time_in' => $avgTimeIn,
                    'pct'         => $total > 0 ? round(($present + $halfDay * 0.5) / $total * 100) : 0,
                ];
            })
            ->sortByDesc('present')
            ->values();

        return [
            'type'  => 'attendance_summary',
            'title' => 'All Employees Attendance Summary',
            'data'  => $rows,
        ];
    }

    private function reportAttendanceDetail(): array
    {
        if (! $this->selectedEmployee) {
            return [
                'type'  => 'attendance_detail',
                'title' => 'Single Employee Detail',
                'data'  => collect(),
                'empty' => 'Select an employee above to view their attendance detail.',
            ];
        }

        $emp  = User::find($this->selectedEmployee);
        $late = $this->lateThreshold . ':00';

        $records = Attendance::where('user_id', $this->selectedEmployee)
            ->whereBetween('date', [$this->from(), $this->to()])
            ->orderBy('date')
            ->get()
            ->map(function ($r) use ($late) {
                $isLate = $r->time_in && $r->time_in > $late;

                $hours = '—';
                if ($r->time_in && $r->time_out) {
                    [$ih, $im] = explode(':', substr($r->time_in, 0, 5));
                    [$oh, $om] = explode(':', substr($r->time_out, 0, 5));
                    $diff  = ((int) $oh * 60 + (int) $om) - ((int) $ih * 60 + (int) $im);
                    $hours = $diff > 0 ? sprintf('%dh %dm', intdiv($diff, 60), $diff % 60) : '—';
                }

                return [
                    'date'     => Carbon::parse($r->date)->format('d/m/Y'),
                    'day'      => Carbon::parse($r->date)->format('D'),
                    'status'   => $r->status,
                    'time_in'  => $r->time_in  ? substr($r->time_in, 0, 5)  : '—',
                    'time_out' => $r->time_out ? substr($r->time_out, 0, 5) : '—',
                    'hours'    => $hours,
                    'late'     => $isLate,
                    'note'     => $r->note ?? '',
                ];
            });

        return [
            'type'  => 'attendance_detail',
            'title' => ($emp?->name ?? 'Employee') . ' — Attendance Detail',
            'data'  => $records,
        ];
    }

    private function reportAttendanceLate(): array
    {
        $late = $this->lateThreshold . ':00';
        [$lh, $lm]   = explode(':', substr($late, 0, 5));
        $lateMinutes = (int) $lh * 60 + (int) $lm;

        $rows = Attendance::where('status', 'present')
            ->whereBetween('date', [$this->from(), $this->to()])
            ->whereNotNull('time_in')
            ->where('time_in', '>', $late)
            ->with('user')
            ->orderBy('date', 'desc')
            ->get()
            ->when($this->search, fn ($c) => $c->filter(
                fn ($r) => str_contains(strtolower($r->user?->name ?? ''), strtolower($this->search))
            ))
            ->map(function ($r) use ($lateMinutes) {
                [$ih, $im] = explode(':', substr($r->time_in, 0, 5));
                $minLate   = ((int) $ih * 60 + (int) $im) - $lateMinutes;

                return [
                    'name'      => $r->user?->name ?? '—',
                    'date'      => Carbon::parse($r->date)->format('d/m/Y'),
                    'day'       => Carbon::parse($r->date)->format('D'),
                    'time_in'   => substr($r->time_in, 0, 5),
                    'mins_late' => $minLate,
                ];
            })
            ->sortByDesc('mins_late')
            ->values();

        return [
            'type'  => 'attendance_late',
            'title' => 'Late Arrivals Report',
            'data'  => $rows,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  BUSINESS ACTIVITY REPORTS
    // ─────────────────────────────────────────────────────────────────────────

    private function reportBusinessOverview(): array
    {
        $from = $this->from();
        $to   = $this->to();

        $rows = User::where('role', 'employee')
            ->get()
            ->map(function ($emp) use ($from, $to) {
                $rentals = Rental::where('created_by', $emp->id)
                    ->whereBetween('booking_date', [$from, $to])
                    ->whereNotIn('status', ['cancelled', 'abandoned'])
                    ->get();

                $sales = Sale::where('created_by', $emp->id)
                    ->whereBetween('sale_date', [$from, $to])
                    ->whereNotIn('status', ['cancelled'])
                    ->get();

                return [
                    'name'          => $emp->name,
                    'rental_count'  => $rentals->count(),
                    'rental_value'  => $rentals->sum('total_amount'),
                    'sale_count'    => $sales->count(),
                    'sale_value'    => $sales->sum('total_amount'),
                    'total_count'   => $rentals->count() + $sales->count(),
                    'total_revenue' => $rentals->sum('total_amount') + $sales->sum('total_amount'),
                ];
            })
            ->filter(fn ($r) => $r['total_count'] > 0)
            ->sortByDesc('total_revenue')
            ->values();

        return [
            'type'  => 'business_overview',
            'title' => 'All Employees Business Overview',
            'data'  => $rows,
        ];
    }

    private function reportBusinessRentals(): array
    {
        $from = $this->from();
        $to   = $this->to();

        $rows = User::where('role', 'employee')
            ->get()
            ->map(function ($emp) use ($from, $to) {
                $rentals = Rental::where('created_by', $emp->id)
                    ->whereBetween('booking_date', [$from, $to])
                    ->whereNotIn('status', ['cancelled', 'abandoned'])
                    ->get();

                return [
                    'name'  => $emp->name,
                    'count' => $rentals->count(),
                    'total' => $rentals->sum('total_amount'),
                    'avg'   => $rentals->count() ? round($rentals->average('total_amount'), 2) : 0,
                ];
            })
            ->filter(fn ($r) => $r['count'] > 0)
            ->sortByDesc('count')
            ->values();

        return [
            'type'  => 'business_rentals',
            'title' => 'Rentals by Employee',
            'data'  => $rows,
        ];
    }

    private function reportBusinessSales(): array
    {
        $from = $this->from();
        $to   = $this->to();

        $rows = User::where('role', 'employee')
            ->get()
            ->map(function ($emp) use ($from, $to) {
                $sales = Sale::where('created_by', $emp->id)
                    ->whereBetween('sale_date', [$from, $to])
                    ->whereNotIn('status', ['cancelled'])
                    ->get();

                return [
                    'name'  => $emp->name,
                    'count' => $sales->count(),
                    'total' => $sales->sum('total_amount'),
                    'avg'   => $sales->count() ? round($sales->average('total_amount'), 2) : 0,
                ];
            })
            ->filter(fn ($r) => $r['count'] > 0)
            ->sortByDesc('count')
            ->values();

        return [
            'type'  => 'business_sales',
            'title' => 'Sales by Employee',
            'data'  => $rows,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  HR REPORTS
    // ─────────────────────────────────────────────────────────────────────────

    private function reportHrAdvances(): array
    {
        $rows = Advance::with('user')
            ->whereBetween('advance_date', [$this->from(), $this->to()])
            ->when($this->search, fn ($q) => $q->whereHas(
                'user', fn ($u) => $u->where('name', 'like', "%{$this->search}%")
            ))
            ->orderBy('advance_date', 'desc')
            ->get()
            ->map(fn ($a) => [
                'name'     => $a->user?->name ?? '—',
                'date'     => Carbon::parse($a->advance_date)->format('d/m/Y'),
                'amount'   => $a->amount,
                'note'     => $a->note ?? '—',
                'deducted' => $a->is_deducted ? 'Yes' : 'Pending',
            ]);

        return [
            'type'  => 'hr_advances',
            'title' => 'Advance Records',
            'data'  => $rows,
        ];
    }

    private function reportHrSalary(): array
    {
        $rows = SalaryRecord::with('user')
            ->when($this->search, fn ($q) => $q->whereHas(
                'user', fn ($u) => $u->where('name', 'like', "%{$this->search}%")
            ))
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->map(fn ($s) => [
                'name'      => $s->user?->name ?? '—',
                'period'    => Carbon::create($s->year, $s->month, 1)->format('M Y'),
                'days'      => $s->days_present,
                'base'      => $s->base_salary,
                'earned'    => $s->earned_salary,
                'advances'  => $s->total_advances,
                'bonus'     => $s->total_bonus,
                'net'       => $s->net_salary,
                'status'    => $s->status,
                'paid_date' => $s->paid_date ? Carbon::parse($s->paid_date)->format('d/m/Y') : '—',
            ]);

        return [
            'type'  => 'hr_salary',
            'title' => 'Salary Summary',
            'data'  => $rows,
        ];
    }

    public function render()
    {
        $menu      = $this->getReportMenu();
        $report    = null;
        $employees = User::where('role', 'employee')->orderBy('name')->get();

        if ($this->activeReport) {
            $method = 'report' . str()->studly($this->activeReport);
            if (method_exists($this, $method)) {
                $report = $this->$method();
            }
        }

        return view('livewire.reports.employee-report', compact('menu', 'report', 'employees'));
    }
}
