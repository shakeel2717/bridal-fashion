<?php

namespace App\Livewire\Concerns;

use Carbon\Carbon;

/**
 * Shared Day / Week / Month / Year / Custom period filter with
 * < prev | label | next > navigation.
 *
 * Host component only needs to call initPeriod() in mount() and then use
 * periodStart() / periodEnd() when querying.
 */
trait HasPeriodFilter
{
    /** day | week | month | year | custom */
    public string $period = 'day';

    /** Anchor date (Y-m-d) — the period that contains this date is the one shown. */
    public string $anchor = '';

    public string $customFrom = '';

    public string $customTo = '';

    public function initPeriod(string $period = 'day'): void
    {
        $this->period = $period;
        $this->anchor = now()->toDateString();
        $this->customFrom = now()->startOfMonth()->toDateString();
        $this->customTo = now()->toDateString();
    }

    public function setPeriod(string $period): void
    {
        if (! in_array($period, ['day', 'week', 'month', 'year', 'custom'], true)) {
            return;
        }

        $this->period = $period;

        if ($period !== 'custom') {
            $this->anchor = now()->toDateString();
        }

        $this->afterPeriodChange();
    }

    public function periodPrev(): void
    {
        $this->shiftPeriod(-1);
    }

    public function periodNext(): void
    {
        $this->shiftPeriod(1);
    }

    public function periodReset(): void
    {
        $this->anchor = now()->toDateString();

        if ($this->period === 'custom') {
            $this->customFrom = now()->startOfMonth()->toDateString();
            $this->customTo = now()->toDateString();
        }

        $this->afterPeriodChange();
    }

    protected function shiftPeriod(int $direction): void
    {
        if ($this->period === 'custom') {
            // Slide the custom window by its own length.
            $from = Carbon::parse($this->customFrom);
            $to = Carbon::parse($this->customTo);
            $days = (int) floor($from->diffInDays($to)) + 1;

            $this->customFrom = $from->addDays($days * $direction)->toDateString();
            $this->customTo = $to->addDays($days * $direction)->toDateString();
        } else {
            $date = Carbon::parse($this->anchor);

            $shifted = match ($this->period) {
                'day' => $date->addDays($direction),
                'week' => $date->addWeeks($direction),
                'month' => $date->addMonthsNoOverflow($direction),
                'year' => $date->addYears($direction),
                default => $date,
            };

            $this->anchor = $shifted->toDateString();
        }

        $this->afterPeriodChange();
    }

    protected function afterPeriodChange(): void
    {
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    public function updatedCustomFrom(): void
    {
        $this->afterPeriodChange();
    }

    public function updatedCustomTo(): void
    {
        $this->afterPeriodChange();
    }

    public function periodStart(): Carbon
    {
        $date = Carbon::parse($this->anchor ?: now()->toDateString());

        return match ($this->period) {
            'day' => $date->copy()->startOfDay(),
            'week' => $date->copy()->startOfWeek(),
            'month' => $date->copy()->startOfMonth(),
            'year' => $date->copy()->startOfYear(),
            default => Carbon::parse($this->customFrom ?: now()->startOfMonth()->toDateString())->startOfDay(),
        };
    }

    public function periodEnd(): Carbon
    {
        $date = Carbon::parse($this->anchor ?: now()->toDateString());

        return match ($this->period) {
            'day' => $date->copy()->endOfDay(),
            'week' => $date->copy()->endOfWeek(),
            'month' => $date->copy()->endOfMonth(),
            'year' => $date->copy()->endOfYear(),
            default => Carbon::parse($this->customTo ?: now()->toDateString())->endOfDay(),
        };
    }

    /** Y-m-d string — safe for date-column comparisons. */
    public function periodFrom(): string
    {
        return $this->periodStart()->toDateString();
    }

    public function periodTo(): string
    {
        return $this->periodEnd()->toDateString();
    }

    /**
     * Date columns are persisted as "Y-m-d 00:00:00", so a plain BETWEEN on
     * Y-m-d strings silently drops the last day. Always compare date-to-date.
     */
    protected function applyPeriod($query, string $column)
    {
        return $this->applyDateRange($query, $column, $this->periodFrom(), $this->periodTo());
    }

    protected function applyDateRange($query, string $column, string $from, string $to)
    {
        return $query->whereDate($column, '>=', $from)
            ->whereDate($column, '<=', $to);
    }

    /** Human label shown between the < > arrows. */
    public function periodLabel(): string
    {
        $start = $this->periodStart();
        $end = $this->periodEnd();

        return match ($this->period) {
            'day' => $start->isToday()
                ? 'Today'
                : ($start->isYesterday()
                    ? 'Yesterday'
                    : $start->format('D, d M Y')),

            'week' => $this->isCurrentPeriod()
                ? 'This Week'
                : $start->format('d M').' – '.$end->format('d M Y'),

            'month' => $this->isCurrentPeriod()
                ? 'This Month'
                : $start->format('F Y'),

            'year' => $this->isCurrentPeriod()
                ? 'This Year'
                : $start->format('Y'),

            default => $start->format('d M Y').' – '.$end->format('d M Y'),
        };
    }

    /** Secondary line under the label — exact range. */
    public function periodRangeLabel(): string
    {
        $start = $this->periodStart();
        $end = $this->periodEnd();

        if ($start->isSameDay($end)) {
            return $start->format('d M Y');
        }

        return $start->format('d M Y').' — '.$end->format('d M Y');
    }

    public function isCurrentPeriod(): bool
    {
        return now()->between($this->periodStart(), $this->periodEnd());
    }

    /** Don't let the user page into an empty future. */
    public function canGoNext(): bool
    {
        return $this->periodEnd()->lt(now()->endOfDay());
    }

    /** Equivalent range one period back — used for "vs previous" comparisons. */
    public function previousPeriodRange(): array
    {
        $start = $this->periodStart();
        $end = $this->periodEnd();
        $days = (int) floor($start->diffInDays($end)) + 1;

        return match ($this->period) {
            'day' => [$start->copy()->subDay(), $end->copy()->subDay()],
            'week' => [$start->copy()->subWeek(), $end->copy()->subWeek()],
            'month' => [$start->copy()->subMonthNoOverflow(), $start->copy()->subMonthNoOverflow()->endOfMonth()],
            'year' => [$start->copy()->subYear(), $start->copy()->subYear()->endOfYear()],
            default => [$start->copy()->subDays($days), $end->copy()->subDays($days)],
        };
    }
}
