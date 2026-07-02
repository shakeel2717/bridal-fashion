<?php

namespace App\Livewire\Rentals;

use App\Models\RentalItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;

class RentalCalendar extends Component
{
    public int $year;

    public int $month;

    public string $search = '';

    public ?string $selectedDate = null;

    public function mount(): void
    {
        $this->year = now()->year;
        $this->month = now()->month;
    }

    public function prevMonth(): void
    {
        $d = Carbon::create($this->year, $this->month, 1)->subMonth();
        $this->year = $d->year;
        $this->month = $d->month;
        $this->selectedDate = null;
    }

    public function nextMonth(): void
    {
        $d = Carbon::create($this->year, $this->month, 1)->addMonth();
        $this->year = $d->year;
        $this->month = $d->month;
        $this->selectedDate = null;
    }

    public function selectDate(string $date): void
    {
        $this->selectedDate = $this->selectedDate === $date ? null : $date;
    }

    public function closeModal(): void
    {
        $this->selectedDate = null;
    }

    private function getItems(): Collection
    {
        return RentalItem::with(['rental:id,customer_name,customer_phone1,bill_ref,status,pickup_date,return_date,booking_date'])
            ->whereHas('rental', fn ($q) => $q->whereNotIn('status', ['cancelled', 'abandoned']))
            ->when($this->search, fn ($q) => $q->where('product_code', 'like', "%{$this->search}%"))
            ->whereNotNull('rental_id')
            ->get();
    }

    /**
     * Build a stable color per product_code.
     * Same code always gets same color within a render cycle.
     * If a code is returned on the same date it's also picked up (chained booking),
     * it keeps its assigned color.
     */
    private function buildColorMap(Collection $items): array
    {
        $palette = [
            '#e53e3e', '#dd6b20', '#d69e2e', '#38a169', '#3182ce',
            '#805ad5', '#d53f8c', '#00b5d8', '#e53e3e', '#319795',
            '#f6ad55', '#68d391', '#76e4f7', '#b794f4', '#fbb6ce',
            '#fc8181', '#f6e05e', '#9ae6b4', '#90cdf4', '#c3dafe',
        ];

        $colorMap = [];
        $index = 0;

        foreach ($items->sortBy('product_code')->unique('product_code') as $item) {
            $code = $item->product_code;
            if (! isset($colorMap[$code])) {
                $colorMap[$code] = $palette[$index % count($palette)];
                $index++;
            }
        }

        return $colorMap;
    }

