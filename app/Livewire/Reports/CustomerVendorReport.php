<?php

namespace App\Livewire\Reports;

use App\Models\Customer;
use App\Models\PurchaseOrder;
use App\Models\Rental;
use App\Models\Sale;
use App\Models\Vendor;
use Illuminate\Support\Collection;
use Livewire\Component;

class CustomerVendorReport extends Component
{
    public string $activeFilter = 'this_year';

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $activeReport = '';

    public string $search = '';

    public string $sortBy = 'revenue';

    public string $sortDir = 'desc';

    // Modal state
    public ?array $modalRow = null;  // the customer/vendor array being viewed

    public string $modalType = '';    // 'customer' or 'vendor'

    public function mount(): void
    {
        $this->dateFrom = now()->startOfYear()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function getReportMenu(): array
    {
        return [
            'customers' => [
                'label' => 'Customers',
                'icon' => 'bi-people',
                'items' => [
                    'customer_all' => ['label' => 'All Customers',    'icon' => 'bi-list-ul'],
                    'customer_rentals' => ['label' => 'By Rentals',       'icon' => 'bi-journal-bookmark'],
                    'customer_sales' => ['label' => 'By Sales',         'icon' => 'bi-bag'],
                    'customer_baqi' => ['label' => 'With Baqi (Due)',  'icon' => 'bi-exclamation-circle'],
                    'customer_repeat' => ['label' => 'Repeat Customers', 'icon' => 'bi-arrow-repeat'],
                    'customer_address' => ['label' => 'By Area / Chak', 'icon' => 'bi-geo-alt'],
                ],
            ],
            'vendors' => [
                'label' => 'Vendors',
                'icon' => 'bi-shop',
                'items' => [
                    'vendor_all' => ['label' => 'All Vendors',       'icon' => 'bi-list-ul'],
                    'vendor_baqi' => ['label' => 'Vendor Baqi (Due)', 'icon' => 'bi-exclamation-circle'],
                    'vendor_top' => ['label' => 'Top Vendors',       'icon' => 'bi-trophy'],
                ],
            ],
        ];
    }

    private function reportCustomerAddress(): array
    {
        $from = $this->from();
        $to = $this->to();

        $rows = Customer::when($this->search, fn ($q) => $q->where('address', 'like', "%{$this->search}%")
            ->orWhere('name', 'like', "%{$this->search}%")
        )
            ->whereNotNull('address')
            ->where('address', '!=', '')
            ->get()
            ->map(function ($customer) use ($from, $to) {
                $rentals = Rental::where('customer_id', $customer->id)
                    ->whereBetween('booking_date', [$from, $to])
                    ->whereNotIn('status', ['cancelled', 'abandoned'])
                    ->withSum('payments', 'amount')
                    ->get();

                $sales = Sale::where('customer_id', $customer->id)
                    ->whereBetween('sale_date', [$from, $to])
                    ->whereNotIn('status', ['cancelled'])
                    ->get();

                return [
                    'address' => trim($customer->address),
                    'rental_count' => $rentals->count(),
                    'sale_count' => $sales->count(),
                    'total' => $rentals->count() + $sales->count(),
                    'revenue' => $rentals->sum('total_amount') + $sales->sum('total_amount'),
                ];
            })
            ->filter(fn ($r) => $r['total'] > 0)
            // Group by normalised address (lowercase, trimmed)
            ->groupBy(fn ($r) => strtolower(trim($r['address'])))
            ->map(function ($group) {
                return [
                    'area' => $group->first()['address'],
                    'customers' => $group->count(),
                    'rental_count' => $group->sum('rental_count'),
                    'sale_count' => $group->sum('sale_count'),
                    'total' => $group->sum('total'),
                    'revenue' => $group->sum('revenue'),
                ];
            })
            ->sortByDesc('total')
            ->values();

        return ['data' => $rows, 'type' => 'address_wise', 'title' => 'Clients by Area / Chak'];
    }

    public function setFilter(string $filter): void
    {
        $this->activeFilter = $filter;
        match ($filter) {
            'today' => [$this->dateFrom, $this->dateTo] = [now()->format('Y-m-d'), now()->format('Y-m-d')],
            'yesterday' => [$this->dateFrom, $this->dateTo] = [now()->subDay()->format('Y-m-d'), now()->subDay()->format('Y-m-d')],
            'this_week' => [$this->dateFrom, $this->dateTo] = [now()->startOfWeek(0)->format('Y-m-d'), now()->endOfWeek(6)->format('Y-m-d')],
            'last_week' => [$this->dateFrom, $this->dateTo] = [now()->subWeek()->startOfWeek(0)->format('Y-m-d'), now()->subWeek()->endOfWeek(6)->format('Y-m-d')],
            'this_month' => [$this->dateFrom, $this->dateTo] = [now()->startOfMonth()->format('Y-m-d'), now()->format('Y-m-d')],
            'last_month' => [$this->dateFrom, $this->dateTo] = [now()->subMonthNoOverflow()->startOfMonth()->format('Y-m-d'), now()->subMonthNoOverflow()->endOfMonth()->format('Y-m-d')],
            'last_3_months' => [$this->dateFrom, $this->dateTo] = [now()->subMonths(3)->startOfMonth()->format('Y-m-d'), now()->format('Y-m-d')],
            'last_6_months' => [$this->dateFrom, $this->dateTo] = [now()->subMonths(6)->startOfMonth()->format('Y-m-d'), now()->format('Y-m-d')],
            'this_year' => [$this->dateFrom, $this->dateTo] = [now()->startOfYear()->format('Y-m-d'), now()->format('Y-m-d')],
            'last_year' => [$this->dateFrom, $this->dateTo] = [now()->subYear()->startOfYear()->format('Y-m-d'), now()->subYear()->endOfYear()->format('Y-m-d')],
            'all_time' => [$this->dateFrom, $this->dateTo] = ['2000-01-01', now()->format('Y-m-d')],
            default => null,
        };
    }

    public function updatedDateFrom(): void
    {
        $this->activeFilter = 'custom';
    }

    public function updatedDateTo(): void
    {
        $this->activeFilter = 'custom';
    }

    public function selectReport(string $report): void
    {
        $this->activeReport = $report;
        $this->search = '';
        $this->modalRow = null;
    }

    public function openModal(int $index): void
    {
        if (! $this->activeReport) {
            return;
        }

        $method = 'report'.str()->studly($this->activeReport);
        if (! method_exists($this, $method)) {
            return;
        }

        $result = $this->$method();
        $this->modalRow = $result['data']->values()->get($index) ?? null;
        $this->modalType = $result['type'] === 'vendor_table' ? 'vendor' : 'customer';

        $this->dispatch('open-detail-modal');
    }

    public function closeModal(): void
    {
        $this->modalRow = null;
        $this->modalType = '';
    }

    private function from(): string
    {
        return $this->dateFrom ?: '2000-01-01';
    }

    private function to(): string
    {
        return $this->dateTo ?: now()->format('Y-m-d');
    }

    private function customerRows(): Collection
    {
        return Customer::when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
            ->orWhere('phone1', 'like', "%{$this->search}%")
        )
            ->get()
            ->map(function ($customer) {
                $rentals = Rental::where('customer_id', $customer->id)
                    ->whereBetween('booking_date', [$this->from(), $this->to()])
                    ->whereNotIn('status', ['cancelled', 'abandoned'])
                    ->withSum('payments', 'amount')
                    ->orderByDesc('booking_date')
                    ->get();

                $sales = Sale::where('customer_id', $customer->id)
                    ->whereBetween('sale_date', [$this->from(), $this->to()])
                    ->whereNotIn('status', ['cancelled'])
                    ->orderByDesc('sale_date')
                    ->get();

                $rentalTotal = $rentals->sum('total_amount');
                $rentalPaid = $rentals->sum('payments_sum_amount');
                $rentalDue = $rentals->sum(fn ($r) => max(0, $r->total_amount - (float) ($r->payments_sum_amount ?? 0)));
                $saleTotal = $sales->sum('total_amount');
                $salePaid = $sales->sum('advance_paid');
                $saleDue = $sales->sum('remaining_balance');

                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone1,
                    'cnic' => $customer->cnic,
                    'rental_count' => $rentals->count(),
                    'sale_count' => $sales->count(),
                    'revenue' => $rentalTotal + $saleTotal,
                    'paid' => $rentalPaid + $salePaid,
                    'due' => $rentalDue + $saleDue,
                    'last_visit' => collect([$rentals->max('booking_date'), $sales->max('sale_date')])->filter()->max(),
                    'rentals' => $rentals->map(fn ($r) => [
                        'id' => $r->id,
                        'bill_ref' => $r->bill_ref ?? '#'.$r->id,
                        'booking_date' => $r->booking_date,
                        'pickup_date' => $r->pickup_date,
                        'return_date' => $r->return_date,
                        'total_amount' => $r->total_amount,
                        'paid' => (float) ($r->payments_sum_amount ?? 0),
                        'due' => max(0, $r->total_amount - (float) ($r->payments_sum_amount ?? 0)),
                        'status' => $r->status,
                    ])->toArray(),
                    'sales' => $sales->map(fn ($s) => [
                        'id' => $s->id,
                        'bill_ref' => $s->bill_ref ?? '#'.$s->id,
                        'sale_date' => $s->sale_date,
                        'total_amount' => $s->total_amount,
                        'paid' => (float) $s->advance_paid,
                        'due' => (float) $s->remaining_balance,
                        'status' => $s->status,
                    ])->toArray(),
                ];
            });
    }

    private function vendorRows(): Collection
    {
        return Vendor::when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
        )
            ->get()
            ->map(function ($vendor) {
                $pos = PurchaseOrder::where('vendor_id', $vendor->id)
                    ->whereBetween('order_date', [$this->from(), $this->to()])
                    ->whereNotIn('status', ['cancelled'])
                    ->orderByDesc('order_date')
                    ->get();

                return [
                    'id' => $vendor->id,
                    'name' => $vendor->name,
                    'phone' => $vendor->phone ?? '—',
                    'po_count' => $pos->count(),
                    'total' => $pos->sum('total_amount'),
                    'paid' => $pos->sum('amount_paid'),
                    'due' => $pos->sum('balance_due'),
                    'last_order' => $pos->max('order_date'),
                    'pos' => $pos->map(fn ($p) => [
                        'id' => $p->id,
                        'po_number' => $p->po_number ?? '#'.$p->id,
                        'order_date' => $p->order_date,
                        'total_amount' => $p->total_amount,
                        'amount_paid' => $p->amount_paid,
                        'balance_due' => $p->balance_due,
                        'status' => $p->status,
                    ])->toArray(),
                ];
            });
    }

    private function reportCustomerAll()
    {
        $rows = $this->customerRows()->filter(fn ($c) => $c['rental_count'] > 0 || $c['sale_count'] > 0)->sortByDesc('revenue')->values();

        return ['data' => $rows, 'type' => 'customer_table', 'title' => 'All Customers'];
    }

    private function reportCustomerRentals()
    {
        $rows = $this->customerRows()->filter(fn ($c) => $c['rental_count'] > 0)->sortByDesc('rental_count')->values();

        return ['data' => $rows, 'type' => 'customer_table', 'title' => 'Customers by Rentals'];
    }

    private function reportCustomerSales()
    {
        $rows = $this->customerRows()->filter(fn ($c) => $c['sale_count'] > 0)->sortByDesc('sale_count')->values();

        return ['data' => $rows, 'type' => 'customer_table', 'title' => 'Customers by Sales'];
    }

    private function reportCustomerBaqi()
    {
        $rows = $this->customerRows()->filter(fn ($c) => $c['due'] > 0)->sortByDesc('due')->values();

        return ['data' => $rows, 'type' => 'customer_table', 'title' => 'Customers with Baqi (Due)', 'highlight' => 'due'];
    }

    private function reportCustomerRepeat()
    {
        $rows = $this->customerRows()->filter(fn ($c) => ($c['rental_count'] + $c['sale_count']) >= 2)->sortByDesc('revenue')->values();

        return ['data' => $rows, 'type' => 'customer_table', 'title' => 'Repeat Customers'];
    }

    private function reportVendorAll()
    {
        $rows = $this->vendorRows()->filter(fn ($v) => $v['po_count'] > 0)->sortByDesc('total')->values();

        return ['data' => $rows, 'type' => 'vendor_table', 'title' => 'All Vendors'];
    }

    private function reportVendorBaqi()
    {
        $rows = $this->vendorRows()->filter(fn ($v) => $v['due'] > 0)->sortByDesc('due')->values();

        return ['data' => $rows, 'type' => 'vendor_table', 'title' => 'Vendors with Baqi (Due)', 'highlight' => 'due'];
    }

    private function reportVendorTop()
    {
        $rows = $this->vendorRows()->filter(fn ($v) => $v['po_count'] > 0)->sortByDesc('total')->take(20)->values();

        return ['data' => $rows, 'type' => 'vendor_table', 'title' => 'Top Vendors by PO Value'];
    }

    public function render()
    {
        $menu = $this->getReportMenu();
        $report = null;

        if ($this->activeReport) {
            $method = 'report'.str()->studly($this->activeReport);
            if (method_exists($this, $method)) {
                $report = $this->$method();
            }
        }

        return view('livewire.reports.customer-vendor-report', compact('menu', 'report'));
    }
}
