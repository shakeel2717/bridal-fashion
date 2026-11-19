<?php

namespace App\Livewire\Sales;

use App\Models\SaleItem;
use Carbon\Carbon;
use Livewire\Component;

class SaleCalendar extends Component
{
    public int $year;

    public int $month;

    public string $search = '';

    public bool $showPendingOnly = true;

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

    public function render()
    {
        $start = Carbon::create($this->year, $this->month, 1);
        $end = $start->clone()->endOfMonth();

        // Sales have no return — only the day the customer collects the order matters
        $items = SaleItem::with('sale:id,bill_ref,customer_name,customer_phone1,status')
            ->whereHas('sale', fn ($q) => $q->whereNotIn('status', ['cancelled', 'refunded']))
            ->whereNotNull('pickup_date')
            ->whereBetween('pickup_date', [
                $start->clone()->startOfWeek(Carbon::SUNDAY)->toDateString(),
                $end->clone()->endOfWeek(Carbon::SATURDAY)->toDateString(),
            ])
            ->when($this->search, fn ($q) => $q->where('product_code', 'like', "%{$this->search}%"))
            ->when($this->showPendingOnly, fn ($q) => $q->where('pickup_status', 'pending'))
            ->orderBy('product_code')
            ->get();

        $calendarData = $items->groupBy(fn ($item) => Carbon::parse($item->pickup_date)->toDateString())
            ->map(fn ($dayItems) => $dayItems->map(fn ($item) => [
                'code' => $item->product_code,
                'name' => $item->product_name,
                'qty' => (float) $item->qty,
                'customer' => $item->sale?->customer_name,
                'phone' => $item->sale?->customer_phone1,
                'bill_ref' => $item->sale?->bill_ref ?? '#'.$item->sale_id,
                'sale_id' => $item->sale_id,
                'pickup_status' => $item->pickup_status,
            ])->values()->all());

        // Build weeks array for the calendar grid
        $weeks = [];
        $current = $start->clone()->startOfWeek(Carbon::SUNDAY);
        $endOfCalendar = $end->clone()->endOfWeek(Carbon::SATURDAY);

        while ($current->lte($endOfCalendar)) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $dateStr = $current->toDateString();
                $week[] = [
                    'date' => $dateStr,
                    'day' => $current->day,
                    'inMonth' => $current->month === $this->month,
                    'isToday' => $current->isToday(),
                    'pickups' => $calendarData[$dateStr] ?? [],
                ];
                $current->addDay();
            }
            $weeks[] = $week;
        }

        $modalData = $this->selectedDate ? ($calendarData[$this->selectedDate] ?? null) : null;

        // --- Stats for current month (always counts pending and taken) ---
        $monthItems = SaleItem::whereHas('sale', fn ($q) => $q->whereNotIn('status', ['cancelled', 'refunded']))
            ->whereBetween('pickup_date', [$start->toDateString(), $end->toDateString()])
            ->get(['pickup_date', 'pickup_status']);

        $today = now()->toDateString();

        $stats = [
            'total' => $monthItems->count(),
            'pending' => $monthItems->where('pickup_status', 'pending')->count(),
            'taken' => $monthItems->where('pickup_status', 'taken')->count(),
            'late' => $monthItems->where('pickup_status', 'pending')
                ->filter(fn ($i) => Carbon::parse($i->pickup_date)->toDateString() < $today)->count(),
        ];

        $monthLabel = $start->format('F Y');

        return view('livewire.sales.sale-calendar', compact('weeks', 'monthLabel', 'modalData', 'stats'));
    }
}