    public function render()
    {
        $start = Carbon::create($this->year, $this->month, 1);
        $end = $start->clone()->endOfMonth();

        $items = $this->getItems();
        $colorMap = $this->buildColorMap($items);

        // Build calendar grid data: date => ['pickups' => [...], 'returns' => [...]]
        $calendarData = [];

        foreach ($items as $item) {
            $pickupDate = $item->rental?->pickup_date
                ? Carbon::parse($item->rental->pickup_date)->toDateString()
                : null;

            $returnDate = $item->rental?->return_date
                ? Carbon::parse($item->rental->return_date)->toDateString()
                : null;

            if (! $pickupDate && ! $returnDate) {
                continue; // skip items with no dates at all
            }

            $code = $item->product_code;
            $color = $colorMap[$code] ?? '#718096';

            if ($pickupDate) {
                $calendarData[$pickupDate]['pickups'][] = [
                    'code' => $code,
                    'name' => $item->product_name,
                    'color' => $color,
                    'customer' => $item->rental?->customer_name,
                    'phone' => $item->rental?->customer_phone1,
                    'bill_ref' => $item->rental?->bill_ref ?? '#'.$item->rental_id,
                    'status' => $item->rental?->status,
                    'rental_id' => $item->rental_id,
                    'return_date' => $returnDate,
                ];
            }

            if ($returnDate && $returnDate !== $pickupDate) {
                $calendarData[$returnDate]['returns'][] = [
                    'code' => $code,
                    'name' => $item->product_name,
                    'color' => $color,
                    'customer' => $item->rental?->customer_name,
                    'phone' => $item->rental?->customer_phone1,
                    'bill_ref' => $item->rental?->bill_ref ?? '#'.$item->rental_id,
                    'status' => $item->rental?->status,
                    'rental_id' => $item->rental_id,
                    'pickup_date' => $pickupDate,
                ];
            }
        }

        // Build weeks array for the calendar grid
        $startOfCalendar = $start->clone()->startOfWeek(Carbon::SUNDAY);
        $endOfCalendar = $end->clone()->endOfWeek(Carbon::SATURDAY);

        $weeks = [];
        $current = $startOfCalendar->clone();

        while ($current->lte($endOfCalendar)) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $dateStr = $current->toDateString();
                $inMonth = $current->month === $this->month;
                $isToday = $current->isToday();
                $dayData = $calendarData[$dateStr] ?? [];

                $week[] = [
                    'date' => $dateStr,
                    'day' => $current->day,
                    'inMonth' => $inMonth,
                    'isToday' => $isToday,
                    'pickups' => $dayData['pickups'] ?? [],
                    'returns' => $dayData['returns'] ?? [],
                ];
                $current->addDay();
            }
            $weeks[] = $week;
        }

        // Modal data for selected date
        $modalData = null;
        if ($this->selectedDate && isset($calendarData[$this->selectedDate])) {
            $modalData = $calendarData[$this->selectedDate];
        }

        // --- Stats for current month ---
        $monthStart = $start->toDateString();
        $monthEnd = $end->toDateString();

        $allMonthItems = RentalItem::with('rental')
            ->whereHas('rental', function ($q) use ($monthStart, $monthEnd) {
                $q->whereNotIn('status', ['cancelled', 'abandoned'])
                    ->where(function ($q) use ($monthStart, $monthEnd) {
                        $q->whereBetween('pickup_date', [$monthStart, $monthEnd])
                            ->orWhereBetween('return_date', [$monthStart, $monthEnd]);
                    });
            })
            ->get();

        $today = now()->toDateString();

        $stats = [
            'total_pickups' => $allMonthItems->filter(fn ($i) => $i->rental?->pickup_date && Carbon::parse($i->rental->pickup_date)->between($start, $end))->count(),
            'total_returns' => $allMonthItems->filter(fn ($i) => $i->rental?->return_date && Carbon::parse($i->rental->return_date)->between($start, $end))->count(),
            'late_pickups' => $allMonthItems->filter(fn ($i) => $i->rental?->pickup_date &&
                Carbon::parse($i->rental->pickup_date)->between($start, $end) &&
                Carbon::parse($i->rental->pickup_date)->lt($today) &&
                in_array($i->rental?->status, ['booked', 'ready'])
            )->count(),
            'late_returns' => $allMonthItems->filter(fn ($i) => $i->rental?->return_date &&
                Carbon::parse($i->rental->return_date)->between($start, $end) &&
                Carbon::parse($i->rental->return_date)->lt($today) &&
                ! in_array($i->rental?->status, ['returned', 'cancelled', 'abandoned'])
            )->count(),
            'picked_up' => $allMonthItems->filter(fn ($i) => $i->rental?->pickup_date &&
                Carbon::parse($i->rental->pickup_date)->between($start, $end) &&
                in_array($i->rental?->status, ['picked_up', 'partially_picked_up', 'returned'])
            )->count(),
            'returned' => $allMonthItems->filter(fn ($i) => $i->rental?->return_date &&
                Carbon::parse($i->rental->return_date)->between($start, $end) &&
                $i->rental?->status === 'returned'
            )->count(),
            'pending_pickup' => $allMonthItems->filter(fn ($i) => $i->rental?->pickup_date &&
                Carbon::parse($i->rental->pickup_date)->between($start, $end) &&
                in_array($i->rental?->status, ['booked', 'ready'])
            )->count(),
            'pending_return' => $allMonthItems->filter(fn ($i) => $i->rental?->return_date &&
                Carbon::parse($i->rental->return_date)->between($start, $end) &&
                in_array($i->rental?->status, ['picked_up', 'partially_picked_up'])
            )->count(),
        ];

        $monthLabel = Carbon::create($this->year, $this->month, 1)->format('F Y');

        return view('livewire.rentals.rental-calendar', compact(
            'weeks', 'monthLabel', 'modalData', 'stats', 'colorMap'
        ));
    }
}
